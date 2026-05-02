<?php
define('HUTCHMOOT', 1);
require_once __DIR__ . '/../../api/config.php';
require_once __DIR__ . '/../../api/db.php';

cors_headers();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$session = db()->query(
    "SELECT id FROM reflect_sessions WHERE closed_at IS NULL ORDER BY id DESC LIMIT 1"
)->fetch();
if (!$session) json_error('No active session', 409);

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$phrase = mb_substr(trim($body['phrase'] ?? ''), 0, 300);
if (!$phrase) json_error('phrase required');

$db = db();
$db->prepare("INSERT INTO reflect_responses (session_id, phrase) VALUES (?, ?)")->execute([$session['id'], $phrase]);
json_out(['ok' => true, 'id' => (int)$db->lastInsertId()]);
