<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
cors_headers();

$is_admin = is_admin();
$is_open  = gate_open() || $is_admin;

$tz   = new DateTimeZone(GATE_TIMEZONE);
$gate = new DateTime(GATE_OPEN_TIME, $tz);
$now  = new DateTime('now', $tz);

json_out([
    'open'     => $is_open,
    'admin'    => $is_admin,
    'opens_at' => $gate->format('c'),
    'now'      => $now->format('c'),
]);
