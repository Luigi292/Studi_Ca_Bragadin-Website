<?php
// Configuration
$subscribers_file = "subscribers.txt"; // Your existing subscriber file
$newsletter_file = "newsletter_content.html";
$log_file = "newsletter_log.txt";
$admin_email = "maretto88@gmail.com";
$from_email = "info@studicabragadin.it";
$from_name = "Studio Ca' Bragadin";
$website_url = "https://www.studicabragadin.it"; // Add your website URL

// Function to log actions
function logAction($message, $log_file) {
    $timestamp = date("Y-m-d H:i:s");
    $log_message = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

// Check if newsletter content exists
if (!file_exists($newsletter_file)) {
    die("Error: Newsletter content file not found.");
}

// Create the file if it doesn't exist
if (!file_exists($subscribers_file)) {
    file_put_contents($subscribers_file, "");
    chmod($subscribers_file, 0600); // Secure file permissions
}

// Get subscribers
$subscribers = file($subscribers_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (empty($subscribers)) {
    die("Error: No subscribers found in the list.");
}

// Get newsletter content
$newsletter_content = file_get_contents($newsletter_file);
$newsletter_subject = "Studio Ca' Bragadin - Aggiornamento " . date("d/m/Y");

$sent_count = 0;
$failed_count = 0;

foreach ($subscribers as $subscriber) {
    // Skip empty lines
    if (empty(trim($subscriber))) continue;
    
    // Extract subscriber data (email|name|date)
    $subscriber_data = explode("|", $subscriber);
    if (count($subscriber_data) < 2) continue;
    
    $email = trim($subscriber_data[0]);
    $name = trim($subscriber_data[1]);
    $date = isset($subscriber_data[2]) ? trim($subscriber_data[2]) : date("Y-m-d");
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logAction("Invalid email skipped: $email", $log_file);
        $failed_count++;
        continue;
    }

    // Personalize content
    $personalized_content = str_replace(
        ["[NOME]", "[DATA]", "[SITO]"],
        [$name, $date, $website_url],
        $newsletter_content
    );

    // Add unsubscribe link
    $unsubscribe_link = "$website_url/unsubscribe.php?email=" . urlencode($email);
    $personalized_content .= "\n\n<p style=\"font-size:12px;color:#777;\">"
                          . "Per cancellare l'iscrizione: <a href=\"$unsubscribe_link\">Clicca qui</a></p>";

    // Prepare email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $from_name <$from_email>\r\n";
    $headers .= "Reply-To: $from_email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Send email
    $result = mail($email, $newsletter_subject, $personalized_content, $headers);
    
    if ($result) {
        $sent_count++;
        logAction("Sent to: $name <$email>", $log_file);
    } else {
        $failed_count++;
        logAction("Failed to send to: $name <$email>", $log_file);
    }
    
    // Small delay to prevent server overload
    usleep(200000); // 0.2 seconds
}

// Send report to admin
$report_subject = "Rapporto invio newsletter del " . date("d/m/Y");
$report_message = "Invio newsletter completato.\n\n";
$report_message .= "Totale iscritti: " . count($subscribers) . "\n";
$report_message .= "Email inviate con successo: $sent_count\n";
$report_message .= "Invii falliti: $failed_count\n";
$report_message .= "Data: " . date("d/m/Y H:i:s") . "\n";
$report_message .= "\nDettaglio errori nel file di log: $log_file";

mail($admin_email, $report_subject, $report_message, "From: $from_name <$from_email>");

echo "Invio newsletter completato.<br>";
echo "Email inviate: $sent_count<br>";
echo "Invii falliti: $failed_count<br>";
echo "Un rapporto è stato inviato a $admin_email";
?>