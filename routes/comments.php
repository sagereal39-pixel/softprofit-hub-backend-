<?php
$db = getDB();

// GET /api/comments?post_id=1 - get approved comments for a post
if ($method === 'GET' && $sub === '') {
    $post_id = intval($_GET['post_id'] ?? 0);
    if (!$post_id) {
        echo json_encode([]);
        exit();
    }
    $stmt = $db->prepare("SELECT id, name, content, created_at FROM comments WHERE post_id = :post_id AND status = 'approved' ORDER BY created_at DESC");
    $stmt->execute(['post_id' => $post_id]);
    echo json_encode($stmt->fetchAll());
    exit();
}

// POST /api/comments - add a comment
if ($method === 'POST' && $sub === '') {
    $b = json_decode(file_get_contents('php://input'), true);
    $post_id = intval($b['post_id'] ?? 0);
    $name    = $b['name'] ?? '';
    $email   = $b['email'] ?? '';
    $content = $b['content'] ?? '';

    if (!$post_id || !$name || !$email || !$content) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        exit();
    }

    $stmt = $db->prepare("INSERT INTO comments (post_id, name, email, content) VALUES (:post_id, :name, :email, :content) RETURNING id");
    $stmt->execute(['post_id' => $post_id, 'name' => $name, 'email' => $email, 'content' => $content]);
    $newId = $stmt->fetch()['id'];

    echo json_encode(['message' => 'Comment added', 'id' => $newId]);
    exit();
}

// GET /api/comments/all - admin: get all comments
if ($method === 'GET' && $sub === 'all') {
    requireAuth();
    $stmt = $db->query("SELECT c.*, p.title as post_title FROM comments c JOIN posts p ON c.post_id = p.id ORDER BY c.created_at DESC");
    echo json_encode($stmt->fetchAll());
    exit();
}

// PUT /api/comments/{id} - admin: update status
if ($method === 'PUT' && $sub !== '') {
    requireAuth();
    $b = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("UPDATE comments SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $b['status'] ?? 'approved', 'id' => intval($sub)]);
    echo json_encode(['message' => 'Comment updated']);
    exit();
}

// DELETE /api/comments/{id} - admin: delete comment
if ($method === 'DELETE' && $sub !== '') {
    requireAuth();
    $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
    $stmt->execute(['id' => intval($sub)]);
    echo json_encode(['message' => 'Comment deleted']);
    exit();
}

http_response_code(404);
echo json_encode(['error' => 'Comments route not matched']);