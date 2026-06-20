<?php
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// Must have completed both forgot-password steps
if (empty($_SESSION['fp_username']) || empty($_SESSION['fp_verified'])) {
    echo json_encode(['success' => false, 'error' => 'Session expired or unauthorized. Please start over.']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$newPw    = $input['password'] ?? '';
$confirmPw = $input['confirm_password'] ?? '';

if (!$newPw || strlen($newPw) < 6) {
    echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
    exit;
}
if ($newPw !== $confirmPw) {
    echo json_encode(['success' => false, 'error' => 'Passwords do not match.']);
    exit;
}

$db       = getDB();
$username = $_SESSION['fp_username'];
$hashed   = password_hash($newPw, PASSWORD_BCRYPT);

$stmt = $db->prepare('UPDATE users SET password = ? WHERE username = ?');
$stmt->execute([$hashed, $username]);

if ($stmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'error' => 'User not found. Please start over.']);
    exit;
}

// Fetch user id for logging
$stmt = $db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$row = $stmt->fetch();
if ($row) addLog($row['id'], 'Password reset via Forgot Password');

// Clear forgot-password session flags
unset($_SESSION['fp_username'], $_SESSION['fp_verified']);

echo json_encode(['success' => true, 'message' => 'Password reset successfully.']);
