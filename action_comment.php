<?php
// 1. Tamponu aç
ob_start();

require 'db.php';

// 2. Gereksiz CSS'leri temizle
ob_end_clean();

error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $pid = $_POST['post_id']; 
    $txt = trim($_POST['comment']); 
    $uid = $_SESSION['user_id'];

    if ($txt) {
        // Kaydet
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, comment) VALUES (?, ?, ?)"); 
        $stmt->execute([$uid, $pid, $txt]);
        $cid = $pdo->lastInsertId();
        
        // Bildirim
        $owner = $pdo->query("SELECT user_id FROM posts WHERE id=$pid")->fetchColumn();
        if($owner != $uid) {
            $pdo->prepare("INSERT INTO notifications (receiver_id, sender_id, type, post_id) VALUES (?, ?, 'comment', ?)")->execute([$owner, $uid, $pid]);
        }

        // HTML Çıktısı (Bu kısım temiz, çünkü yukarıda ob_clean yaptık)
        $u = $pdo->query("SELECT username, profile_pic FROM users WHERE id=$uid")->fetch();
        
        echo '<div id="comment-'.$cid.'" class="flex gap-3 relative group animate-pulse">
                <a href="'.$u['username'].'" class="shrink-0"><img src="'.$u['profile_pic'].'" class="w-8 h-8 rounded-full"></a>
                <div class="flex-1">
                    <div class="bg-[#252736] p-2.5 rounded-xl rounded-tl-none relative">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-xs text-white">'.$u['username'].'</span>
                            <span class="text-[10px] text-gray-500">Şimdi</span>
                        </div>
                        <p class="text-sm text-gray-300">'.htmlspecialchars($txt).'</p>
                    </div>
                    <div class="flex items-center gap-3 mt-1 ml-1 text-xs text-gray-500">
                         <button onclick="deleteComment('.$cid.')" class="hover:text-red-500 transition"><i class="fa-solid fa-trash"></i> Sil</button>
                    </div>
                </div>
              </div>';
        exit;
    }
}
?>