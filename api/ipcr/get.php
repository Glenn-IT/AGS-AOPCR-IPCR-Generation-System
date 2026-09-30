<?php
require_once '../../config/session.php';
require_once '../../config/helpers.php';
header('Content-Type: application/json');

$user = requireAuth(['user', 'admin', 'superadmin']);

$ipcr_id    = intval($_GET['id'] ?? 0);
$timeline_id = intval($_GET['timeline_id'] ?? 0);

$db = getDB();
ensureIpcrColumns($db);

if ($ipcr_id > 0) {
    $stmt = $db->prepare('SELECT f.*, u.name AS user_name, u.department_id, u.position,
           d.name AS department_name,
           ru.name AS reviewed_by_name, ru.position AS reviewed_by_position
           FROM ipcr_forms f
           JOIN users u ON f.user_id = u.id
           LEFT JOIN departments d ON u.department_id = d.id
           LEFT JOIN users ru ON f.reviewed_by = ru.id
           WHERE f.id = ?');
    $stmt->execute([$ipcr_id]);
    $form = $stmt->fetch();

    if (!$form) {
        echo json_encode(['success' => false, 'error' => 'Form not found.']);
        exit;
    }

    // Access control: user can only see own forms; admin can see their dept; superadmin sees all
    if ($user['role'] === 'user' && $form['user_id'] != $user['id']) {
        echo json_encode(['success' => false, 'error' => 'Access denied.']);
        exit;
    }
    if ($user['role'] === 'admin' && $form['department_id'] !== $user['department_id']) {
        echo json_encode(['success' => false, 'error' => 'Access denied.']);
        exit;
    }
} elseif ($timeline_id > 0) {
    // Get current user's form for a given timeline
    $stmt = $db->prepare('SELECT f.*, u.name AS user_name, u.department_id, u.position,
           d.name AS department_name,
           ru.name AS reviewed_by_name, ru.position AS reviewed_by_position
           FROM ipcr_forms f
           JOIN users u ON f.user_id = u.id
           LEFT JOIN departments d ON u.department_id = d.id
           LEFT JOIN users ru ON f.reviewed_by = ru.id
           WHERE f.user_id = ? AND f.timeline_id = ?
           ORDER BY f.created_at DESC LIMIT 1');
    $stmt->execute([$user['id'], $timeline_id]);
    $form = $stmt->fetch();

    if (!$form) {
        echo json_encode(['success' => true, 'form' => null]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'id or timeline_id required.']);
    exit;
}

// Immediate supervisor — whoever actually reviewed it, else the department head
$sup = getImmediateSupervisor($db, $form['department_id']);
$form['supervisor_name']     = $form['reviewed_by_name']     ?: ($sup['name'] ?? '');
$form['supervisor_position'] = $form['reviewed_by_position'] ?: ($sup['position'] ?? '');

// Load line items
$items = $db->prepare('SELECT i.*, 
    COALESCE(NULLIF(i.mfo, ""), k.mfo) AS mfo, 
    COALESCE(NULLIF(i.target, ""), k.target) AS target, 
    COALESCE(NULLIF(i.measure, ""), k.measure) AS measure,
    COALESCE(i.budget, 0) AS budget
    FROM ipcr_items i LEFT JOIN kpi_items k ON i.kpi_id = k.id
    WHERE i.ipcr_form_id = ? ORDER BY i.id');
$items->execute([$form['id']]);
$allItems = $items->fetchAll();

$form['items'] = [
    'core'      => array_values(array_filter($allItems, fn($r) => $r['function_type'] === 'core')),
    'strategic' => array_values(array_filter($allItems, fn($r) => $r['function_type'] === 'strategic')),
    'support'   => array_values(array_filter($allItems, fn($r) => $r['function_type'] === 'support')),
];

// Load evidence files
try {
    ensureEvidenceColumns($db);
    if ($form['status'] === 'draft') {
        $evStmt = $db->prepare('SELECT id, ipcr_form_id, opcr_form_id, user_id, original_name, stored_name, file_path, file_size, mime_type, category, mfo, description, uploaded_at, DATE_FORMAT(uploaded_at, "%m/%d/%Y") AS date FROM evidence_files WHERE ipcr_form_id = ? OR (ipcr_form_id IS NULL AND user_id = ?) ORDER BY uploaded_at DESC');
        $evStmt->execute([$form['id'], $form['user_id']]);
    } else {
        $evStmt = $db->prepare('SELECT id, ipcr_form_id, opcr_form_id, user_id, original_name, stored_name, file_path, file_size, mime_type, category, mfo, description, uploaded_at, DATE_FORMAT(uploaded_at, "%m/%d/%Y") AS date FROM evidence_files WHERE ipcr_form_id = ? ORDER BY uploaded_at DESC');
        $evStmt->execute([$form['id']]);
    }
    $form['evidence_files'] = $evStmt->fetchAll();
} catch (Exception $e) {
    $form['evidence_files'] = [];
}
// Check for newly added KPIs that were added after this form was submitted
$ownerUser = [
    'id' => $form['user_id'],
    'role' => ($form['user_id'] == $user['id']) ? $user['role'] : 'user',
    'department_id' => $form['department_id']
];
if ($form['user_id'] != $user['id']) {
    $uStmt = $db->prepare('SELECT role FROM users WHERE id = ?');
    $uStmt->execute([$form['user_id']]);
    $uRole = $uStmt->fetchColumn();
    if ($uRole) $ownerUser['role'] = $uRole;
}
$newKpiInfo = checkNewKpisForUser($db, $ownerUser, $form['id']);
$form['has_new_kpis']  = $newKpiInfo['has_new'];
$form['new_kpi_count'] = $newKpiInfo['new_count'];
$form['new_kpi_ids']   = $newKpiInfo['new_kpi_ids'];
$form['can_resubmit']  = ($form['status'] === 'draft') || $newKpiInfo['has_new'];

echo json_encode(['success' => true, 'form' => $form]);
