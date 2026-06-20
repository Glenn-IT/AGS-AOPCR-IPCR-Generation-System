<?php
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$input     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$currentPw = $input['current_password'] ?? '';
$newPw     = $input['new_password'] ?? '';
$confirmPw = $input['confirm_password'] ?? '';

if (!$currentPw || !$newPw) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}
if (strlen($newPw) < 6) {
    echo json_encode(['success' => false, 'error' => 'New password must be at least 6 characters.']);
    exit;
}
if ($newPw !== $confirmPw) {
    echo json_encode(['success' => false, 'error' => 'New passwords do not match.']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$user['id']]);
$row  = $stmt->fetch();

if (!$row || !password_verify($currentPw, $row['password'])) {
    echo json_encode(['success' => false, 'error' => 'Current password is incorrect.']);
    exit;
}

$hashed = password_hash($newPw, PASSWORD_BCRYPT);
$db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hashed, $user['id']]);
addLog($user['id'], 'Changed password');

echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
