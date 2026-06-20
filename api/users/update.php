<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

requireAuth(['superadmin']);

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id    = (int)($input['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Invalid user ID.']);
    exit;
}

$name        = trim($input['name'] ?? '');
$email       = trim($input['email'] ?? '');
$gender      = $input['gender'] ?? '';
$dept        = $input['department'] ?? '';
$position    = trim($input['position'] ?? '');
$designation = $input['designation'] ?? '';
$role        = $input['role'] ?? '';
$status      = $input['status'] ?? '';

if (!$name) {
    echo json_encode(['success' => false, 'error' => 'Name is required.']);
    exit;
}

$allowedRoles        = ['admin', 'user'];
$allowedStatuses     = ['active', 'inactive', 'pending'];
$allowedDesignations = ['Dean', 'Department Head', 'Office Head', 'Faculty', 'Staff'];

if ($role && !in_array($role, $allowedRoles)) {
    echo json_encode(['success' => false, 'error' => 'Invalid role.']);
    exit;
}
if ($status && !in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid status.']);
    exit;
}

$designation = in_array($designation, $allowedDesignations) ? $designation : null;

// Auto-assign role based on designation
$adminDesignations = ['Dean', 'Department Head', 'Office Head'];
if ($designation !== null) {
    $role = in_array($designation, $adminDesignations) ? 'admin' : 'user';
}

$db = getDB();

// Make sure user exists and is not superadmin
$stmt = $db->prepare('SELECT id, role FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found.']);
    exit;
}
if ($user['role'] === 'superadmin') {
    echo json_encode(['success' => false, 'error' => 'Cannot edit Super Admin account.']);
    exit;
}

// Validate department if provided
if ($dept) {
    $stmt = $db->prepare('SELECT id FROM departments WHERE id = ? LIMIT 1');
    $stmt->execute([$dept]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Invalid department.']);
        exit;
    }
}

$sets   = ['name = ?', 'email = ?', 'gender = ?', 'position = ?', 'designation = ?'];
$params = [$name, $email ?: null, $gender ?: null, $position ?: null, $designation];

if ($dept)   { $sets[] = 'department_id = ?'; $params[] = $dept; }
if ($role)   { $sets[] = 'role = ?';          $params[] = $role; }
if ($status) { $sets[] = 'status = ?';        $params[] = $status; }

$params[] = $id;

$db->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);

addLog(getSessionUser()['id'], "Edited account of user ID $id");

echo json_encode(['success' => true, 'message' => 'Account updated successfully.']);
