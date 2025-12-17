<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$current_user_id = $_SESSION['user_id'];

if (isset($_GET['username'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?"); $stmt->execute([$_GET['username']]); $profile_user = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?"); $stmt->execute([$current_user_id]); $profile_user = $stmt->fetch();
}
if (!$profile_user) { die("Kullanıcı bulunamadı!"); }
$profile_id = $profile_user['id']; $is_own_profile = ($current_user_id == $profile_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'follow') {
        $pdo->prepare("INSERT IGNORE INTO follows (follower_id, following_id) VALUES (?, ?)")->execute([$current_user_id, $profile_id]);
        $pdo->prepare("INSERT INTO notifications (receiver_id, sender_id, type) VALUES (?, ?, 'follow')")->execute([$profile_id, $current_user_id]);
    } elseif ($_POST['action'] == 'unfollow') {
        $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?")->execute([$current_user_id, $profile_id]);
    }
    header("Location: " . $profile_user['username']); exit;
}

$post_count = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?"); $post_count->execute([$profile_id]); $post_count = $post_count->fetchColumn();
$follower_count = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE following_id = ?"); $follower_count->execute([$profile_id]); $follower_count = $follower_count->fetchColumn();
$following_count = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?"); $following_count->execute([$profile_id]); $following_count = $following_count->fetchColumn();
$is_following = $pdo->query("SELECT COUNT(*) FROM follows WHERE follower_id=$current_user_id AND following_id=$profile_id")->fetchColumn() > 0;

$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY uploaded_at DESC"); $stmt->execute([$profile_id]); $posts = $stmt->fetchAll();
$stmt_followers = $pdo->prepare("SELECT u.username, u.full_name, u.profile_pic FROM follows f JOIN users u ON f.follower_id = u.id WHERE f.following_id = ?"); $stmt_followers->execute([$profile_id]); $followers_list = $stmt_followers->fetchAll();
$stmt_following = $pdo->prepare("SELECT u.username, u.full_name, u.profile_pic FROM follows f JOIN users u ON f.following_id = u.id WHERE f.follower_id = ?"); $stmt_following->execute([$profile_id]); $following_list = $stmt_following->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title><?php echo htmlspecialchars($profile_user['username']); ?> - Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>.modal-scroll::-webkit-scrollbar { width: 6px; } .modal-scroll::-webkit-scrollbar-track { background: #1c1d2b; } .modal-scroll::-webkit-scrollbar-thumb { background: #3f88ff; border-radius: 10px; }</style>
</head>
<body class="bg-[#0f101a] text-white font-sans min-h-screen">
    <nav class="fixed top-0 w-full h-16 bg-[#1c1d2b] border-b border-gray-800 z-50 flex items-center justify-between px-6">
        <div class="flex items-center gap-2"><a href="index.php" class="flex items-center gap-2"><i class="fa-solid fa-moon text-[#3f88ff] text-2xl"></i><span class="text-xl font-bold tracking-wide">SkySocial</span></a></div>
        <div class="flex items-center gap-6">
             <?php if($_SESSION['role_id'] <= 1): ?><a href="admin.php" class="text-red-500 hover:text-red-400 transition" title="Yönetici Paneli"><i class="fa-solid fa-shield-halved text-xl"></i></a><?php endif; ?>
             <a href="index.php" class="text-gray-400 hover:text-white transition" title="Akışa Dön"><i class="fa-solid fa-house text-xl"></i></a>
             <?php if($is_own_profile): ?><button onclick="document.getElementById('settingsModal').classList.remove('hidden')" class="text-gray-400 hover:text-[#3f88ff] transition" title="Ayarlar"><i class="fa-solid fa-gear text-xl"></i></button><?php endif; ?>
             <a href="logout.php" class="text-gray-400 hover:text-red-500 transition" title="Çıkış"><i class="fa-solid fa-right-from-bracket text-xl"></i></a>
        </div>
    </nav>

    <div class="pt-24 container mx-auto px-4 pb-10 max-w-5xl">
        <div class="bg-[#1c1d2b] rounded-2xl p-6 md:p-8 mb-8 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-r from-[#3f88ff]/20 to-[#1c1d2b]"></div>
            <div class="relative flex flex-col md:flex-row items-center md:items-end gap-6 pt-10">
                <div class="w-32 h-32 rounded-full border-4 border-[#0f101a] overflow-hidden bg-black shadow-lg shrink-0"><img src="<?php echo $profile_user['profile_pic']; ?>" class="w-full h-full object-cover"></div>
                <div class="text-center md:text-left flex-1 w-full">
                    <h1 class="text-2xl font-bold text-white flex items-center justify-center md:justify-start gap-2"><?php echo htmlspecialchars($profile_user['full_name']); ?><?php if($profile_user['role_id'] == 1): ?><i class="fa-solid fa-circle-check text-[#3f88ff] text-sm" title="Yönetici"></i><?php endif; ?></h1>
                    <p class="text-gray-400 text-sm mb-2">@<?php echo htmlspecialchars($profile_user['username']); ?></p>
                    <p class="text-gray-300 max-w-lg mx-auto md:mx-0"><?php echo htmlspecialchars($profile_user['bio']); ?></p>
                    <div class="flex items-center justify-center md:justify-start gap-4 mt-3 text-sm text-gray-500 flex-wrap">
                        <span><i class="fa-solid fa-location-dot mr-1 text-[#3f88ff]"></i> <?php echo htmlspecialchars($profile_user['location']); ?></span>
                        <?php if(!empty($profile_user['birth_date'])): require_once 'functions.php'; $moon = getMoonPhase($profile_user['birth_date']); ?>
                            <span class="mt-2 sm:mt-0" title="Doğduğu Günün Ay Evresi"><span class="text-xl"><?php echo $moon['icon']; ?></span> <span class="text-sm text-gray-400 ml-1"><?php echo $moon['name']; ?></span></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex flex-col items-center md:items-end gap-4 w-full md:w-auto">
                    <div class="flex gap-6 text-center select-none">
                        <div><div class="font-bold text-xl text-white"><?php echo $post_count; ?></div><div class="text-xs text-gray-400">Gözlem</div></div>
                        <div onclick="openModal('followersModal')" class="cursor-pointer hover:text-[#3f88ff] transition group"><div class="font-bold text-xl text-white group-hover:text-[#3f88ff]"><?php echo $follower_count; ?></div><div class="text-xs text-gray-400">Takipçi</div></div>
                        <div onclick="openModal('followingModal')" class="cursor-pointer hover:text-[#3f88ff] transition group"><div class="font-bold text-xl text-white group-hover:text-[#3f88ff]"><?php echo $following_count; ?></div><div class="text-xs text-gray-400">Takip</div></div>
                    </div>
                    <?php if ($is_own_profile): ?>
                        <div class="flex gap-2 w-full md:w-auto justify-center">
                            <a href="upload.php" class="bg-[#3f88ff] hover:bg-blue-600 text-white px-4 py-2 rounded-full font-bold transition flex items-center gap-2 text-sm shadow-lg shadow-blue-500/20"><i class="fa-solid fa-plus"></i> Gözlem Ekle</a>
                            <a href="edit_profile.php" class="bg-[#1c1d2b] border border-gray-600 hover:border-[#3f88ff] hover:text-[#3f88ff] text-white px-4 py-2 rounded-full font-medium transition text-sm">Düzenle</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="w-full md:w-auto flex justify-center">
                            <?php if($is_following): ?>
                                <input type="hidden" name="action" value="unfollow"><button type="submit" class="bg-transparent border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-6 py-2 rounded-full font-medium transition w-full md:w-auto">Takibi Bırak</button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="follow"><button type="submit" class="bg-[#3f88ff] hover:bg-blue-600 text-white px-6 py-2 rounded-full font-bold transition shadow-lg shadow-blue-500/30 w-full md:w-auto">Takip Et</button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <h3 class="text-xl font-bold mb-4 border-b border-gray-800 pb-2 mt-8">Paylaşılan Gözlemler</h3>
        <?php if(count($posts) == 0): ?>
            <div class="text-center py-20 text-gray-500"><i class="fa-regular fa-image text-4xl mb-3 opacity-50"></i><p>Henüz hiç gözlem paylaşılmamış.</p></div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach($posts as $post): ?>
                <a href="view_post.php?id=<?php echo $post['id']; ?>" class="group relative aspect-square bg-[#1c1d2b] rounded-lg overflow-hidden cursor-pointer hover:shadow-xl hover:shadow-[#3f88ff]/20 transition block border border-gray-800">
                    <?php if(!empty($post['image_path'])): 
                        $q = $settings['image_quality']; $img_src = $post['image_path'];
                        if ($q == 'low') { $low = str_replace('.', '_low.', $post['image_path']); if(file_exists($low)) $img_src = $low; } 
                        elseif ($q == 'medium') { $thumb = str_replace('.', '_thumb.', $post['image_path']); if(file_exists($thumb)) $img_src = $thumb; }
                    ?>
                        <img src="<?php echo $img_src; ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center p-6 bg-[#252736] text-center group-hover:bg-[#2a2c3d] transition"><i class="fa-solid fa-align-left text-3xl text-[#3f88ff]/30 mb-3"></i><h4 class="font-bold text-white text-lg line-clamp-2 leading-tight mb-2"><?php echo htmlspecialchars($post['title']); ?></h4><p class="text-xs text-gray-400 line-clamp-3"><?php echo htmlspecialchars($post['description']); ?></p></div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition flex flex-col justify-end p-4"><h4 class="font-bold text-white truncate"><?php echo htmlspecialchars($post['title']); ?></h4><div class="text-xs text-gray-300 flex justify-between mt-1"><span class="flex items-center gap-1"><i class="fa-solid fa-eye text-[#3f88ff]"></i> İncele</span><span><?php echo date("d M", strtotime($post['captured_at'])); ?></span></div></div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="followersModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-[60] flex items-center justify-center p-4" onclick="closeModal('followersModal')">
        <div class="bg-[#1c1d2b] w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-gray-700" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#151621]"><h3 class="font-bold text-lg">Takipçiler</h3><button onclick="closeModal('followersModal')" class="text-gray-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button></div>
            <div class="p-4 max-h-[60vh] overflow-y-auto modal-scroll space-y-3"><?php if(count($followers_list) == 0): ?><div class="text-center text-gray-500 py-4">Henüz takipçi yok.</div><?php endif; foreach($followers_list as $f): ?><a href="<?php echo $f['username']; ?>" class="flex items-center gap-3 p-2 hover:bg-[#252736] rounded transition"><img src="<?php echo $f['profile_pic']; ?>" class="w-10 h-10 rounded-full object-cover"><div><div class="font-bold text-sm"><?php echo $f['full_name']; ?></div><div class="text-xs text-gray-400">@<?php echo $f['username']; ?></div></div></a><?php endforeach; ?></div>
        </div>
    </div>
    <div id="followingModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-[60] flex items-center justify-center p-4" onclick="closeModal('followingModal')">
        <div class="bg-[#1c1d2b] w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-gray-700" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#151621]"><h3 class="font-bold text-lg">Takip Edilenler</h3><button onclick="closeModal('followingModal')" class="text-gray-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button></div>
            <div class="p-4 max-h-[60vh] overflow-y-auto modal-scroll space-y-3"><?php if(count($following_list) == 0): ?><div class="text-center text-gray-500 py-4">Kimseyi takip etmiyor.</div><?php endif; foreach($following_list as $f): ?><a href="<?php echo $f['username']; ?>" class="flex items-center gap-3 p-2 hover:bg-[#252736] rounded transition"><img src="<?php echo $f['profile_pic']; ?>" class="w-10 h-10 rounded-full object-cover"><div><div class="font-bold text-sm"><?php echo $f['full_name']; ?></div><div class="text-xs text-gray-400">@<?php echo $f['username']; ?></div></div></a><?php endforeach; ?></div>
        </div>
    </div>

    <div id="settingsModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-[70] flex items-center justify-center p-4">
        <div class="bg-[#1c1d2b] w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-gray-700">
            <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#151621]"><h3 class="font-bold text-lg text-white"><i class="fa-solid fa-sliders mr-2 text-[#3f88ff]"></i> Ayarlar</h3><button onclick="document.getElementById('settingsModal').classList.add('hidden')" class="text-gray-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button></div>
            <form id="settingsForm" class="p-6 space-y-6">
                <div class="pb-4 border-b border-gray-800">
                    <label class="block text-sm font-bold text-gray-400 mb-4 text-center">Tema Renkleri</label>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center"><input type="color" id="input_bg" name="theme_bg" value="<?php echo $settings['theme_bg']; ?>" class="w-12 h-12 rounded-full cursor-pointer border-2 border-gray-600 p-0 bg-transparent overflow-hidden"><span class="text-xs text-gray-500">Arka Plan</span></div>
                        <div class="text-center"><input type="color" id="input_surface" name="theme_surface" value="<?php echo $settings['theme_surface']; ?>" class="w-12 h-12 rounded-full cursor-pointer border-2 border-gray-600 p-0 bg-transparent overflow-hidden"><span class="text-xs text-gray-500">Kartlar</span></div>
                        <div class="text-center"><input type="color" id="input_primary" name="theme_primary" value="<?php echo $settings['theme_primary']; ?>" class="w-12 h-12 rounded-full cursor-pointer border-2 border-gray-600 p-0 bg-transparent overflow-hidden"><span class="text-xs text-gray-500">Vurgu</span></div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2"><i class="fa-regular fa-image mr-1"></i> Görsel Kalitesi</label>
                    <div class="relative"><select name="image_quality" class="w-full bg-[#0f101a] border border-gray-700 rounded-lg p-3 text-white outline-none focus:border-[#3f88ff] appearance-none cursor-pointer"><option value="low" <?php if($settings['image_quality']=='low') echo 'selected'; ?>>🟢 Düşük (Hızlı)</option><option value="medium" <?php if($settings['image_quality']=='medium') echo 'selected'; ?>>🟡 Orta (Dengeli)</option><option value="high" <?php if($settings['image_quality']=='high') echo 'selected'; ?>>🔴 Yüksek (Orijinal)</option></select><div class="absolute right-3 top-3.5 text-gray-500 pointer-events-none"><i class="fa-solid fa-chevron-down"></i></div></div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="resetTheme()" class="w-1/3 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 rounded-lg transition text-sm"><i class="fa-solid fa-rotate-left"></i> Sıfırla</button>
                    <button type="button" onclick="saveSettings()" class="w-2/3 bg-[#3f88ff] hover:bg-blue-600 text-white font-bold py-3 rounded-lg transition shadow-lg shadow-blue-500/20">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) { document.getElementById(modalId).classList.remove('hidden'); }
        function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }
        function resetTheme() { document.getElementById('input_bg').value = '#0f101a'; document.getElementById('input_surface').value = '#1c1d2b'; document.getElementById('input_primary').value = '#3f88ff'; }
        function saveSettings() {
            const formData = new FormData(document.getElementById('settingsForm'));
            fetch('save_settings.php', { method: 'POST', body: formData }).then(r => r.text()).then(d => { 
                if(d.includes('success')) { location.reload(); } else { alert('Hata oluştu.'); }
            });
        }
    </script>
</body>
</html>