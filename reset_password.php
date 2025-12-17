<?php
require 'db.php';
require 'functions.php';

$email = $_GET['email'] ?? '';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_code = $_POST['email_code'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email_post = $_POST['email']; // Hidden inputtan gelen

    // 1. Kod Kontrolü (Sadece Email kodu ile sıfırlama yapıyoruz kolaylık için, istersen SMS de ekle)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND email_otp = ?");
    $stmt->execute([$email_post, $email_code]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Şifre Kuralları Kontrolü
        if (!check_password_strength($new_password)) {
            $error = "Şifre 8-16 karakter, büyük/küçük harf, rakam ve sembol içermeli.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Şifreler uyuşmuyor.";
        } else {
            // Şifreyi Güncelle ve Kodları Temizle
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ?, email_otp = NULL, phone_otp = NULL WHERE id = ?");
            $update->execute([$hashed, $user['id']]);

            header("Location: login.php?reset=success");
            exit;
        }
    } else {
        $error = "Doğrulama kodu hatalı veya süresi dolmuş.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Şifre Belirle</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0f101a] text-white flex items-center justify-center min-h-screen p-4">
    <div class="bg-[#1c1d2b] p-8 rounded-lg shadow-lg w-full max-w-md border border-gray-800">
        <h2 class="text-2xl font-bold mb-4 text-[#3f88ff] text-center">Yeni Şifre</h2>

        <?php if($error): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded mb-4 text-sm text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            
            <div>
                <label class="text-xs text-gray-500 ml-1">Doğrulama Kodu (E-Mail)</label>
                <input type="text" name="email_code" placeholder="E-postanıza gelen kod" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none text-center tracking-widest">
            </div>

            <div class="relative">
                <input type="password" name="new_password" placeholder="Yeni Şifre" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none">
                <p class="text-[10px] text-gray-500 mt-1 pl-1">Min 8, Max 16, Aa1!</p>
            </div>
            
            <input type="password" name="confirm_password" placeholder="Yeni Şifre Tekrar" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none">
            
            <button type="submit" class="w-full bg-[#3f88ff] p-3 rounded font-bold hover:bg-blue-600 transition">Şifreyi Güncelle</button>
        </form>
    </div>
</body>
</html>