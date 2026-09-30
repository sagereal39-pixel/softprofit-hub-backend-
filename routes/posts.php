<?php
$db = getDB();

// GET /api/posts/popular - top 4 by views
if ($method === 'GET' && $sub === 'popular') {
    $stmt = $db->query("SELECT id, title, slug, excerpt, featured_image, views FROM posts WHERE status='published' ORDER BY views DESC LIMIT 4");
    echo json_encode($stmt->fetchAll());
    exit();
}

// GET /api/posts/{slug} - single post
if ($method === 'GET' && $sub !== '') {
    $stmt = $db->prepare("SELECT * FROM posts WHERE slug = :slug LIMIT 1");
    $stmt->execute(['slug' => $sub]);
    $post = $stmt->fetch();

    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found', 'slug' => $sub]);
        exit();
    }

    $upd = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = :id");
    $upd->execute(['id' => $post['id']]);

    $affStmt = $db->prepare("SELECT a.* FROM affiliates a JOIN post_affiliates pa ON a.id = pa.affiliate_id WHERE pa.post_id = :id");
    $affStmt->execute(['id' => $post['id']]);
    $post['affiliates'] = $affStmt->fetchAll();

    $ccStmt = $db->prepare("SELECT COUNT(*) as c FROM comments WHERE post_id = :id AND status = 'approved'");
    $ccStmt->execute(['id' => $post['id']]);
    $post['comment_count'] = intval($ccStmt->fetch()['c']);

    echo json_encode($post);
    exit();
}

// GET /api/posts - list published posts
if ($method === 'GET' && $sub === '') {
    $page = intval($_GET['page'] ?? 1);
    $limit = 5;
    $offset = ($page - 1) * $limit;

    $totalStmt = $db->query("SELECT COUNT(*) as c FROM posts WHERE status='published'");
    $total = $totalStmt->fetch()['c'];

    $stmt = $db->prepare("
        SELECT 
            p.id, p.title, p.slug, p.excerpt, p.category,
            p.featured_image, p.meta_title, p.meta_description,
            p.meta_keywords, p.author, p.views, p.created_at,
            COUNT(c.id) as comment_count
        FROM posts p
        LEFT JOIN comments c ON c.post_id = p.id AND c.status = 'approved'
        WHERE p.status = 'published'
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();

    echo json_encode([
        'posts' => $posts,
        'total' => $total,
        'page'  => $page,
        'pages' => ceil($total / $limit)
    ]);
    exit();
}

// POST /api/posts - create
if ($method === 'POST') {
    requireAuth();
    $b = json_decode(file_get_contents('php://input'), true);

    $title = $b['title'] ?? '';
    $slug  = $b['slug'] ?? strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-'));

    $stmt = $db->prepare("INSERT INTO posts (title, slug, content, excerpt, category, author, meta_title, meta_description, meta_keywords, status, featured_image)
                VALUES (:title, :slug, :content, :excerpt, :category, :author, :meta_title, :meta_description, :meta_keywords, :status, :featured_image)
                RETURNING id");
    $stmt->execute([
        'title' => $title,
        'slug' => $slug,
        'content' => $b['content'] ?? '',
        'excerpt' => $b['excerpt'] ?? '',
        'category' => $b['category'] ?? '',
        'author' => $b['author'] ?? '',
        'meta_title' => $b['meta_title'] ?? $title,
        'meta_description' => $b['meta_description'] ?? '',
        'meta_keywords' => $b['meta_keywords'] ?? '',
        'status' => $b['status'] ?? 'draft',
        'featured_image' => $b['featured_image'] ?? '',
    ]);
    $newId = $stmt->fetch()['id'];

    echo json_encode(['message' => 'Post created', 'id' => $newId, 'slug' => $slug]);
    exit();
}

// PUT /api/posts/{id}
if ($method === 'PUT' && $sub !== '') {
    requireAuth();
    $b = json_decode(file_get_contents('php://input'), true);
    $postId = intval($sub);
    $allowed = ['title', 'slug', 'content', 'excerpt', 'category', 'author', 'meta_title', 'meta_description', 'meta_keywords', 'status', 'featured_image'];

    $setParts = [];
    $params = ['id' => $postId];
    foreach ($allowed as $f) {
        if (isset($b[$f])) {
            $setParts[] = "$f = :$f";
            $params[$f] = $b[$f];
        }
    }

    if ($setParts) {
        $sql = "UPDATE posts SET " . implode(', ', $setParts) . " WHERE id = :id";
        $db->prepare($sql)->execute($params);
    }
    echo json_encode(['message' => 'Post updated']);
    exit();
}

// DELETE /api/posts/{id}
if ($method === 'DELETE' && $sub !== '') {
    requireAuth();
    $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
    $stmt->execute(['id' => intval($sub)]);
    echo json_encode(['message' => 'Post deleted']);
    exit();
}

http_response_code(404);
echo json_encode(['error' => 'Posts route not matched', 'method' => $method, 'sub' => $sub]);