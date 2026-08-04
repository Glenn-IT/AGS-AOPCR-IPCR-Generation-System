<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

// Allow both superadmin and admin
$user  = requireAuth(['superadmin', 'admin']);
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$id = intval($input['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'KPI ID required.']);
    exit;
}

$db = getDB();

// Fetch the KPI first
$stmt = $db->prepare('SELECT id, created_by, scope FROM kpi_items WHERE id = ? AND is_active = 1');
$stmt->execute([$id]);
$kpi = $stmt->fetch();

if (!$kpi) {
    echo json_encode(['success' => false, 'error' => 'KPI not found.']);
    exit;
}

// Admin can only delete KPIs they created — cannot delete superadmin KPIs
if ($user['role'] === 'admin') {
    if ($kpi['created_by'] != $user['id']) {
        echo json_encode(['success' => false, 'error' => 'You can only delete KPIs you created.']);
        exit;
    }
}

$db->prepare('UPDATE kpi_items SET is_active = 0 WHERE id = ?')->execute([$id]);
addLog($user['id'], "Deleted KPI ID: $id");

echo json_encode(['success' => true, 'message' => 'KPI deleted.']);
