<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth();
$db   = getDB();

$category = $_GET['category']      ?? '';
$dept_id  = $_GET['department_id'] ?? '';
$role     = $user['role'];
$userId   = $user['id'];
$userDept = $user['department_id'] ?? null;

$where  = ['k.is_active = 1'];
$params = [];

// --- Role-based scope filtering ---
if ($role === 'superadmin') {
    // Superadmin sees ALL KPIs
    if ($category !== '') {
        $where[]  = 'k.category = ?';
        $params[] = $category;
    }
    if ($dept_id !== '') {
        $where[]  = '(k.department_id = ? OR k.department_id IS NULL)';
        $params[] = $dept_id;
    }

} elseif ($role === 'admin') {
    // Admin sees:
    //   - Global KPIs assigned to them (scope='user', assigned_to = their id)
    //   - KPIs for their dept (scope='global' or scope='department', dept matches or null)
    //   - KPIs they created themselves
    $where[]  = '(
        (k.scope = "global")
        OR (k.scope = "department" AND (k.department_id = ? OR k.department_id IS NULL))
        OR (k.scope = "user" AND k.assigned_to = ?)
        OR (k.created_by = ?)
    )';
    $params[] = $userDept;
    $params[] = $userId;
    $params[] = $userId;

    if ($category !== '') {
        $where[]  = 'k.category = ?';
        $params[] = $category;
    }

} else {
    // Regular user sees:
    //   - Global KPIs (no dept restriction)
    //   - Dept-wide KPIs for their department
    //   - KPIs specifically assigned to them
    $where[]  = '(
        (k.scope = "global" AND k.department_id IS NULL)
        OR (k.scope = "department" AND k.department_id = ?)
        OR (k.scope = "user" AND k.assigned_to = ?)
    )';
    $params[] = $userDept;
    $params[] = $userId;

    if ($category !== '') {
        $where[]  = 'k.category = ?';
        $params[] = $category;
    }
}

$sql = 'SELECT k.*,
               cb.name AS created_by_name,
               at.name AS assigned_to_name,
               at.department_id AS assigned_to_dept
        FROM kpi_items k
        LEFT JOIN users cb ON cb.id = k.created_by
        LEFT JOIN users at ON at.id = k.assigned_to
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY k.category, k.id';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$grouped = ['core' => [], 'strategic' => [], 'support' => []];
foreach ($items as $item) {
    $grouped[$item['category']][] = $item;
}

echo json_encode(['success' => true, 'items' => $items, 'grouped' => $grouped]);
