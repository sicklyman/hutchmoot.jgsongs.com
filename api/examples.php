<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/r2.php';
cors_headers();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!gate_open() && !is_admin()) json_error('Content not yet available', 403);

    $rows = db()->query("SELECT * FROM examples ORDER BY sort_order, id")->fetchAll();
    json_out($rows);
}

if ($method === 'POST') {
    require_admin();

    $id         = isset($_POST['id']) && $_POST['id'] ? (int)$_POST['id'] : null;
    $title      = trim($_POST['title'] ?? '');
    $commentary = trim($_POST['commentary'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$title) json_error('title required');

    $audio_url = null;
    if (!empty($_FILES['audio']['tmp_name'])) {
        $file = $_FILES['audio'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $key  = 'hutchmoot/examples/' . uniqid('ex_') . '.' . $ext;
        $ct   = r2_content_type($file['name']);
        $audio_url = r2_upload($file['tmp_name'], $key, $ct);
    }

    if ($id) {
        $sql = "UPDATE examples SET title=?, commentary=?, sort_order=?" . ($audio_url ? ', audio_url=?' : '') . " WHERE id=?";
        $params = [$title, $commentary ?: null, $sort_order];
        if ($audio_url) $params[] = $audio_url;
        $params[] = $id;
        db()->prepare($sql)->execute($params);
    } else {
        db()->prepare("INSERT INTO examples (title, audio_url, commentary, sort_order) VALUES (?,?,?,?)")
            ->execute([$title, $audio_url, $commentary ?: null, $sort_order]);
        $id = (int)db()->lastInsertId();
    }

    json_out(['ok' => true, 'id' => $id]);
}

json_error('Method not allowed', 405);
