<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();
require_admin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = db()->prepare("SELECT body FROM content WHERE page_key = 'site_live' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();
    json_out(['live' => ($row['body'] ?? '0') === '1']);
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $live = !empty($body['live']) ? '1' : '0';
    $stmt = db()->prepare("UPDATE content SET body = ? WHERE page_key = 'site_live'");
    $stmt->execute([$live]);
    json_out(['live' => $live === '1']);
}

json_error('Method not allowed', 405);
