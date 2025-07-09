<?php
// Start session and check authentication
session_start();
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Access denied');
}

// Verify credentials (alternative to .htaccess if preferred)
$valid_username = 'yourusername'; // Change this
$valid_password = 'yourpassword'; // Change this

if ($_SERVER['PHP_AUTH_USER'] != $valid_username || 
    $_SERVER['PHP_AUTH_PASSWORD'] != $valid_password) {
    header('HTTP/1.0 401 Unauthorized');
    exit('Invalid credentials');
}

// Configuration
require_once '../newsletter-config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $content = $_POST['content']; // Allowed HTML
    
    // Generate a clean URL slug
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
    $slug = trim($slug, '-');
    
    // Save to news archive
    $news_data = [
        'id' => uniqid(),
        'title' => $title,
        'content' => $content,
        'slug' => $slug,
        'date' => date('Y-m-d H:i:s')
    ];
    
    // Append to JSON file (or CSV if preferred)
    $news_file = dirname($news_archive_file) . '/news.json';
    $all_news = [];
    
    if (file_exists($news_file)) {
        $all_news = json_decode(file_get_contents($news_file), true);
    }
    
    $all_news[] = $news_data;
    file_put_contents($news_file, json_encode($all_news, JSON_PRETTY_PRINT));
    
    // Create HTML file for the news
    $news_html = <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} | Studio Ca' Bragadin</title>
    <link rel="stylesheet" href="../css/news.css">
</head>
<body>
    <!-- Include navbar -->
    <?php include '../components/navbar.html'; ?>
    
    <main class="news-detail">
        <article>
            <h1>{$title}</h1>
            <time datetime="{$news_data['date']}">Pubblicato il {$news_data['date']}</time>
            <div class="news-content">
                {$content}
            </div>
        </article>
    </main>
    
    <!-- Include footer -->
    <?php include '../components/footer.html'; ?>
</body>
</html>
HTML;

    file_put_contents("../news-{$slug}.html", $news_html);
    
    $_SESSION['success'] = "News creata con successo! <a href='send-newsletter.php?id={$news_data['id']}'>Invia agli iscritti</a>";
    header("Location: add-news.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Aggiungi News - Admin</title>
    <link rel="stylesheet" href="../css/professional.css">
    <style>
        .admin-form { max-width: 800px; margin: 2rem auto; padding: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input[type="text"] { width: 100%; padding: 0.5rem; }
        textarea { width: 100%; min-height: 300px; padding: 0.5rem; }
        .success { color: green; margin: 1rem 0; }
    </style>
</head>
<body>
    <?php include '../components/navbar.html'; ?>
    
    <main class="admin-form">
        <h1>Aggiungi Nuova News</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="title">Titolo:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="content">Contenuto (HTML permesso):</label>
                <textarea id="content" name="content" required></textarea>
            </div>
            
            <button type="submit" class="cta-button">Pubblica News</button>
        </form>
        
        <p><a href="news-manager.php">Gestisci News Esistenti</a></p>
    </main>
    
    <?php include '../components/footer.html'; ?>
    
    <script src="../js/main.js"></script>
</body>
</html>