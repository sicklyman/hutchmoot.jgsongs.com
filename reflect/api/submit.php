<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_NAME') ?: 'hutchmoot'),
    getenv('DB_USER') ?: '',
    getenv('DB_PASS') ?: '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$phrase = mb_substr(trim($body['phrase'] ?? ''), 0, 300);

if (!$phrase) { http_response_code(400); echo json_encode(['error' => 'phrase required']); exit; }

$pdo->prepare("INSERT INTO reflect_responses (phrase) VALUES (?)")->execute([$phrase]);
echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
