<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user  = requireAuth(['superadmin']);
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$id = intval($input['id'] ?? 0);
if (!$id) { echo json_encode(['success' => false, 'error' => 'KPI ID required.']); exit; }

$db = getDB();
$db->prepare('UPDATE kpi_items SET is_active=0 WHERE id=?')->execute([$id]);
addLog($user['id'], "Deleted KPI ID: $id");

echo json_encode(['success' => true, 'message' => 'KPI deleted.']);
