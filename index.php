<?php
require 'db.php';

// 1. OTURUM KONTROLÜ
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];

// 2. URL PARAMETRELERİ
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'today';
$search_query = isset($_GET['q']) ? trim($_GET['q']) : null;

// 3. VERİ ÇEKME İŞLEMLERİ
$posts = [];
$search_users = [];
$search_posts = [];

// A) ARAMA SONUÇLARI
if ($search_query) {
    // Kullanıcılar
    $stmt_u = $pdo->prepare("SELECT * FROM users WHERE username LIKE ? OR full_name LIKE ? LIMIT 5");
    $stmt_u->execute(["%$search_query%", "%$search_query%"]);
    $search_users = $stmt_u->fetchAll();

    // Postlar
    $stmt_p = $pdo->prepare("SELECT id, title, image_path FROM posts WHERE title LIKE ? OR tags LIKE ? LIMIT 5");
    $stmt_p->execute(["%$search_query%", "%$search_query%"]);
    $search_posts = $stmt_p->fetchAll();

    // Akış (Arama Sonuçları)
    $sql = "SELECT p.*, u.username, u.full_name, u.profile_pic, 
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = $user_id) as is_liked,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
            FROM posts p JOIN users u ON p.user_id = u.id 
            WHERE p.title LIKE ? OR p.tags LIKE ? 
            ORDER BY p.uploaded_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$search_query%", "%$search_query%"]);
    $posts = $stmt->fetchAll();

} else {
    // B) NORMAL AKIŞ
    $sql_base = "SELECT p.*, u.username, u.full_name, u.profile_pic, 
                 (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                 (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = $user_id) as is_liked,
                 (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
                 FROM posts p JOIN users u ON p.user_id = u.id ";

    if ($tab == 'feed') {
        // Sadece takip edilenler
        $sql = $sql_base . "WHERE p.user_id IN (SELECT following_id FROM follows WHERE follower_id = $user_id) ORDER BY p.uploaded_at DESC";
    } else {
        // Son 24 saat (Herkes)
        $sql = $sql_base . "WHERE p.uploaded_at >= NOW() - INTERVAL 24 HOUR ORDER BY p.uploaded_at DESC";
    }
    $posts = $pdo->query($sql)->fetchAll();
}

// 4. SAĞ PANEL TRENDLERİ (DÜZELTİLMİŞ HALİ)
// Etiketleri Ayrıştır ve Say
$stmt_tags = $pdo->query("SELECT tags FROM posts WHERE uploaded_at >= NOW() - INTERVAL 7 DAY");
$all_tags_rows = $stmt_tags->fetchAll(PDO::FETCH_COLUMN);
$tag_counts = [];

foreach ($all_tags_rows as $row_tags) {
    $clean_row = trim(preg_replace('/\s+/', ' ', str_replace(',', ' ', $row_tags)));
    $tags_array = explode(' ', $clean_row);

    foreach ($tags_array as $tag) {
        if (!empty($tag)) {
            if (substr($tag, 0, 1) !== '#') $tag = '#' . $tag;
            if (isset($tag_counts[$tag])) {
                $tag_counts[$tag]++;
            } else {
                $tag_counts[$tag] = 1;
            }
        }
    }
}
arsort($tag_counts);
$trend_tags_final = array_slice($tag_counts, 0, 5, true);

$trend_users = $pdo->query("SELECT u.username, u.profile_pic, COUNT(l.id) as total_likes FROM users u JOIN posts p ON u.id = p.user_id JOIN likes l ON p.id = l.post_id GROUP BY u.id ORDER BY total_likes DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkySocial - Ana Sayfa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #1c1d2b; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #3f88ff; border-radius: 10px; }
        .fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body class="bg-[#0f101a] text-white font-sans overflow-x-hidden min-h-screen">

    <nav class="fixed top-0 w-full h-16 bg-[#1c1d2b] border-b border-gray-800 z-50">
        <div class="flex items-center justify-between px-3 h-full w-full">
            
            <div class="flex items-center gap-3">
                <button onclick="openSearchOverlay()" class="lg:hidden text-gray-300 hover:text-white p-2 transition hover:scale-110">
                    <i class="fa-solid fa-magnifying-glass text-xl"></i>
                </button>
                <a href="index.php" class="hidden sm:flex items-center gap-2">
                    <i class="fa-solid fa-moon text-[#3f88ff] text-2xl"></i>
                    <span class="text-xl font-bold tracking-wide">SkySocial</span>
                </a>
            </div>

            <div class="flex gap-1 bg-[#0f101a]/50 p-1 rounded-full border border-gray-800">
                <a href="?tab=today" class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs sm:text-sm font-bold transition <?php echo $tab == 'today' && !$search_query ? 'bg-[#3f88ff] text-white shadow-lg shadow-blue-500/30' : 'text-gray-400 hover:text-white'; ?>">
                    <i class="fa-solid fa-sun"></i>
                    <span class="lg:hidden">Bugün</span>
                    <span class="hidden lg:inline">Bugün Gökyüzünde</span>
                </a>
                <a href="?tab=feed" class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs sm:text-sm font-bold transition <?php echo $tab == 'feed' && !$search_query ? 'bg-[#3f88ff] text-white shadow-lg shadow-blue-500/30' : 'text-gray-400 hover:text-white'; ?>">
                    <i class="fa-solid fa-users-viewfinder"></i>
                    <span class="lg:hidden">Akış</span>
                    <span class="hidden lg:inline">Gözlem Günlüğü</span>
                </a>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <a href="upload.php" class="bg-[#3f88ff] hover:bg-blue-600 w-8 h-8 sm:w-auto sm:h-auto sm:px-3 sm:py-1.5 rounded-full sm:rounded flex items-center justify-center text-xs sm:text-sm font-bold transition">
                    <i class="fa-solid fa-plus sm:mr-1"></i> <span class="hidden sm:inline">Gözlem Ekle</span>
                </a>
                
                <div class="relative">
                    <button onclick="toggleNotifications(event)" class="relative focus:outline-none p-1">
                        <i class="fa-regular fa-bell text-xl text-gray-400 hover:text-white transition"></i>
                        <span id="notif-badge" class="absolute top-0 right-0 bg-red-500 text-[10px] w-3 h-3 sm:w-4 sm:h-4 rounded-full flex items-center justify-center hidden">0</span>
                    </button>
                    <div id="notificationDropdown" class="absolute right-0 top-full mt-3 w-72 sm:w-80 bg-[#1c1d2b] border border-gray-700 rounded-xl shadow-2xl hidden z-50 overflow-hidden" onclick="event.stopPropagation()">
                        <div class="flex justify-between items-center p-3 border-b border-gray-800 bg-[#151621]">
                            <span class="text-sm font-bold text-gray-300">Bildirimler</span>
                        </div>
                        <div id="notif-list" class="max-h-80 overflow-y-auto custom-scrollbar">
                            <div class="text-center py-4 text-gray-500 text-xs">Yükleniyor...</div>
                        </div>
                    </div>
                </div>

                <a href="<?php echo $_SESSION['username']; ?>" class="w-8 h-8 rounded-full overflow-hidden border-2 border-transparent hover:border-[#3f88ff]">
                    <img src="<?php echo $_SESSION['profile_pic']; ?>" class="w-full h-full object-cover">
                </a>
            </div>
        </div>
    </nav>

    <div id="search-overlay" class="fixed inset-0 z-[100] bg-black/60 backdrop-blur-md hidden fade-in overflow-y-auto">
        <div class="bg-[#1c1d2b] p-4 shadow-2xl border-b border-gray-700 sticky top-0 z-10">
            <form action="index.php" method="GET" class="flex items-center gap-3 container mx-auto max-w-2xl">
                <i class="fa-solid fa-search text-[#3f88ff] text-lg"></i>
                <input type="text" name="q" id="overlay-search-input" placeholder="Kullanıcı veya içerik ara..." 
                       class="w-full bg-transparent text-white text-lg outline-none placeholder-gray-500 font-medium" autocomplete="off">
                <button type="button" onclick="closeSearchOverlay()" class="text-gray-400 hover:text-white font-bold text-sm px-2">Vazgeç</button>
            </form>
        </div>
        <div id="live-search-results" class="container mx-auto px-4 pb-10 pt-2"></div>
        <div class="w-full h-full cursor-pointer" onclick="closeSearchOverlay()"></div>
    </div>

    <div class="pt-20 container mx-auto px-4 pb-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <aside class="hidden lg:block lg:col-span-3">
                <div class="sticky top-24 space-y-4">
                    <div class="bg-[#1c1d2b] rounded-xl p-4 shadow-lg border border-gray-800">
                        <form action="index.php" method="GET" class="relative">
                            <input type="hidden" name="tab" value="<?php echo $tab; ?>">
                            <i class="fa-solid fa-search absolute left-3 top-3.5 text-gray-500"></i>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Ara..." class="w-full bg-[#0f101a] pl-10 pr-3 py-3 rounded-lg border border-gray-700 focus:border-[#3f88ff] outline-none text-sm transition">
                        </form>
                    </div>
                    <?php if($_SESSION['role_id'] <= 1): ?>
                    <div class="bg-red-900/20 border border-red-500/50 p-4 rounded-xl"><h3 class="text-red-400 font-bold mb-2 text-sm">Yönetici</h3><p class="text-xs text-gray-300">İçerik silme aktif.</p></div>
                    <?php endif; ?>
                </div>
            </aside>

            <main class="col-span-1 lg:col-span-6 space-y-6">
                
                <?php if($search_query): ?>
                    <div class="bg-[#1c1d2b] rounded-xl p-4 shadow-lg border border-[#3f88ff]/30 mb-4 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-[#3f88ff]">"<?php echo htmlspecialchars($search_query); ?>" Sonuçları</h3>
                        <a href="index.php?tab=<?php echo $tab; ?>" class="text-xs text-gray-500 hover:text-white"><i class="fa-solid fa-xmark"></i> Temizle</a>
                    </div>
                    <?php if($search_users): ?>
                        <div class="flex gap-4 overflow-x-auto pb-2 mb-2 custom-scrollbar">
                            <?php foreach($search_users as $su): ?>
                                <a href="<?php echo $su['username']; ?>" class="flex-shrink-0 flex flex-col items-center gap-1 w-16 group">
                                    <img src="<?php echo $su['profile_pic']; ?>" class="w-12 h-12 rounded-full object-cover border-2 border-gray-600 group-hover:border-[#3f88ff] transition">
                                    <div class="text-[10px] truncate w-full text-center text-gray-300 group-hover:text-white"><?php echo $su['username']; ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(count($posts) == 0 && !$search_query): ?>
                    <div class="text-center py-20 bg-[#1c1d2b] rounded-xl border border-gray-800">
                        <i class="fa-solid fa-satellite-dish text-4xl text-gray-600 mb-4"></i>
                        <p class="text-gray-400 font-bold">Gösterilecek Gözlem Yok</p>
                        <p class="text-xs text-gray-500 mt-2"><?php echo ($tab == 'feed') ? 'Henüz kimseyi takip etmiyor olabilirsiniz.' : 'Son 24 saatte paylaşım yok.'; ?></p>
                    </div>
                <?php endif; ?>

                <?php foreach($posts as $post): ?>
                <div class="bg-[#1c1d2b] rounded-xl overflow-hidden shadow-lg border border-gray-800">
                    
                    <div class="p-4 flex justify-between items-start">
                        <div class="flex gap-3">
                            <a href="<?php echo $post['username']; ?>">
                                <img src="<?php echo $post['profile_pic']; ?>" class="w-10 h-10 rounded-full bg-gray-700 hover:opacity-80 transition">
                            </a>
                            <div>
                                <a href="<?php echo $post['username']; ?>" class="font-bold text-sm hover:text-[#3f88ff] transition"><?php echo htmlspecialchars($post['full_name']); ?></a>
                                <div class="text-xs text-gray-400 flex items-center gap-1"><i class="fa-solid fa-location-dot text-[#3f88ff] text-[10px]"></i> <?php echo htmlspecialchars($post['location_text']); ?></div>
                            </div>
                        </div>
                        <?php if($_SESSION['role_id'] <= 1 || $post['user_id'] == $user_id): ?>
                            <a href="action_delete.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Silmek istiyor musun?')" class="text-gray-500 hover:text-red-500"><i class="fa-solid fa-trash"></i></a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($post['image_path'])): 
                        $q = $settings['image_quality'];
                        $img_src = $post['image_path'];
                        if ($q == 'low') {
                            $low = str_replace('.', '_low.', $post['image_path']);
                            if(file_exists($low)) $img_src = $low;
                        } elseif ($q == 'medium') {
                            $thumb = str_replace('.', '_thumb.', $post['image_path']);
                            if(file_exists($thumb)) $img_src = $thumb;
                        }
                    ?>
                    <div class="w-full bg-black">
                        <a href="view_post.php?id=<?php echo $post['id']; ?>">
                            <img src="<?php echo $img_src; ?>" loading="lazy" class="w-full max-h-[600px] object-contain hover:opacity-90 transition">
                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="p-4 pb-2">
                        <a href="view_post.php?id=<?php echo $post['id']; ?>" class="block mb-2"><h3 class="font-bold text-white hover:text-[#3f88ff] transition inline-block text-lg"><?php echo htmlspecialchars($post['title']); ?></h3></a>
                        <p class="text-sm text-gray-300 mb-2 leading-relaxed"><?php echo htmlspecialchars($post['description']); ?></p>
                        <div class="text-[#3f88ff] text-sm mb-4 font-medium"><?php echo htmlspecialchars($post['tags']); ?></div>

                        <div class="flex items-center justify-between border-t border-gray-800 pt-3 mb-3">
                            <div class="flex gap-5 text-xl">
                                <button onclick="toggleLike(<?php echo $post['id']; ?>, this)" class="transition flex items-center gap-1 <?php echo $post['is_liked'] ? 'text-red-500' : 'text-gray-400 hover:text-red-500'; ?>"><i class="<?php echo $post['is_liked'] ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i></button>
                                <a href="view_post.php?id=<?php echo $post['id']; ?>" class="text-gray-400 hover:text-[#3f88ff] transition flex items-center gap-1"><i class="fa-regular fa-comment"></i></a>
                                <button onclick="sharePost(<?php echo $post['id']; ?>)" class="text-gray-400 hover:text-green-500 transition flex items-center gap-1"><i class="fa-solid fa-share-nodes"></i></button>
                            </div>
                            <div class="text-xs font-bold text-gray-500"><span id="like-count-<?php echo $post['id']; ?>"><?php echo $post['like_count']; ?></span> Beğeni</div>
                        </div>
                        
                        <?php 
                            $stmt_c = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC LIMIT 2");
                            $stmt_c->execute([$post['id']]);
                            $comments = $stmt_c->fetchAll();
                        ?>
                        
                        <div id="comments-box-<?php echo $post['id']; ?>" class="space-y-1 mb-3 bg-[#151621] p-2 rounded-lg <?php echo empty($comments) ? 'hidden' : ''; ?>">
                            <?php foreach($comments as $c): ?>
                                <div class="text-xs truncate"><span class="font-bold text-gray-400"><?php echo $c['username']; ?></span> <span class="text-gray-500"><?php echo htmlspecialchars($c['comment']); ?></span></div>
                            <?php endforeach; ?>
                        </div>

                        <form onsubmit="submitComment(event, this)" class="flex gap-2 mb-2">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <input type="text" name="comment" placeholder="Yorumunu yaz..." class="bg-[#0f101a] border border-gray-700 rounded-full py-2 px-4 w-full text-xs outline-none focus:border-[#3f88ff] text-white">
                            <button type="submit" class="text-[#3f88ff] font-bold px-2 text-xs">Paylaş</button>
                        </form>

                    </div>
                </div>
                <?php endforeach; ?>
            </main>

            <aside class="hidden lg:block lg:col-span-3 space-y-6">
                <div class="sticky top-24 space-y-6">
                    <div class="bg-[#1c1d2b] rounded-xl p-5 shadow-lg border border-gray-800">
                        <h3 class="font-bold text-lg mb-4 text-white border-b border-gray-700 pb-2">Trend Etiketler</h3>
                        <ul class="space-y-3">
                            <?php foreach($trend_tags_final as $tag => $count): ?>
                            <li class="flex justify-between items-center group cursor-pointer">
                                <a href="index.php?q=<?php echo str_replace('#', '', $tag); ?>" class="text-gray-400 group-hover:text-[#3f88ff] transition text-sm"><?php echo $tag; ?></a>
                                <span class="text-xs bg-[#0f101a] py-1 px-2 rounded text-gray-500"><?php echo $count; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="bg-[#1c1d2b] rounded-xl p-5 shadow-lg border border-gray-800">
                        <h3 class="font-bold text-lg mb-4 text-white border-b border-gray-700 pb-2">Yükselen Gözlemciler</h3>
                        <div class="space-y-4">
                            <?php foreach($trend_users as $tu): ?>
                            <a href="<?php echo $tu['username']; ?>" class="flex items-center gap-3 group">
                                <img src="<?php echo $tu['profile_pic']; ?>" class="w-8 h-8 rounded-full bg-gray-700">
                                <div class="flex-1">
                                    <div class="text-sm font-bold group-hover:text-[#3f88ff] transition"><?php echo $tu['username']; ?></div>
                                    <div class="text-xs text-gray-400"><?php echo $tu['total_likes']; ?> beğeni</div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </aside>

        </div>
    </div>

    <script>
        // 1. Canlı Arama
        $(document).ready(function() {
            $('#overlay-search-input').on('keyup', function() {
                var query = $(this).val();
                if (query.length > 1) {
                    $.ajax({ url: 'search_ajax.php', method: 'POST', data: { q: query }, success: function(data) { $('#live-search-results').html(data); } });
                } else { $('#live-search-results').html(''); }
            });
        });

        // 2. Beğeni Sistemi
        function toggleLike(postId, btn) {
            let icon = $(btn).find('i');
            let countSpan = $('#like-count-' + postId);
            let currentCount = parseInt(countSpan.text()) || 0;

            if (icon.hasClass('fa-regular')) {
                icon.removeClass('fa-regular').addClass('fa-solid text-red-500');
                $(btn).removeClass('text-gray-400').addClass('text-red-500');
                countSpan.text(currentCount + 1);
            } else {
                icon.removeClass('fa-solid text-red-500').addClass('fa-regular');
                $(btn).removeClass('text-red-500').addClass('text-gray-400');
                countSpan.text(Math.max(0, currentCount - 1));
            }

            $.post("action_like.php", { post_id: postId }, function(data) {
               if(data.trim() == 'error') alert("Giriş yapmalısınız.");
            });
        }

        // 3. Yorum Ekleme (Anlık)
        function submitComment(e, form) {
            e.preventDefault();
            let formData = $(form).serialize();
            let inputField = $(form).find('input[name="comment"]');
            let postId = $(form).find('input[name="post_id"]').val();
            let commentContainer = $('#comments-box-' + postId);

            if(inputField.val().trim() === "") return;

            $.post("action_comment.php", formData, function(response) {
                commentContainer.removeClass('hidden');
                commentContainer.prepend(response); 
                inputField.val('');
            });
        }

        function sharePost(postId) { alert("Bağlantı kopyalandı!"); }

        // 4. Bildirim Sistemi (Güvenli)
        function toggleNotifications(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('notificationDropdown');
            const badge = document.getElementById('notif-badge');
            dropdown.classList.toggle('hidden');
            if (!dropdown.classList.contains('hidden')) {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
                badge.innerText = '0';
                $.post('mark_read.php');
                fetchNotifications();
            }
        }
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            if (!dropdown.classList.contains('hidden')) { dropdown.classList.add('hidden'); }
        });
        
        function fetchNotifications() {
            $.getJSON('fetch_notifications.php', function(data) {
                const badge = document.getElementById('notif-badge');
                const dropdown = document.getElementById('notificationDropdown');
                if(badge && dropdown) {
                    if (dropdown.classList.contains('hidden') && data.count > 0) {
                        badge.innerText = data.count;
                        badge.classList.remove('hidden');
                        badge.classList.add('flex');
                    } else if (data.count == 0) {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                    }
                    document.getElementById('notif-list').innerHTML = data.html;
                }
            })
            .fail(function() { console.log("Bildirim hatası"); }); // Hata varsa sessiz kal
        }

        // Overlay Aç/Kapa
        function openSearchOverlay() { document.getElementById('search-overlay').classList.remove('hidden'); document.getElementById('overlay-search-input').focus(); document.body.classList.add('overflow-hidden'); }
        function closeSearchOverlay() { document.getElementById('search-overlay').classList.add('hidden'); document.body.classList.remove('overflow-hidden'); document.getElementById('live-search-results').innerHTML = ''; document.getElementById('overlay-search-input').value = ''; }

        $(document).ready(function() {
            fetchNotifications();
            setInterval(fetchNotifications, 3000);
        });
    </script>
</body>
</html>