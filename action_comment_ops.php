<?php
require 'db.php';
ob_start(); // Çıktı tamponlama
// db.php'den gelen CSS'i temizle
ob_end_clean(); 
error_reporting(0);

if (!isset($_SESSION['user_id']) || !isset($_POST['action'])) exit;

$uid = $_SESSION['user_id'];
$cid = $_POST['comment_id'];
$act = $_POST['action'];
$role = $_SESSION['role_id'];

$c = $pdo->query("SELECT c.*, p.user_id as post_owner FROM comments c JOIN posts p ON c.post_id=p.id WHERE c.id=$cid")->fetch();

if(!$c) exit;

if ($act == 'delete') {
    // YETKİ: Yorum Sahibi VEYA Post Sahibi VEYA (Admin/Süper Admin)
    if ($c['user_id'] == $uid || $c['post_owner'] == $uid || $role <= 1) {
        $pdo->query("DELETE FROM comments WHERE id=$cid");
        echo "deleted";
    }
} 
elseif ($act == 'pin' && $c['post_owner'] == $uid) {
    $pdo->query("UPDATE comments SET is_pinned=1 WHERE id=$cid"); echo "pinned";
} 
elseif ($act == 'unpin' && $c['post_owner'] == $uid) {
    $pdo->query("UPDATE comments SET is_pinned=0 WHERE id=$cid"); echo "unpinned";
}
exit;
?>