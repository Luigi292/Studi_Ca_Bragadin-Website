<?php
// Set content type to JSON
header('Content-Type: application/json');

// Configuration
$admin_email = "maretto88@gmail.com";
$subscribers_file = $_SERVER['DOCUMENT_ROOT'] . "/../private/subscribers.txt"; // Stored outside web root
$log_file = $_SERVER['DOCUMENT_ROOT'] . "/../private/newsletter_log.txt"; // Stored outside web root
$website_url = "https://www.studicabragadin.it";
$studio_name = "Studio Ca' Bragadin";

// Create private directory if it doesn't exist
if (!file_exists(dirname($subscribers_file))) {
    mkdir(dirname($subscribers_file), 0700, true);
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Function to log actions
function logAction($message, $log_file) {
    $timestamp = date("Y-m-d H:i:s");
    $log_message = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $errors = [];
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $privacy = isset($_POST['privacy']) ? $_POST['privacy'] : '';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    if (empty($name)) {
        $errors['name'] = "Il nome è obbligatorio";
    } elseif (strlen($name) > 100) {
        $errors['name'] = "Il nome non può superare i 100 caratteri";
    }
    
    if (empty($email)) {
        $errors['email'] = "L'email è obbligatoria";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Formato email non valido";
    } elseif (strlen($email) > 255) {
        $errors['email'] = "L'email non può superare i 255 caratteri";
    }
    
    if (empty($privacy)) {
        $errors['privacy'] = "Devi accettare l'informativa sulla privacy";
    }
    
    // If no errors, process subscription
    if (empty($errors)) {
        // Check if email already exists
        $subscribers = [];
        if (file_exists($subscribers_file)) {
            $subscribers = file($subscribers_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
        
        $email_exists = false;
        foreach ($subscribers as $subscriber) {
            list($stored_email, $stored_name) = explode("|", $subscriber);
            if (strtolower($stored_email) == strtolower($email)) {
                $email_exists = true;
                break;
            }
        }
        
        if ($email_exists) {
            echo json_encode([
                'success' => false,
                'message' => 'Questo indirizzo email è già iscritto alla newsletter.'
            ]);
            exit;
        }
        
        // Add new subscriber with additional security data
        $new_subscriber = implode("|", [
            $email,
            $name,
            date("Y-m-d H:i:s"),
            $ip_address,
            bin2hex(random_bytes(16)) // Unsubscribe token
        ]) . "\n";
        
        file_put_contents($subscribers_file, $new_subscriber, FILE_APPEND | LOCK_EX);
        chmod($subscribers_file, 0600); // Secure file permissions
        
        // ==============================================
        // SUBSCRIBER CONFIRMATION EMAIL (HTML)
        // ==============================================
        $subscriber_subject = "Conferma iscrizione alla newsletter - $studio_name";
        $subscriber_message = <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma iscrizione newsletter</title>
    <style>
        body { font-family: 'Raleway', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9f9f9; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { background-color: #2c3e50; padding: 30px; text-align: center; color: white; }
        .logo { max-width: 180px; height: auto; }
        .content { padding: 30px; }
        .footer { background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 13px; color: #777; }
        .button { display: inline-block; background-color: #2c3e50; color: white !important; padding: 12px 25px; text-decoration: none; border-radius: 4px; margin: 15px 0; font-weight: 600; }
        h1 { color: white; margin: 0; font-size: 24px; }
        p { margin-bottom: 15px; }
        ul { margin: 15px 0; padding-left: 20px; }
        li { margin-bottom: 8px; }
        a { color: #2c3e50; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="$website_url/images/logo.jpg.avif" alt="$studio_name" class="logo">
            <h1>Conferma iscrizione</h1>
        </div>
        
        <div class="content">
            <p>Gentile $name,</p>
            <p>Grazie per esserti iscritto alla newsletter professionale di $studio_name. La tua iscrizione è stata registrata con successo.</p>
            
            <p><strong>Cosa riceverai:</strong></p>
            <ul>
                <li>Aggiornamenti normativi e fiscali</li>
                <li>Novità legislative e circolari</li>
                <li>Inviti a eventi e webinar</li>
                <li>Approfondimenti professionali</li>
            </ul>
            
            <p>Se non hai richiesto questa iscrizione, puoi ignorare questa email o <a href="$website_url/unsubscribe.php?email=$email">annullare l'iscrizione</a>.</p>
            
            <p>Per qualsiasi domanda, non esitare a contattarci.</p>
            
            <p>Cordiali saluti,<br>
            <strong>Il Team di $studio_name</strong></p>
        </div>
        
        <div class="footer">
            <p>$studio_name &copy; {date('Y')}</p>
            <p>Via G. Belzoni 180, 35121 Padova</p>
            <p>P.IVA 12345678901 | <a href="$website_url">$website_url</a></p>
        </div>
    </div>
</body>
</html>
HTML;

        $subscriber_headers = "MIME-Version: 1.0\r\n";
        $subscriber_headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $subscriber_headers .= "From: $studio_name <info@studicabragadin.it>\r\n";
        $subscriber_headers .= "Reply-To: info@studicabragadin.it\r\n";
        $subscriber_headers .= "X-Mailer: PHP/" . phpversion();
        
        // ==============================================
        // ADMIN NOTIFICATION EMAIL (HTML)
        // ==============================================
        $admin_subject = "[Newsletter] Nuova iscrizione: $email";
        $admin_message = <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuova iscrizione newsletter</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background-color: #2c3e50; padding: 20px; text-align: center; color: white; }
        .content { padding: 25px; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #777; }
        .data-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .data-table th { background-color: #f5f5f5; text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
        .data-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .button { display: inline-block; background-color: #2c3e50; color: white !important; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nuova iscrizione newsletter</h2>
        </div>
        
        <div class="content">
            <table class="data-table">
                <tr>
                    <th>Nome:</th>
                    <td>$name</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>$email</td>
                </tr>
                <tr>
                    <th>Data:</th>
                    <td>{date('d/m/Y H:i:s')}</td>
                </tr>
                <tr>
                    <th>IP:</th>
                    <td>$ip_address</td>
                </tr>
                <tr>
                    <th>Totale iscritti:</th>
                    <td>{count($subscribers) + 1}</td>
                </tr>
            </table>
            
            <p><a href="$website_url/admin/newsletter-subscribers.php" class="button">Gestisci iscritti</a></p>
        </div>
        
        <div class="footer">
            <p>$studio_name - Sistema Newsletter</p>
            <p>{date('d/m/Y H:i')}</p>
        </div>
    </div>
</body>
</html>
HTML;

        $admin_headers = "MIME-Version: 1.0\r\n";
        $admin_headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $admin_headers .= "From: Newsletter System <newsletter@studicabragadin.it>\r\n";
        $admin_headers .= "X-Mailer: PHP/" . phpversion();
        
        // Send emails
        $mail_sent = mail($email, $subscriber_subject, $subscriber_message, $subscriber_headers);
        mail($admin_email, $admin_subject, $admin_message, $admin_headers);
        
        // Log the subscription
        logAction("New subscription: $name <$email> from IP: $ip_address", $log_file);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Grazie per esserti iscritto! Ti abbiamo inviato una email di conferma.'
        ]);
        exit;
    } else {
        // Return errors
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit;
    }
}

// If not a POST request
echo json_encode([
    'success' => false,
    'message' => 'Metodo di richiesta non valido.'
]);
?>