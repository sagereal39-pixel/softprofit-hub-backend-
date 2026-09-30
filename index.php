<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');
$parts = explode('/', $uri);

$resource = $parts[1] ?? '';
$sub      = $parts[2] ?? '';
$id       = $parts[3] ?? null;

$method = $_SERVER['REQUEST_METHOD'];

switch ($resource) {
    case 'auth':
        require_once __DIR__ . '/routes/auth.php';
        break;
    case 'posts':
        require_once __DIR__ . '/routes/posts.php';
        break;
    case 'affiliates':
        require_once __DIR__ . '/routes/affiliates.php';
        break;
    case 'comments':
        require_once __DIR__ . '/routes/comments.php';
        break;
    case 'admin':
        requireAuth();
        require_once __DIR__ . '/routes/admin.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
}
