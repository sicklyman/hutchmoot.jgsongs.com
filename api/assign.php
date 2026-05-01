<?php
define('HUTCHMOOT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
cors_headers();

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$db = db();

$pending = $db->query("
    SELECT id, experience_level FROM checkins WHERE group_id IS NULL ORDER BY checked_in_at
")->fetchAll();

if (empty($pending)) {
    json_out(['assigned' => 0]);
}

$groups = $db->query("
    SELECT g.id, g.max_size,
           COUNT(c.id) AS assigned_count
    FROM `groups` g
    LEFT JOIN checkins c ON c.group_id = g.id
    GROUP BY g.id
    ORDER BY g.sort_order, g.id
")->fetchAll();

if (empty($groups)) json_error('No groups configured', 503);

// Bucket by level, shuffle each
$buckets = ['confident' => [], 'some' => [], 'beginner' => []];
foreach ($pending as $p) {
    $buckets[$p['experience_level']][] = $p['id'];
}
foreach ($buckets as &$b) shuffle($b);
unset($b);

// Only open as many groups as can each receive at least 2 people (no solo attendees)
$nPending     = count($pending);
$nActive      = max(1, min(count($groups), intdiv($nPending, 2)));
$activeGroups = array_slice($groups, 0, $nActive);

// Remaining capacity per group
$capacity = [];
foreach ($activeGroups as $g) {
    $capacity[$g['id']] = max(0, (int)$g['max_size'] - (int)$g['assigned_count']);
}

// Assign: confident writers first round-robin, then some, then beginner
$allIds  = array_merge($buckets['confident'], $buckets['some'], $buckets['beginner']);
$gCount  = count($activeGroups);
$gi      = 0;
$assigned = 0;

$stmt = $db->prepare("UPDATE checkins SET group_id = ? WHERE id = ?");

foreach ($allIds as $id) {
    $found = false;
    for ($t = 0; $t < $gCount; $t++) {
        $g = $activeGroups[$gi % $gCount];
        $gi++;
        if ($capacity[$g['id']] > 0) {
            $stmt->execute([$g['id'], $id]);
            $capacity[$g['id']]--;
            $assigned++;
            $found = true;
            break;
        }
    }
    if (!$found) {
        // Overflow into last active group
        $last = end($activeGroups);
        $stmt->execute([$last['id'], $id]);
        $assigned++;
    }
}

json_out(['assigned' => $assigned]);
