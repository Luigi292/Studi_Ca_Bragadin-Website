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
define('GMAIL_PASS', 'bbfd brrw eaem efsq'); // Replace with your current app password

// Professional email mapping - HARDCODED to ensure no mistakes
$professionalEmails = [
    'avv_lenzi' => 'avvocatolenzi@studicabragadin.it',
    'dott_maretto' => 'maretto88@gmail.com', // ONLY this address for Maretto
    'dott_cecolin' => 'studio@studiocecolin.com'
];

$professionalNames = [
    'avv_lenzi' => 'Avv. Maximiliano Lenzi',
    'dott_maretto' => 'Dott. Andrea Maretto',
    'dott_cecolin' => 'Dott. Alberto Cecolin'
];

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Required fields validation
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
        $phone = !empty($_POST['phone']) ? sanitizeInput($_POST['phone']) : 'Non fornito';
        $professional = sanitizeInput($_POST['professional']);
        $service = sanitizeInput($_POST['service']);
        $message = sanitizeInput($_POST['message']);

        // Validate professional selection
        if (!array_key_exists($professional, $professionalEmails)) {
            throw new Exception('Selezione del professionista non valida');
        }

        // Get recipient details
        $recipientEmail = $professionalEmails[$professional];
        $recipientName = $professionalNames[$professional];

        // Create PHPMailer instance
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = GMAIL_USER;
        $mail->Password = GMAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        // Email to professional
        $mail->setFrom(GMAIL_USER, 'Studio Ca\' Bragadin');
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->addReplyTo($email, "$firstName $lastName");
        $mail->Subject = "Nuova richiesta da $firstName $lastName";
        
        $mail->isHTML(true);
        $mail->Body = "<h2>Nuova richiesta di contatto</h2>
            <p><strong>Nome:</strong> $firstName $lastName</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Telefono:</strong> $phone</p>
            <p><strong>Servizio:</strong> $service</p>
            <p><strong>Messaggio:</strong></p>
            <p>".nl2br($message)."</p>";
        
        $mail->AltBody = "Nuova richiesta di contatto\n\nNome: $firstName $lastName\nEmail: $email\nTelefono: $phone\nServizio: $service\nMessaggio:\n$message";
        
        $mail->send();

        // Confirmation email to user
        $confirmationMail = new PHPMailer(true);
        $confirmationMail->isSMTP();
        $confirmationMail->Host = 'smtp.gmail.com';
        $confirmationMail->SMTPAuth = true;
        $confirmationMail->Username = GMAIL_USER;
        $confirmationMail->Password = GMAIL_PASS;
        $confirmationMail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $confirmationMail->Port = 465;
        $confirmationMail->CharSet = 'UTF-8';

        $confirmationMail->setFrom(GMAIL_USER, 'Studio Ca\' Bragadin');
        $confirmationMail->addAddress($email, "$firstName $lastName");
        $confirmationMail->Subject = "Conferma richiesta - Studio Ca' Bragadin";
        
        $confirmationMail->isHTML(true);
        $confirmationMail->Body = "<h2>Grazie per il tuo messaggio, $firstName!</h2>
            <p>Abbiamo ricevuto la tua richiesta per <strong>$service</strong> indirizzata a <strong>$recipientName</strong> ($recipientEmail).</p>
            <p>Ti risponderemo al più presto.</p>
            <h3>Riepilogo:</h3>
            <p>".nl2br($message)."</p>
            <hr>
            <p>Questo è un messaggio automatico, non rispondere.</p>";
        
        $confirmationMail->AltBody = "Grazie per il tuo messaggio, $firstName!\n\nAbbiamo ricevuto la tua richiesta per $service indirizzata a $recipientName ($recipientEmail).\n\nRiepilogo:\n$message\n\n---\nQuesto è un messaggio automatico, non rispondere.";
        
        $confirmationMail->send();

        // Success response
        echo json_encode([
            'success' => true,
            'message' => "Grazie $firstName! La tua richiesta è stata inviata a $recipientEmail. Ti abbiamo inviato una email di conferma."
        ]);

    } catch (Exception $e) {
        // Error response
        echo json_encode([
            'success' => false,
            'message' => 'Errore: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Metodo di richiesta non valido'
    ]);
}