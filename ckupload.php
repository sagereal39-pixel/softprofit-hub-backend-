<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

if (!isset($_FILES['upload'])) {
  echo json_encode(['error' => ['message' => 'No file uploaded']]);
  exit();
}

$file = $_FILES['upload'];
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (!in_array($file['type'], $allowed)) {
  echo json_encode(['error' => ['message' => 'Invalid file type']]);
  exit();
}

if ($file['size'] > 5 * 1024 * 1024) {
  echo json_encode(['error' => ['message' => 'File too large. Max 5MB']]);
  exit();
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('ck_') . '.' . $ext;
$uploadDir = __DIR__ . '/uploads/';
$uploadPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
  $url = 'http://localhost:5001/api/uploads/' . $filename;
  echo json_encode(['url' => $url]);
} else {
  echo json_encode(['error' => ['message' => 'Upload failed']]);
}
