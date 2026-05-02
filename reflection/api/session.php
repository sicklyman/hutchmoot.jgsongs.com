<?php
define('HUTCHMOOT', 1);
require_once __DIR__ . '/../../api/config.php';
require_once __DIR__ . '/../../api/db.php';

cors_headers();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['all'])) {
        $rows = db()->query(
            "SELECT id, label, prompt, created_at, closed_at FROM reflect_sessions ORDER BY id ASC"
        )->fetchAll();
        json_out($rows);
    } else {
        $row = db()->query(
            "SELECT id, label, prompt FROM reflect_sessions WHERE closed_at IS NULL ORDER BY id DESC LIMIT 1"
        )->fetch();
        json_out(['active' => (bool)$row, 'session' => $row ?: null]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $body['action'] ?? '';

    if ($action === 'open') {
        $label  = mb_substr(trim($body['label']  ?? ''), 0, 100);
        $prompt = mb_substr(trim($body['prompt'] ?? ''), 0, 500);
        if (!$label || !$prompt) json_error('label and prompt required');
        db()->exec("UPDATE reflect_sessions SET closed_at = NOW() WHERE closed_at IS NULL");
        $db = db();
        $db->prepare("INSERT INTO reflect_sessions (label, prompt) VALUES (?, ?)")->execute([$label, $prompt]);
        $id = (int)$db->lastInsertId();
        json_out(['ok' => true, 'session' => ['id' => $id, 'label' => $label, 'prompt' => $prompt]]);
    }

    if ($action === 'close') {
        db()->exec("UPDATE reflect_sessions SET closed_at = NOW() WHERE closed_at IS NULL");
        json_out(['ok' => true]);
    }

    if ($action === 'reset') {
        db()->exec("DELETE FROM reflect_responses");
        db()->exec("DELETE FROM reflect_sessions");
        json_out(['ok' => true]);
    }

    json_error('Unknown action');
}

json_error('Method not allowed', 405);
