<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

// Allow both superadmin and admin roles
$user  = requireAuth(['superadmin', 'admin']);
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$db = getDB();

// Check if batch creation is requested
$rawItems = $input['items'] ?? null;

if (is_array($rawItems)) {
    if (empty($rawItems)) {
        echo json_encode(['success' => false, 'error' => 'No KPI items provided in batch.']);
        exit;
    }

    $topCategory   = in_array($input['category'] ?? '', ['core','strategic','support']) ? $input['category'] : '';
    $topScope      = in_array($input['scope'] ?? '', ['global','department','user']) ? $input['scope'] : null;
    $topAssignedTo = intval($input['assigned_to'] ?? 0) ?: null;
    $topDeptId     = $input['department_id'] ?? null;

    // Validate scope and permissions for the batch
    if ($user['role'] === 'superadmin') {
        if ($topScope === null) $topScope = 'global';
        if ($topScope === 'user' && $topAssignedTo) {
            $chk = $db->prepare('SELECT id, role, department_id FROM users WHERE id = ? AND status = "active"');
            $chk->execute([$topAssignedTo]);
            $target_user = $chk->fetch();
            if (!$target_user || $target_user['role'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Assigned user must be an active Admin (Dean/Head).']);
                exit;
            }
            $topDeptId = $target_user['department_id'];
        } elseif ($topScope !== 'user') {
            $topAssignedTo = null;
        }
    } else {
        $topDeptId = $user['department_id'];
        if ($topScope === 'global') {
            echo json_encode(['success' => false, 'error' => 'Admins cannot create global KPIs.']);
            exit;
        }
        if ($topScope === null) $topScope = 'department';
        if ($topScope === 'user' && $topAssignedTo) {
            $chk = $db->prepare('SELECT id, role, department_id FROM users WHERE id = ? AND status = "active"');
            $chk->execute([$topAssignedTo]);
            $target_user = $chk->fetch();
            if (!$target_user || $target_user['role'] !== 'user') {
                echo json_encode(['success' => false, 'error' => 'Assigned user must be an active Faculty/Staff member.']);
                exit;
            }
            if ($target_user['department_id'] !== $topDeptId) {
                echo json_encode(['success' => false, 'error' => 'You can only assign KPIs to faculty in your own department.']);
                exit;
            }
        } elseif ($topScope !== 'user') {
            $topAssignedTo = null;
        }
    }

    // Filter and validate each item in the batch
    $validItems = [];
    foreach ($rawItems as $idx => $raw) {
        $cat = in_array($raw['category'] ?? '', ['core','strategic','support']) ? $raw['category'] : $topCategory;
        $mfo = trim($raw['mfo'] ?? '');
        $ind = trim($raw['success_indicator'] ?? '');
        $tgt = trim($raw['target'] ?? '');
        $msr = trim($raw['measure'] ?? '');

        // If row is entirely blank, skip it
        if ($mfo === '' && $ind === '' && $tgt === '') {
            continue;
        }

        if (!$cat) {
            echo json_encode(['success' => false, 'error' => "Function #" . ($idx + 1) . ": Category is required."]);
            exit;
        }
        if (!$mfo) {
            echo json_encode(['success' => false, 'error' => "Function #" . ($idx + 1) . ": MFO / PAP is required."]);
            exit;
        }
        if (!$ind) {
            echo json_encode(['success' => false, 'error' => "Function #" . ($idx + 1) . ": Success Indicator is required."]);
            exit;
        }

        $validItems[] = [
            'category'          => $cat,
            'mfo'               => $mfo,
            'success_indicator' => $ind,
            'target'            => $tgt,
            'measure'           => $msr,
            'department_id'     => $topDeptId,
            'scope'             => $topScope,
            'assigned_to'       => $topAssignedTo,
        ];
    }

    if (empty($validItems)) {
        echo json_encode(['success' => false, 'error' => 'Please provide at least one valid function with MFO and Success Indicator.']);
        exit;
    }

    try {
        $db->beginTransaction();
        $stmt = $db->prepare('INSERT INTO kpi_items (category, mfo, success_indicator, target, measure, department_id, scope, assigned_to, is_active, created_by) VALUES (?,?,?,?,?,?,?,?,1,?)');
        $insertedIds = [];

        foreach ($validItems as $vi) {
            $stmt->execute([
                $vi['category'],
                $vi['mfo'],
                $vi['success_indicator'],
                $vi['target'],
                $vi['measure'],
                $vi['department_id'],
                $vi['scope'],
                $vi['assigned_to'],
                $user['id']
            ]);
            $insertedIds[] = $db->lastInsertId();
        }

        $count = count($insertedIds);
        $assignedLabel = $topAssignedTo ? "user ID $topAssignedTo" : ($topDeptId ?? 'all');
        addLog($user['id'], "Added $count KPIs (scope: $topScope, assigned: $assignedLabel)");

        $db->commit();
        echo json_encode([
            'success' => true,
            'count'   => $count,
            'message' => "$count KPIs added successfully.",
            'ids'     => $insertedIds
        ]);
        exit;
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Failed to save KPIs: ' . $e->getMessage()]);
        exit;
    }
}

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
