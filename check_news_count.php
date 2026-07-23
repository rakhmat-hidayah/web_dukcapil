<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=dukcapil_dompukab", "root", "");
$countDeleted = $pdo->query("SELECT COUNT(*) FROM news WHERE deleted_at IS NOT NULL")->fetchColumn();
$countActive = $pdo->query("SELECT COUNT(*) FROM news WHERE deleted_at IS NULL")->fetchColumn();
echo "Deleted news: $countDeleted\nActive news: $countActive\n";
