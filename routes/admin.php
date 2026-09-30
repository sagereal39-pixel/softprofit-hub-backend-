<?php
$db = getDB();

if ($method === 'GET' && $sub === 'stats') {
    $posts       = $db->query("SELECT COUNT(*) as c FROM posts")->fetch()['c'];
    $published   = $db->query("SELECT COUNT(*) as c FROM posts WHERE status='published'")->fetch()['c'];
    $drafts      = $db->query("SELECT COUNT(*) as c FROM posts WHERE status='draft'")->fetch()['c'];
    $affiliates  = $db->query("SELECT COUNT(*) as c FROM affiliates")->fetch()['c'];
    $totalClicks = $db->query("SELECT SUM(clicks) as c FROM affiliates")->fetch()['c'] ?? 0;
    $totalViews  = $db->query("SELECT SUM(views) as c FROM posts")->fetch()['c'] ?? 0;

    $topPostsResult = $db->query("SELECT id, title, views FROM posts ORDER BY views DESC LIMIT 5");
    $topPosts = $topPostsResult->fetchAll();

    $topAffResult = $db->query("SELECT id, name, clicks, commission FROM affiliates ORDER BY clicks DESC LIMIT 5");
    $topAff = $topAffResult->fetchAll();

    echo json_encode([
        'posts'          => $posts,
        'published'      => $published,
        'drafts'         => $drafts,
        'affiliates'     => $affiliates,
        'total_clicks'   => $totalClicks,
        'total_views'    => $totalViews,
        'top_posts'      => $topPosts,
        'top_affiliates' => $topAff,
    ]);
    exit();
}

// GET /api/admin/posts - ALL posts including drafts
if ($method === 'GET' && $sub === 'posts') {
    $result = $db->query("SELECT id, title, slug, status, category, views, excerpt, content, author, meta_title, meta_description, meta_keywords, featured_image, created_at FROM posts ORDER BY created_at DESC");
    echo json_encode($result->fetchAll());
    exit();
}

http_response_code(404);
echo json_encode(['error' => 'Admin route not found']);