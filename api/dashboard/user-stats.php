<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['user']);
$db   = getDB();

// IPCR form counts
$counts = $db->prepare('SELECT status, COUNT(*) AS cnt FROM ipcr_forms WHERE user_id=? GROUP BY status');
$counts->execute([$user['id']]);
$statusCounts = ['draft' => 0, 'pending' => 0, 'reviewed' => 0, 'approved' => 0, 'disapproved' => 0];
foreach ($counts->fetchAll() as $row) {
    $statusCounts[$row['status']] = intval($row['cnt']);
}

// Latest rating
$latestRating = $db->prepare('SELECT overall_rating FROM ipcr_forms WHERE user_id=? AND overall_rating > 0 ORDER BY created_at DESC LIMIT 1');
$latestRating->execute([$user['id']]);
$latest = $latestRating->fetchColumn();

// Active timeline
$tl = $db->query('SELECT * FROM timelines WHERE status="open" ORDER BY created_at DESC LIMIT 1')->fetch();

// Recent submissions (last 5)
$recent = $db->prepare('SELECT f.id, f.covered_period, f.date_submitted, f.status, f.overall_rating,
        t.academic_year, t.semester
        FROM ipcr_forms f LEFT JOIN timelines t ON f.timeline_id = t.id
        WHERE f.user_id = ? ORDER BY f.created_at DESC LIMIT 5');
$recent->execute([$user['id']]);

echo json_encode([
    'success'      => true,
    'status_counts' => $statusCounts,
    'latest_rating' => $latest ? floatval($latest) : null,
    'active_timeline' => $tl ?: null,
    'recent_forms'  => $recent->fetchAll(),
]);
