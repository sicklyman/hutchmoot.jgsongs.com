<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

require_admin();

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$key  = trim($body['key']  ?? '');
$text = $body['body'] ?? '';

if (!in_array($key, ['tips', 'theory'], true)) json_error('Invalid page key');

db()->prepare("INSERT INTO content (page_key, body) VALUES (?,?) ON DUPLICATE KEY UPDATE body=VALUES(body)")
    ->execute([$key, $text]);

json_out(['ok' => true]);
