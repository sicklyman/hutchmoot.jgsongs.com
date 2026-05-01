<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    require_admin();
    db()->exec("DELETE FROM checkins");
    json_out(['ok' => true]);
}

json_error('Method not allowed', 405);
