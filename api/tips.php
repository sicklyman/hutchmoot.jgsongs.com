<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();

if (!gate_open() && !is_admin()) json_error('Content not yet available', 403);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = db()->prepare("SELECT body FROM content WHERE page_key = 'tips' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();
    json_out(['body' => $row['body'] ?? '']);
}

json_error('Method not allowed', 405);
