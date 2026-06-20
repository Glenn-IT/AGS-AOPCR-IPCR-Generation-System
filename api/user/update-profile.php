<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user  = requireAuth();
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$name     = trim($input['name'] ?? '');
$email    = trim($input['email'] ?? '');
$gender   = trim($input['gender'] ?? '');
$position = trim($input['position'] ?? '');

if (!$name) {
    echo json_encode(['success' => false, 'error' => 'Name is required.']);
    exit;
}

$avatar = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $name), 0, 2))));

$db = getDB();
$stmt = $db->prepare('UPDATE users SET name=?, email=?, gender=?, position=?, avatar=? WHERE id=?');
$stmt->execute([$name, $email, $gender, $position, $avatar, $user['id']]);

$updated = $db->prepare('SELECT id,username,role,name,position,department_id AS department,email,gender,avatar,status FROM users WHERE id=?');
$updated->execute([$user['id']]);
$row = $updated->fetch();

$_SESSION['user']['name']     = $row['name'];
$_SESSION['user']['email']    = $row['email'];
$_SESSION['user']['gender']   = $row['gender'];
$_SESSION['user']['position'] = $row['position'];
$_SESSION['user']['avatar']   = $row['avatar'];

addLog($user['id'], 'Updated profile information');
echo json_encode(['success' => true, 'user' => $row]);
