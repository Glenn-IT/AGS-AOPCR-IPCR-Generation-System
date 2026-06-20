<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

requireAuth(['superadmin']);
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$id = intval($input['user_id'] ?? 0);
if (!$id) { echo json_encode(['success' => false, 'error' => 'User ID required.']); exit; }

$db = getDB();
$user = $db->prepare('SELECT id, name, status, role FROM users WHERE id=? AND role != "superadmin"');
$user->execute([$id]);
$u = $user->fetch();

if (!$u) { echo json_encode(['success' => false, 'error' => 'User not found.']); exit; }

$newStatus = $u['status'] === 'active' ? 'inactive' : 'active';
$db->prepare('UPDATE users SET status=? WHERE id=?')->execute([$newStatus, $id]);

$admin = requireAuth(['superadmin']);
addLog($admin['id'], ($newStatus === 'active' ? 'Activated' : 'Deactivated') . ' account of ' . $u['name']);

echo json_encode(['success' => true, 'new_status' => $newStatus, 'message' => 'Account ' . $newStatus . '.']);
