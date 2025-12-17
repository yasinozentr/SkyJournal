<?php
// functions.php - Tüm Yardımcı Fonksiyonlar

// 1. Doğrulama Kodu Gönder (Simülasyon)
function send_verification_codes($user_id, $email, $phone) {
    global $pdo;

    // 6 Haneli Rastgele Kodlar Üret
    $email_code = rand(100000, 999999);
    $phone_code = rand(100000, 999999);
    
    // Kodların geçerlilik süresi (15 dakika)
    $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    // Veritabanına kaydet
    $stmt = $pdo->prepare("UPDATE users SET email_otp = ?, phone_otp = ?, otp_expiry = ? WHERE id = ?");
    $stmt->execute([$email_code, $phone_code, $expiry, $user_id]);

    // --- SİMÜLASYON (GERÇEK API YERİNE) ---
    $log_message = "Zaman: " . date("H:i:s") . "\n";
    $log_message .= "Kullanıcı: $email ($phone)\n";
    $log_message .= "Email Kodu: $email_code\n";
    $log_message .= "SMS Kodu: $phone_code\n";
    $log_message .= "---------------------------------\n";
    
    // Ana dizine debug dosyası olarak yaz
    file_put_contents("debug_otp.txt", $log_message, FILE_APPEND);

    return true;
}

// 2. Güçlü Şifre Kontrolü
function check_password_strength($password) {
    // En az 8, en çok 16 karakter
    if (strlen($password) < 8 || strlen($password) > 16) return false;
    // En az 1 büyük harf
    if (!preg_match('/[A-Z]/', $password)) return false;
    // En az 1 küçük harf
    if (!preg_match('/[a-z]/', $password)) return false;
    // En az 1 rakam
    if (!preg_match('/[0-9]/', $password)) return false;
    // En az 1 özel karakter (!@#$%^&*)
    if (!preg_match('/[\W]/', $password)) return false;

    return true;
}

// 3. Yaş Hesaplama
function calculateAge($birthDate) {
    if (empty($birthDate)) return 0;
    $birthDate = new DateTime($birthDate);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}

// 4. Ay Evresi Hesaplama
function getMoonPhase($dateString) {
    if (empty($dateString)) return ['icon' => '', 'name' => ''];

    $year = date('Y', strtotime($dateString));
    $month = date('m', strtotime($dateString));
    $day = date('d', strtotime($dateString));

    if ($month < 3) { $year--; $month += 12; }
    ++$month;
    $c = 365.25 * $year;
    $e = 30.6 * $month;
    $jd = $c + $e + $day - 694039.09; // Julian Date
    $b = $jd / 29.5305882; // Ay Döngüsü
    $ip = $b - (int)$b; // Ondalık kısım (Evre)
    $phase = round($ip * 8); // 8 Evreye böl

    if ($phase >= 8) $phase = 0;

    // Evre İsimleri ve İkonları
    $phases = [
        0 => ['icon' => '🌑', 'name' => 'Yeni Ay'],
        1 => ['icon' => '🌒', 'name' => 'Hilal'],
        2 => ['icon' => '🌓', 'name' => 'İlk Dördün'],
        3 => ['icon' => '🌔', 'name' => 'Şişkin Ay'],
        4 => ['icon' => '🌕', 'name' => 'Dolunay'],
        5 => ['icon' => '🌖', 'name' => 'Azalan Şişkin'],
        6 => ['icon' => '🌗', 'name' => 'Son Dördün'],
        7 => ['icon' => '🌘', 'name' => 'Balsamik']
    ];
    return $phases[$phase];
}
?>