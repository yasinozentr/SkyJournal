<?php
require 'db.php';
require 'functions.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST['input']; // Email veya Telefon

    // Kullanıcıyı bul
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$input, $input]);
    $user = $stmt->fetch();

    if ($user) {
        // Yeni kodlar üret ve gönder
        send_verification_codes($user['id'], $user['email'], $user['phone']);
        
        // Şifre sıfırlama ekranına yönlendir
        header("Location: reset_password.php?email=" . urlencode($user['email']));
        exit;
    } else {
        $message = "Bu bilgiye ait kullanıcı bulunamadı.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifremi Unuttum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0f101a] text-white flex items-center justify-center min-h-screen p-4">
    <div class="bg-[#1c1d2b] p-8 rounded-lg shadow-lg w-full max-w-md border border-gray-800">
        <h2 class="text-2xl font-bold mb-4 text-[#3f88ff] text-center">Şifremi Unuttum</h2>
        
        <?php if($message): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded mb-4 text-sm text-center"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <p class="text-sm text-gray-400 text-center mb-2">Kayıtlı E-Posta adresinizi veya Telefon numaranızı girin.</p>
            <input type="text" name="input" placeholder="E-Mail veya Telefon" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none">
            <button type="submit" class="w-full bg-[#3f88ff] p-3 rounded font-bold hover:bg-blue-600 transition">Kod Gönder</button>
        </form>
        <div class="mt-4 text-center">
            <a href="login.php" class="text-sm text-gray-500 hover:text-white">Geri Dön</a>
        </div>
    </div>
</body>
</html>