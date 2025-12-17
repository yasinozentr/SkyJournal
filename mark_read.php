<?php
require 'db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Giriş yapan kullanıcının tüm bildirimlerini 'okundu' (1) yap
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE receiver_id = ?");
    $stmt->execute([$user_id]);
}
?>