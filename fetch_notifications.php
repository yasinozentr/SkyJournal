<?php
// 1. Hata mesajlarını gizle (JSON yapısını bozmaması için)
error_reporting(0);
ini_set('display_errors', 0);

// 2. İçeriğin JSON olduğunu belirt
header('Content-Type: application/json; charset=utf-8');

require 'db.php';

// Oturum yoksa boş JSON dön
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0, 'html' => '<div class="p-4 text-center text-gray-500 text-xs">Giriş yapmalısınız.</div>']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 3. Okunmamış Bildirim Sayısı
    $count = $pdo->query("SELECT COUNT(*) FROM notifications WHERE receiver_id = $user_id AND is_read = 0")->fetchColumn();

    // 4. Bildirimleri Çek
    // IFNULL kullanımı: Eğer kullanıcı silinmişse profil resmi varsayılan gelsin diye
    $sql = "SELECT n.*, u.username, u.full_name, u.profile_pic 
            FROM notifications n 
            LEFT JOIN users u ON n.sender_id = u.id 
            WHERE n.receiver_id = $user_id 
            ORDER BY n.created_at DESC LIMIT 10";
            
    $stmt = $pdo->query($sql);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = "";

    if (count($notifications) > 0) {
        foreach ($notifications as $n) {
            // Gönderen kullanıcı silinmişse (u.username NULL ise)
            if (!$n['username']) {
                $n['username'] = "Eski Kullanıcı";
                $n['profile_pic'] = "https://ui-avatars.com/api/?name=Unknown";
                $link = "#";
            } else {
                // Bildirim Tipine Göre Link
                $link = ($n['type'] == 'follow') ? $n['username'] : "view_post.php?id=" . $n['post_id'];
            }

            // Metin ve İkon
            $text = "bir işlem yaptı.";
            $icon = "";
            
            if ($n['type'] == 'like') {
                $icon = '<i class="fa-solid fa-heart text-red-500"></i>';
                $text = "gönderini beğendi.";
            } elseif ($n['type'] == 'comment') {
                $icon = '<i class="fa-solid fa-comment text-[#3f88ff]"></i>';
                $text = "yorum yaptı.";
            } elseif ($n['type'] == 'follow') {
                $icon = '<i class="fa-solid fa-user-plus text-green-500"></i>';
                $text = "seni takip etti.";
            }

            // Okunmamışsa arka planı vurgula
            $bg_class = ($n['is_read'] == 0) ? 'bg-[#2a2c3d]' : 'hover:bg-[#252736]';
            $time = date("H:i", strtotime($n['created_at']));

            // HTML Oluştur
            $html .= '
            <a href="'.$link.'" class="flex items-center gap-3 p-3 border-b border-gray-700 transition '.$bg_class.'">
                <div class="relative shrink-0">
                    <img src="'.$n['profile_pic'].'" class="w-10 h-10 rounded-full object-cover border border-gray-600">
                    <div class="absolute -bottom-1 -right-1 bg-[#1c1d2b] rounded-full p-0.5 text-[10px] shadow-sm">
                        '.$icon.'
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-300 truncate">
                        <span class="font-bold text-white">'.$n['username'].'</span> '.$text.'
                    </p>
                    <span class="text-[10px] text-gray-500">'.$time.'</span>
                </div>
            </a>';
        }
    } else {
        $html = '<div class="text-center py-6 text-gray-500 text-sm flex flex-col items-center">
                    <i class="fa-regular fa-bell-slash text-2xl mb-2 opacity-50"></i>
                    Henüz bildirim yok.
                 </div>';
    }

    // JSON Çıktısı Ver
    echo json_encode(['count' => $count, 'html' => $html]);

} catch (Exception $e) {
    // Hata olursa JSON formatında hata döndür
    echo json_encode(['count' => 0, 'html' => '<div class="p-3 text-red-500">Hata oluştu.</div>']);
}
?>