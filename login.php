<?php
require 'db.php';

// Oturum açık mı kontrol et
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

// URL'den gelen mesajları yakala
if (isset($_GET['verified']) && $_GET['verified'] == 1) {
    $success = "Hesabınız başarıyla doğrulandı! Şimdi giriş yapabilirsiniz.";
}
if (isset($_GET['reset']) && $_GET['reset'] == 'success') {
    $success = "Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapın.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = trim($_POST['login_input']); // Kullanıcı Adı, Mail veya Telefon
    $password = $_POST['password'];

    // 1. Kullanıcıyı bul
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR phone = ?");
    $stmt->execute([$login_input, $login_input, $login_input]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Şifreyi kontrol et
        if (password_verify($password, $user['password'])) {
            
            // 3. BAN KONTROLÜ (YENİ)
            if ($user['is_banned'] == 1) {
                $error = "Hesabınız yönetici tarafından askıya alınmıştır. Giriş yapamazsınız.";
            }
            // 4. DOĞRULAMA KONTROLÜ
            elseif ($user['is_verified'] == 1) {
                // Giriş Başarılı -> Session Başlat
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['location'] = $user['location'];
                $_SESSION['profile_pic'] = $user['profile_pic'];

                header("Location: index.php");
                exit;
            } else {
                // Şifre doğru ama hesap onaylı değil
                $error = "Hesabınız henüz doğrulanmamış. <a href='verify.php?email=".$user['email']."' class='text-[#3f88ff] underline hover:text-blue-400'>Buraya tıklayarak doğrulayın.</a>";
            }

        } else {
            $error = "Girdiğiniz şifre hatalı!";
        }
    } else {
        $error = "Bu bilgilere sahip bir kullanıcı bulunamadı.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - SkySocial</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-[#0f101a] text-white flex items-center justify-center min-h-screen p-4">

    <div class="bg-[#1c1d2b] p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-800">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#252736] mb-4">
                <i class="fa-solid fa-moon text-[#3f88ff] text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-white tracking-wide">Tekrar Hoşgeldin!</h2>
            <p class="text-gray-400 text-sm mt-2">SkySocial evrenine giriş yap.</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-6 text-sm border border-red-500/30 text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="bg-green-500/20 text-green-400 p-3 rounded-lg mb-6 text-sm border border-green-500/30 text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-5">
            
            <div class="relative">
                <i class="fa-regular fa-user absolute left-4 top-4 text-gray-500"></i>
                <input type="text" name="login_input" placeholder="Kullanıcı Adı, Mail veya Telefon" required 
                       class="w-full pl-12 pr-4 py-3 bg-[#0f101a] border border-gray-700 rounded-xl text-white focus:border-[#3f88ff] outline-none transition placeholder-gray-600">
            </div>

            <div>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-4 top-4 text-gray-500"></i>
                    
                    <input type="password" name="password" id="loginPassword" placeholder="Şifre" required 
                           class="w-full pl-12 pr-12 py-3 bg-[#0f101a] border border-gray-700 rounded-xl text-white focus:border-[#3f88ff] outline-none transition placeholder-gray-600">
                    
                    <button type="button" onclick="togglePassword()" class="absolute right-4 top-3.5 text-gray-500 hover:text-white transition focus:outline-none">
                        <i id="eyeIcon" class="fa-solid fa-eye"></i>
                    </button>
                </div>
                
                <div class="text-right mt-2">
                    <a href="forgot_password.php" class="text-xs text-gray-400 hover:text-[#3f88ff] transition">Şifremi Unuttum?</a>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-[#3f88ff] hover:bg-blue-600 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-500/30 transition active:scale-95">
                Giriş Yap
            </button>
        </form>

        <div class="mt-8 text-center border-t border-gray-800 pt-6">
            <p class="text-gray-400 text-sm">Hesabın yok mu? <a href="register.php" class="text-[#3f88ff] font-bold hover:underline">Hemen Kayıt Ol</a></p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('loginPassword');
            const icon = document.getElementById('eyeIcon');
            
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