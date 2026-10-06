<?php
// routes/sitemap.php
// Generates the XML sitemap dynamically from published posts.
// Content-Type is set by index.php before this file is included.

$db = getDB();

// Same env-var pattern used in share.php — update FRONTEND_URL on Render
// once a custom domain is ready.
$siteUrl = rtrim(getenv('FRONTEND_URL') ?: 'https://softprofit-hub-frontend.vercel.app', '/');

$stmt = $db->query("SELECT slug, updated_at FROM posts WHERE status='published' ORDER BY updated_at DESC");
$posts = $stmt->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

  <!-- Static Pages -->
  <url>
    <loc><?= $siteUrl ?>/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc><?= $siteUrl ?>/blog</loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc><?= $siteUrl ?>/tools</loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc><?= $siteUrl ?>/about</loc>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>

  <!-- Blog Posts -->
  <?php foreach ($posts as $post): ?>
    <url>
      <loc><?= $siteUrl ?>/blog/<?= htmlspecialchars($post['slug']) ?></loc>
      <lastmod><?= date('Y-m-d', strtotime($post['updated_at'])) ?></lastmod>
      <changefreq>weekly</changefreq>
      <priority>0.8</priority>
    </url>
  <?php endforeach; ?>

</urlset>