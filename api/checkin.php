<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$body  = json_decode(file_get_contents('php://input'), true) ?? [];
$level = trim($body['level'] ?? '');

if (!in_array($level, ['beginner', 'some', 'confident'], true)) {
    json_error('level must be beginner, some or confident');
}

$db = db();

// Load all groups with current check-in counts and level breakdown
$stmt = $db->query("
    SELECT g.id, g.name, g.max_size, g.sort_order,
           a.location AS artwork_location, a.slug AS artwork_slug,
           COUNT(c.id) AS checkin_count
    FROM `groups` g
    JOIN artworks a ON a.id = g.artwork_id
    LEFT JOIN checkins c ON c.group_id = g.id
    GROUP BY g.id
    ORDER BY g.sort_order, g.id
");
$groups = $stmt->fetchAll();

if (empty($groups)) json_error('No groups configured yet', 503);

// Fetch experience level breakdown per group
foreach ($groups as &$group) {
    $s = $db->prepare("SELECT experience_level, COUNT(*) AS cnt FROM checkins WHERE group_id = ? GROUP BY experience_level");
    $s->execute([$group['id']]);
    $group['levels'] = [];
    foreach ($s->fetchAll() as $row) {
        $group['levels'][$row['experience_level']] = (int)$row['cnt'];
    }
    $group['checkin_count'] = (int)$group['checkin_count'];
}
unset($group);

// Assignment algorithm:
// 1. First non-full group that doesn't yet have this experience level (sequential + balance)
// 2. Fallback: first non-full group overall (sequential, ignore balance)
// 3. Final fallback: last group (overflow — event has more attendees than expected)

$assigned = null;

foreach ($groups as $g) {
    if ($g['checkin_count'] >= $g['max_size']) continue;
    if (empty($g['levels'][$level])) {
        $assigned = $g;
        break;
    }
}

if (!$assigned) {
    foreach ($groups as $g) {
        if ($g['checkin_count'] < $g['max_size']) {
            $assigned = $g;
            break;
        }
    }
}

if (!$assigned) {
    $assigned = end($groups); // overflow
}

// Record the check-in
$db->prepare("INSERT INTO checkins (group_id, experience_level) VALUES (?, ?)")
   ->execute([$assigned['id'], $level]);

json_out([
    'group_name'   => $assigned['name'],
    'location'     => $assigned['artwork_location'],
    'artwork_slug' => $assigned['artwork_slug'],
]);
