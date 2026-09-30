<?php
// routes/auth.php

$db = getDB();

if ($method === 'POST' && $sub === 'login') {
    $body = json_decode(file_get_contents('php://input'), true);
    $email = $body['email'] ?? '';
    $password = $body['password'] ?? '';

    $stmt = $db->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit();
    }

    // TEMP: if password is not a valid bcrypt hash, rehash it
    if (substr($user['password'], 0, 4) !== '$2y$') {
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $upd = $db->prepare("UPDATE users SET password = :password WHERE email = :email");
        $upd->execute(['password' => $newHash, 'email' => $email]);
        $token = generateToken($user['id'], $user['role']);
        echo json_encode([
            'token' => $token,
            'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']]
        ]);
        exit();
    }

    // Normal bcrypt verify
    if (password_verify($password, $user['password'])) {
        $token = generateToken($user['id'], $user['role']);
        echo json_encode([
            'token' => $token,
            'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
    exit();
}

echo json_encode(['error' => 'Auth route not matched', 'method' => $method, 'sub' => $sub]);