<?php
require_once '../../../config/session.php';
header('Content-Type: application/json');

$user  = requireAuth(['superadmin']);
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$id        = intval($input['id'] ?? 0);
$category  = in_array($input['category'] ?? '', ['core','strategic','support']) ? $input['category'] : '';
$mfo       = trim($input['mfo'] ?? '');
$indicator = trim($input['success_indicator'] ?? '');
$target    = trim($input['target'] ?? '');
$measure   = trim($input['measure'] ?? '');

if (!$category || !$mfo || !$indicator) {
    echo json_encode(['success' => false, 'error' => 'Category, MFO, and Success Indicator are required.']);
    exit;
}

$db = getDB();

if ($id > 0) {
    $stmt = $db->prepare('UPDATE kpi_items SET category=?, mfo=?, success_indicator=?, target=?, measure=? WHERE id=?');
    $stmt->execute([$category, $mfo, $indicator, $target, $measure, $id]);
    addLog($user['id'], "Updated KPI: $mfo");
    echo json_encode(['success' => true, 'message' => 'KPI updated.', 'id' => $id]);
} else {
    $stmt = $db->prepare('INSERT INTO kpi_items (category, mfo, success_indicator, target, measure, is_active, created_by) VALUES (?,?,?,?,?,1,?)');
    $stmt->execute([$category, $mfo, $indicator, $target, $measure, $user['id']]);
    $newId = $db->lastInsertId();
    addLog($user['id'], "Added KPI: $mfo");
    echo json_encode(['success' => true, 'message' => 'KPI added.', 'id' => $newId]);
}
