<?php
define('HUTCHMOOT', 1);
require_once __DIR__ . '/../../api/config.php';
require_once __DIR__ . '/../../api/db.php';

cors_headers();

$since   = max(0, (int)($_GET['since']   ?? 0));
$session = max(0, (int)($_GET['session'] ?? 0));

if ($session === 0) {
    json_out([]);
}

$stmt = db()->prepare(
    "SELECT id, phrase FROM reflect_responses WHERE session_id = ? AND id > ? ORDER BY id ASC LIMIT 100"
);
$stmt->execute([$session, $since]);
json_out($stmt->fetchAll());
