<?php
require 'db.php';

// Oturum Kontrolü
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$my_role = $_SESSION['role_id'];
$my_id = $_SESSION['user_id'];

// Eğer Rol 0 veya 1 değilse sayfadan at (Normal üye giremez)
if (!in_array($my_role, [0, 1])) { header("Location: index.php"); exit; }

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $target_id = $_GET['id'];

    // Kendine işlem yapamazsın
    if ($target_id == $my_id) { header("Location: admin.php"); exit; }

    // Hedef kullanıcının mevcut rolünü öğren (Güvenlik için)
    $stmt = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->execute([$target_id]);
    $target_role = $stmt->fetchColumn();

    // -----------------------------------------------------
    // 1. YETKİ VERME / ALMA (SADECE SÜPER YÖNETİCİ - ROL 0)
    // -----------------------------------------------------
    if ($my_role == 0) {
        if ($action == 'make_admin') {
            $pdo->prepare("UPDATE users SET role_id = 1 WHERE id = ?")->execute([$target_id]);
        }
        elseif ($action == 'remove_admin') {
            $pdo->prepare("UPDATE users SET role_id = 2 WHERE id = ?")->execute([$target_id]);
        }
    }

    // -----------------------------------------------------
    // 2. BANLAMA / BAN AÇMA
    // -----------------------------------------------------
    // Süper Yönetici (0) -> Herkesi banlar.
    // Admin (1) -> Sadece Üyeleri (2) banlar.
    
    $can_ban = false;
    if ($my_role == 0) {
        $can_ban = true; // Süper yönetici herkesi banlar
    } elseif ($my_role == 1 && $target_role == 2) {
        $can_ban = true; // Admin sadece üyeyi banlar
    }

    if ($can_ban) {
        if ($action == 'ban') {
            $pdo->prepare("UPDATE users SET is_banned = 1 WHERE id = ?")->execute([$target_id]);
        }
        elseif ($action == 'unban') {
            $pdo->prepare("UPDATE users SET is_banned = 0 WHERE id = ?")->execute([$target_id]);
        }
    }
}

header("Location: admin.php");
exit;
?>