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

    // Bcrypt verify. A stored value that isn't a valid bcrypt hash simply
    // fails verification, so nobody can log in with it.
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