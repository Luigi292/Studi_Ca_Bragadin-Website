<?php
require __DIR__ . '/newsletter-config.php';

$email = filter_var($_GET['email'] ?? '', FILTER_SANITIZE_EMAIL);
$token = $_GET['token'] ?? '';

if (!empty($email) {
    try {
        $stmt = $pdo->prepare("UPDATE subscribers SET is_active = 0 WHERE email = ? AND unsubscribe_token = ?");
        $stmt->execute([$email, $token]);
        
        if ($stmt->rowCount() > 0) {
            echo "Iscrizione cancellata con successo";
        } else {
            echo "Indirizzo non trovato o già cancellato";
        }
    } catch (PDOException $e) {
        die("Errore di sistema");
    }
} else {
    echo "Richiesta non valida";
}
?>