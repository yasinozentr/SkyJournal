<?php
require 'db.php';

if (!isset($_GET['id'])) { header("Location: index.php"); exit; }
$post_id = $_GET['id'];
$user_id = $_SESSION['user_id'] ?? 0;
$my_role = $_SESSION['role_id'] ?? 2;

$stmt = $pdo->prepare("SELECT p.*, u.username, u.full_name, u.profile_pic, (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count, (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$user_id, $post_id]);
$post = $stmt->fetch();
if (!$post) { echo "Gözlem bulunamadı!"; exit; }

$post_owner_id = $post['user_id'];
$has_image = !empty($post['image_path']); 
$grid_layout = $has_image ? "grid-cols-1 lg:grid-cols-2" : "grid-cols-1 max-w-3xl mx-auto";

$sql_comments = "SELECT c.*, u.username, u.full_name, u.profile_pic FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.is_pinned DESC, c.created_at ASC";
$stmt_c = $pdo->prepare($sql_comments);
$stmt_c->execute([$post_id]);
$comments = $stmt_c->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title><?php echo htmlspecialchars($post['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>.custom-scrollbar::-webkit-scrollbar { width: 6px; } .custom-scrollbar::-webkit-scrollbar-track { background: #1c1d2b; } .custom-scrollbar::-webkit-scrollbar-thumb { background: #3f88ff; border-radius: 10px; } #lightbox-img { cursor: grab; transition: transform 0.1s ease-out; transform-origin: center center; } #lightbox-img:active { cursor: grabbing; }</style>
</head>
<body class="bg-[#0f101a] text-white font-sans min-h-screen">
    <nav class="fixed top-0 w-full h-16 bg-[#1c1d2b] border-b border-gray-800 z-50 flex items-center justify-between px-4">
        <a href="index.php" class="flex items-center gap-2 text-gray-400 hover:text-white"><i class="fa-solid fa-arrow-left text-xl"></i></a><div class="font-bold text-lg tracking-wide">Gözlem Detayı</div><div class="w-6"></div>
    </nav>

    <div class="pt-20 container mx-auto px-4 pb-10">
        <div class="grid <?php echo $grid_layout; ?> gap-8 bg-[#1c1d2b] rounded-2xl overflow-hidden shadow-2xl border border-gray-800">
            <?php if ($has_image): ?>
            <div class="bg-black flex items-center justify-center bg-[#050505] lg:h-[85vh] relative group overflow-hidden"><img src="<?php echo $post['image_path']; ?>" onclick="openLightbox(this.src)" class="max-h-[50vh] lg:max-h-full w-full object-contain cursor-zoom-in hover:opacity-95 transition"></div>
            <?php endif; ?>

            <div class="p-4 flex flex-col h-[85vh]">
                <div class="flex items-center justify-between mb-4 border-b border-gray-800 pb-4">
                    <a href="profile.php?username=<?php echo $post['username']; ?>" class="flex items-center gap-3"><img src="<?php echo $post['profile_pic']; ?>" class="w-10 h-10 rounded-full border border-gray-600"><div><div class="font-bold hover:text-[#3f88ff] transition text-sm"><?php echo $post['full_name']; ?></div><?php if ($post['user_id'] == $post_owner_id): ?><div class="text-xs text-[#3f88ff] font-bold">Üretici</div><?php endif; ?></div></a>
                    <?php if($my_role <= 1 || $post['user_id'] == $user_id): ?><a href="action_delete.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Silmek istiyor musun?')" class="text-gray-500 hover:text-red-500"><i class="fa-solid fa-trash"></i></a><?php endif; ?>
                </div>
                <div class="mb-4 overflow-y-auto max-h-48 custom-scrollbar pr-2"><h1 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($post['title']); ?></h1><p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars($post['description']); ?></p><div class="mt-4 flex flex-wrap gap-2 text-xs text-gray-400"><span><i class="fa-regular fa-clock mr-1"></i> <?php echo date("d M Y H:i", strtotime($post['captured_at'])); ?></span><?php if($post['location_text']): ?><span><i class="fa-solid fa-location-dot mr-1 ml-2"></i> <?php echo htmlspecialchars($post['location_text']); ?></span><?php endif; ?></div><div class="mt-2 text-[#3f88ff] text-xs font-medium"><?php echo htmlspecialchars($post['tags']); ?></div></div>

                <div id="comments-container" class="flex-1 overflow-y-auto border-t border-gray-800 pt-4 space-y-4 pr-1 custom-scrollbar">
                    <?php if (empty($comments)) { echo '<div id="no-comments" class="text-center text-gray-500 text-sm mt-10">Henüz yorum yok.</div>'; }
                    foreach ($comments as $comment) {
                        $is_comment_author = ($comment['user_id'] == $user_id); $is_post_author = ($user_id == $post_owner_id); $is_admin = ($my_role <= 1);
                        ?>
                        <div id="comment-<?php echo $comment['id']; ?>" class="flex gap-3 relative group <?php echo $comment['is_pinned'] ? 'bg-[#3f88ff]/10 p-2 rounded-lg border border-[#3f88ff]/30' : ''; ?>">
                            <?php if($comment['is_pinned']): ?><div class="absolute -right-1 -top-2 text-[#3f88ff] text-xs bg-[#1c1d2b] p-1 rounded-full border border-[#3f88ff]"><i class="fa-solid fa-thumbtack"></i></div><?php endif; ?>
                            <a href="profile.php?username=<?php echo $comment['username']; ?>" class="shrink-0"><img src="<?php echo $comment['profile_pic']; ?>" class="w-8 h-8 rounded-full"></a>
                            <div class="flex-1">
                                <div class="bg-[#252736] p-2.5 rounded-xl rounded-tl-none relative">
                                    <div class="flex items-center justify-between mb-1"><div class="flex items-center gap-2"><span class="font-bold text-xs text-white"><?php echo $comment['username']; ?></span><?php if($comment['user_id'] == $post_owner_id): ?><span class="bg-[#3f88ff]/20 text-[#3f88ff] text-[9px] px-1.5 py-0.5 rounded font-bold">Üretici</span><?php endif; ?></div><span class="text-[10px] text-gray-500"><?php echo date("d M H:i", strtotime($comment['created_at'])); ?></span></div>
                                    <p class="text-sm text-gray-300"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                </div>
                                <div class="flex items-center gap-3 mt-1 ml-1 text-xs text-gray-500">
                                    <?php if($is_comment_author || $is_post_author || $is_admin): ?><button onclick="deleteComment(<?php echo $comment['id']; ?>)" class="hover:text-red-500 transition"><i class="fa-solid fa-trash"></i> Sil</button><?php endif; ?>
                                    <?php if($is_post_author): ?><?php if($comment['is_pinned']): ?><button onclick="pinComment(<?php echo $comment['id']; ?>, 'unpin')" class="hover:text-yellow-500 transition text-[#3f88ff]"><i class="fa-solid fa-thumbtack-slash"></i> Kaldır</button><?php else: ?><button onclick="pinComment(<?php echo $comment['id']; ?>, 'pin')" class="hover:text-[#3f88ff] transition"><i class="fa-solid fa-thumbtack"></i> Sabitle</button><?php endif; ?><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    } ?>
                </div>

                <div class="mt-4 border-t border-gray-800 pt-4 bg-[#1c1d2b]">
                     <div class="flex items-center gap-4 mb-3"><button onclick="togglePostLike(<?php echo $post['id']; ?>, this)" class="text-2xl transition <?php echo $post['is_liked'] ? 'text-red-500' : 'text-gray-400 hover:text-red-500'; ?>"><i class="<?php echo $post['is_liked'] ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i></button><div class="font-bold text-sm"><span id="post-like-count"><?php echo $post['like_count']; ?></span> Beğeni</div></div>
                    <form onsubmit="submitComment(event, this)" class="flex gap-2"><input type="hidden" name="post_id" value="<?php echo $post['id']; ?>"><input type="hidden" name="is_ajax" value="1"><input type="text" name="comment" placeholder="Yorumunu yaz..." class="bg-[#0f101a] border border-gray-700 rounded-full py-3 px-4 w-full text-sm outline-none focus:border-[#3f88ff]"><button type="submit" class="text-[#3f88ff] font-bold px-4">Paylaş</button></form>
                </div>
            </div>
        </div>
    </div>

    <div id="lightbox" class="fixed inset-0 z-[100] bg-black/90 hidden flex flex-col justify-center items-center overflow-hidden"><div class="absolute top-4 right-4 flex gap-4 z-[101]"><button onclick="zoomOut()" class="bg-gray-800 hover:bg-gray-700 text-white w-10 h-10 rounded-full flex items-center justify-center"><i class="fa-solid fa-minus"></i></button><button onclick="zoomIn()" class="bg-gray-800 hover:bg-gray-700 text-white w-10 h-10 rounded-full flex items-center justify-center"><i class="fa-solid fa-plus"></i></button><button onclick="resetZoom()" class="bg-gray-800 hover:bg-gray-700 text-white w-10 h-10 rounded-full flex items-center justify-center"><i class="fa-solid fa-compress"></i></button><button onclick="closeLightbox()" class="bg-red-600 hover:bg-red-700 text-white w-10 h-10 rounded-full flex items-center justify-center"><i class="fa-solid fa-xmark"></i></button></div><div id="lightbox-container" class="w-full h-full flex items-center justify-center overflow-hidden" onmousedown="startDrag(event)" onmouseup="endDrag()" onmousemove="drag(event)"><img id="lightbox-img" src="" class="max-w-none max-h-none shadow-2xl"></div></div>
    <script>
        function togglePostLike(postId, btn) { $(btn).prop('disabled', true); $.post("action_like.php", { post_id: postId }, function(data) { let response = data.trim(); let icon = $(btn).find('i'); let countSpan = $('#post-like-count'); let currentCount = parseInt(countSpan.text()) || 0; if (response === "liked") { icon.removeClass('fa-regular').addClass('fa-solid text-red-500'); $(btn).removeClass('text-gray-400').addClass('text-red-500'); countSpan.text(currentCount + 1); } else if (response === "unliked") { icon.removeClass('fa-solid text-red-500').addClass('fa-regular'); $(btn).removeClass('text-red-500').addClass('text-gray-400'); countSpan.text(Math.max(0, currentCount - 1)); } }).always(function() { $(btn).prop('disabled', false); }); }
        function submitComment(e, form) { e.preventDefault(); let formData = $(form).serialize(); let inputField = $(form).find('input[name="comment"]'); let commentContainer = $('#comments-container'); let noCommentsText = $('#no-comments'); if(inputField.val().trim() === "") return; $.post("action_comment.php", formData, function(response) { if(noCommentsText.length > 0) noCommentsText.remove(); commentContainer.append(response); inputField.val(''); commentContainer.scrollTop(commentContainer.prop("scrollHeight")); }); }
        function deleteComment(commentId) { if(!confirm("Yorumu silmek istediğine emin misin?")) return; $.post("action_comment_ops.php", { action: 'delete', comment_id: commentId }, function(data) { if(data.trim() == 'deleted') $('#comment-' + commentId).fadeOut(300, function(){ $(this).remove(); }); else alert("Silme başarısız."); }); }
        function pinComment(commentId, action) { $.post("action_comment_ops.php", { action: action, comment_id: commentId }, function(data) { if(data.trim() == 'pinned' || data.trim() == 'unpinned') location.reload(); }); }
        let scale = 1, panning = false, pointX = 0, pointY = 0, startX = 0, startY = 0; let img = document.getElementById("lightbox-img"); function openLightbox(src) { document.getElementById("lightbox").classList.remove("hidden"); document.body.classList.add("overflow-hidden"); img.src = src; resetZoom(); } function closeLightbox() { document.getElementById("lightbox").classList.add("hidden"); document.body.classList.remove("overflow-hidden"); } function updateTransform() { img.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`; } function zoomIn() { scale += 0.2; updateTransform(); } function zoomOut() { if(scale > 0.4) scale -= 0.2; updateTransform(); } function resetZoom() { scale = 1; pointX = 0; pointY = 0; img.style.maxWidth = "90vw"; img.style.maxHeight = "90vh"; updateTransform(); } document.getElementById('lightbox-container').addEventListener('wheel', function(e) { e.preventDefault(); if (e.deltaY < 0) zoomIn(); else zoomOut(); }); function startDrag(e) { if(scale > 1) { e.preventDefault(); panning = true; startX = e.clientX - pointX; startY = e.clientY - pointY; img.style.cursor = "grabbing"; } } function endDrag() { panning = false; img.style.cursor = "grab"; } function drag(e) { if (panning) { e.preventDefault(); pointX = e.clientX - startX; pointY = e.clientY - startY; updateTransform(); } }
    </script>
</body>
</html>