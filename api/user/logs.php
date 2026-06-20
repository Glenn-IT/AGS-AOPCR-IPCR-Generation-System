<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth();
$db   = getDB();

$stmt = $db->prepare(
    'SELECT id, activity, ip_address, created_at FROM activity_logs WHERE user_id=? ORDER BY created_at DESC LIMIT 100'
);
$stmt->execute([$user['id']]);
$logs = $stmt->fetchAll();

$formatted = array_map(function($row) {
    $dt = new DateTime($row['created_at']);
    return [
        'id'       => $row['id'],
        'activity' => $row['activity'],
        'date'     => $dt->format('M d, Y'),
        'time'     => $dt->format('h:i A'),
        'ip'       => $row['ip_address'],
    ];
}, $logs);

echo json_encode(['success' => true, 'logs' => $formatted]);
