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

// --- Rate limiting: Max 3 attempts, 30 seconds lockout across all accounts ---
$lockoutSecs = (int) LOCKOUT_SECONDS;
$maxAttempts = (int) MAX_LOGIN_ATTEMPTS;
$windowSecs  = 900; // 15-minute window for failed attempt accumulation

// Clean up stale attempts older than 24 hours silently
try {
    $db->exec("DELETE FROM login_attempts WHERE attempted_at <= NOW() - INTERVAL 1 DAY");
} catch (Exception $e) {}

// Check current failed attempts for this username OR IP address within the accumulation window
$ipCondition = ($ip !== 'unknown' && !empty($ip)) ? '(username = ? OR ip_address = ?)' : 'username = ?';
$queryParams = ($ip !== 'unknown' && !empty($ip)) ? [$username, $ip, $windowSecs] : [$username, $windowSecs];
$clearParams = ($ip !== 'unknown' && !empty($ip)) ? [$username, $ip] : [$username];

$stmt = $db->prepare(
    "SELECT COUNT(*) AS total,
            MAX(attempted_at) AS last_attempt_time,
            TIMESTAMPDIFF(SECOND, MAX(attempted_at), NOW()) AS elapsed_since_last
     FROM login_attempts
     WHERE {$ipCondition}
       AND attempted_at > NOW() - INTERVAL ? SECOND"
);
$stmt->execute($queryParams);
$lockStatus = $stmt->fetch(PDO::FETCH_ASSOC);

$totalAttempts    = (int) ($lockStatus['total'] ?? 0);
$elapsedSinceLast = $lockStatus['elapsed_since_last'] !== null ? (int) $lockStatus['elapsed_since_last'] : 999999;

if ($totalAttempts >= $maxAttempts) {
    if ($elapsedSinceLast < $lockoutSecs) {
        $secondsRemaining = max(1, $lockoutSecs - $elapsedSinceLast);
        echo json_encode([
            'success'           => false,
            'error'             => "Too many failed attempts. Account locked for {$lockoutSecs} seconds. Please wait {$secondsRemaining} second(s) before trying again.",
            'locked'            => true,
            'attempts'          => $totalAttempts,
            'seconds_remaining' => $secondsRemaining,
        ]);
        exit;
    } else {
        // Lockout period has elapsed; clear prior attempts to give a fresh start
        $clearSql = ($ip !== 'unknown' && !empty($ip))
            ? "DELETE FROM login_attempts WHERE username = ? OR ip_address = ?"
            : "DELETE FROM login_attempts WHERE username = ?";
        $db->prepare($clearSql)->execute($clearParams);
        $totalAttempts = 0;
    }
}

// --- Fetch user ---
$stmt = $db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

$isValid = ($user && password_verify($password, $user['password']));

if (!$isValid) {
    // Log failed attempt with current timestamp
    $db->prepare('INSERT INTO login_attempts (username, ip_address, attempted_at) VALUES (?, ?, NOW())')->execute([$username, $ip]);
    $newAttempts = $totalAttempts + 1;

    if ($newAttempts >= $maxAttempts) {
        echo json_encode([
            'success'           => false,
            'error'             => "Too many failed attempts. Account locked for {$lockoutSecs} seconds.",
            'locked'            => true,
            'attempts'          => $newAttempts,
            'seconds_remaining' => $lockoutSecs,
        ]);
        exit;
    } else {
        $remaining = $maxAttempts - $newAttempts;
        echo json_encode([
            'success'           => false,
            'error'             => "Invalid username or password. {$remaining} attempt(s) remaining.",
            'locked'            => false,
            'attempts'          => $newAttempts,
            'seconds_remaining' => 0,
        ]);
        exit;
    }
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
$clearSql = ($ip !== 'unknown' && !empty($ip))
    ? "DELETE FROM login_attempts WHERE username = ? OR ip_address = ?"
    : "DELETE FROM login_attempts WHERE username = ?";
$db->prepare($clearSql)->execute($clearParams);
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
    'first_name'      => $user['first_name'] ?? '',
    'middle_name'     => $user['middle_name'] ?? '',
    'last_name'       => $user['last_name'] ?? '',
    'fname'           => $user['first_name'] ?? '',
    'mname'           => $user['middle_name'] ?? '',
    'lname'           => $user['last_name'] ?? '',
    'position'        => $user['position'],
    'department'      => $user['department_id'],
    'department_id'   => $user['department_id'],
    'department_name' => $deptName,
    'email'           => $user['email'],
    'gender'          => $user['gender'],
    'avatar'          => $user['avatar'],
    'status'          => $user['status'],
    'last_login'      => date('Y-m-d H:i:s'),
    'lastLogin'       => date('Y-m-d H:i:s'),
];

setSessionUser($sessionUser);
addLog($user['id'], 'Logged in successfully');

echo json_encode([
    'success' => true,
    'user'    => $sessionUser,
]);
