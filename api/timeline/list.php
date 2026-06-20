<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

requireAuth();
$db = getDB();

$status = $_GET['status'] ?? '';

$where  = ['1=1'];
$params = [];

if ($status !== '') {
    $where[]  = 't.status = ?';
    $params[] = $status;
}

$stmt = $db->prepare('SELECT t.*, u.name AS created_by_name FROM timelines t
        LEFT JOIN users u ON t.created_by = u.id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY t.created_at DESC');
$stmt->execute($params);
$timelines = $stmt->fetchAll();

echo json_encode(['success' => true, 'timelines' => $timelines]);
