<?php
require 'db.php';
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results_users = [];
$results_posts = [];

if ($query) {
    // Kullanıcı Ara
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE ? OR full_name LIKE ?");
    $stmt->execute(["%$query%", "%$query%"]);
    $results_users = $stmt->fetchAll();

    // Post Ara (Başlık veya Etiket)
    $stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE title LIKE ? OR tags LIKE ?");
    $stmt->execute(["%$query%", "%$query%"]);
    $results_posts = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Arama Sonuçları: <?php echo htmlspecialchars($query); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-[#0f101a] text-white font-sans">
    
    <nav class="p-4 bg-[#1c1d2b] flex justify-between items-center">
        <div class="font-bold text-xl text-[#3f88ff]">SkySocial Arama</div>
        <a href="index.php" class="text-gray-400 hover:text-white">Ana Sayfaya Dön</a>
    </nav>

    <div class="container mx-auto p-6 max-w-4xl">
        <h2 class="text-2xl font-bold mb-6">"<?php echo htmlspecialchars($query); ?>" için sonuçlar</h2>

        <?php if($results_users): ?>
            <h3 class="text-[#3f88ff] font-bold mb-4 border-b border-gray-800 pb-2">Kullanıcılar</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <?php foreach($results_users as $u): ?>
    <a href="<?php echo $u['username']; ?>" class="bg-[#1c1d2b] p-4 rounded flex items-center gap-3 hover:bg-[#252736] transition">
        <img src="<?php echo $u['profile_pic']; ?>" class="w-12 h-12 rounded-full object-cover">
        <div>
            <div class="font-bold"><?php echo $u['full_name']; ?></div>
            <div class="text-xs text-gray-400">@<?php echo $u['username']; ?></div>
        </div>
    </a>
<?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if($results_posts): ?>
            <h3 class="text-[#3f88ff] font-bold mb-4 border-b border-gray-800 pb-2">Gözlemler</h3>
            <div class="space-y-4">
                <?php foreach($results_posts as $p): ?>
                    <div class="bg-[#1c1d2b] p-4 rounded flex gap-4">
                        <img src="<?php echo $p['image_path']; ?>" class="w-24 h-24 object-cover rounded">
                        <div>
                            <h4 class="font-bold"><?php echo $p['title']; ?></h4>
                            <p class="text-sm text-gray-400 mb-2"><?php echo $p['description']; ?></p>
                            <div class="text-xs text-[#3f88ff]"><?php echo $p['tags']; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if(!$results_users && !$results_posts): ?>
            <p class="text-gray-500">Hiçbir sonuç bulunamadı.</p>
        <?php endif; ?>
    </div>
</body>
</html>