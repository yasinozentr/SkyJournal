<?php
// Hata mesajlarını ekrana basmayı kapat (AJAX yanıtını bozmasın diye)
error_reporting(0);
ini_set('display_errors', 0);

require 'db.php';

// Çıktı tamponlamayı temizle (Önceki dosyalardan gelen boşlukları siler)
if (ob_get_length()) ob_clean();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    $user_id = $_SESSION['user_id'];
    $fieldsToUpdate = [];
    $params = [];

    // Gelen verileri kontrol et ve varsa listeye ekle
    // 1. Renkler
    if (isset($_POST['theme_bg'])) { 
        $fieldsToUpdate[] = "theme_bg = ?"; 
        $params[] = $_POST['theme_bg']; 
    }
    if (isset($_POST['theme_surface'])) { 
        $fieldsToUpdate[] = "theme_surface = ?"; 
        $params[] = $_POST['theme_surface']; 
    }
    if (isset($_POST['theme_primary'])) { 
        $fieldsToUpdate[] = "theme_primary = ?"; 
        $params[] = $_POST['theme_primary']; 
    }

    // 2. Diğer Ayarlar (Gelecekte eklenecekler için hazır olsun)
    if (isset($_POST['image_quality'])) { 
        $fieldsToUpdate[] = "image_quality = ?"; 
        $params[] = $_POST['image_quality']; 
    }
    if (isset($_POST['language'])) { 
        $fieldsToUpdate[] = "language = ?"; 
        $params[] = $_POST['language']; 
    }

    // Eğer güncellenecek veri varsa sorguyu çalıştır
    if (count($fieldsToUpdate) > 0) {
        $sql = "UPDATE users SET " . implode(", ", $fieldsToUpdate) . " WHERE id = ?";
        $params[] = $user_id;

        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($params)) {
            echo "success";
        } else {
            echo "error_db";
        }
    } else {
        // Hiçbir veri gönderilmediyse bile hata verme, başarılı say
        echo "success";
    }

} else {
    echo "error_auth";
}
?>