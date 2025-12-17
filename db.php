<?php
// db.php
$host = 'localhost';
$dbname = 'sky_social';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

session_start();

// --- 1. VARSAYILAN AYARLAR ---
$settings = [
    'theme_bg' => '#0f101a',
    'theme_surface' => '#1c1d2b',
    'theme_primary' => '#3f88ff',
    'image_quality' => 'medium',
    'language' => 'tr' // Varsayılanı direkt TR yaptık
];

// --- 2. KULLANICI AYARLARINI ÇEK ---
// (Burada veritabanında 'en' veya 'auto' yazsa bile aşağıda TR'yi zorlayacağız)
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT theme_bg, theme_surface, theme_primary, image_quality FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user_settings) {
        foreach ($settings as $key => $val) {
            if (!empty($user_settings[$key])) {
                $settings[$key] = $user_settings[$key];
            }
        }
    }
}

// --- 3. DİL AYARLARI (SADECE TÜRKÇE) ---
// Otomatik algılama kodlarını sildim.
$lang_code = 'tr'; 

$translations = [
    'tr' => [
        'today' => 'Bugün', 'today_full' => 'Bugün Gökyüzünde',
        'feed' => 'Akış', 'feed_full' => 'Gözlem Günlüğü',
        'upload' => 'Gözlem Ekle', 'settings' => 'Ayarlar', 'logout' => 'Çıkış',
        'theme' => 'Tema Renkleri', 'quality' => 'Görsel Kalitesi', 'language' => 'Dil',
        'save' => 'Kaydet', 'bg' => 'Arka Plan', 'surface' => 'Kartlar', 'primary' => 'Vurgu',
        'low' => 'Düşük (Hızlı)', 'medium' => 'Orta (Dengeli)', 'high' => 'Yüksek (Orijinal)'
    ]
    // İngilizce dizisini de sildim, gerek kalmadı.
];

// Her zaman TR yüklenir
$L = $translations['tr'];

// --- 4. CSS DEĞİŞKENLERİ ---
echo "<style>
    :root { --bg-main: {$settings['theme_bg']}; --bg-surface: {$settings['theme_surface']}; --color-primary: {$settings['theme_primary']}; }
    .bg-\[\#0f101a\] { background-color: var(--bg-main) !important; }
    .bg-\[\#1c1d2b\] { background-color: var(--bg-surface) !important; }
    .text-\[\#3f88ff\] { color: var(--color-primary) !important; }
    .bg-\[\#3f88ff\] { background-color: var(--color-primary) !important; }
    .border-\[\#3f88ff\] { border-color: var(--color-primary) !important; }
    .hover\:text-\[\#3f88ff\]:hover { color: var(--color-primary) !important; }
</style>";
?>