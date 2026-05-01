<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$pin  = trim($body['pin'] ?? '');

if (empty(ADMIN_PIN)) json_error('Admin PIN not configured', 500);

json_out(['ok' => hash_equals(ADMIN_PIN, $pin)]);
