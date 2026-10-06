<?php
// routes/share.php
// Serves a minimal, server-rendered HTML page per post with Open Graph
// and Twitter Card meta tags, then redirects real visitors into the
// actual React site. Link-preview crawlers (WhatsApp, Facebook, X,
// Slack) don't run JavaScript, so they stop here and read the tags
// instead of hitting the React app, which would show them nothing.

$db = getDB();

$slug = $sub ?? '';

if (!$slug) {
    http_response_code(404);
    echo 'Not found';
    exit();
}

$stmt = $db->prepare("SELECT title, excerpt, slug, featured_image FROM posts WHERE slug = :slug AND status = 'published' LIMIT 1");
$stmt->execute(['slug' => $slug]);
$post = $stmt->fetch();

// Set this on Render as an environment variable once your real domain
// is ready; falls back to the current Vercel URL otherwise.
$frontendUrl = rtrim(getenv('FRONTEND_URL') ?: 'https://softprofit-hub-frontend.vercel.app', '/');

if (!$post) {
    // Unknown slug — just send people to the homepage
    header("Location: $frontendUrl");
    exit();
}

$title       = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($post['excerpt'] ?? '', ENT_QUOTES, 'UTF-8');
$image       = $post['featured_image'] ?? '';
$postUrl     = htmlspecialchars("$frontendUrl/blog/" . $post['slug'], ENT_QUOTES, 'UTF-8');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $title ?></title>
<meta name="description" content="<?= $description ?>">

<meta property="og:type" content="article">
<meta property="og:title" content="<?= $title ?>">
<meta property="og:description" content="<?= $description ?>">
<meta property="og:url" content="<?= $postUrl ?>">
<?php if ($image): ?>
<meta property="og:image" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= $title ?>">
<meta name="twitter:description" content="<?= $description ?>">
<?php if ($image): ?>
<meta name="twitter:image" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<meta http-equiv="refresh" content="0; url=<?= $postUrl ?>">
<script>window.location.replace("<?= $postUrl ?>");</script>
</head>
<body>
  <p>Redirecting to <a href="<?= $postUrl ?>"><?= $title ?></a>&hellip;</p>
</body>
</html>