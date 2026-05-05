<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/r2.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

// Validate submission PIN (or admin PIN)
$pin = trim($_POST['pin'] ?? '');
if (!hash_equals(SUBMIT_PIN, $pin) && !is_admin()) {
    json_error('Invalid PIN', 403);
}

$slug   = trim($_POST['slug'] ?? '');
$lyrics = trim($_POST['lyrics'] ?? '') ?: null;
if (!$slug) json_error('slug required');

// Resolve artwork
$stmt = db()->prepare("SELECT id FROM artworks WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$artwork = $stmt->fetch();
if (!$artwork) json_error('Artwork not found', 404);

// Fetch existing submission (needed for lyrics-only path)
$existing = db()->prepare("SELECT id, audio_url FROM submissions WHERE artwork_id = ? LIMIT 1");
$existing->execute([$artwork['id']]);
$row = $existing->fetch() ?: null;

$has_audio = !empty($_FILES['audio']['tmp_name']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK;

// Lyrics-only path: update lyrics on existing submission without a new audio file
if (!$has_audio) {
    if (!$row) json_error('Audio file required');
    db()->prepare("UPDATE submissions SET lyrics=? WHERE id=?")->execute([$lyrics, $row['id']]);
    json_out(['ok' => true, 'audio_url' => $row['audio_url'], 'lyrics' => $lyrics]);
}

// Audio upload path
$file = $_FILES['audio'];
$allowed_types = ['audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/wav', 'audio/webm', 'audio/x-m4a', 'audio/aac', 'audio/3gpp', 'audio/amr'];
$ct = $file['type'];
if (!in_array($ct, $allowed_types, true)) {
    $ct = r2_content_type($file['name']);
    if ($ct === 'application/octet-stream') json_error('Unsupported audio format');
}

$ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$r2_key    = "hutchmoot/submissions/{$slug}-" . time() . "." . $ext;
$audio_url = r2_upload($file['tmp_name'], $r2_key, $ct);

if ($row) {
    db()->prepare("UPDATE submissions SET audio_url=?, lyrics=?, submitted_at=NOW() WHERE id=?")
        ->execute([$audio_url, $lyrics, $row['id']]);
} else {
    db()->prepare("INSERT INTO submissions (artwork_id, audio_url, lyrics) VALUES (?,?,?)")
        ->execute([$artwork['id'], $audio_url, $lyrics]);
}

json_out(['ok' => true, 'audio_url' => $audio_url, 'lyrics' => $lyrics]);
