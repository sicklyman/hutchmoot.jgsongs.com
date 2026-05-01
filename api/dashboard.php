<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();

require_admin();

$db = db();

$stmt = $db->query("
    SELECT g.id, g.name, g.max_size, g.sort_order,
           a.title AS artwork_title, a.location AS artwork_location,
           COUNT(c.id) AS checkin_count
    FROM `groups` g
    JOIN artworks a ON a.id = g.artwork_id
    LEFT JOIN checkins c ON c.group_id = g.id
    GROUP BY g.id
    ORDER BY g.sort_order, g.id
");
$groups = $stmt->fetchAll();

foreach ($groups as &$group) {
    $s = $db->prepare("
        SELECT experience_level, COUNT(*) AS cnt
        FROM checkins WHERE group_id = ?
        GROUP BY experience_level
    ");
    $s->execute([$group['id']]);
    $levels = [];
    foreach ($s->fetchAll() as $row) {
        $levels[$row['experience_level']] = (int)$row['cnt'];
    }
    $group['levels'] = (object)$levels;

    $m = $db->prepare("
        SELECT first_name, last_initial, experience_level
        FROM checkins WHERE group_id = ?
        ORDER BY checked_in_at
    ");
    $m->execute([$group['id']]);
    $group['members'] = $m->fetchAll();

    $group['checkin_count'] = (int)$group['checkin_count'];
    $group['max_size']      = (int)$group['max_size'];
}
unset($group);

json_out($groups);
