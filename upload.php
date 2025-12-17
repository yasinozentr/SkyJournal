<?php
// --- 1. PERFORMANS AYARLARI ---
ini_set('memory_limit', '-1');
set_time_limit(0); 
ini_set('max_execution_time', 0);

require 'db.php';

// Resim Sıkıştırma Fonksiyonu
function createResizedImage($sourcePath, $targetPath, $fileType, $quality, $maxWidth) {
    if (!extension_loaded('gd')) return false;

    if ($fileType == 'jpg' || $fileType == 'jpeg') $image = @imagecreatefromjpeg($sourcePath);
    elseif ($fileType == 'png') $image = @imagecreatefrompng($sourcePath);
    else return false;

    if (!$image) return false;

    $width = imagesx($image);
    $height = imagesy($image);

    if ($width > $maxWidth) {
        $ratio = $maxWidth / $width;
        $newHeight = $height * $ratio;
        $newWidth = $maxWidth;
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    if ($fileType == 'png') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }

    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if ($fileType == 'png') {
        $pngQuality = 9 - round(($quality / 100) * 9);
        imagepng($newImage, $targetPath, $pngQuality); 
    } else {
        imagejpeg($newImage, $targetPath, $quality); 
    }

    imagedestroy($image);
    imagedestroy($newImage);
    return true;
}

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location_text = $_POST['location'];
    $captured_at = $_POST['captured_at'];
    
    // --- ETİKET DÜZENLEME (YENİ KISIM) ---
    $raw_tags = $_POST['tags'];
    
    // 1. Virgülleri boşluğa çevir (Kullanıcı virgül kullanırsa diye)
    $raw_tags = str_replace(',', ' ', $raw_tags);
    
    // 2. Fazla boşlukları temizle ve kelimelere ayır
    $raw_tags = preg_replace('/\s+/', ' ', trim($raw_tags));
    $words = explode(' ', $raw_tags);
    
    $formatted_tags = [];
    foreach ($words as $word) {
        if(!empty($word)) {
            // Başında # var mı kontrol et, yoksa ekle
            if (strpos($word, '#') === 0) {
                $formatted_tags[] = $word;
            } else {
                $formatted_tags[] = '#' . $word;
            }
        }
    }
    // Tekrar birleştir (Örn: #ay #yıldız #gece)
    $tags = implode(' ', $formatted_tags);
    // -------------------------------------

    $user_id = $_SESSION['user_id'];
    $target_file = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $base = time() . "_" . uniqid();
        $file_orig = $target_dir . $base . "." . $ext;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $file_orig)) {
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                createResizedImage($file_orig, $target_dir . $base . "_thumb." . $ext, $ext, 40, 800);
                createResizedImage($file_orig, $target_dir . $base . "_low." . $ext, $ext, 10, 300);
            } else {
                copy($file_orig, $target_dir . $base . "_thumb." . $ext);
                copy($file_orig, $target_dir . $base . "_low." . $ext);
            }
            $target_file = $file_orig;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, description, image_path, location_text, captured_at, tags) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $description, $target_file, $location_text, $captured_at, $tags]);
    
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gözlem Ekle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style> 
        ::-webkit-calendar-picker-indicator { filter: invert(1); opacity: 0.6; } 
        .fa-spin-fast { animation: fa-spin 1s infinite linear; } 
    </style>
</head>
<body class="bg-[#0f101a] text-white font-sans min-h-screen">

    <div id="loadingOverlay" class="fixed inset-0 bg-black/90 z-[100] hidden flex flex-col items-center justify-center text-center">
        <div class="relative">
            <i class="fa-solid fa-earth-americas text-6xl text-[#3f88ff] animate-spin mb-4"></i>
        </div>
        <h2 class="text-xl font-bold text-white mt-4">Gözlem Yükleniyor...</h2>
        <p class="text-gray-400 text-sm mt-2 max-w-xs">Yüksek çözünürlüklü fotoğrafları işlemek biraz zaman alabilir. Lütfen bekleyin.</p>
    </div>

    <nav class="fixed top-0 w-full h-16 bg-[#1c1d2b] border-b border-gray-800 z-50 flex items-center justify-between px-4">
        <a href="index.php" class="text-gray-400 hover:text-white flex items-center gap-2"><i class="fa-solid fa-arrow-left text-lg"></i><span class="text-sm font-medium">İptal</span></a>
        <div class="font-bold text-lg tracking-wide">Yeni Gözlem</div><div class="w-10"></div>
    </nav>

    <div class="pt-20 pb-10 container mx-auto px-4 max-w-2xl">
        <form id="uploadForm" method="POST" enctype="multipart/form-data" class="space-y-5" onsubmit="showLoading()">
            <div class="w-full">
                <label for="file-upload" class="relative flex flex-col items-center justify-center w-full h-48 border-2 border-gray-700 border-dashed rounded-xl cursor-pointer bg-[#1c1d2b] hover:bg-[#252736] transition overflow-hidden group">
                    <div id="upload-placeholder" class="flex flex-col items-center justify-center pt-5 pb-6">
                        <i class="fa-solid fa-image text-4xl text-gray-500 mb-3 group-hover:text-[#3f88ff] transition"></i>
                        <p class="mb-2 text-sm text-gray-400"><span class="font-semibold">Fotoğraf ekle (İsteğe bağlı)</span></p>
                    </div>
                    <img id="image-preview" class="hidden absolute inset-0 w-full h-full object-contain bg-black p-2" />
                    <input id="file-upload" name="image" type="file" class="hidden" accept="image/*" onchange="previewImage(event)" />
                </label>
                <button type="button" id="remove-img-btn" onclick="removeImage()" class="hidden text-red-500 text-xs mt-2 hover:underline"><i class="fa-solid fa-trash mr-1"></i>Fotoğrafı Kaldır</button>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1 ml-1 font-bold uppercase">Başlık</label><input type="text" name="title" placeholder="Başlık..." required class="w-full p-4 bg-[#1c1d2b] border border-gray-700 rounded-xl text-white focus:border-[#3f88ff] outline-none"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs text-gray-500 mb-1 ml-1 font-bold uppercase">Tarih</label><input type="datetime-local" name="captured_at" id="dateInput" required class="w-full p-4 bg-[#1c1d2b] border border-gray-700 rounded-xl text-white focus:border-[#3f88ff] outline-none scheme-dark"></div>
                <div><label class="block text-xs text-gray-500 mb-1 ml-1 font-bold uppercase">Konum</label><div class="relative flex items-center"><i class="fa-solid fa-location-dot absolute left-4 text-gray-500 z-10"></i><input type="text" name="location" id="locationInput" placeholder="Konum" value="<?php echo $_SESSION['location'] ?? ''; ?>" class="w-full pl-10 pr-12 py-4 bg-[#1c1d2b] border border-gray-700 rounded-xl text-white focus:border-[#3f88ff] outline-none"><button type="button" onclick="getLocation()" class="absolute right-2 p-2 text-[#3f88ff] hover:text-white transition bg-[#1c1d2b] rounded-lg"><i id="gps-icon" class="fa-solid fa-location-crosshairs text-xl"></i></button></div></div>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1 ml-1 font-bold uppercase">Açıklama</label><textarea name="description" placeholder="Ne düşünüyorsun?" rows="4" class="w-full p-4 bg-[#1c1d2b] border border-gray-700 rounded-xl text-white focus:border-[#3f88ff] outline-none resize-none"></textarea></div>
            
            <div>
                <label class="block text-xs text-gray-500 mb-1 ml-1 font-bold uppercase">Etiketler</label>
                <input type="text" name="tags" placeholder="uzay ay dolunay (boşluk bırakarak yazın)" 
                       class="w-full p-4 bg-[#1c1d2b] border border-gray-700 rounded-xl text-white focus:border-[#3f88ff] outline-none">
            </div>
            
            <button type="submit" class="w-full bg-[#3f88ff] hover:bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/20 transition active:scale-95 text-lg">Paylaş</button>
        </form>
    </div>
    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
            document.getElementById('loadingOverlay').classList.add('flex');
        }
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('upload-placeholder');
            const removeBtn = document.getElementById('remove-img-btn');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result; preview.classList.remove('hidden'); placeholder.classList.add('hidden'); removeBtn.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        function removeImage() {
            document.getElementById('file-upload').value = ""; document.getElementById('image-preview').classList.add('hidden'); document.getElementById('image-preview').src = ""; document.getElementById('upload-placeholder').classList.remove('hidden'); document.getElementById('remove-img-btn').classList.add('hidden');
        }
        window.onload = function() { const now = new Date(); now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); document.getElementById('dateInput').value = now.toISOString().slice(0,16); };
        function getLocation() {
            const icon = document.getElementById('gps-icon');
            if (navigator.geolocation) { icon.className = "fa-solid fa-spinner fa-spin-fast"; navigator.geolocation.getCurrentPosition(showPosition, showError); } else { alert("GPS desteklenmiyor."); }
        }
        function showPosition(position) {
            const input = document.getElementById('locationInput'); const icon = document.getElementById('gps-icon');
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`).then(r => r.json()).then(d => {
                input.value = d.address.city || d.address.town || d.address.county || (position.coords.latitude.toFixed(4) + ", " + position.coords.longitude.toFixed(4));
            }).finally(() => { icon.className = "fa-solid fa-location-crosshairs"; });
        }
        function showError(e) { alert("Konum alınamadı."); document.getElementById('gps-icon').className = "fa-solid fa-location-crosshairs"; }
    </script>
</body>
</html>