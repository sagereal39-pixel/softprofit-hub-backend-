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

// Figure out which URL segment is the base folder (if any), so this
// works both locally (e.g. /softprofit-api/posts) and on Render,
// where the app is deployed at the domain root (e.g. /posts).
$scriptDir = trim(dirname($_SERVER['SCRIPT_NAME']), '/'); // '' at root, 'softprofit-api' locally

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

// Strip the base folder prefix from the URI, if present
if ($scriptDir !== '' && strpos($uri, $scriptDir) === 0) {
    $uri = trim(substr($uri, strlen($scriptDir)), '/');
}

$parts = explode('/', $uri);

$resource = $parts[0] ?? '';
$sub      = $parts[1] ?? '';
$id       = $parts[2] ?? null;

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
    case 'share':
        // Server-rendered HTML with Open Graph tags, for link previews
        // and crawlers — overrides the default JSON Content-Type above.
        header('Content-Type: text/html; charset=utf-8');
        require_once __DIR__ . '/routes/share.php';
        break;
    case 'sitemap':
        header('Content-Type: application/xml; charset=utf-8');
        require_once __DIR__ . '/routes/sitemap.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
}