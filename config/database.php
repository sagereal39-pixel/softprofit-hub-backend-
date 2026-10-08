<?php
// Reads from environment variables when set (e.g. on Render),
// falls back to local XAMPP/PostgreSQL defaults for local development.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: 5432);
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'postgres');
define('DB_NAME', getenv('DB_NAME') ?: 'affiliate_blog');

// JWT secret: must be set as an environment variable in production.
// A dev-only fallback is allowed ONLY when running on localhost, so a
// missing variable on a live server fails loudly instead of silently
// signing admin tokens with a publicly known value.
$jwtSecret = getenv('JWT_SECRET');
if (!$jwtSecret) {
    $host = $_SERVER['SERVER_NAME'] ?? '';
    if ($host === 'localhost' || $host === '127.0.0.1') {
        $jwtSecret = 'local-dev-only-secret-not-used-in-production';
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Server is not configured correctly']);
        exit();
    }
}
define('JWT_SECRET', $jwtSecret);

function getDB()
{
    static $conn = null;
    if ($conn === null) {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
            $conn = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit();
        }
    }
    return $conn;
}