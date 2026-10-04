<?php
require_once "connexion.php";

// Only accept POST requests for delete (security)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

try {
    // Get article to find image path
    $stmt = $pdo->prepare("SELECT image FROM article WHERE id_article = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        header("Location: index.php");
        exit;
    }

    // Delete associated offers first
    $stmt = $pdo->prepare("DELETE FROM offre WHERE id_article = ?");
    $stmt->execute([$id]);

    // Delete the article
    $stmt = $pdo->prepare("DELETE FROM article WHERE id_article = ?");
    $stmt->execute([$id]);

    // Delete the image file if it exists
    if (!empty($article['image']) && file_exists($article['image'])) {
        unlink($article['image']);
    }

    header("Location: index.php?success=deleted");
    exit;

} catch (Exception $e) {
    header("Location: index.php");
    exit;
}