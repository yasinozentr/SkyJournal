<?php
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['comment_id'])) { exit; }

$user_id = $_SESSION['user_id'];
$comment_id = $_POST['comment_id'];

// Beğeni var mı kontrol et
$check = $pdo->prepare("SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?");
$check->execute([$user_id, $comment_id]);

if ($check->rowCount() > 0) {
    // Varsa SİL (Unlike)
    $pdo->prepare("DELETE FROM comment_likes WHERE user_id = ? AND comment_id = ?")->execute([$user_id, $comment_id]);
    echo "unliked";
} else {
    // Yoksa EKLE (Like)
    $pdo->prepare("INSERT INTO comment_likes (user_id, comment_id) VALUES (?, ?)")->execute([$user_id, $comment_id]);
    echo "liked";
}
?>