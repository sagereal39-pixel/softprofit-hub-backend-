<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';

// Verify token
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';
if (!preg_match('/Bearer\s+(.+)/', $auth, $m)) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}
$tokenData = verifyToken($m[1]);
if (!$tokenData) {
  http_response_code(401);
  echo json_encode(['error' => 'Invalid token']);
  exit();
}

if (!isset($_FILES['image'])) {
  http_response_code(400);
  echo json_encode(['error' => 'No image uploaded']);
  exit();
}

$file = $_FILES['image'];
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (!in_array($file['type'], $allowed)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.']);
  exit();
}

if ($file['size'] > 5 * 1024 * 1024) {
  http_response_code(400);
  echo json_encode(['error' => 'File too large. Max 5MB.']);
  exit();
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_') . '.' . $ext;
$uploadDir = __DIR__ . '/uploads/';
$uploadPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
  $url = 'http://localhost:5001/api/uploads/' . $filename;
  echo json_encode(['url' => $url, 'filename' => $filename]);
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to save file']);
}
