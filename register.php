<?php
require 'db.php';
require 'functions.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $location = trim($_POST['location']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (!check_password_strength($password)) {
        $error = "Şifre 8-16 karakter olmalı; Büyük harf, küçük harf, rakam ve özel karakter içermelidir.";
    } elseif ($password !== $password_confirm) {
        $error = "Şifreler eşleşmiyor!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $profile_pic = 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=3f88ff&color=fff';

        $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, phone, password, location, profile_pic, role_id, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 2, 0)");
        
        try {
            $stmt->execute([$username, $full_name, $email, $phone, $hashed_password, $location, $profile_pic]);
            $user_id = $pdo->lastInsertId();
            send_verification_codes($user_id, $email, $phone);
            header("Location: verify.php?email=" . urlencode($email));
            exit;
        } catch (PDOException $e) {
            $error = "Kayıt hatası (Bilgiler kullanılıyor olabilir).";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol - Güvenli</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-[#0f101a] text-white flex items-center justify-center min-h-screen p-4">
    <div class="bg-[#1c1d2b] p-8 rounded-lg shadow-lg w-full max-w-md border border-gray-800">
        <h2 class="text-2xl font-bold mb-2 text-[#3f88ff] text-center">Hesap Oluştur</h2>
        <p class="text-xs text-center text-gray-500 mb-6">Telefon ve E-posta doğrulaması zorunludur.</p>
        
        <?php if($error): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded mb-4 text-sm border border-red-500/50 text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <input type="text" name="username" placeholder="Kullanıcı Adı" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none">
            <input type="text" name="full_name" placeholder="Ad Soyad" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none">
            <div class="grid grid-cols-2 gap-4">
                <input type="email" name="email" placeholder="E-Mail" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none">
                <input type="text" name="phone" placeholder="Telefon (5XX...)" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none">
            </div>
            <input type="text" name="location" placeholder="Konum" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none">
            
            <div class="relative">
                <input type="password" name="password" id="regPassword" placeholder="Şifre" required class="w-full p-3 pr-10 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none">
                <button type="button" onclick="togglePassword('regPassword', 'eyeIcon1')" class="absolute right-3 top-3.5 text-gray-500 hover:text-white focus:outline-none">
                    <i id="eyeIcon1" class="fa-solid fa-eye"></i>
                </button>
                <p class="text-[10px] text-gray-500 mt-1 pl-1">Min 8, Max 16 karakter. Büyük/Küçük harf, rakam ve sembol.</p>
            </div>

            <div class="relative">
                <input type="password" name="password_confirm" id="regPasswordConfirm" placeholder="Şifre Tekrar" required class="w-full p-3 pr-10 bg-[#0f101a] border border-gray-700 rounded text-white focus:border-[#3f88ff] outline-none">
                <button type="button" onclick="togglePassword('regPasswordConfirm', 'eyeIcon2')" class="absolute right-3 top-3.5 text-gray-500 hover:text-white focus:outline-none">
                    <i id="eyeIcon2" class="fa-solid fa-eye"></i>
                </button>
            </div>
            
            <button type="submit" class="w-full bg-[#3f88ff] p-3 rounded font-bold hover:bg-blue-600 transition">Devam Et</button>
        </form>
        <p class="mt-4 text-center text-gray-400 text-sm">Zaten hesabın var mı? <a href="login.php" class="text-[#3f88ff]">Giriş Yap</a></p>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>