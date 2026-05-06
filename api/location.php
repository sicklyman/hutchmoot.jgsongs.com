<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/r2.php';
cors_headers();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = db()->prepare("SELECT body FROM content WHERE page_key = 'location_image'");
    $stmt->execute();
    $url = $stmt->fetchColumn();
    json_out(['image_url' => $url ?: null]);
}

if ($method === 'POST') {
    require_admin();
    $image_url = null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $file = $_FILES['image'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $key  = 'hutchmoot/location/map-' . time() . '.' . $ext;
        $ct   = r2_content_type($file['name']);
        $image_url = r2_upload($file['tmp_name'], $key, $ct);
        db()->prepare("INSERT INTO content (page_key, body) VALUES ('location_image', ?) ON DUPLICATE KEY UPDATE body=VALUES(body)")
            ->execute([$image_url]);
    }
    json_out(['ok' => true, 'image_url' => $image_url]);
}

json_error('Method not allowed', 405);
