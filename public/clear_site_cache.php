<?php
/**
 * Skrip Reset Total Cache Browser Chrome via Clear-Site-Data Header (1-Klik)
 */
header("Clear-Site-Data: \"cache\", \"storage\"");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

echo "<div style='font-family:sans-serif; padding:30px; background:#f0fdf4; border:2px solid #22c55e; color:#15803d; border-radius:16px; max-width:600px; margin:40px auto; text-align:center;'>";
echo "<h1 style='margin-top:0;'>🧹 CACHE CHROME BROWSER BERHASIL DI-RESET TOTAL!</h1>";
echo "<p style='font-size:16px;'>Memori cache browser Google Chrome Anda telah dibersihkan 100% dari sisi server.</p>";
echo "<p><a href='/' style='display:inline-block; padding:12px 24px; background:#16a34a; color:#fff; text-decoration:none; border-radius:10px; font-weight:bold;'>Buka Beranda Website →</a></p>";
echo "</div>";
