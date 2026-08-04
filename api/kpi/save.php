<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

// Allow both superadmin and admin roles
$user  = requireAuth(['superadmin', 'admin']);
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$db = getDB();

$id        = intval($input['id'] ?? 0);
$category  = in_array($input['category'] ?? '', ['core','strategic','support']) ? $input['category'] : '';
$mfo       = trim($input['mfo'] ?? '');
$indicator = trim($input['success_indicator'] ?? '');
$target    = trim($input['target'] ?? '');
$measure   = trim($input['measure'] ?? '');
$scope     = in_array($input['scope'] ?? '', ['global','department','user']) ? $input['scope'] : null;
$assignedTo = intval($input['assigned_to'] ?? 0) ?: null;

if (!$category || !$mfo || !$indicator) {
    echo json_encode(['success' => false, 'error' => 'Category, MFO, and Success Indicator are required.']);
    exit;
}

// --- Role-based scope enforcement ---
if ($user['role'] === 'superadmin') {
    // Superadmin can create global, department, or user-scoped KPIs
    if ($scope === null) $scope = 'global';
    $deptId = $input['department_id'] ?? null;

    // If assigning to a specific admin user, validate they are actually an admin
    if ($scope === 'user' && $assignedTo) {
        $chk = $db->prepare('SELECT id, role, department_id FROM users WHERE id = ? AND status = "active"');
        $chk->execute([$assignedTo]);
        $target_user = $chk->fetch();
        if (!$target_user || $target_user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Assigned user must be an active Admin (Dean/Head).']);
            exit;
        }
        $deptId = $target_user['department_id'];
    } elseif ($scope !== 'user') {
        $assignedTo = null;
    }

} else {
    // Admin can only create department or user-scoped KPIs within their own dept
    $deptId = $user['department_id'];

    if ($scope === 'global') {
        echo json_encode(['success' => false, 'error' => 'Admins cannot create global KPIs.']);
        exit;
    }
    if ($scope === null) $scope = 'department';

    // If assigning to a specific faculty, validate they belong to this admin's dept
    if ($scope === 'user' && $assignedTo) {
        $chk = $db->prepare('SELECT id, role, department_id FROM users WHERE id = ? AND status = "active"');
        $chk->execute([$assignedTo]);
        $target_user = $chk->fetch();
        if (!$target_user || $target_user['role'] !== 'user') {
            echo json_encode(['success' => false, 'error' => 'Assigned user must be an active Faculty/Staff member.']);
            exit;
        }
        if ($target_user['department_id'] !== $deptId) {
            echo json_encode(['success' => false, 'error' => 'You can only assign KPIs to faculty in your own department.']);
            exit;
        }
    } elseif ($scope !== 'user') {
        $assignedTo = null;
    }
}

if ($id > 0) {
    // On edit: verify ownership — admin can only edit KPIs they created
    if ($user['role'] === 'admin') {
        $own = $db->prepare('SELECT created_by FROM kpi_items WHERE id = ? AND is_active = 1');
        $own->execute([$id]);
        $existing = $own->fetch();
        if (!$existing || $existing['created_by'] != $user['id']) {
            echo json_encode(['success' => false, 'error' => 'You can only edit KPIs you created.']);
            exit;
        }
    }
    $stmt = $db->prepare('UPDATE kpi_items SET category=?, mfo=?, success_indicator=?, target=?, measure=?, department_id=?, scope=?, assigned_to=? WHERE id=?');
    $stmt->execute([$category, $mfo, $indicator, $target, $measure, $deptId, $scope, $assignedTo, $id]);

    $assignedLabel = $assignedTo ? "user ID $assignedTo" : ($deptId ?? 'all');
    addLog($user['id'], "Updated KPI: $mfo (scope: $scope, assigned: $assignedLabel)");
    echo json_encode(['success' => true, 'message' => 'KPI updated.', 'id' => $id]);

} else {
    $stmt = $db->prepare('INSERT INTO kpi_items (category, mfo, success_indicator, target, measure, department_id, scope, assigned_to, is_active, created_by) VALUES (?,?,?,?,?,?,?,?,1,?)');
    $stmt->execute([$category, $mfo, $indicator, $target, $measure, $deptId, $scope, $assignedTo, $user['id']]);
    $newId = $db->lastInsertId();

    $assignedLabel = $assignedTo ? "user ID $assignedTo" : ($deptId ?? 'all');
    addLog($user['id'], "Added KPI: $mfo (scope: $scope, assigned: $assignedLabel)");
    echo json_encode(['success' => true, 'message' => 'KPI added.', 'id' => $newId]);
}
