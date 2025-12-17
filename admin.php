<?php
require 'db.php';

// YETKİ KONTROLÜ: Sadece Süper Yönetici (0) ve Adminler (1) girebilir
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [0, 1])) {
    header("Location: index.php");
    exit;
}

$my_role = $_SESSION['role_id']; // Benim yetkim ne? (0 mı 1 mi?)

// İstatistikleri Çek
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$total_likes = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();

// Kullanıcı Listesi
$users = $pdo->query("SELECT * FROM users ORDER BY role_id ASC, created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Paneli - SkySocial</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-[#0f101a] text-white font-sans min-h-screen">

    <nav class="bg-[#1c1d2b] p-4 border-b border-gray-800 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <?php if($my_role == 0): ?>
                <i class="fa-solid fa-crown text-yellow-500 text-2xl"></i>
                <span class="text-xl font-bold text-yellow-500">Süper Yönetici</span>
            <?php else: ?>
                <i class="fa-solid fa-shield-halved text-red-500 text-2xl"></i>
                <span class="text-xl font-bold">Admin Paneli</span>
            <?php endif; ?>
        </div>
        <a href="index.php" class="text-gray-400 hover:text-white flex items-center gap-2">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Siteye Dön
        </a>
    </nav>

    <div class="container mx-auto p-6">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-[#1c1d2b] p-6 rounded-xl border border-gray-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-500/20 text-blue-500 rounded-full flex items-center justify-center text-2xl"><i class="fa-solid fa-users"></i></div>
                <div><h3 class="text-2xl font-bold"><?php echo $total_users; ?></h3><p class="text-sm text-gray-400">Kullanıcı</p></div>
            </div>
            <div class="bg-[#1c1d2b] p-6 rounded-xl border border-gray-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-green-500/20 text-green-500 rounded-full flex items-center justify-center text-2xl"><i class="fa-regular fa-images"></i></div>
                <div><h3 class="text-2xl font-bold"><?php echo $total_posts; ?></h3><p class="text-sm text-gray-400">Gözlem</p></div>
            </div>
            <div class="bg-[#1c1d2b] p-6 rounded-xl border border-gray-800 flex items-center gap-4">
                <div class="w-12 h-12 bg-red-500/20 text-red-500 rounded-full flex items-center justify-center text-2xl"><i class="fa-solid fa-heart"></i></div>
                <div><h3 class="text-2xl font-bold"><?php echo $total_likes; ?></h3><p class="text-sm text-gray-400">Beğeni</p></div>
            </div>
        </div>

        <div class="bg-[#1c1d2b] rounded-xl overflow-hidden border border-gray-800">
            <div class="p-4 border-b border-gray-800 bg-[#151621] flex justify-between items-center">
                <h3 class="font-bold">Kullanıcı Yönetimi</h3>
                <?php if($my_role == 0): ?>
                    <span class="text-xs text-yellow-500 bg-yellow-500/10 px-2 py-1 rounded border border-yellow-500/20">Tam Yetki Modu</span>
                <?php endif; ?>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#0f101a] text-gray-400 uppercase">
                        <tr>
                            <th class="p-4">Kullanıcı</th>
                            <th class="p-4">Rol</th>
                            <th class="p-4">Durum</th>
                            <th class="p-4 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <?php foreach($users as $u): ?>
                        <tr class="hover:bg-[#252736] transition">
                            <td class="p-4 flex items-center gap-3">
                                <img src="<?php echo $u['profile_pic']; ?>" class="w-8 h-8 rounded-full object-cover">
                                <div>
                                    <div class="font-bold"><?php echo $u['username']; ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $u['email']; ?></div>
                                </div>
                            </td>
                            <td class="p-4">
                                <?php if($u['role_id'] == 0): ?>
                                    <span class="bg-yellow-500/20 text-yellow-500 px-2 py-1 rounded text-xs font-bold border border-yellow-500/30">👑 Süper Yönetici</span>
                                <?php elseif($u['role_id'] == 1): ?>
                                    <span class="bg-red-500/20 text-red-400 px-2 py-1 rounded text-xs font-bold border border-red-500/30">🛡️ Admin</span>
                                <?php else: ?>
                                    <span class="bg-gray-700/50 text-gray-300 px-2 py-1 rounded text-xs">Üye</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <?php if($u['is_banned'] == 1): ?>
                                    <span class="text-red-500 font-bold"><i class="fa-solid fa-ban"></i> Yasaklı</span>
                                <?php else: ?>
                                    <span class="text-green-500"><i class="fa-solid fa-check"></i> Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <?php if($u['id'] != $_SESSION['user_id']): ?>
                                    
                                    <?php if($my_role == 0): ?>
                                        
                                        <?php if($u['role_id'] == 2): ?>
                                            <a href="admin_actions.php?action=make_admin&id=<?php echo $u['id']; ?>" onclick="return confirm('Bu kullanıcıyı Admin yapmak istiyor musun?')" class="text-blue-400 hover:underline border border-blue-400/30 px-2 py-1 rounded text-xs">Admin Yap</a>
                                        
                                        <?php elseif($u['role_id'] == 1): ?>
                                            <a href="admin_actions.php?action=remove_admin&id=<?php echo $u['id']; ?>" onclick="return confirm('Admin yetkisini almak istiyor musun?')" class="text-yellow-400 hover:underline border border-yellow-400/30 px-2 py-1 rounded text-xs">Yetkiyi Al</a>
                                        <?php endif; ?>

                                    <?php endif; ?>

                                    <?php if( $my_role == 0 || ($my_role == 1 && $u['role_id'] == 2) ): ?>
                                        
                                        <?php if($u['is_banned'] == 0): ?>
                                            <a href="admin_actions.php?action=ban&id=<?php echo $u['id']; ?>" onclick="return confirm('Yasaklamak istiyor musun?')" class="bg-red-500/20 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded transition text-xs">Banla</a>
                                        <?php else: ?>
                                            <a href="admin_actions.php?action=unban&id=<?php echo $u['id']; ?>" class="bg-green-500/20 text-green-500 hover:bg-green-500 hover:text-white px-3 py-1 rounded transition text-xs">Aç</a>
                                        <?php endif; ?>

                                    <?php endif; ?>

                                <?php else: ?>
                                    <span class="text-xs text-gray-500">(Sen)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>