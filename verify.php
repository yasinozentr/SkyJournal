<?php
require 'db.php';

$email = $_GET['email'] ?? '';
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_input = $_POST['email'];
    $email_code = $_POST['email_code'];
    $phone_code = $_POST['phone_code'];

    // Kodları Kontrol Et
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND email_otp = ? AND phone_otp = ?");
    $stmt->execute([$email_input, $email_code, $phone_code]);
    $user = $stmt->fetch();

    if ($user) {
        // Süre kontrolü
        if (strtotime($user['otp_expiry']) < time()) {
            $error = "Kodların süresi dolmuş. Lütfen tekrar kayıt olun veya kod isteyin.";
        } else {
            // Başarılı! Hesabı onayla.
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, email_otp = NULL, phone_otp = NULL WHERE id = ?");
            $update->execute([$user['id']]);
            
            // Giriş sayfasına yönlendir
            header("Location: login.php?verified=1");
            exit;
        }
    } else {
        $error = "Girdiğiniz kodlar hatalı veya eşleşmiyor!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Doğrulama - SkySocial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0f101a] text-white flex items-center justify-center min-h-screen p-4">
    <div class="bg-[#1c1d2b] p-8 rounded-lg shadow-lg w-full max-w-md border border-gray-800">
        <h2 class="text-2xl font-bold mb-4 text-[#3f88ff] text-center">Hesabı Doğrula</h2>
        <p class="text-sm text-center text-gray-400 mb-6">
            <span class="text-white font-bold"><?php echo htmlspecialchars($email); ?></span> adresine ve telefonunuza gönderilen kodları giriniz.
            <br><span class="text-xs text-yellow-500">(Test için: debug_otp.txt dosyasına bak)</span>
        </p>

        <?php if($error): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded mb-4 text-sm border border-red-500/50 text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            
            <div>
                <label class="text-xs text-gray-500 ml-1">E-Posta Kodu</label>
                <input type="text" name="email_code" placeholder="6 Haneli Kod" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none text-center tracking-widest text-lg">
            </div>

            <div>
                <label class="text-xs text-gray-500 ml-1">SMS Kodu</label>
                <input type="text" name="phone_code" placeholder="6 Haneli Kod" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none text-center tracking-widest text-lg">
            </div>
            
            <button type="submit" class="w-full bg-[#3f88ff] p-3 rounded font-bold hover:bg-blue-600 transition">Doğrulamayı Tamamla</button>
        </form>
    </div>
</body>
</html>