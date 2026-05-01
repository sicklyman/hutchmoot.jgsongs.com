<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();

$method = $_SERVER['REQUEST_METHOD'];

// GET — list groups (with artwork info)
if ($method === 'GET') {
    $rows = db()->query("
        SELECT g.*, a.title AS artwork_title, a.location AS artwork_location, a.slug AS artwork_slug
        FROM `groups` g
        JOIN artworks a ON a.id = g.artwork_id
        ORDER BY g.id
    ")->fetchAll();
    json_out($rows);
}

// POST — create or update (admin only)
if ($method === 'POST') {
    require_admin();

    $body       = json_decode(file_get_contents('php://input'), true) ?? [];
    $id         = isset($body['id']) && $body['id'] ? (int)$body['id'] : null;
    $name       = trim($body['name'] ?? '');
    $artwork_id = (int)($body['artwork_id'] ?? 0);
    $max_size   = max(1, (int)($body['max_size'] ?? 5));

    if (!$name || !$artwork_id) json_error('name and artwork_id required');

    if ($id) {
        db()->prepare("UPDATE `groups` SET name=?, artwork_id=?, max_size=? WHERE id=?")
            ->execute([$name, $artwork_id, $max_size, $id]);
    } else {
        db()->prepare("INSERT INTO `groups` (name, artwork_id, max_size) VALUES (?,?,?)")
            ->execute([$name, $artwork_id, $max_size]);
        $id = (int)db()->lastInsertId();
    }

    json_out(['ok' => true, 'id' => $id]);
}

json_error('Method not allowed', 405);
