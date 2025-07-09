<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
define('GMAIL_USER', 'luigimaretto292@gmail.com');
define('GMAIL_PASS', 'pzyc onct pkaw yjej');
define('LOGO_PATH', __DIR__ . '/images/logo.jpg.avif');
define('MIN_MESSAGE_LENGTH', 20);
define('MAX_MESSAGE_LENGTH', 2000);
define('TIME_BETWEEN_REQUESTS', 60);

// Service name mappings
$serviceDisplayNames = [
    'consulenza_legale' => 'Consulenza Legale',
    'contratti' => 'Redazione Contratti',
    'diritto_societario' => 'Diritto Societario',
    'diritto_lavoro' => 'Diritto del Lavoro',
    'tutela_privati' => 'Tutela Privati',
    'altro_legale' => 'Altro',
    'consulenza_fiscale' => 'Consulenza Fiscale',
    'contabilita' => 'Contabilità',
    'bilanci' => 'Redazione Bilanci',
    'fisco_internazionale' => 'Fisco Internazionale',
    'pianificazione_fiscale' => 'Pianificazione Fiscale',
    'altro_commerciale' => 'Altro',
    'consulenza_societaria' => 'Consulenza Societaria',
    'costituzione_societa' => 'Costituzione Società',
    'fisco_societario' => 'Fisco Societario',
    'revisione_contabile' => 'Revisione Contabile',
    'consulenza_strategica' => 'Consulenza Strategica',
    'altro_societario' => 'Altro'
];

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
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
    return $mailer;
}

function getProfessionalEmailTemplate($data) {
    $serviceDisplay = $data['serviceDisplayNames'][$data['service']] ?? $data['service'];
    $messageContent = nl2br(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'));
    $logoSource = file_exists($data['logoPath']) ? 'cid:logo' : '';

    return <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuova Richiesta di Contatto</title>
    <style>
        body {
            font-family: 'Crimson Text', serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: #fff;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 0 25px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
        }
        .email-header {
            background-color: #1a3e72;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 5px solid #d4af37;
        }
        .email-body {
            padding: 35px;
        }
        .email-title {
            color: #1a3e72;
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: 700;
            font-family: 'Raleway', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .email-section {
            margin-bottom: 30px;
        }
        .email-section-title {
            color: #1a3e72;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 8px;
            font-family: 'Raleway', sans-serif;
        }
        .info-row {
            margin-bottom: 12px;
            display: flex;
        }
        .info-label {
            font-weight: 600;
            color: #555;
            width: 180px;
            flex-shrink: 0;
        }
        .info-value {
            color: #333;
        }
        .message-content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            border-left: 4px solid #d4af37;
            font-size: 15px;
            line-height: 1.7;
        }
        .email-footer {
            background-color: #1a3e72;
            padding: 25px;
            text-align: center;
            font-size: 14px;
            color: #fff;
        }
        .highlight {
            color: #1a3e72;
            font-weight: 600;
        }
        .gold {
            color: #d4af37;
        }
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

function getConfirmationEmailTemplate($data) {
    $serviceDisplay = $data['serviceDisplayNames'][$data['service']] ?? $data['service'];
    $messageContent = nl2br(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'));
    $logoSource = file_exists($data['logoPath']) ? 'cid:logo' : '';
    $professionalPhone = $data['professionalPhone'];

    return <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma Richiesta</title>
    <style>
        body {
            font-family: 'Crimson Text', serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: #fff;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 0 25px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
        }
        .email-header {
            background-color: #1a3e72;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 5px solid #d4af37;
        }
        .email-body {
            padding: 35px;
        }
        .email-title {
            color: #1a3e72;
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 700;
            font-family: 'Raleway', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .thank-you {
            font-size: 20px;
            color: #1a3e72;
            margin-bottom: 25px;
            font-style: italic;
        }
        .email-section {
            margin-bottom: 30px;
        }
        .email-section-title {
            color: #1a3e72;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 8px;
            font-family: 'Raleway', sans-serif;
        }
        .info-row {
            margin-bottom: 12px;
            display: flex;
        }
        .info-label {
            font-weight: 600;
            color: #555;
            width: 180px;
            flex-shrink: 0;
        }
        .info-value {
            color: #333;
        }
        .message-content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            border-left: 4px solid #d4af37;
            font-size: 15px;
            line-height: 1.7;
        }
        .professional-info {
            background-color: #f5f8fc;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
        }
        .email-footer {
            background-color: #1a3e72;
            padding: 25px;
            text-align: center;
            font-size: 14px;
            color: #fff;
        }
        .highlight {
            color: #1a3e72;
            font-weight: 600;
        }
        .gold {
            color: #d4af37;
        }
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        session_start();
        if (isset($_SESSION['last_submission_time']) && 
            (time() - $_SESSION['last_submission_time']) < TIME_BETWEEN_REQUESTS) {
            throw new Exception('Per favore, attendi qualche istante prima di inviare un\'altra richiesta.');
        }
        
        $required = ['firstName', 'lastName', 'email', 'professional', 'service', 'message'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception('Per favore, compila tutti i campi obbligatori (*)');
            }
        }
        
        $firstName = sanitizeInput($_POST['firstName']);
        $lastName = sanitizeInput($_POST['lastName']);
        $email = sanitizeInput($_POST['email']);
        $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : 'Non fornito';
        $professional = sanitizeInput($_POST['professional']);
        $service = sanitizeInput($_POST['service']);
        $message = $_POST['message'];
        
        if (!isValidEmail($email)) {
            throw new Exception('Per favore, inserisci un indirizzo email valido');
        }
        
        if (strlen($message) < MIN_MESSAGE_LENGTH) {
            throw new Exception('Il messaggio è troppo breve (minimo ' . MIN_MESSAGE_LENGTH . ' caratteri)');
        }
        
        if (strlen($message) > MAX_MESSAGE_LENGTH) {
            throw new Exception('Il messaggio è troppo lungo (massimo ' . MAX_MESSAGE_LENGTH . ' caratteri)');
        }
        
        if (!empty($_POST['website'])) {
            throw new Exception('Errore di invio');
        }
        
        $recipients = [
            'avv_lenzi' => [
                'email' => 'avvocatolenzi@studicabragadin.it',
                'name' => 'Avv. Maximiliano Lenzi',
                'confirmation_name' => 'Avv. Maximiliano Lenzi',
                'phone' => '049-8751356'
            ],
            'dott_maretto' => [
                'email' => 'maretto88@gmail.com',
                'name' => 'Dott. Andrea Maretto',
                'confirmation_name' => 'Dott. Andrea Maretto',
                'phone' => '049-9562428'
            ],
            'dott_cecolin' => [
                'email' => 'studio@studiocecolin.com',
                'name' => 'Dott. Alberto Cecolin',
                'confirmation_name' => 'Dott. Alberto Cecolin',
                'phone' => '049-7851468'
            ]
        ];
        
        if (!array_key_exists($professional, $recipients)) {
            throw new Exception('Selezione del professionista non valida');
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
        $mail->Subject = 'Nuova richiesta di contatto da ' . $firstName . ' ' . $lastName;
        $mail->Body = getProfessionalEmailTemplate([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'service' => $service,
            'message' => $message,
            'serviceDisplayNames' => $serviceDisplayNames,
            'logoPath' => LOGO_PATH
        ]);
        
        $mail->AltBody = "Nuova richiesta di contatto\n\nNome: {$firstName} {$lastName}\nEmail: {$email}\nTelefono: {$phone}\nServizio: {$serviceDisplayNames[$service]}\n\nMessaggio:\n{$message}";
        $mail->send();
        
        // Send confirmation
        $confirmationMail = configureMailer(new PHPMailer(true));
        $confirmationMail->setFrom(GMAIL_USER, 'Studio Ca\' Bragadin');
        $confirmationMail->addAddress($email, $firstName . ' ' . $lastName);
        
        if (file_exists(LOGO_PATH)) {
            $confirmationMail->addEmbeddedImage(LOGO_PATH, 'logo');
        }
        
        $confirmationMail->isHTML(true);
        $confirmationMail->Subject = 'Conferma richiesta - Studio Ca\' Bragadin';
        $confirmationMail->Body = getConfirmationEmailTemplate([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'service' => $service,
            'message' => $message,
            'professionalName' => $recipient['confirmation_name'],
            'professionalEmail' => $recipient['email'],
            'professionalPhone' => $recipient['phone'],
            'serviceDisplayNames' => $serviceDisplayNames,
            'logoPath' => LOGO_PATH
        ]);
        
        $confirmationMail->AltBody = "Gentile {$firstName},\n\nLa sua richiesta per {$serviceDisplayNames[$service]} è stata inviata a:\n{$recipient['confirmation_name']}\nEmail: {$recipient['email']}\nTelefono: {$recipient['phone']}\n\nMessaggio:\n{$message}\n\nDistinti saluti,\nStudio Ca' Bragadin";
        $confirmationMail->send();
        
        $_SESSION['last_submission_time'] = time();
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => "La sua richiesta è stata inviata correttamente. Riceverà una risposta al più presto. Se la richiesta è urgente o non dovesse ricevere risposta entro 48 ore, la invitiamo a contattare telefonicamente il professionista al numero indicato nella email di conferma."
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metodo di richiesta non valido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}