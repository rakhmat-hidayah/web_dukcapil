<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=dukcapil_dompukab", "root", "");
$stmt = $pdo->query("SELECT id, title, slug, status, deleted_at FROM news LIMIT 10");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo "ID: {$r['id']} | Status: {$r['status']} | Deleted At: '{$r['deleted_at']}' | Slug: {$r['slug']}\n";
}
