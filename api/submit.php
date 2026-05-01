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

$slug = trim($_POST['slug'] ?? '');
if (!$slug) json_error('slug required');

// Resolve artwork
$stmt = db()->prepare("SELECT id FROM artworks WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$artwork = $stmt->fetch();
if (!$artwork) json_error('Artwork not found', 404);

// Validate audio upload
if (empty($_FILES['audio']['tmp_name']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
    json_error('Audio file required');
}

$file = $_FILES['audio'];
$max_bytes = 50 * 1024 * 1024; // 50 MB
if ($file['size'] > $max_bytes) json_error('File too large (max 50 MB)');

$allowed_types = ['audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/wav', 'audio/webm', 'audio/x-m4a'];
$ct = $file['type'];
if (!in_array($ct, $allowed_types, true)) {
    // Fallback: check extension
    $ct = r2_content_type($file['name']);
    if ($ct === 'application/octet-stream') json_error('Unsupported audio format');
}

$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$r2_key  = "hutchmoot/submissions/{$slug}." . $ext;
$audio_url = r2_upload($file['tmp_name'], $r2_key, $ct);

$shaped    = mb_substr(trim($_POST['shaped']    ?? ''), 0, 300);
$surprised = mb_substr(trim($_POST['surprised'] ?? ''), 0, 300);
$phrase    = mb_substr(trim($_POST['phrase']    ?? ''), 0, 300);

// Upsert: replace existing submission for this artwork
$existing = db()->prepare("SELECT id FROM submissions WHERE artwork_id = ? LIMIT 1");
$existing->execute([$artwork['id']]);
$row = $existing->fetch();

if ($row) {
    db()->prepare("
        UPDATE submissions SET audio_url=?, reflection_shaped=?, reflection_surprised=?, reflection_phrase=?, submitted_at=NOW()
        WHERE id=?
    ")->execute([$audio_url, $shaped ?: null, $surprised ?: null, $phrase ?: null, $row['id']]);
} else {
    db()->prepare("
        INSERT INTO submissions (artwork_id, audio_url, reflection_shaped, reflection_surprised, reflection_phrase)
        VALUES (?,?,?,?,?)
    ")->execute([$artwork['id'], $audio_url, $shaped ?: null, $surprised ?: null, $phrase ?: null]);
}

json_out(['ok' => true, 'audio_url' => $audio_url]);
