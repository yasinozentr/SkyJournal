<?php
require 'db.php';
error_reporting(0);

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];

// Postun sahibini bul
$stmt = $pdo->prepare("SELECT user_id, image_path FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if ($post) {
    // YETKİ KONTROLÜ:
    // 1. Post sahibi silebilir.
    // 2. Süper Yönetici (0) veya Admin (1) silebilir.
    if ($post['user_id'] == $user_id || $role_id <= 1) {
        
        // Resmi klasörden sil (varsa)
        if (!empty($post['image_path']) && file_exists($post['image_path'])) {
            // Thumb ve Low versiyonlarını da temizlemek iyi olur ama şart değil
            unlink($post['image_path']); 
        }

        // Veritabanından sil
        $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$post_id]);
    }
}

// İşlem bitince geldiği yere dönsün (Referer)
if(isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: index.php");
}
exit;
?>