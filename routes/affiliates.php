<?php
$db = getDB();

// POST /api/affiliates/click/{id} - track click
if ($method === 'POST' && $sub === 'click' && $id) {
    $aid = intval($id);
    $db->prepare("UPDATE affiliates SET clicks = clicks + 1 WHERE id = :id")->execute(['id' => $aid]);
    $stmt = $db->prepare("SELECT url FROM affiliates WHERE id = :id");
    $stmt->execute(['id' => $aid]);
    $aff = $stmt->fetch();
    echo json_encode(['redirect' => $aff['url'] ?? '#']);
    exit();
}

// GET /api/affiliates - list all
if ($method === 'GET' && $sub === '') {
    $stmt = $db->query("SELECT * FROM affiliates ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll());
    exit();
}

// GET /api/affiliates/{id}
if ($method === 'GET' && $sub !== '') {
    $stmt = $db->prepare("SELECT * FROM affiliates WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => intval($sub)]);
    $aff = $stmt->fetch();
    if (!$aff) {
        http_response_code(404);
        echo json_encode(['error' => 'Affiliate not found']);
        exit();
    }
    echo json_encode($aff);
    exit();
}

// POST /api/affiliates - create
if ($method === 'POST') {
    requireAuth();
    $b = json_decode(file_get_contents('php://input'), true);

    $stmt = $db->prepare("INSERT INTO affiliates (name, url, description, commission, category, logo)
                VALUES (:name, :url, :description, :commission, :category, :logo)
                RETURNING id");
    $stmt->execute([
        'name' => $b['name'] ?? '',
        'url' => $b['url'] ?? '',
        'description' => $b['description'] ?? '',
        'commission' => floatval($b['commission'] ?? 0),
        'category' => $b['category'] ?? '',
        'logo' => $b['logo'] ?? '',
    ]);
    $newId = $stmt->fetch()['id'];

    echo json_encode(['message' => 'Affiliate created', 'id' => $newId]);
    exit();
}

// PUT /api/affiliates/{id} - update
if ($method === 'PUT' && $sub !== '') {
    requireAuth();
    $b = json_decode(file_get_contents('php://input'), true);
    $affId = intval($sub);
    $allowed = ['name', 'url', 'description', 'category', 'logo'];

    $setParts = [];
    $params = ['id' => $affId];
    foreach ($allowed as $f) {
        if (isset($b[$f])) {
            $setParts[] = "$f = :$f";
            $params[$f] = $b[$f];
        }
    }
    if (isset($b['commission'])) {
        $setParts[] = "commission = :commission";
        $params['commission'] = floatval($b['commission']);
    }

    if ($setParts) {
        $sql = "UPDATE affiliates SET " . implode(', ', $setParts) . " WHERE id = :id";
        $db->prepare($sql)->execute($params);
    }
    echo json_encode(['message' => 'Affiliate updated']);
    exit();
}

// DELETE /api/affiliates/{id}
if ($method === 'DELETE' && $sub !== '') {
    requireAuth();
    $stmt = $db->prepare("DELETE FROM affiliates WHERE id = :id");
    $stmt->execute(['id' => intval($sub)]);
    echo json_encode(['message' => 'Affiliate deleted']);
    exit();
}

http_response_code(404);
echo json_encode(['error' => 'Affiliates route not matched']);