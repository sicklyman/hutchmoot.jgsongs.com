<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_NAME') ?: 'hutchmoot'),
    getenv('DB_USER') ?: '',
    getenv('DB_PASS') ?: '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$since = max(0, (int)($_GET['since'] ?? 0));

$stmt = $pdo->prepare("SELECT id, phrase FROM reflect_responses WHERE id > ? ORDER BY id ASC LIMIT 50");
$stmt->execute([$since]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
