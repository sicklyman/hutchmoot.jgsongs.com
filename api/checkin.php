<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();

$method = $_SERVER['REQUEST_METHOD'];

// GET ?id=X — check assignment status
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) json_error('Missing id', 400);

    $db = db();
    $stmt = $db->prepare("
        SELECT c.group_id, g.name AS group_name, a.location, a.slug AS artwork_slug
        FROM checkins c
        LEFT JOIN `groups` g ON g.id = c.group_id
        LEFT JOIN artworks a ON a.id = g.artwork_id
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) json_error('Check-in not found', 404);

    if ($row['group_id'] === null) {
        json_out(['status' => 'pending']);
    } else {
        json_out([
            'status'       => 'assigned',
            'group_name'   => $row['group_name'],
            'location'     => $row['location'],
            'artwork_slug' => $row['artwork_slug'],
        ]);
    }
}

// POST — record check-in, no group assigned yet
if ($method === 'POST') {
    $body         = json_decode(file_get_contents('php://input'), true) ?? [];
    $level        = trim($body['level'] ?? '');
    $first_name   = trim($body['first_name'] ?? '') ?: null;
    $last_initial = strtoupper(trim($body['last_initial'] ?? '')) ?: null;
    $last_initial = $last_initial ? substr($last_initial, 0, 1) : null;

    if (!in_array($level, ['beginner', 'some', 'confident'], true)) {
        json_error('level must be beginner, some or confident');
    }

    $db = db();
    $stmt = $db->prepare("INSERT INTO checkins (group_id, experience_level, first_name, last_initial) VALUES (NULL, ?, ?, ?)");
    $stmt->execute([$level, $first_name, $last_initial]);

    json_out(['checkin_id' => (int)$db->lastInsertId()]);
}

json_error('Method not allowed', 405);
