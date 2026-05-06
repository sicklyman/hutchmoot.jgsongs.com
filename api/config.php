<?php
if (!defined('HUTCHMOOT')) die();

// Load local credentials file if present (gitignored, lives on server only).
// Create public/api/config.local.php on the server — see .env.example for values.
$_local = __DIR__ . '/config.local.php';
if (file_exists($_local)) require_once $_local;

define('DB_HOST',      getenv('DB_HOST')      ?: 'localhost');
define('DB_NAME',      getenv('DB_NAME')      ?: 'hutchmoot');
define('DB_USER',      getenv('DB_USER')      ?: '');
define('DB_PASS',      getenv('DB_PASS')      ?: '');

define('R2_ACCOUNT_ID', getenv('R2_ACCOUNT_ID') ?: '');
define('R2_ACCESS_KEY', getenv('R2_ACCESS_KEY') ?: '');
define('R2_SECRET_KEY', getenv('R2_SECRET_KEY') ?: '');
define('R2_BUCKET',     getenv('R2_BUCKET')     ?: 'jgsongs');
define('R2_PUBLIC_URL', getenv('R2_PUBLIC_URL') ?: '');

define('ADMIN_PIN',   getenv('ADMIN_PIN')   ?: '');
define('SUBMIT_PIN',  getenv('SUBMIT_PIN')  ?: '');
define('PREVIEW_PIN', getenv('PREVIEW_PIN') ?: '110772');

function cors_headers(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Admin-Pin');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function json_out(mixed $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $message, int $code = 400): void {
    json_out(['error' => $message], $code);
}

function is_admin(): bool {
    $pin = $_SERVER['HTTP_X_ADMIN_PIN'] ?? '';
    return !empty(ADMIN_PIN) && hash_equals(ADMIN_PIN, $pin);
}

function is_preview(): bool {
    $pin = $_SERVER['HTTP_X_PREVIEW_PIN'] ?? '';
    return !empty(PREVIEW_PIN) && hash_equals(PREVIEW_PIN, $pin);
}

function require_admin(): void {
    if (!is_admin()) json_error('Unauthorized', 403);
}

function gate_open(): bool {
    try {
        $stmt = db()->prepare("SELECT body FROM content WHERE page_key = 'site_live' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        return ($row['body'] ?? '0') === '1';
    } catch (Throwable $e) {
        return false;
    }
}
