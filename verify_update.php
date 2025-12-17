<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['code'];
    
    // Kodu kontrol et
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND update_otp = ?");
    $stmt->execute([$user_id, $code]);
    $user = $stmt->fetch();

    if ($user) {
        // Kod doğru! Geçici bilgileri asıl bilgi yap.
        $new_email = $user['pending_email'];
        $new_phone = $user['pending_phone'];

        $update = $pdo->prepare("UPDATE users SET email = ?, phone = ?, pending_email = NULL, pending_phone = NULL, update_otp = NULL WHERE id = ?");
        $update->execute([$new_email, $new_phone, $user_id]);

        header("Location: profile.php?msg=contact_updated");
        exit;
    } else {
        $error = "Hatalı kod girdiniz.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doğrulama</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0f101a] text-white flex items-center justify-center min-h-screen p-4">
    <div class="bg-[#1c1d2b] p-8 rounded-lg shadow-lg w-full max-w-md border border-gray-800">
        <h2 class="text-2xl font-bold mb-4 text-[#3f88ff] text-center">Değişikliği Onayla</h2>
        <p class="text-sm text-center text-gray-400 mb-6">
            İletişim bilgilerinizi değiştirmek için gönderilen kodu giriniz.
            <br><span class="text-xs text-yellow-500">(Test: debug_otp_update.txt dosyasına bak)</span>
        </p>

        <?php if($error): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded mb-4 text-sm text-center border border-red-500/50"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="text" name="code" placeholder="6 Haneli Kod" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none text-center tracking-widest text-lg">
            
            <button type="submit" class="w-full bg-[#3f88ff] p-3 rounded font-bold hover:bg-blue-600 transition">Onayla</button>
            <a href="profile.php" class="block text-center text-sm text-gray-500 mt-2 hover:text-white">İptal</a>
        </form>
    </div>
</body>
</html>