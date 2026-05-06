<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/r2.php';
cors_headers();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!gate_open() && !is_admin() && !is_preview()) json_error('Content not yet available', 403);

    $slug = trim($_GET['slug'] ?? '');

    if ($slug) {
        $rows = db()->query(
            "SELECT id, slug, title, image_url, audio_url, description, lyrics, sort_order FROM examples ORDER BY sort_order, id"
        )->fetchAll();

        $idx = null;
        foreach ($rows as $i => $r) {
            if ($r['slug'] === $slug) { $idx = $i; break; }
        }
        if ($idx === null) json_error('Not found', 404);

        $example = $rows[$idx];
        $prev = $idx > 0
            ? ['slug' => $rows[$idx - 1]['slug'], 'title' => $rows[$idx - 1]['title']]
            : null;
        $next = $idx < count($rows) - 1
            ? ['slug' => $rows[$idx + 1]['slug'], 'title' => $rows[$idx + 1]['title']]
            : null;

        json_out(compact('example', 'prev', 'next'));
    }

    $rows = db()->query(
        "SELECT id, slug, title, image_url, audio_url, description, lyrics, sort_order FROM examples ORDER BY sort_order, id"
    )->fetchAll();
    json_out($rows);
}

if ($method === 'POST') {
    require_admin();

    $id          = isset($_POST['id']) && $_POST['id'] ? (int)$_POST['id'] : null;
    $slug        = trim($_POST['slug'] ?? '');
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $lyrics      = trim($_POST['lyrics'] ?? '') ?: null;
    $sort_order  = (int)($_POST['sort_order'] ?? 0);

    if (!$slug)  json_error('slug required');
    if (!$title) json_error('title required');
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        json_error('Slug must be lowercase letters, numbers and hyphens only');
    }

    try {
        $image_url = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $file = $_FILES['image'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $key  = "hutchmoot/examples/{$slug}-image-" . time() . ".{$ext}";
            $image_url = r2_upload($file['tmp_name'], $key, r2_content_type($file['name']));
        }

        $audio_url = null;
        if (!empty($_FILES['audio']['tmp_name'])) {
            $file = $_FILES['audio'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $key  = "hutchmoot/examples/{$slug}-audio-" . time() . ".{$ext}";
            $audio_url = r2_upload($file['tmp_name'], $key, r2_content_type($file['name']));
        }

        if ($id) {
            $sets   = 'title=?, description=?, lyrics=?, sort_order=?';
            $params = [$title, $description ?: null, $lyrics, $sort_order];
            if ($image_url) { $sets .= ', image_url=?'; $params[] = $image_url; }
            if ($audio_url) { $sets .= ', audio_url=?'; $params[] = $audio_url; }
            $params[] = $id;
            db()->prepare("UPDATE examples SET {$sets} WHERE id=?")->execute($params);
        } else {
            db()->prepare(
                "INSERT INTO examples (slug, title, image_url, audio_url, description, lyrics, sort_order) VALUES (?,?,?,?,?,?,?)"
            )->execute([$slug, $title, $image_url, $audio_url, $description ?: null, $lyrics, $sort_order]);
            $id = (int)db()->lastInsertId();
        }
    } catch (Exception $e) {
        json_error($e->getMessage(), 500);
    }

    json_out(['ok' => true, 'id' => $id]);
}

if ($method === 'DELETE') {
    require_admin();
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = (int)($body['id'] ?? 0);
    if (!$id) json_error('id required');
    db()->prepare("DELETE FROM examples WHERE id=?")->execute([$id]);
    json_out(['ok' => true]);
}

json_error('Method not allowed', 405);
