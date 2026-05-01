<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/r2.php';
cors_headers();

$method = $_SERVER['REQUEST_METHOD'];

// GET — public list
if ($method === 'GET') {
    $rows = db()->query("
        SELECT a.*, (SELECT COUNT(*) FROM submissions s WHERE s.artwork_id = a.id) > 0 AS has_submission
        FROM artworks a
        ORDER BY a.id
    ")->fetchAll();

    foreach ($rows as &$row) {
        $row['has_submission'] = (bool)$row['has_submission'];
    }
    json_out($rows);
}

// POST — create or update (admin only)
if ($method === 'POST') {
    require_admin();

    $id          = isset($_POST['id']) && $_POST['id'] ? (int)$_POST['id'] : null;
    $slug        = trim($_POST['slug'] ?? '');
    $title       = trim($_POST['title'] ?? '');
    $artist_name = trim($_POST['artist_name'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    if (!$slug || !$title || !$artist_name || !$location) {
        json_error('slug, title, artist_name and location are required');
    }
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        json_error('Slug must be lowercase letters, numbers and hyphens only');
    }

    $image_url = null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $file = $_FILES['image'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $key  = "hutchmoot/artworks/{$slug}.{$ext}";
        $ct   = r2_content_type($file['name']);
        $image_url = r2_upload($file['tmp_name'], $key, $ct);
    }

    if ($id) {
        $sql = "UPDATE artworks SET title=?, artist_name=?, location=?" . ($image_url ? ', image_url=?' : '') . " WHERE id=?";
        $params = [$title, $artist_name, $location];
        if ($image_url) $params[] = $image_url;
        $params[] = $id;
        db()->prepare($sql)->execute($params);
    } else {
        $stmt = db()->prepare("INSERT INTO artworks (slug, title, artist_name, location, image_url) VALUES (?,?,?,?,?)");
        $stmt->execute([$slug, $title, $artist_name, $location, $image_url]);
        $id = (int)db()->lastInsertId();
    }

    json_out(['ok' => true, 'id' => $id]);
}

if ($method === 'DELETE') {
    require_admin();
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = (int)($body['id'] ?? 0);
    if (!$id) json_error('id required');
    db()->prepare("DELETE FROM artworks WHERE id=?")->execute([$id]);
    json_out(['ok' => true]);
}

json_error('Method not allowed', 405);
