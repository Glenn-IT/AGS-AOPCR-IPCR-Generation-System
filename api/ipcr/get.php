<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['user', 'admin', 'superadmin']);

$ipcr_id    = intval($_GET['id'] ?? 0);
$timeline_id = intval($_GET['timeline_id'] ?? 0);

$db = getDB();

if ($ipcr_id > 0) {
    $stmt = $db->prepare('SELECT f.*, u.name AS user_name, u.department_id, u.position,
           d.name AS department_name
           FROM ipcr_forms f
           JOIN users u ON f.user_id = u.id
           LEFT JOIN departments d ON u.department_id = d.id
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
           d.name AS department_name
           FROM ipcr_forms f
           JOIN users u ON f.user_id = u.id
           LEFT JOIN departments d ON u.department_id = d.id
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

// Load line items
$items = $db->prepare('SELECT i.*, k.mfo, k.target, k.measure
    FROM ipcr_items i LEFT JOIN kpi_items k ON i.kpi_id = k.id
    WHERE i.ipcr_form_id = ? ORDER BY i.id');
$items->execute([$form['id']]);
$allItems = $items->fetchAll();

$form['items'] = [
    'core'      => array_values(array_filter($allItems, fn($r) => $r['function_type'] === 'core')),
    'strategic' => array_values(array_filter($allItems, fn($r) => $r['function_type'] === 'strategic')),
    'support'   => array_values(array_filter($allItems, fn($r) => $r['function_type'] === 'support')),
];

echo json_encode(['success' => true, 'form' => $form]);
