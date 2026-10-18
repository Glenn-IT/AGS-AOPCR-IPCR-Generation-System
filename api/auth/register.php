<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$fname = trim($input['fname'] ?? $input['first_name'] ?? '');
$mname = trim($input['mname'] ?? $input['middle_name'] ?? '');
$lname = trim($input['lname'] ?? $input['last_name'] ?? '');

$name  = trim($input['name'] ?? '');
if (!$name) {
    $name = trim(implode(' ', array_filter([$fname, $mname, $lname])));
} elseif (!$fname && !$lname) {
    // If only full name was submitted, split into first, middle, last
    $parts = preg_split('/\s+/', $name);
    $fname = $parts[0] ?? '';
    if (count($parts) === 2) {
        $lname = $parts[1];
    } elseif (count($parts) > 2) {
        $mname = $parts[1];
        $lname = implode(' ', array_slice($parts, 2));
    }
}

$username    = trim($input['username'] ?? '');
$password    = $input['password'] ?? '';
$email       = trim($input['email'] ?? '');
$gender      = $input['gender'] ?? '';
$dept        = $input['department'] ?? '';
$position    = trim($input['position'] ?? '');
$designation = $input['designation'] ?? '';
$secQ        = $input['security_question'] ?? '';
$secA        = trim($input['security_answer'] ?? '');

// Validation
if (!$fname || !$lname || !$username || !$password || !$dept || !$secQ || !$secA) {
    echo json_encode(['success' => false, 'error' => 'Please fill in all required fields (First Name, Last Name, Username, etc.).']);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
    exit;
}
if ($password !== ($input['confirm_password'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Passwords do not match.']);
    exit;
}

$db = getDB();
ensureUserColumns($db);

// Check username taken
$stmt = $db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Username already exists. Please choose another.']);
    exit;
}

// Validate department exists
$stmt = $db->prepare('SELECT id FROM departments WHERE id = ? LIMIT 1');
$stmt->execute([$dept]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Invalid department selected.']);
    exit;
}

// Build avatar initials (First letter of first name + First letter of last name)
$firstInitial = mb_substr($fname ?: $name, 0, 1);
$lastInitial  = mb_substr($lname ?: '', 0, 1);
$avatar       = strtoupper($firstInitial . $lastInitial);
if (!$avatar) {
    $avatar = 'U';
}

// Hash password and security answer
$hashedPw  = password_hash($password, PASSWORD_BCRYPT);
$hashedAns = password_hash(strtolower($secA), PASSWORD_BCRYPT);

$allowedDesignations = ['Dean', 'Department Head', 'Office Head', 'Faculty', 'Staff'];
$designation = in_array($designation, $allowedDesignations) ? $designation : null;

$adminDesignations = ['Dean', 'Department Head', 'Office Head'];
$assignedRole = in_array($designation, $adminDesignations) ? ROLE_ADMIN : ROLE_USER;

$stmt = $db->prepare(
    'INSERT INTO users (username, password, role, name, first_name, middle_name, last_name, position, designation, department_id, email, gender, status, avatar, security_question, security_answer)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $username, $hashedPw, $assignedRole, $name,
    $fname ?: null, $mname ?: null, $lname ?: null,
    $position, $designation,
    $dept, $email, $gender ?: null, 'pending',
    $avatar, $secQ, $hashedAns
]);

$newId = (int) $db->lastInsertId();
addLog($newId, 'Account registered — pending approval');

echo json_encode([
    'success' => true,
    'message' => 'Account registered successfully! Please wait for administrator approval before logging in.',
]);
