<?php
require 'db.php';
require 'functions.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];
$msg = ""; $error = "";

// Mevcut bilgileri çek
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = trim($_POST['username']);
    $new_fullname = trim($_POST['full_name']);
    $new_email = trim($_POST['email']);
    $new_phone = trim($_POST['phone']);
    $new_bio = trim($_POST['bio']);
    $new_location = trim($_POST['location']);
    $new_birth = $_POST['birth_date'];

    // 1. YAŞ KONTROLÜ
    if (calculateAge($new_birth) < 15) {
        $error = "Üzgünüz, 15 yaşından küçükler kayıt olamaz.";
    } 
    else {
        // --- 2. KULLANICI ADI DEĞİŞİKLİĞİ (30 GÜN) ---
        if ($new_username != $user['username']) {
            $last_change = $user['last_username_change'] ? strtotime($user['last_username_change']) : 0;
            $diff = time() - $last_change;
            if ($diff < (30 * 24 * 60 * 60)) { // 30 Gün
                $days_left = 30 - floor($diff / (24 * 60 * 60));
                $error = "Kullanıcı adını değiştirmek için $days_left gün daha beklemelisin.";
            } else {
                // Müsait mi kontrol et
                $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $check->execute([$new_username, $user_id]);
                if ($check->rowCount() > 0) {
                    $error = "Bu kullanıcı adı alınmış.";
                } else {
                    // Güncelle ve Zamanı Kaydet
                    $pdo->prepare("UPDATE users SET username = ?, last_username_change = NOW() WHERE id = ?")->execute([$new_username, $user_id]);
                    $_SESSION['username'] = $new_username; // Session güncelle
                }
            }
        }

        // --- 3. AD SOYAD DEĞİŞİKLİĞİ (7 GÜN) ---
        if ($new_fullname != $user['full_name'] && empty($error)) {
            $last_change = $user['last_fullname_change'] ? strtotime($user['last_fullname_change']) : 0;
            $diff = time() - $last_change;
            if ($diff < (7 * 24 * 60 * 60)) { // 7 Gün
                $days_left = 7 - floor($diff / (24 * 60 * 60));
                $error = "Adını değiştirmek için $days_left gün daha beklemelisin.";
            } else {
                $pdo->prepare("UPDATE users SET full_name = ?, last_fullname_change = NOW() WHERE id = ?")->execute([$new_fullname, $user_id]);
            }
        }

        // --- 4. STANDART GÜNCELLEMELER (Bio, Konum, Doğum Tarihi) ---
        if (empty($error)) {
            // Profil Fotoğrafı
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
                $target = "uploads/p_" . $user_id . "_" . time() . ".jpg";
                move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target);
                $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")->execute([$target, $user_id]);
                $_SESSION['profile_pic'] = $target;
            }

            // Diğer veriler
            $pdo->prepare("UPDATE users SET bio = ?, location = ?, birth_date = ? WHERE id = ?")
                ->execute([$new_bio, $new_location, $new_birth, $user_id]);
            
            $msg = "Profil bilgileri güncellendi.";

            // --- 5. KRİTİK DEĞİŞİKLİK (Mail/Tel) - DOĞRULAMA GEREKTİRİR ---
            if ($new_email != $user['email'] || $new_phone != $user['phone']) {
                $otp = rand(100000, 999999);
                // Geçici olarak kaydet
                $stmt = $pdo->prepare("UPDATE users SET pending_email = ?, pending_phone = ?, update_otp = ? WHERE id = ?");
                $stmt->execute([$new_email, $new_phone, $otp, $user_id]);

                // Simülasyon: Dosyaya yaz
                file_put_contents("debug_otp_update.txt", "Kullanıcı: $new_username\nKod: $otp\nMail: $new_email\nTel: $new_phone");

                // Doğrulama sayfasına at
                header("Location: verify_update.php");
                exit;
            }
        }
    }
    
    // Verileri tazelemek için tekrar çek
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profili Düzenle</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0f101a] text-white font-sans min-h-screen p-4">
    <div class="max-w-xl mx-auto bg-[#1c1d2b] rounded-2xl shadow-xl overflow-hidden border border-gray-800">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center">
            <h2 class="font-bold text-lg text-[#3f88ff]">Profili Düzenle</h2>
            <a href="profile.php" class="text-sm text-gray-400 hover:text-white">İptal</a>
        </div>
        
        <div class="p-6">
            <?php if($error): ?><div class="bg-red-500/20 text-red-400 p-3 rounded mb-4 text-sm text-center border border-red-500/50"><?php echo $error; ?></div><?php endif; ?>
            <?php if($msg): ?><div class="bg-green-500/20 text-green-400 p-3 rounded mb-4 text-sm text-center border border-green-500/50"><?php echo $msg; ?></div><?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-5">
                
                <div class="flex items-center gap-4">
                    <img src="<?php echo $user['profile_pic']; ?>" class="w-16 h-16 rounded-full object-cover border-2 border-gray-600">
                    <label class="cursor-pointer bg-[#0f101a] border border-gray-700 hover:border-[#3f88ff] text-xs py-2 px-4 rounded transition">
                        Fotoğraf Değiştir <input type="file" name="profile_pic" class="hidden" accept="image/*">
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500 ml-1">Kullanıcı Adı (30 günde 1)</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded-lg text-white text-sm focus:border-[#3f88ff] outline-none">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 ml-1">Ad Soyad (7 günde 1)</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded-lg text-white text-sm focus:border-[#3f88ff] outline-none">
                    </div>
                </div>

                <div>
                    <label class="text-xs text-gray-500 ml-1">Doğum Tarihi (Gizli - Ay Evresi Görünür)</label>
                    <input type="date" name="birth_date" value="<?php echo $user['birth_date']; ?>" required class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded-lg text-white text-sm focus:border-[#3f88ff] outline-none scheme-dark">
                </div>

                <div>
                    <label class="text-xs text-gray-500 ml-1">Biyografi</label>
                    <textarea name="bio" rows="3" class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded-lg text-white text-sm focus:border-[#3f88ff] outline-none"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                </div>

                <div>
                    <label class="text-xs text-gray-500 ml-1">Konum</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded-lg text-white text-sm focus:border-[#3f88ff] outline-none">
                </div>

                <div class="p-4 bg-[#151621] rounded-xl border border-gray-800">
                    <p class="text-xs text-yellow-500 mb-2"><i class="fa-solid fa-triangle-exclamation"></i> Bu bilgileri değiştirirsen doğrulama kodu istenir.</p>
                    <div class="space-y-3">
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded-lg text-white text-sm focus:border-[#3f88ff] outline-none">
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="w-full p-3 bg-[#0f101a] border border-gray-700 rounded-lg text-white text-sm focus:border-[#3f88ff] outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#3f88ff] hover:bg-blue-600 text-white font-bold py-3 rounded-lg transition shadow-lg">Kaydet</button>
            </form>
        </div>
    </div>
</body>
</html>