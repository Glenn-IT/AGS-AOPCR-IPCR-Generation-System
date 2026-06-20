<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['admin', 'superadmin']);

$opcr_id    = intval($_GET['id'] ?? 0);
$timeline_id = intval($_GET['timeline_id'] ?? 0);

$db = getDB();

if ($opcr_id > 0) {
    $stmt = $db->prepare('SELECT f.*, u.name AS admin_name, u.position, d.name AS department_name
        FROM opcr_forms f JOIN users u ON f.admin_id=u.id
        LEFT JOIN departments d ON f.department_id=d.id
        WHERE f.id=?');
    $stmt->execute([$opcr_id]);
    $form = $stmt->fetch();

    if (!$form) { echo json_encode(['success' => false, 'error' => 'Form not found.']); exit; }

    if ($user['role'] === 'admin' && $form['admin_id'] != $user['id']) {
        echo json_encode(['success' => false, 'error' => 'Access denied.']); exit;
    }
} elseif ($timeline_id > 0) {
    $stmt = $db->prepare('SELECT f.*, u.name AS admin_name, u.position, d.name AS department_name
        FROM opcr_forms f JOIN users u ON f.admin_id=u.id
        LEFT JOIN departments d ON f.department_id=d.id
        WHERE f.admin_id=? AND f.timeline_id=? ORDER BY f.created_at DESC LIMIT 1');
    $stmt->execute([$user['id'], $timeline_id]);
    $form = $stmt->fetch();
    if (!$form) { echo json_encode(['success' => true, 'form' => null]); exit; }
} else {
    echo json_encode(['success' => false, 'error' => 'id or timeline_id required.']); exit;
}

$items = $db->prepare('SELECT * FROM opcr_items WHERE opcr_form_id=? ORDER BY id');
$items->execute([$form['id']]);
$allItems = $items->fetchAll();

$form['items'] = [
    'core'      => array_values(array_filter($allItems, fn($r) => $r['function_type']==='core')),
    'strategic' => array_values(array_filter($allItems, fn($r) => $r['function_type']==='strategic')),
    'support'   => array_values(array_filter($allItems, fn($r) => $r['function_type']==='support')),
];

echo json_encode(['success' => true, 'form' => $form]);
