<?php
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(['success' => false, 'error' => 'Username and password are required.']);
    exit;
}

$db = getDB();
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// --- Rate limiting: count failed attempts in the last LOCKOUT_SECONDS ---
$windowStart = date('Y-m-d H:i:s', time() - LOCKOUT_SECONDS);

// Purge all expired attempts so stale rows never interfere with future cycles
$db->prepare('DELETE FROM login_attempts WHERE attempted_at <= ?')->execute([$windowStart]);
$stmt = $db->prepare(
    'SELECT COUNT(*) FROM login_attempts WHERE username = ? AND attempted_at > ?'
);
$stmt->execute([$username, $windowStart]);
$attempts = (int) $stmt->fetchColumn();

if ($attempts >= MAX_LOGIN_ATTEMPTS) {
    $oldestStmt = $db->prepare(
        'SELECT MIN(UNIX_TIMESTAMP(attempted_at)) FROM login_attempts WHERE username = ? AND attempted_at > ?'
    );
    $oldestStmt->execute([$username, $windowStart]);
    $oldestTs         = (int) $oldestStmt->fetchColumn();
    $secondsRemaining = max(1, LOCKOUT_SECONDS - (time() - $oldestTs));

    echo json_encode([
        'success'           => false,
        'error'             => 'Too many failed attempts. Account locked for ' . LOCKOUT_SECONDS . ' seconds.',
        'locked'            => true,
        'attempts'          => $attempts,
        'seconds_remaining' => $secondsRemaining,
    ]);
    exit;
}

// --- Fetch user ---
$stmt = $db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    // Log failed attempt
    $db->prepare('INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)')->execute([$username, $ip]);

    $remaining = MAX_LOGIN_ATTEMPTS - ($attempts + 1);
    $nowLocked = $remaining <= 0;
    $msg = $nowLocked
        ? 'Too many failed attempts. Account locked for ' . LOCKOUT_SECONDS . ' seconds.'
        : "Invalid username or password. {$remaining} attempt(s) remaining.";

    $payload = [
        'success'  => false,
        'error'    => $msg,
        'attempts' => $attempts + 1,
    ];
    if ($nowLocked) {
        // Re-query oldest attempt after inserting so seconds_remaining is accurate
        $windowNow   = date('Y-m-d H:i:s', time() - LOCKOUT_SECONDS);
        $oldestStmt2 = $db->prepare(
            'SELECT MIN(UNIX_TIMESTAMP(attempted_at)) FROM login_attempts WHERE username = ? AND attempted_at > ?'
        );
        $oldestStmt2->execute([$username, $windowNow]);
        $oldestTs2              = (int) $oldestStmt2->fetchColumn();
        $secsLeft               = LOCKOUT_SECONDS - (time() - $oldestTs2);
        $payload['locked']            = true;
        $payload['seconds_remaining'] = max(1, $secsLeft);
    }
    echo json_encode($payload);
    exit;
}

if ($user['status'] === 'inactive') {
    echo json_encode(['success' => false, 'error' => 'Your account has been deactivated. Contact the administrator.']);
    exit;
}

if ($user['status'] === 'pending') {
    echo json_encode(['success' => false, 'error' => 'Your account is pending approval by the administrator.']);
    exit;
}

// --- Success: clear failed attempts, update last_login, set session ---
$db->prepare('DELETE FROM login_attempts WHERE username = ?')->execute([$username]);
$db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

// Fetch department name
$deptName = '';
if ($user['department_id']) {
    $dstmt = $db->prepare('SELECT name FROM departments WHERE id = ? LIMIT 1');
    $dstmt->execute([$user['department_id']]);
    $deptName = $dstmt->fetchColumn() ?: '';
}

$sessionUser = [
    'id'              => $user['id'],
    'username'        => $user['username'],
    'role'            => $user['role'],
    'name'            => $user['name'],
    'position'        => $user['position'],
    'department'      => $user['department_id'],
    'department_id'   => $user['department_id'],
    'department_name' => $deptName,
    'email'           => $user['email'],
    'gender'          => $user['gender'],
    'avatar'          => $user['avatar'],
    'status'          => $user['status'],
];

setSessionUser($sessionUser);
addLog($user['id'], 'Logged in successfully');

echo json_encode([
    'success' => true,
    'user'    => $sessionUser,
]);
