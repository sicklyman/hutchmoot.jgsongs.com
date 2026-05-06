<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$pin  = trim($body['pin'] ?? '');

if (!empty(PREVIEW_PIN) && hash_equals(PREVIEW_PIN, $pin)) {
    json_out(['ok' => true]);
}
json_out(['ok' => false]);
