<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();

$is_admin = is_admin();
$is_open  = gate_open() || $is_admin;

json_out([
    'open'  => $is_open,
    'admin' => $is_admin,
]);
