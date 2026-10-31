<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Str;

class NewsSeoService
{
    /**
     * Generate complete SEO & Open Graph metadata array for a news item.
     * Delegated to NewsMetaService (Single Source of Truth).
     */
    public static function generateMetadata(News $news): array
    {
        return NewsMetaService::generate($news);
    }

    /**
     * Audit SEO Score & Open Graph Readiness.
     */
    public static function auditSeo(array $data): array
    {
        $score = 100;
        $warnings = [];
        $readiness = [
            'whatsapp' => true,
            'facebook' => true,
            'telegram' => true,
            'twitter'  => true,
            'linkedin' => true,
        ];

        $title = trim($data['title'] ?? '');
        $description = trim($data['excerpt'] ?? ($data['meta_description'] ?? ''));
        $thumbnail = $data['thumbnail'] ?? null;
        $content = trim($data['content'] ?? '');

        // Title checks
        if (empty($title)) {
            $score -= 25;
            $warnings[] = 'Judul berita masih kosong.';
            $readiness['whatsapp'] = $readiness['facebook'] = $readiness['telegram'] = $readiness['twitter'] = $readiness['linkedin'] = false;
        } elseif (mb_strlen($title) < 20) {
            $score -= 10;
            $warnings[] = 'Judul berita terlalu pendek (< 20 karakter). Rekomendasi 40 - 70 karakter.';
        } elseif (mb_strlen($title) > 100) {
            $score -= 5;
            $warnings[] = 'Judul berita terlalu panjang (> 100 karakter). Dapat terpotong di pencarian Google.';
        }

        // Description checks
        if (empty($description)) {
            $score -= 25;
            $warnings[] = 'Ringkasan/Meta Description belum diisi. Preview WhatsApp & Facebook akan mengambil teks acak.';
            $readiness['whatsapp'] = $readiness['facebook'] = false;
        } elseif (mb_strlen($description) < 60) {
            $score -= 10;
            $warnings[] = 'Ringkasan berita terlalu singkat (< 60 karakter). Rekomendasi 120 - 160 karakter.';
        }

        // Thumbnail checks
        if (empty($thumbnail)) {
            $score -= 20;
            $warnings[] = 'Thumbnail cover tidak diunggah. Sistem akan menggunakan Dynamic OG Card Banner otomatis.';
        } else {
            // Check dimensions if available
            $imgWidth = $data['image_width'] ?? null;
            $imgHeight = $data['image_height'] ?? null;
            if ($imgWidth && $imgHeight && ($imgWidth < 1200 || $imgHeight < 630)) {
                $score -= 10;
                $warnings[] = "Ukuran thumbnail ({$imgWidth}x{$imgHeight}px) kurang dari 1200x630px. Tampilan kartu WhatsApp/Facebook mungkin terdistorsi.";
            }
        }

        // Content checks
        if (empty($content) || mb_strlen(strip_tags($content)) < 100) {
            $score -= 15;
            $warnings[] = 'Isi berita terlalu singkat (< 100 kata).';
        }

        return [
            'seo_score' => max(0, min(100, $score)),
            'warnings'  => $warnings,
            'readiness' => $readiness,
        ];
    }

    /**
     * Dynamically generate a 1200x630 Open Graph Banner PNG image using PHP GD.
     */
    public static function renderDynamicOgBanner(News $news)
    {
        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
            return redirect(asset('android-chrome-512x512.png'));
        }

        $width = 1200;
        $height = 630;

        $image = imagecreatetruecolor($width, $height);

        // Define Brand Palette (Dukcapil Dompu Royal Blue Gradient)
        $bgStart = imagecolorallocate($image, 2, 116, 203);   // #0274cb
        $bgEnd   = imagecolorallocate($image, 8, 42, 74);     // #082a4a
        $white   = imagecolorallocate($image, 255, 255, 255);
        $amber   = imagecolorallocate($image, 251, 191, 36);   // #fbbf24
        $slateLight = imagecolorallocate($image, 203, 213, 225);

        // Draw background gradient
        for ($y = 0; $y < $height; $y++) {
            $r = (int) (2 + ($y / $height) * (8 - 2));
            $g = (int) (116 + ($y / $height) * (42 - 116));
            $b = (int) (203 + ($y / $height) * (74 - 203));
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }

        // Header Pill Badge (Category)
        $categoryName = mb_strtoupper($news->category ? $news->category->name : 'BERITA RESMI');
        imagefilledrectangle($image, 80, 70, 380, 115, imagecolorallocatealpha($image, 255, 255, 255, 100));
        imagestring($image, 5, 100, 85, $categoryName, $amber);

        // Department Title Line
        imagestring($image, 5, 80, 140, "DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL KABUPATEN DOMPU", $slateLight);

        // Article Title (Word Wrapped)
        $title = $news->title;
        $font = 5; // Built-in font size
        $wrappedTitle = wordwrap($title, 45, "\n");
        $lines = explode("\n", $wrappedTitle);
        $yPos = 200;
        foreach (array_slice($lines, 0, 4) as $line) {
            imagestring($image, 5, 80, $yPos, $line, $white);
            $yPos += 35;
        }

        // Footer Border & Watermark
        imageline($image, 80, 520, 1120, 520, imagecolorallocatealpha($image, 255, 255, 255, 80));
        imagestring($image, 5, 80, 545, "dukcapil.dompukab.go.id - Pelayanan Cepat, Tepat, dan Gratis", $white);
        
        $dateStr = $news->published_at ? $news->published_at->format('d M Y') : date('d M Y');
        imagestring($image, 5, 960, 545, $dateStr, $amber);

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
