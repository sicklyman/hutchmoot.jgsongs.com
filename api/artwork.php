<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();

$slug = trim($_GET['slug'] ?? '');
if (!$slug) json_error('slug parameter required');

$stmt = db()->prepare("SELECT * FROM artworks WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$artwork = $stmt->fetch();

if (!$artwork) json_error('Artwork not found', 404);

// Fetch latest submission
$stmt = db()->prepare("SELECT * FROM submissions WHERE artwork_id = ? ORDER BY submitted_at DESC LIMIT 1");
$stmt->execute([$artwork['id']]);
$submission = $stmt->fetch() ?: null;

$artwork['submission'] = $submission;
json_out($artwork);
