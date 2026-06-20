<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['user', 'admin', 'superadmin']);

$db = getDB();

$status      = $_GET['status'] ?? '';
$timeline_id = intval($_GET['timeline_id'] ?? 0);
$dept_id     = $_GET['department_id'] ?? '';

$where  = ['1=1'];
$params = [];

if ($user['role'] === 'user') {
    $where[]  = 'f.user_id = ?';
    $params[] = $user['id'];
} elseif ($user['role'] === 'admin') {
    // Admin sees only their department's users
    $where[]  = 'u.department_id = ?';
    $params[] = $user['department_id'];
} else {
    // Superadmin — optional dept filter
    if ($dept_id !== '') {
        $where[]  = 'u.department_id = ?';
        $params[] = $dept_id;
    }
}

if ($status !== '') {
    $where[]  = 'f.status = ?';
    $params[] = $status;
}
if ($timeline_id > 0) {
    $where[]  = 'f.timeline_id = ?';
    $params[] = $timeline_id;
}

$sql = 'SELECT f.id, f.user_id, f.timeline_id, f.covered_period, f.date_submitted,
        f.status, f.overall_rating, f.remarks, f.reviewed_at, f.created_at,
        u.name AS user_name, u.position, u.department_id,
        d.name AS department_name,
        t.academic_year, t.semester,
        ru.name AS reviewed_by_name
        FROM ipcr_forms f
        JOIN users u ON f.user_id = u.id
        LEFT JOIN departments d ON u.department_id = d.id
        LEFT JOIN timelines t ON f.timeline_id = t.id
        LEFT JOIN users ru ON f.reviewed_by = ru.id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY f.created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$forms = $stmt->fetchAll();

echo json_encode(['success' => true, 'forms' => $forms, 'total' => count($forms)]);
