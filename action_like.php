<?php
// 1. Tamponu aç
ob_start();

require 'db.php';

// 2. Gereksiz CSS'leri temizle
ob_end_clean();

error_reporting(0);

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) { echo "error"; exit; }

$pid = $_POST['post_id']; 
$uid = $_SESSION['user_id'];

// Beğeni kontrolü
$check = $pdo->prepare("SELECT id FROM likes WHERE user_id=? AND post_id=?"); 
$check->execute([$uid, $pid]);

if ($check->rowCount() > 0) {
    $pdo->prepare("DELETE FROM likes WHERE user_id=? AND post_id=?")->execute([$uid, $pid]); 
    echo "unliked";
} else {
    $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)")->execute([$uid, $pid]);
    
    $owner = $pdo->query("SELECT user_id FROM posts WHERE id=$pid")->fetchColumn();
    if($owner && $owner != $uid) {
        $pdo->prepare("INSERT INTO notifications (receiver_id, sender_id, type, post_id) VALUES (?, ?, 'like', ?)")->execute([$owner, $uid, $pid]);
    }
    echo "liked";
}
exit;
?>