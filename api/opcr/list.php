<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['admin', 'superadmin']);
$db   = getDB();

$status      = $_GET['status'] ?? '';
$timeline_id = intval($_GET['timeline_id'] ?? 0);
$dept_id     = $_GET['department_id'] ?? '';

$where  = ['1=1'];
$params = [];

if ($user['role'] === 'admin') {
    $where[]  = 'f.admin_id = ?';
    $params[] = $user['id'];
} else {
    if ($dept_id !== '') {
        $where[]  = 'f.department_id = ?';
        $params[] = $dept_id;
    }
}
if ($status !== '') { $where[] = 'f.status = ?'; $params[] = $status; }
if ($timeline_id > 0) { $where[] = 'f.timeline_id = ?'; $params[] = $timeline_id; }

$sql = 'SELECT f.id, f.admin_id, f.department_id, f.timeline_id, f.covered_period,
        f.date_submitted, f.status, f.overall_rating, f.remarks, f.created_at,
        u.name AS admin_name, u.position, d.name AS department_name,
        t.academic_year, t.semester
        FROM opcr_forms f
        JOIN users u ON f.admin_id=u.id
        LEFT JOIN departments d ON f.department_id=d.id
        LEFT JOIN timelines t ON f.timeline_id=t.id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY f.created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$forms = $stmt->fetchAll();

echo json_encode(['success' => true, 'forms' => $forms, 'total' => count($forms)]);
