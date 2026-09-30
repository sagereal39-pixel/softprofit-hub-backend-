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

// Cloudinary credentials from environment variables
$cloudName = getenv('CLOUDINARY_CLOUD_NAME');
$apiKey    = getenv('CLOUDINARY_API_KEY');
$apiSecret = getenv('CLOUDINARY_API_SECRET');

if (!$cloudName || !$apiKey || !$apiSecret) {
    http_response_code(500);
    echo json_encode(['error' => 'Cloudinary is not configured on the server']);
    exit();
}

// Build the signed upload request
$timestamp = time();
$paramsToSign = "timestamp=$timestamp";
$signature = sha1($paramsToSign . $apiSecret);

$cfile = new CURLFile($file['tmp_name'], $file['type'], $file['name']);

$postFields = [
    'file'      => $cfile,
    'api_key'   => $apiKey,
    'timestamp' => $timestamp,
    'signature' => $signature,
];

$ch = curl_init("https://api.cloudinary.com/v1_1/$cloudName/image/upload");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload request failed: ' . $curlError]);
    exit();
}

$result = json_decode($response, true);

if ($httpCode !== 200 || !isset($result['secure_url'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Cloudinary upload failed', 'details' => $result]);
    exit();
}

echo json_encode([
    'url' => $result['secure_url'],
    'filename' => $result['public_id'],
]);