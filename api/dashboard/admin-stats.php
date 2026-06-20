<?php
require_once '../../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['admin']);
$db   = getDB();

$deptId = $user['department_id'];

// Faculty count in department
$facultyCount = $db->prepare('SELECT COUNT(*) FROM users WHERE department_id=? AND role="user" AND status="active"');
$facultyCount->execute([$deptId]);

// IPCR status counts for dept
$ipcr = $db->prepare('SELECT f.status, COUNT(*) AS cnt FROM ipcr_forms f
        JOIN users u ON f.user_id = u.id
        WHERE u.department_id=? GROUP BY f.status');
$ipcr->execute([$deptId]);
$ipcrCounts = ['draft' => 0, 'pending' => 0, 'reviewed' => 0, 'approved' => 0, 'disapproved' => 0];
foreach ($ipcr->fetchAll() as $row) {
    $ipcrCounts[$row['status']] = intval($row['cnt']);
}

// Average rating in dept
$avgR = $db->prepare('SELECT AVG(f.overall_rating) FROM ipcr_forms f
        JOIN users u ON f.user_id = u.id
        WHERE u.department_id=? AND f.overall_rating > 0');
$avgR->execute([$deptId]);
$avgRating = $avgR->fetchColumn();

// Pending submissions (need review)
$pending = $db->prepare('SELECT f.id, f.covered_period, f.date_submitted, f.status, u.name AS user_name, u.position
        FROM ipcr_forms f JOIN users u ON f.user_id=u.id
        WHERE u.department_id=? AND f.status="pending" ORDER BY f.date_submitted ASC LIMIT 10');
$pending->execute([$deptId]);

// Active timeline
$tl = $db->query('SELECT * FROM timelines WHERE status="open" ORDER BY created_at DESC LIMIT 1')->fetch();

echo json_encode([
    'success'         => true,
    'faculty_count'   => intval($facultyCount->fetchColumn()),
    'ipcr_counts'     => $ipcrCounts,
    'avg_rating'      => $avgRating ? round(floatval($avgRating), 2) : null,
    'pending_reviews' => $pending->fetchAll(),
    'active_timeline' => $tl ?: null,
]);
