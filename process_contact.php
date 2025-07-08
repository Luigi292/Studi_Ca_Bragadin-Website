<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Enable error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gmail configuration
define('GMAIL_USER', 'luigimaretto292@gmail.com');
define('GMAIL_PASS', 'bbfd brrw eaem efsq'); // Your Gmail app password

// Anti-spam measures
define('MIN_MESSAGE_LENGTH', 20);
define('MAX_MESSAGE_LENGTH', 2000);
define('TIME_BETWEEN_REQUESTS', 60); // 60 seconds between submissions from same IP

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to configure mailer
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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Basic anti-spam - check submission time
        session_start();
        if (isset($_SESSION['last_submission_time']) && 
            (time() - $_SESSION['last_submission_time']) < TIME_BETWEEN_REQUESTS) {
            throw new Exception('Per favore, attendi qualche istante prima di inviare un\'altra richiesta.');
        }
        
        // Verify required fields
        $required = ['firstName', 'lastName', 'email', 'professional', 'service', 'message'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception('Per favore, compila tutti i campi obbligatori (*)');
            }
        }
        
        // Sanitize inputs
        $firstName = sanitizeInput($_POST['firstName']);
        $lastName = sanitizeInput($_POST['lastName']);
        $email = sanitizeInput($_POST['email']);
        $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
        $professional = sanitizeInput($_POST['professional']);
        $service = sanitizeInput($_POST['service']);
        $message = sanitizeInput($_POST['message']);
        
        // Validate email
        if (!isValidEmail($email)) {
            throw new Exception('Per favore, inserisci un indirizzo email valido');
        }
        
        // Validate message length
        if (strlen($message) < MIN_MESSAGE_LENGTH) {
            throw new Exception('Il messaggio è troppo breve (minimo ' . MIN_MESSAGE_LENGTH . ' caratteri)');
        }
        
        if (strlen($message) > MAX_MESSAGE_LENGTH) {
            throw new Exception('Il messaggio è troppo lungo (massimo ' . MAX_MESSAGE_LENGTH . ' caratteri)');
        }
        
        // Honeypot field (anti-bot)
        if (!empty($_POST['website'])) {
            throw new Exception('Errore di invio');
        }
        
        // Define professional recipients - ONLY these exact emails will be used
        $recipients = [
            'avv_lenzi' => [
                'email' => 'avvocatolenzi@studicabragadin.it', // Only Lenzi gets this
                'name' => 'Avv. Maximiliano Lenzi',
                'confirmation_name' => 'Avv. Maximiliano Lenzi'
            ],
            'dott_maretto' => [
                'email' => 'maretto88@gmail.com', // ONLY this email for Maretto
                'name' => 'Dott. Andrea Maretto',
                'confirmation_name' => 'Dott. Andrea Maretto'
            ],
            'dott_cecolin' => [
                'email' => 'studio@studiocecolin.com', // Only Cecolin gets this
                'name' => 'Dott. Alberto Cecolin',
                'confirmation_name' => 'Dott. Alberto Cecolin'
            ]
        ];
        
        // Validate professional selection
        if (!array_key_exists($professional, $recipients)) {
            throw new Exception('Selezione del professionista non valida');
        }
        
        $recipient = $recipients[$professional];
        
        // Create and configure mailer for professional
        $mail = configureMailer(new PHPMailer(true));
        $mail->setFrom(GMAIL_USER, 'Studio Ca\' Bragadin');
        $mail->addAddress($recipient['email'], $recipient['name']);
        $mail->addReplyTo($email, $firstName . ' ' . $lastName);
        
        // Email content for professional
        $mail->isHTML(true);
        $mail->Subject = 'Nuova richiesta di contatto da ' . $firstName . ' ' . $lastName;
        $mail->Body = "<h2>Nuova richiesta di contatto</h2>
            <p><strong>Nome:</strong> {$firstName} {$lastName}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Telefono:</strong> " . ($phone ?: 'Non fornito') . "</p>
            <p><strong>Servizio richiesto:</strong> {$service}</p>
            <p><strong>Messaggio:</strong></p>
            <p>" . nl2br($message) . "</p>
            <hr><p>Messaggio inviato dal modulo contatti del sito web.</p>";
        
        $mail->AltBody = "Nuova richiesta di contatto\n\n" .
            "Nome: {$firstName} {$lastName}\n" .
            "Email: {$email}\n" .
            "Telefono: " . ($phone ?: 'Non fornito') . "\n" .
            "Servizio: {$service}\n\n" .
            "Messaggio:\n{$message}\n\n" .
            "---\nMessaggio inviato dal modulo contatti del sito web.";
        
        // Send to professional
        $mail->send();
        
        // Create and configure mailer for confirmation
        $confirmationMail = configureMailer(new PHPMailer(true));
        $confirmationMail->setFrom(GMAIL_USER, 'Studio Ca\' Bragadin');
        $confirmationMail->addAddress($email, $firstName . ' ' . $lastName);
        
        // Confirmation email content - shows the correct email address
        $confirmationMail->isHTML(true);
        $confirmationMail->Subject = 'Conferma richiesta - Studio Ca\' Bragadin';
        $confirmationMail->Body = "<h2>Grazie per il tuo messaggio, {$firstName}!</h2>
            <p>La tua richiesta per <strong>{$service}</strong> è stata inviata a:</p>
            <p><strong>{$recipient['confirmation_name']}</strong><br>
            Email: {$recipient['email']}</p>
            
            <h3>Riepilogo:</h3>
            <p><strong>Messaggio:</strong></p>
            <p>" . nl2br($message) . "</p>
            
            <hr>
            <p>Riceverai una risposta al più presto.</p>
            <p><em>Questo è un messaggio automatico, si prega di non rispondere.</em></p>";
        
        $confirmationMail->AltBody = "Grazie per il tuo messaggio, {$firstName}!\n\n" .
            "La tua richiesta per {$service} è stata inviata a:\n" .
            "{$recipient['confirmation_name']}\n" .
            "Email: {$recipient['email']}\n\n" .
            "Riepilogo:\n" .
            "Messaggio:\n{$message}\n\n" .
            "---\n" .
            "Riceverai una risposta al più presto.\n\n" .
            "Questo è un messaggio automatico, si prega di non rispondere.";
        
        // Send confirmation
        $confirmationMail->send();
        
        // Update submission time
        $_SESSION['last_submission_time'] = time();
        
        // Return success - shows the correct email in confirmation
        echo json_encode([
            'success' => true,
            'message' => "Grazie {$firstName} {$lastName}! La tua richiesta è stata inviata a {$recipient['email']}. Ti contatteremo al più presto."
        ]);
        
    } catch (Exception $e) {
        // Return error
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    // Invalid request method
    echo json_encode([
        'success' => false,
        'message' => 'Metodo di richiesta non valido.'
    ]);
}