<?php
// Authentication (same as add-news.php)
require_once 'add-news.php'; // Reuses the auth code

// Get all news
$news_file = dirname($news_archive_file) . '/news.json';
$all_news = [];

if (file_exists($news_file)) {
    $all_news = json_decode(file_get_contents($news_file), true);
    $all_news = array_reverse($all_news); // Newest first
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione News - Admin</title>
    <link rel="stylesheet" href="../css/professional.css">
    <style>
        .news-list { max-width: 1000px; margin: 2rem auto; }
        .news-item { border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; }
        .news-actions { margin-top: 1rem; }
    </style>
</head>
<body>
    <?php include '../components/navbar.html'; ?>
    
    <main class="news-list">
        <h1>Gestione News</h1>
        <p><a href="add-news.php">Aggiungi Nuova News</a></p>
        
        <?php foreach ($all_news as $news): ?>
        <div class="news-item">
            <h3><?= htmlspecialchars($news['title']) ?></h3>
            <p><small><?= $news['date'] ?></small></p>
            
            <div class="news-actions">
                <a href="../news-<?= $news['slug'] ?>.html" target="_blank">Vedi</a> |
                <a href="send-newsletter.php?id=<?= $news['id'] ?>">Invia</a> |
                <a href="edit-news.php?id=<?= $news['id'] ?>">Modifica</a> |
                <a href="delete-news.php?id=<?= $news['id'] ?>" onclick="return confirm('Sicuro?')">Elimina</a>
            </div>
        </div>
        <?php endforeach; ?>
    </main>
    
    <?php include '../components/footer.html'; ?>
</body>
</html>