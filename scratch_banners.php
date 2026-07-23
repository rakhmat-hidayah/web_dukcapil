<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Banner;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;

echo "Seeding Real Sample Images...\n";

// Ensure directories exist
if (!file_exists(storage_path('app/public/banners'))) {
    mkdir(storage_path('app/public/banners'), 0777, true);
}
if (!file_exists(storage_path('app/public/gallery/1'))) {
    mkdir(storage_path('app/public/gallery/1'), 0777, true);
}

// Function to create a clean HD gradient image with text using GD
function createBannerImage($width, $height, $text, $subtitle, $filename, $startColor, $endColor) {
    $img = imagecreatetruecolor($width, $height);
    
    // Gradient fill
    for ($y = 0; $y < $height; $y++) {
        $r = (int)($startColor[0] + ($endColor[0] - $startColor[0]) * ($y / $height));
        $g = (int)($startColor[1] + ($endColor[1] - $startColor[1]) * ($y / $height));
        $b = (int)($startColor[2] + ($endColor[2] - $startColor[2]) * ($y / $height));
        $color = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, $width, $y, $color);
    }

    // Grid accent lines
    $gridColor = imagecolorallocatealpha($img, 255, 255, 255, 110);
    for ($x = 0; $x < $width; $x += 60) {
        imageline($img, $x, 0, $x, $height, $gridColor);
    }
    for ($y = 0; $y < $height; $y += 60) {
        imageline($img, 0, $y, $width, $y, $gridColor);
    }

    // Text header
    $white = imagecolorallocate($img, 255, 255, 255);
    $cyan = imagecolorallocate($img, 14, 211, 207);
    $gray = imagecolorallocate($img, 200, 210, 225);

    imagestring($img, 5, 40, (int)($height / 2 - 40), strtoupper(substr($text, 0, 45)), $white);
    imagestring($img, 4, 40, (int)($height / 2 + 10), substr($subtitle ?? '', 0, 55), $gray);
    imagestring($img, 3, 40, (int)($height / 2 + 40), "PORTAL DUKCAPIL KABUPATEN DOMPU", $cyan);

    $savePath = storage_path('app/public/' . $filename);
    imagejpeg($img, $savePath, 90);
    imagedestroy($img);

    echo "Generated: {$savePath}\n";
    return $filename;
}

$bannerConfigs = [
    [
        'title' => 'Tes banner',
        'subtitle' => 'Pelayanan Terintegrasi Kependudukan',
        'start' => [15, 23, 42],
        'end' => [30, 58, 138],
    ],
    [
        'title' => 'Sistem Pendaftaran KIA Online Kabupaten Dompu',
        'subtitle' => 'Buat KIA anak Anda lebih cepat dan mudah dari rumah.',
        'start' => [13, 148, 136],
        'end' => [15, 23, 42],
    ],
    [
        'title' => 'Festival lakey',
        'subtitle' => 'Dokumentasi & Sosialisasi Layanan Adminduk',
        'start' => [180, 83, 9],
        'end' => [15, 23, 42],
    ],
];

$banners = Banner::all();
$idx = 0;
foreach ($banners as $b) {
    $config = $bannerConfigs[$idx % count($bannerConfigs)];
    $filename = 'banners/banner-' . $b->id . '.jpg';
    createBannerImage(1200, 500, $b->title, $b->subtitle ?? $config['subtitle'], $filename, $config['start'], $config['end']);
    $b->update(['image' => $filename]);
    echo "Updated Banner #{$b->id} with image: {$filename}\n";
    $idx++;
}

// Seed Album Cover Image if GalleryAlbum exists
$albums = GalleryAlbum::all();
foreach ($albums as $album) {
    $albumImg = 'gallery/album-cover-' . $album->id . '.jpg';
    createBannerImage(800, 600, $album->title, "Galeri Dokumentasi Resmi", $albumImg, [15, 23, 42], [37, 99, 235]);
    $album->update(['cover_image' => $albumImg]);
    echo "Updated Album #{$album->id} cover: {$albumImg}\n";

    // Also update or create a item
    GalleryItem::updateOrCreate([
        'gallery_album_id' => $album->id,
    ], [
        'title' => $album->title . ' Item 1',
        'file_path' => $albumImg,
        'file_type' => 'image',
        'thumbnail' => $albumImg,
        'caption' => 'Dokumentasi kegiatan resmi Dukcapil Dompu',
    ]);
}

echo "Done!\n";
