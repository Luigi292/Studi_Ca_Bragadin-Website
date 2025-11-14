<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security Configuration
define('GMAIL_USER', 'luigimaretto292@gmail.com');
define('GMAIL_PASS', 'pzyc onct pkaw yjej');
define('LOGO_PATH', __DIR__ . '/images/logo.jpg.avif');
define('MIN_MESSAGE_LENGTH', 20);
define('MAX_MESSAGE_LENGTH', 2000);
define('TIME_BETWEEN_REQUESTS', 60);
define('MAX_ATTEMPTS_PER_HOUR', 10);
define('ALLOWED_SUBMISSION_TIME_MIN', 5);
define('ALLOWED_SUBMISSION_TIME_MAX', 3600);
define('SECURITY_SALT', 'studio-ca-bragadin-padova-2024-security-salt');

// Comprehensive spam words list (Italian and English)
$spamWords = [
    // Pharmaceutical spam
    'viagra', 'cialis', 'levitra', 'propecia', 'xanax', 'valium', 'ambien', 'tramadol',
    'oxycontin', 'vicodin', 'adderall', 'ritalin', 'codeine', 'hydrocodone',
    
    // Adult content
    'porn', 'porno', 'pornography', 'sex', 'sexy', 'adult', 'erotic', 'erotica',
    'escort', 'escorts', 'prostitut', 'prostitution', 'nude', 'naked', 'xxx',
    'fetish', 'bdsm', 'orgasm', 'penis', 'vagina', 'blowjob', 'handjob',
    
    // Casino and gambling
    'casino', 'gambling', 'betting', 'poker', 'roulette', 'slot machine', 'lottery',
    'bet365', 'bets', 'wager', 'jackpot', 'blackjack', 'baccarat',
    
    // Financial scams
    'lottery winner', 'inheritance', 'money transfer', 'wire transfer', 'nigeria',
    'prince', 'billion', 'fortune', 'rich', 'get rich', 'make money',
    'earn money', 'fast cash', 'quick money', 'instant money', 'money making',
    
    // Other spam
    'fake', 'counterfeit', 'hack', 'hacking', 'crack', 'serial key', 'license key', 'activation key',
    
    // Italian spam words
    'viagra', 'cialis', 'levitra', 'sesso', 'porno', 'pornografia', 'adultero',
    'escort', 'prostituta', 'prostituzione', 'casinò', 'giochi d\'azzardo', 'vincita alla lotteria','soldi facili', 'diventa ricco', 'soldi veloci'
];

// Service name mappings - UPDATED WITH CORRECT SERVICES
$serviceDisplayNames = [
    // Avv. Maximiliano Lenzi services
    'consulenza_aziendale' => 'Consulenza Aziendale',
    'contrattualistica_aziendale' => 'Contrattualistica Aziendale',
    'recupero_crediti' => 'Recupero Crediti',
    'ecommerce' => 'E-commerce',
    'contenzioso_civile' => 'Contenzioso Civile',
    'diritto_bancario' => 'Diritto Bancario',
    'crisi_impresa' => 'Crisi d\'Impresa',
    'proprieta_industriale' => 'Proprietà Industriale',
    'diritto_societario' => 'Diritto Societario',
    
    // Dott. Andrea Maretto services
    'contabilita_bilanci' => 'Contabilità e Bilanci',
    'servizi_fiscali' => 'Servizi fiscali',
    'contrattualistica_aziendale_maretto' => 'Contrattualistica aziendale',
    'consulenza_bancaria_finanziaria' => 'Consulenza in materia bancaria e finanziaria',
    'consulenza_controllo_gestione' => 'Consulenza aziendale e controllo di gestione',
    'consulenza_enti_pubblici' => 'Consulenza per Enti Pubblici e Istituzioni',
    
    // Dott. Alberto Cecolin services
    'contabilita_bilanci_cecolin' => 'Contabilità e Bilanci',
    'fiscalita_dichiarazioni' => 'Fiscalità e Dichiarazioni',
    'controllo_gestione' => 'Controllo di Gestione',
    'contrattualistica_impresa' => 'Contrattualistica d\'impresa',
    'operazioni_straordinarie' => 'Operazioni Straordinarie',
    'revisione_governance' => 'Revisione e Governance'
];

// Detect language from form data or referrer
function detectLanguage() {
    if (isset($_POST['language'])) {
        return $_POST['language'];
    }
    
    // Check referrer to determine language
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referrer = $_SERVER['HTTP_REFERER'];
        if (strpos($referrer, '/en/') !== false) {
            return 'en';
        }
    }
    
    return 'it'; // Default to Italian
}

// Response messages for both languages
function getResponseMessages($language) {
    if ($language === 'en') {
        return [
            'success' => 'Your request has been sent successfully. You will receive a response as soon as possible. If the request is urgent or you do not receive a response within 48 hours, we invite you to contact the professional by phone at the number indicated in the confirmation email.',
            'spam' => 'Security error. Please reload the page and try again.',
            'required_fields' => 'Please fill in all required fields (*)',
            'invalid_name' => 'First and last name contain invalid characters.',
            'invalid_email' => 'Please enter a valid and non-temporary email address.',
            'invalid_phone' => 'Invalid phone number.',
            'message_too_short' => 'The message is too short (minimum ' . MIN_MESSAGE_LENGTH . ' characters)',
            'message_too_long' => 'The message is too long (maximum ' . MAX_MESSAGE_LENGTH . ' characters)',
            'spam_content' => 'The message contains prohibited content.',
            'privacy_required' => 'You must accept the privacy policy to proceed.',
            'invalid_professional' => 'Invalid professional selection.',
            'too_many_attempts' => 'Too many submission attempts. Please try again later.',
            'no_data' => 'No data received from the form.',
            'method_error' => 'Invalid request method.'
        ];
    } else {
        return [
            'success' => 'La sua richiesta è stata inviata correttamente. Riceverà una risposta al più presto. Se la richiesta è urgente o non dovesse ricevere risposta entro 48 ore, la invitiamo a contattare telefonicamente il professionista al numero indicato nella email di conferma.',
            'spam' => 'Errore di sicurezza. Ricarica la pagina e riprova.',
            'required_fields' => 'Per favore, compila tutti i campi obbligatori (*)',
            'invalid_name' => 'Nome e cognome contengono caratteri non validi.',
            'invalid_email' => 'Per favore, inserisci un indirizzo email valido e non temporaneo.',
            'invalid_phone' => 'Numero di telefono non valido.',
            'message_too_short' => 'Il messaggio è troppo breve (minimo ' . MIN_MESSAGE_LENGTH . ' caratteri)',
            'message_too_long' => 'Il messaggio è troppo lungo (massimo ' . MAX_MESSAGE_LENGTH . ' caratteri)',
            'spam_content' => 'Il messaggio contiene contenuti non consentiti.',
            'privacy_required' => 'Devi accettare la privacy policy per procedere.',
            'invalid_professional' => 'Selezione del professionista non valida.',
            'too_many_attempts' => 'Troppi tentativi di invio. Riprova più tardi.',
            'no_data' => 'Nessun dato ricevuto dal form.',
            'method_error' => 'Metodo di richiesta non valido.'
        ];
    }
}

// Professional names mapping for both languages
function getProfessionalNames($language) {
    if ($language === 'en') {
        return [
            'avv_lenzi' => 'Lawyer Maximiliano Lenzi',
            'dott_maretto' => 'Dr. Andrea Maretto',
            'dott_cecolin' => 'Dr. Alberto Cecolin'
        ];
    } else {
        return [
            'avv_lenzi' => 'Avv. Maximiliano Lenzi',
            'dott_maretto' => 'Dott. Andrea Maretto',
            'dott_cecolin' => 'Dott. Alberto Cecolin'
        ];
    }
}

// Service names mapping for both languages - UPDATED WITH CORRECT SERVICES
function getServiceNames($language) {
    if ($language === 'en') {
        return [
            // Lawyer Maximiliano Lenzi services
            'consulenza_aziendale' => 'Business Consulting',
            'contrattualistica_aziendale' => 'Corporate Contract Law',
            'recupero_crediti' => 'Credit Recovery',
            'ecommerce' => 'E-commerce',
            'contenzioso_civile' => 'Civil Litigation',
            'diritto_bancario' => 'Banking Law',
            'crisi_impresa' => 'Business Crisis',
            'proprieta_industriale' => 'Industrial Property',
            'diritto_societario' => 'Corporate Law',
            
            // Dr. Andrea Maretto services
            'contabilita_bilanci' => 'Accounting and Financial Statements',
            'servizi_fiscali' => 'Tax Services',
            'contrattualistica_aziendale_maretto' => 'Corporate Contract Law',
            'consulenza_bancaria_finanziaria' => 'Banking and Financial Consulting',
            'consulenza_controllo_gestione' => 'Business Consulting and Management Control',
            'consulenza_enti_pubblici' => 'Consulting for Public Entities and Institutions',
            
            // Dr. Alberto Cecolin services
            'contabilita_bilanci_cecolin' => 'Accounting and Financial Statements',
            'fiscalita_dichiarazioni' => 'Taxation and Declarations',
            'controllo_gestione' => 'Management Control',
            'contrattualistica_impresa' => 'Corporate Contract Law',
            'operazioni_straordinarie' => 'Extraordinary Operations',
            'revisione_governance' => 'Audit and Governance'
        ];
    } else {
        return [
            // Avv. Maximiliano Lenzi services
            'consulenza_aziendale' => 'Consulenza Aziendale',
            'contrattualistica_aziendale' => 'Contrattualistica Aziendale',
            'recupero_crediti' => 'Recupero Crediti',
            'ecommerce' => 'E-commerce',
            'contenzioso_civile' => 'Contenzioso Civile',
            'diritto_bancario' => 'Diritto Bancario',
            'crisi_impresa' => 'Crisi d\'Impresa',
            'proprieta_industriale' => 'Proprietà Industriale',
            'diritto_societario' => 'Diritto Societario',
            
            // Dott. Andrea Maretto services
            'contabilita_bilanci' => 'Contabilità e Bilanci',
            'servizi_fiscali' => 'Servizi fiscali',
            'contrattualistica_aziendale_maretto' => 'Contrattualistica aziendale',
            'consulenza_bancaria_finanziaria' => 'Consulenza in materia bancaria e finanziaria',
            'consulenza_controllo_gestione' => 'Consulenza aziendale e controllo di gestione',
            'consulenza_enti_pubblici' => 'Consulenza per Enti Pubblici e Istituzioni',
            
            // Dott. Alberto Cecolin services
            'contabilita_bilanci_cecolin' => 'Contabilità e Bilanci',
            'fiscalita_dichiarazioni' => 'Fiscalità e Dichiarazioni',
            'controllo_gestione' => 'Controllo di Gestione',
            'contrattualistica_impresa' => 'Contrattualistica d\'impresa',
            'operazioni_straordinarie' => 'Operazioni Straordinarie',
            'revisione_governance' => 'Revisione e Governance'
        ];
    }
}

function generateSecurityToken($data) {
    return hash_hmac('sha256', $data, SECURITY_SALT);
}

function verifySecurityToken($data, $token) {
    if (empty($token)) return false;
    $expected = generateSecurityToken($data);
    return hash_equals($expected, $token);
}

function isHumanSubmission($formData) {
    global $spamWords;
    
    // Multiple honeypot fields - if ANY are filled, it's a bot
    $honeypotFields = ['website', 'company', 'url', 'subject', 'comments'];
    foreach ($honeypotFields as $field) {
        if (!empty($formData[$field])) {
            error_log("Honeypot triggered: $field = " . $formData[$field]);
            return false;
        }
    }
    
    // Time-based validation
    $currentTime = time();
    $formLoadTime = intval($formData['form_load_time'] ?? 0);
    $submissionTime = intval($formData['timestamp'] ?? 0);
    
    if ($formLoadTime === 0 || $submissionTime === 0) {
        error_log("Missing timestamps");
        return false;
    }
    
    // Check if form was filled too quickly (less than 5 seconds)
    $completionTime = $submissionTime - $formLoadTime;
    if ($completionTime < ALLOWED_SUBMISSION_TIME_MIN) {
        error_log("Form filled too quickly: " . $completionTime . " seconds");
        return false;
    }
    
    // Check if form took too long (more than 1 hour)
    if ($completionTime > ALLOWED_SUBMISSION_TIME_MAX) {
        error_log("Form took too long: " . $completionTime . " seconds");
        return false;
    }
    
    // Check for JavaScript validation token
    if (empty($formData['js_validation']) || $formData['js_validation'] !== 'verified') {
        error_log("JavaScript validation failed");
        return false;
    }
    
    return true;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function isValidEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Check for disposable emails
    $disposableDomains = [
        'tempmail.com', 'guerrillamail.com', 'mailinator.com', '10minutemail.com',
        'yopmail.com', 'throwawaymail.com', 'fakeinbox.com', 'trashmail.com',
        'sharklasers.com', 'getairmail.com', 'tmpmail.org', 'fake-mail.com'
    ];
    
    $domain = strtolower(substr(strrchr($email, "@"), 1));
    if (in_array($domain, $disposableDomains)) {
        error_log("Disposable email detected: $email");
        return false;
    }
    
    return true;
}

function validateName($name) {
    // Name should only contain letters, spaces, and basic punctuation
    return preg_match('/^[a-zA-ZÀ-ÿ\s\-\'\.]{2,50}$/u', $name);
}

function validatePhone($phone) {
    if (empty($phone) || $phone === 'Non fornito' || $phone === 'Not provided') {
        return true;
    }
    // Basic international phone validation
    return preg_match('/^[+\-\s\d\(\)]{10,20}$/', $phone);
}

function containsSpamWords($text) {
    global $spamWords;
    
    $text = strtolower($text);
    
    foreach ($spamWords as $word) {
        // Check for exact word matches with word boundaries
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $text)) {
            error_log("Spam word detected: $word in message: $text");
            return true;
        }
    }
    
    // Check for URLs (but allow common domains for professional use)
    $allowedDomains = ['studicabragadin.it', 'gmail.com', 'yahoo.com', 'hotmail.com', 'libero.it', 'tim.it', 'virgilio.it'];
    $urlPattern = '/http[s]?:\/\/(?!' . implode('|', array_map('preg_quote', $allowedDomains)) . ')[^\s]+/i';
    if (preg_match($urlPattern, $text)) {
        error_log("Suspicious URL detected in message");
        return true;
    }
    
    // Check for excessive special characters
    if (preg_match('/[^\p{L}\p{N}\s\.\,\!\?\-\'\@]{5,}/u', $text)) {
        error_log("Excessive special characters detected");
        return true;
    }
    
    return false;
}

function configureMailer(PHPMailer $mailer) {
    $mailer->isSMTP();
    $mailer->Host = 'smtp.gmail.com';
    $mailer->SMTPAuth = true;
    $mailer->Username = GMAIL_USER;
    $mailer->Password = GMAIL_PASS;
    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mailer->Port = 465;
    $mailer->CharSet = 'UTF-8';
    $mailer->SMTPDebug = 0;
    $mailer->Timeout = 30;
    return $mailer;
}

function getProfessionalEmailTemplate($data, $language) {
    $serviceDisplay = $data['serviceDisplayNames'][$data['service']] ?? $data['service'];
    $messageContent = nl2br(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'));
    $logoSource = file_exists($data['logoPath']) ? 'cid:logo' : '';

    if ($language === 'en') {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Request</title>
    <style>
        body { font-family: 'Crimson Text', serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f8f8; }
        .email-container { max-width: 650px; margin: 0 auto; background: #fff; border-radius: 4px; overflow: hidden; box-shadow: 0 0 25px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
        .email-header { background-color: #1a3e72; padding: 30px 20px; text-align: center; border-bottom: 5px solid #d4af37; }
        .email-body { padding: 35px; }
        .email-title { color: #1a3e72; font-size: 24px; margin-top: 0; margin-bottom: 25px; font-weight: 700; font-family: 'Raleway', sans-serif; text-transform: uppercase; letter-spacing: 1px; }
        .email-section { margin-bottom: 30px; }
        .email-section-title { color: #1a3e72; font-size: 18px; margin-bottom: 15px; font-weight: 600; border-bottom: 2px solid #f0f0f0; padding-bottom: 8px; font-family: 'Raleway', sans-serif; }
        .info-row { margin-bottom: 12px; display: flex; }
        .info-label { font-weight: 600; color: #555; width: 180px; flex-shrink: 0; }
        .info-value { color: #333; }
        .message-content { background-color: #f9f9f9; padding: 20px; border-radius: 4px; border-left: 4px solid #d4af37; font-size: 15px; line-height: 1.7; }
        .email-footer { background-color: #1a3e72; padding: 25px; text-align: center; font-size: 14px; color: #fff; }
        .highlight { color: #1a3e72; font-weight: 600; }
        .gold { color: #d4af37; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="$logoSource" alt="Studio Ca' Bragadin" style="max-width: 200px;">
        </div>
        <div class="email-body">
            <h1 class="email-title">New Contact Request</h1>
            <div class="email-section">
                <h2 class="email-section-title">Client Information</h2>
                <div class="info-row">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value highlight">{$data['firstName']} {$data['lastName']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{$data['email']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{$data['phone']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Service:</span>
                    <span class="info-value highlight">$serviceDisplay</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Privacy accepted:</span>
                    <span class="info-value">{$data['privacyConsent']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Language:</span>
                    <span class="info-value">English</span>
                </div>
            </div>
            <div class="email-section">
                <h2 class="email-section-title">Message</h2>
                <div class="message-content">
                    $messageContent
                </div>
            </div>
        </div>
        <div class="email-footer">
            <p style="margin: 0;">Message automatically sent from the contact form</p>
            <p style="margin: 5px 0 0; font-size: 13px;" class="gold">Studio Ca' Bragadin</p>
        </div>
    </div>
</body>
</html>
HTML;
    } else {
        return <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuova Richiesta di Contatto</title>
    <style>
        body { font-family: 'Crimson Text', serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f8f8; }
        .email-container { max-width: 650px; margin: 0 auto; background: #fff; border-radius: 4px; overflow: hidden; box-shadow: 0 0 25px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
        .email-header { background-color: #1a3e72; padding: 30px 20px; text-align: center; border-bottom: 5px solid #d4af37; }
        .email-body { padding: 35px; }
        .email-title { color: #1a3e72; font-size: 24px; margin-top: 0; margin-bottom: 25px; font-weight: 700; font-family: 'Raleway', sans-serif; text-transform: uppercase; letter-spacing: 1px; }
        .email-section { margin-bottom: 30px; }
        .email-section-title { color: #1a3e72; font-size: 18px; margin-bottom: 15px; font-weight: 600; border-bottom: 2px solid #f0f0f0; padding-bottom: 8px; font-family: 'Raleway', sans-serif; }
        .info-row { margin-bottom: 12px; display: flex; }
        .info-label { font-weight: 600; color: #555; width: 180px; flex-shrink: 0; }
        .info-value { color: #333; }
        .message-content { background-color: #f9f9f9; padding: 20px; border-radius: 4px; border-left: 4px solid #d4af37; font-size: 15px; line-height: 1.7; }
        .email-footer { background-color: #1a3e72; padding: 25px; text-align: center; font-size: 14px; color: #fff; }
        .highlight { color: #1a3e72; font-weight: 600; }
        .gold { color: #d4af37; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="$logoSource" alt="Studio Ca' Bragadin" style="max-width: 200px;">
        </div>
        <div class="email-body">
            <h1 class="email-title">Nuova Richiesta di Contatto</h1>
            <div class="email-section">
                <h2 class="email-section-title">Informazioni Cliente</h2>
                <div class="info-row">
                    <span class="info-label">Nome e Cognome:</span>
                    <span class="info-value highlight">{$data['firstName']} {$data['lastName']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{$data['email']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Telefono:</span>
                    <span class="info-value">{$data['phone']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Servizio:</span>
                    <span class="info-value highlight">$serviceDisplay</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Privacy accettata:</span>
                    <span class="info-value">{$data['privacyConsent']}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Lingua:</span>
                    <span class="info-value">Italiano</span>
                </div>
            </div>
            <div class="email-section">
                <h2 class="email-section-title">Messaggio</h2>
                <div class="message-content">
                    $messageContent
                </div>
            </div>
        </div>
        <div class="email-footer">
            <p style="margin: 0;">Messaggio inviato automaticamente dal modulo contatti</p>
            <p style="margin: 5px 0 0; font-size: 13px;" class="gold">Studio Ca' Bragadin</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}

function getConfirmationEmailTemplate($data, $language) {
    $serviceDisplay = $data['serviceDisplayNames'][$data['service']] ?? $data['service'];
    $messageContent = nl2br(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'));
    $logoSource = file_exists($data['logoPath']) ? 'cid:logo' : '';
    $professionalPhone = $data['professionalPhone'];

    if ($language === 'en') {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Confirmation</title>
    <style>
        body { font-family: 'Crimson Text', serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f8f8; }
        .email-container { max-width: 650px; margin: 0 auto; background: #fff; border-radius: 4px; overflow: hidden; box-shadow: 0 0 25px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
        .email-header { background-color: #1a3e72; padding: 30px 20px; text-align: center; border-bottom: 5px solid #d4af37; }
        .email-body { padding: 35px; }
        .email-title { color: #1a3e72; font-size: 24px; margin-top: 0; margin-bottom: 20px; font-weight: 700; font-family: 'Raleway', sans-serif; text-transform: uppercase; letter-spacing: 1px; }
        .thank-you { font-size: 20px; color: #1a3e72; margin-bottom: 25px; font-style: italic; }
        .email-section { margin-bottom: 30px; }
        .email-section-title { color: #1a3e72; font-size: 18px; margin-bottom: 15px; font-weight: 600; border-bottom: 2px solid #f0f0f0; padding-bottom: 8px; font-family: 'Raleway', sans-serif; }
        .info-row { margin-bottom: 12px; display: flex; }
        .info-label { font-weight: 600; color: #555; width: 180px; flex-shrink: 0; }
        .info-value { color: #333; }
        .message-content { background-color: #f9f9f9; padding: 20px; border-radius: 4px; border-left: 4px solid #d4af37; font-size: 15px; line-height: 1.7; }
        .professional-info { background-color: #f5f8fc; padding: 20px; border-radius: 4px; margin-bottom: 25px; border: 1px solid #e0e0e0; }
        .email-footer { background-color: #1a3e72; padding: 25px; text-align: center; font-size: 14px; color: #fff; }
        .highlight { color: #1a3e72; font-weight: 600; }
        .gold { color: #d4af37; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="$logoSource" alt="Studio Ca' Bragadin" style="max-width: 200px;">
        </div>
        <div class="email-body">
            <h1 class="email-title">Request Confirmation</h1>
            <div class="thank-you">Dear {$data['firstName']}, thank you for contacting us</div>
            <div class="professional-info">
                <p style="margin-top: 0;">Your request for <span class="highlight">$serviceDisplay</span> has been sent to:</p>
                <p style="margin-bottom: 5px;"><span class="highlight">{$data['professionalName']}</span></p>
                <p style="margin-bottom: 5px;">Email: <a href="mailto:{$data['professionalEmail']}" style="color: #1a3e72;">{$data['professionalEmail']}</a></p>
                <p style="margin-bottom: 0;">Phone: {$data['professionalPhone']}</p>
            </div>
            <div class="email-section">
                <h2 class="email-section-title">Summary of your request</h2>
                <div class="info-row">
                    <span class="info-label">Service:</span>
                    <span class="info-value highlight">$serviceDisplay</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Privacy accepted:</span>
                    <span class="info-value">{$data['privacyConsent']}</span>
                </div>
            </div>
            <div class="email-section">
                <h2 class="email-section-title">Your message</h2>
                <div class="message-content">
                    $messageContent
                </div>
            </div>
            <div class="email-section">
                <p>Your request has been sent successfully. You will receive a response as soon as possible.</p>
                <p>If the request is urgent or you do not receive a response within 48 hours, we invite you to contact the professional by phone at the number indicated above.</p>
                <p>Best regards,</p>
                <p class="highlight">The Studio Ca' Bragadin team</p>
            </div>
        </div>
        <div class="email-footer">
            <p style="margin: 0;">This is an automatic confirmation message sent by</p>
            <p style="margin: 5px 0 0; font-size: 13px;" class="gold">Studio Ca' Bragadin</p>
        </div>
    </div>
</body>
</html>
HTML;
    } else {
        return <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma Richiesta</title>
    <style>
        body { font-family: 'Crimson Text', serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f8f8; }
        .email-container { max-width: 650px; margin: 0 auto; background: #fff; border-radius: 4px; overflow: hidden; box-shadow: 0 0 25px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
        .email-header { background-color: #1a3e72; padding: 30px 20px; text-align: center; border-bottom: 5px solid #d4af37; }
        .email-body { padding: 35px; }
        .email-title { color: #1a3e72; font-size: 24px; margin-top: 0; margin-bottom: 20px; font-weight: 700; font-family: 'Raleway', sans-serif; text-transform: uppercase; letter-spacing: 1px; }
        .thank-you { font-size: 20px; color: #1a3e72; margin-bottom: 25px; font-style: italic; }
        .email-section { margin-bottom: 30px; }
        .email-section-title { color: #1a3e72; font-size: 18px; margin-bottom: 15px; font-weight: 600; border-bottom: 2px solid #f0f0f0; padding-bottom: 8px; font-family: 'Raleway', sans-serif; }
        .info-row { margin-bottom: 12px; display: flex; }
        .info-label { font-weight: 600; color: #555; width: 180px; flex-shrink: 0; }
        .info-value { color: #333; }
        .message-content { background-color: #f9f9f9; padding: 20px; border-radius: 4px; border-left: 4px solid #d4af37; font-size: 15px; line-height: 1.7; }
        .professional-info { background-color: #f5f8fc; padding: 20px; border-radius: 4px; margin-bottom: 25px; border: 1px solid #e0e0e0; }
        .email-footer { background-color: #1a3e72; padding: 25px; text-align: center; font-size: 14px; color: #fff; }
        .highlight { color: #1a3e72; font-weight: 600; }
        .gold { color: #d4af37; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="$logoSource" alt="Studio Ca' Bragadin" style="max-width: 200px;">
        </div>
        <div class="email-body">
            <h1 class="email-title">Conferma Richiesta</h1>
            <div class="thank-you">Gentile {$data['firstName']}, grazie per averci contattato</div>
            <div class="professional-info">
                <p style="margin-top: 0;">La sua richiesta per <span class="highlight">$serviceDisplay</span> è stata inviata a:</p>
                <p style="margin-bottom: 5px;"><span class="highlight">{$data['professionalName']}</span></p>
                <p style="margin-bottom: 5px;">Email: <a href="mailto:{$data['professionalEmail']}" style="color: #1a3e72;">{$data['professionalEmail']}</a></p>
                <p style="margin-bottom: 0;">Telefono: {$data['professionalPhone']}</p>
            </div>
            <div class="email-section">
                <h2 class="email-section-title">Riepilogo della sua richiesta</h2>
                <div class="info-row">
                    <span class="info-label">Servizio:</span>
                    <span class="info-value highlight">$serviceDisplay</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Privacy accettata:</span>
                    <span class="info-value">{$data['privacyConsent']}</span>
                </div>
            </div>
            <div class="email-section">
                <h2 class="email-section-title">Il suo messaggio</h2>
                <div class="message-content">
                    $messageContent
                </div>
            </div>
            <div class="email-section">
                <p>La sua richiesta è stata inviata correttamente. Riceverà una risposta al più presto.</p>
                <p>Se la richiesta è urgente o non dovesse ricevere risposta entro 48 ore, la invitiamo a contattare telefonicamente il professionista al numero indicato sopra.</p>
                <p>Distinti saluti,</p>
                <p class="highlight">Lo staff di Studio Ca' Bragadin</p>
            </div>
        </div>
        <div class="email-footer">
            <p style="margin: 0;">Questo è un messaggio automatico di conferma inviato da</p>
            <p style="margin: 5px 0 0; font-size: 13px;" class="gold">Studio Ca' Bragadin</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}

// Set JSON header first
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        session_start();
        
        // Detect language
        $language = detectLanguage();
        $messages = getResponseMessages($language);
        $professionalNames = getProfessionalNames($language);
        $serviceNames = getServiceNames($language);
        
        // Initialize session security tracking
        if (!isset($_SESSION['security_data'])) {
            $_SESSION['security_data'] = [
                'submission_attempts' => 0,
                'first_attempt_time' => time(),
                'last_submission_time' => 0
            ];
        }
        
        $security = &$_SESSION['security_data'];
        
        // Rate limiting check
        $currentTime = time();
        if ($security['submission_attempts'] >= MAX_ATTEMPTS_PER_HOUR) {
            if (($currentTime - $security['first_attempt_time']) < 3600) {
                throw new Exception($messages['too_many_attempts']);
            } else {
                $security['submission_attempts'] = 0;
                $security['first_attempt_time'] = $currentTime;
            }
        }
        
        // Check for empty POST data
        if (empty($_POST)) {
            throw new Exception($messages['no_data']);
        }
        
        // Advanced bot detection
        if (!isHumanSubmission($_POST)) {
            $security['submission_attempts']++;
            throw new Exception($messages['spam']);
        }
        
        // Verify security token (relaxed for testing)
        $expectedTokenData = ($_POST['form_load_time'] ?? '');
        $securityToken = $_POST['security_token'] ?? '';
        
        if (!verifySecurityToken($expectedTokenData, $securityToken)) {
            // Log but don't block for now during testing
            error_log("Security token verification failed, but allowing submission for testing");
        }
        
        // Required fields validation
        $required = ['firstName', 'lastName', 'email', 'professional', 'service', 'message', 'privacyConsent'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception($messages['required_fields']);
            }
        }
        
        // Sanitize inputs
        $firstName = sanitizeInput($_POST['firstName']);
        $lastName = sanitizeInput($_POST['lastName']);
        $email = sanitizeInput($_POST['email']);
        $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : ($language === 'en' ? 'Not provided' : 'Non fornito');
        $professional = sanitizeInput($_POST['professional']);
        $service = sanitizeInput($_POST['service']);
        $message = $_POST['message'];
        $privacyConsent = isset($_POST['privacyConsent']) ? ($language === 'en' ? 'Yes' : 'Sì') : ($language === 'en' ? 'No' : 'No');
        
        // Advanced validation
        if (!validateName($firstName) || !validateName($lastName)) {
            throw new Exception($messages['invalid_name']);
        }
        
        if (!isValidEmail($email)) {
            throw new Exception($messages['invalid_email']);
        }
        
        if (!validatePhone($phone)) {
            throw new Exception($messages['invalid_phone']);
        }
        
        if (strlen($message) < MIN_MESSAGE_LENGTH) {
            throw new Exception($messages['message_too_short']);
        }
        
        if (strlen($message) > MAX_MESSAGE_LENGTH) {
            throw new Exception($messages['message_too_long']);
        }
        
        // Spam content detection
        if (containsSpamWords($message) || containsSpamWords($firstName . ' ' . $lastName)) {
            throw new Exception($messages['spam_content']);
        }
        
        if ($privacyConsent === ($language === 'en' ? 'No' : 'No')) {
            throw new Exception($messages['privacy_required']);
        }
        
        // Professional validation
        $recipients = [
            'avv_lenzi' => [
                'email' => 'avvocatolenzi@studicabragadin.it',
                'name' => $professionalNames['avv_lenzi'],
                'confirmation_name' => $professionalNames['avv_lenzi'],
                'phone' => '049-8751356'
            ],
            'dott_maretto' => [
                'email' => 'andrea.maretto@studicabragadin.it',
                'name' => $professionalNames['dott_maretto'],
                'confirmation_name' => $professionalNames['dott_maretto'],
                'phone' => '049-9562428'
            ],
            'dott_cecolin' => [
                'email' => 'studio@studiocecolin.com',
                'name' => $professionalNames['dott_cecolin'],
                'confirmation_name' => $professionalNames['dott_cecolin'],
                'phone' => '049-7851468'
            ]
        ];
        
        if (!array_key_exists($professional, $recipients)) {
            throw new Exception($messages['invalid_professional']);
        }
        
        $recipient = $recipients[$professional];
        
        // Send to professional
        $mail = configureMailer(new PHPMailer(true));
        $mail->setFrom(GMAIL_USER, 'Studio Ca\' Bragadin');
        $mail->addAddress($recipient['email'], $recipient['name']);
        $mail->addReplyTo($email, $firstName . ' ' . $lastName);
        
        if (file_exists(LOGO_PATH)) {
            $mail->addEmbeddedImage(LOGO_PATH, 'logo');
        }
        
        $mail->isHTML(true);
        $mail->Subject = ($language === 'en' ? 'New contact request from ' : 'Nuova richiesta di contatto da ') . $firstName . ' ' . $lastName;
        $mail->Body = getProfessionalEmailTemplate([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'service' => $service,
            'message' => $message,
            'privacyConsent' => $privacyConsent,
            'serviceDisplayNames' => $serviceNames,
            'logoPath' => LOGO_PATH
        ], $language);
        
        $mail->AltBody = ($language === 'en' ? "New contact request" : "Nuova richiesta di contatto") . "\n\n" . 
                        ($language === 'en' ? "Name" : "Nome") . ": {$firstName} {$lastName}\n" . 
                        ($language === 'en' ? "Email" : "Email") . ": {$email}\n" . 
                        ($language === 'en' ? "Phone" : "Telefono") . ": {$phone}\n" . 
                        ($language === 'en' ? "Service" : "Servizio") . ": {$serviceNames[$service]}\n" . 
                        ($language === 'en' ? "Privacy accepted" : "Privacy accettata") . ": {$privacyConsent}\n\n" . 
                        ($language === 'en' ? "Message" : "Messaggio") . ":\n{$message}";
        $mail->send();
        
        // Send confirmation
        $confirmationMail = configureMailer(new PHPMailer(true));
        $confirmationMail->setFrom(GMAIL_USER, 'Studio Ca\' Bragadin');
        $confirmationMail->addAddress($email, $firstName . ' ' . $lastName);
        
        if (file_exists(LOGO_PATH)) {
            $confirmationMail->addEmbeddedImage(LOGO_PATH, 'logo');
        }
        
        $confirmationMail->isHTML(true);
        $confirmationMail->Subject = ($language === 'en' ? 'Request confirmation - Studio Ca\' Bragadin' : 'Conferma richiesta - Studio Ca\' Bragadin');
        $confirmationMail->Body = getConfirmationEmailTemplate([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'service' => $service,
            'message' => $message,
            'privacyConsent' => $privacyConsent,
            'professionalName' => $recipient['confirmation_name'],
            'professionalEmail' => $recipient['email'],
            'professionalPhone' => $recipient['phone'],
            'serviceDisplayNames' => $serviceNames,
            'logoPath' => LOGO_PATH
        ], $language);
        
        $confirmationMail->AltBody = ($language === 'en' ? "Dear {$firstName}," : "Gentile {$firstName},") . "\n\n" . 
                                    ($language === 'en' ? "Your request for {$serviceNames[$service]} has been sent to:" : "La sua richiesta per {$serviceNames[$service]} è stata inviata a:") . "\n" . 
                                    "{$recipient['confirmation_name']}\n" . 
                                    ($language === 'en' ? "Email" : "Email") . ": {$recipient['email']}\n" . 
                                    ($language === 'en' ? "Phone" : "Telefono") . ": {$recipient['phone']}\n\n" . 
                                    ($language === 'en' ? "Message" : "Messaggio") . ":\n{$message}\n\n" . 
                                    ($language === 'en' ? "Best regards," : "Distinti saluti,") . "\n" . 
                                    ($language === 'en' ? "Studio Ca' Bragadin" : "Studio Ca' Bragadin");
        $confirmationMail->send();
        
        // Update security data
        $security['last_submission_time'] = time();
        $security['submission_attempts']++;
        
        echo json_encode([
            'success' => true,
            'message' => $messages['success']
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    $language = detectLanguage();
    $messages = getResponseMessages($language);
    
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => $messages['method_error']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>