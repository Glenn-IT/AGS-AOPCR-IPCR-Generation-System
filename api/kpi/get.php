<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['superadmin', 'admin']);
$db   = getDB();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'KPI ID required.']);
    exit;
}

$stmt = $db->prepare(
    'SELECT k.*,
            cb.name AS created_by_name,
            at.name AS assigned_to_name,
            at.department_id AS assigned_to_dept
     FROM kpi_items k
     LEFT JOIN users cb ON cb.id = k.created_by
     LEFT JOIN users at ON at.id = k.assigned_to
     WHERE k.id = ? AND k.is_active = 1'
);
$stmt->execute([$id]);
$kpi = $stmt->fetch();

if (!$kpi) {
    echo json_encode(['success' => false, 'error' => 'KPI not found.']);
    exit;
}

// Admin can only fetch KPIs visible to them
if ($user['role'] === 'admin') {
    $isVisible = (
        $kpi['scope'] === 'global' ||
        ($kpi['scope'] === 'department' && ($kpi['department_id'] === $user['department_id'] || $kpi['department_id'] === null)) ||
        ($kpi['scope'] === 'user' && $kpi['assigned_to'] == $user['id']) ||
        $kpi['created_by'] == $user['id']
    );
    if (!$isVisible) {
        echo json_encode(['success' => false, 'error' => 'Access denied.']);
        exit;
    }
}

echo json_encode(['success' => true, 'kpi' => $kpi]);
