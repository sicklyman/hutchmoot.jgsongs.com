<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$pin  = trim($body['pin'] ?? '');

if (empty(SUBMIT_PIN)) json_error('Submission PIN not configured', 500);

// Admin PIN also grants submission access
$ok = hash_equals(SUBMIT_PIN, $pin) || (is_admin());

json_out(['ok' => $ok]);
