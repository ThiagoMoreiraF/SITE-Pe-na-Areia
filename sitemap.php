<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = rtrim(SITE_URL, '/');

$stmt = $pdo->query('SELECT codigo, updated_at FROM imoveis WHERE status = "ativo" ORDER BY updated_at DESC');
$imoveis = $stmt->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?= h($baseUrl) ?>/index.php</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc><?= h($baseUrl) ?>/quem-somos.php</loc>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
  <?php foreach ($imoveis as $imovel): ?>
  <url>
    <loc><?= h($baseUrl) ?>/imovel.php?codigo=<?= urlencode($imovel['codigo']) ?></loc>
    <lastmod><?= h(date('Y-m-d', strtotime($imovel['updated_at']))) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <?php endforeach; ?>
</urlset>
