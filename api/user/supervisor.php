<?php
require_once '../../config/session.php';
require_once '../../config/helpers.php';
header('Content-Type: application/json');

$user = requireAuth(['user', 'admin', 'superadmin']);

$db = getDB();

// Faculty may only resolve their own supervisor; admin/superadmin may look up others.
$target_id = $user['id'];
if ($user['role'] !== 'user' && !empty($_GET['user_id'])) {
    $target_id = intval($_GET['user_id']);
}

if ($target_id === $user['id']) {
    $department_id = $user['department_id'] ?? null;
} else {
    $stmt = $db->prepare('SELECT department_id FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$target_id]);
    $department_id = $stmt->fetchColumn() ?: null;

    // An admin may only look outside their own record within their department.
    if ($user['role'] === 'admin' && $department_id !== $user['department_id']) {
        echo json_encode(['success' => false, 'error' => 'Access denied.']);
        exit;
    }
}

echo json_encode(['success' => true, 'supervisor' => getImmediateSupervisor($db, $department_id)]);
