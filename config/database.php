<?php
// Reads from environment variables when set (e.g. on Render),
// falls back to local XAMPP/PostgreSQL defaults for local development.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: 5432);
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'postgres');
define('DB_NAME', getenv('DB_NAME') ?: 'affiliate_blog');
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'softprofithub_secret_key_2026_xyz');

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