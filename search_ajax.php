<?php
require 'db.php';

// Hata raporlamayı kapatalım ki AJAX yanıtı bozulmasın
error_reporting(0);

if (isset($_POST['q'])) {
    $q = trim($_POST['q']);

    if (strlen($q) > 0) {
        // Kullanıcıları Ara
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE ? OR full_name LIKE ? LIMIT 5");
        $stmt->execute(["%$q%", "%$q%"]);
        $users = $stmt->fetchAll();

        if ($users) {
            echo '<div class="mt-4 bg-[#1c1d2b] rounded-xl border border-gray-700 shadow-2xl overflow-hidden container mx-auto max-w-2xl">';
            echo '<div class="p-3 text-xs font-bold text-gray-400 border-b border-gray-700">KULLANICILAR</div>';
            
            foreach ($users as $user) {
                ?>
                <a href="<?php echo htmlspecialchars($user['username']); ?>" class="flex items-center gap-3 p-3 hover:bg-[#252736] transition border-b border-gray-800 last:border-0">
                    <img src="<?php echo $user['profile_pic']; ?>" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <div class="font-bold text-white text-sm"><?php echo htmlspecialchars($user['full_name']); ?></div>
                        <div class="text-xs text-gray-400">@<?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                </a>
                <?php
            }
            echo '</div>';
        } else {
            echo '<div class="mt-4 text-center text-gray-400 text-sm container mx-auto max-w-2xl p-2">Kullanıcı bulunamadı.</div>';
        }
    }
}
?>