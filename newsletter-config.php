<?php
/**
 * Newsletter Configuration File
 * For Studio Ca' Bragadin - Secure Newsletter System
 */

// Basic security - prevent direct access
defined('NEWSLETTER_SYSTEM') or die('Access denied.');

// ==============================================
// CORE CONFIGURATION
// ==============================================

// Admin and email settings
$admin_email = "maretto88@gmail.com";
$from_email = "newsletter@studicabragadin.it";
$reply_to = "info@studicabragadin.it";
$studio_name = "Studio Ca' Bragadin";
$website_url = "https://www.studicabragadin.it";
$physical_address = "Via G. Belzoni 180, 35121 Padova";
$piva = "12345678901";

// Security settings
$unsubscribe_secret = "abcd1234efgh5678"; // Change this to a strong random string
$allowed_html_tags = '<p><a><strong><em><ul><ol><li><h2><h3><h4><br>';

// ==============================================
// FILE SYSTEM CONFIGURATION
// ==============================================

// Base private directory (outside web root)
$private_dir = $_SERVER['DOCUMENT_ROOT'] . '/../private/newsletter_data/';

// Subscriber management
$subscribers_file = $private_dir . 'subscribers.csv';
$unsubscribed_file = $private_dir . 'unsubscribed.csv';

// News content management
$news_archive_file = $private_dir . 'news_archive.json'; // Changed to JSON for better structure
$news_template_file = $_SERVER['DOCUMENT_ROOT'] . '/newsletter_template.html';

// Logging and temporary files
$log_file = $private_dir . 'newsletter.log';
$temp_dir = $private_dir . 'temp/';

// ==============================================
// INITIALIZATION & SECURITY CHECKS
// ==============================================

// Create directories with secure permissions if they don't exist
$directories_to_create = [
    $private_dir,
    $temp_dir
];

foreach ($directories_to_create as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0700, true);
        // Add an index.html to prevent directory listing
        file_put_contents($dir . 'index.html', '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Directory access forbidden</h1></body></html>');
    }
}

// Initialize files with headers if they don't exist
$files_to_initialize = [
    $subscribers_file => "email,name,join_date,ip,token,is_active\n",
    $unsubscribed_file => "email,unsubscribe_date,reason\n",
    $news_archive_file => "[]", // Empty JSON array
    $log_file => "" // Empty log file
];

foreach ($files_to_initialize as $file => $content) {
    if (!file_exists($file)) {
        file_put_contents($file, $content);
        chmod($file, 0600); // Secure file permissions
    }
}

// ==============================================
// EMAIL TEMPLATE DEFAULTS
// ==============================================

$email_header_template = <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{subject}</title>
    <style>
        body { font-family: 'Raleway', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .header { background-color: #2c3e50; padding: 30px; text-align: center; color: white; }
        .content { padding: 30px; }
        .footer { background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #777; }
        a { color: #2c3e50; text-decoration: underline; }
        .button { display: inline-block; background-color: #2c3e50; color: white !important; padding: 12px 25px; 
                text-decoration: none; border-radius: 4px; margin: 15px 0; font-weight: 600; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{studio_name}</h1>
            <h2>{subject}</h2>
        </div>
        <div class="content">
HTML;

$email_footer_template = <<<HTML
        </div>
        <div class="footer">
            <p>{studio_name} &copy; {year}</p>
            <p>{physical_address}</p>
            <p>P.IVA {piva} | <a href="{website_url}">{website_url}</a></p>
            <p><small>Per non ricevere più queste comunicazioni: <a href="{unsubscribe_link}">Cancella iscrizione</a></small></p>
        </div>
    </div>
</body>
</html>
HTML;

// ==============================================
// HELPER FUNCTIONS
// ==============================================

/**
 * Sanitize input data
 */
function sanitizeInput($data, $allow_html = false) {
    $data = trim($data);
    $data = stripslashes($data);
    
    if (!$allow_html) {
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Log actions with timestamp
 */
function logAction($message, $log_file) {
    $timestamp = date('[Y-m-d H:i:s]');
    $log_message = "$timestamp $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

/**
 * Generate unsubscribe token
 */
function generateUnsubscribeToken($email) {
    global $unsubscribe_secret;
    return hash('sha256', $email . $unsubscribe_secret . time());
}

// ==============================================
// SECURITY HEADERS
// ==============================================

// Add security headers if running in web context
if (php_sapi_name() !== 'cli') {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Only allow this to be included from certain files
    $allowed_includers = ['process-newsletter.php', 'send-newsletter.php', 'add-news.php'];
    $current_file = basename($_SERVER['PHP_SELF']);
    
    if (!in_array($current_file, $allowed_includers)) {
        die('Unauthorized access attempt.');
    }
}
?>