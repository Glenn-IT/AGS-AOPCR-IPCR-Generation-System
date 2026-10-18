<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

require_once '../../config/helpers.php';

$user  = requireAuth();
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$fname    = trim($input['fname'] ?? $input['first_name'] ?? '');
$mname    = trim($input['mname'] ?? $input['middle_name'] ?? '');
$lname    = trim($input['lname'] ?? $input['last_name'] ?? '');
$name     = trim($input['name'] ?? '');

if (!$name && ($fname || $lname)) {
    $name = trim(implode(' ', array_filter([$fname, $mname, $lname])));
} elseif ($name && !$fname && !$lname) {
    $parts = preg_split('/\s+/', $name);
    $fname = $parts[0] ?? '';
    if (count($parts) === 2) {
        $lname = $parts[1];
    } elseif (count($parts) > 2) {
        $mname = $parts[1];
        $lname = implode(' ', array_slice($parts, 2));
    }
}

$email    = trim($input['email'] ?? '');
$gender   = trim($input['gender'] ?? '');
$position = trim($input['position'] ?? '');

if (!$name) {
    echo json_encode(['success' => false, 'error' => 'Name is required.']);
    exit;
}

// Keep image avatar if user has uploaded a profile picture, otherwise compute initials
$currAvatar = $user['avatar'] ?? '';
$isImage = (str_starts_with($currAvatar, 'uploads/') || preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $currAvatar));
$firstInitial = mb_substr($fname ?: $name, 0, 1);
$lastInitial  = mb_substr($lname ?: '', 0, 1);
$avatar = $isImage ? $currAvatar : strtoupper($firstInitial . ($lastInitial ?: ''));
if (!$avatar) $avatar = 'U';

$db = getDB();
ensureUserColumns($db);

$stmt = $db->prepare('UPDATE users SET name=?, first_name=?, middle_name=?, last_name=?, email=?, gender=?, position=?, avatar=? WHERE id=?');
$stmt->execute([$name, $fname ?: null, $mname ?: null, $lname ?: null, $email, $gender, $position, $avatar, $user['id']]);

$updated = $db->prepare('SELECT id,username,role,name,first_name,middle_name,last_name,position,department_id AS department,email,gender,avatar,status FROM users WHERE id=?');
$updated->execute([$user['id']]);
$row = $updated->fetch();

$_SESSION['user']['name']        = $row['name'];
$_SESSION['user']['first_name']  = $row['first_name'] ?? '';
$_SESSION['user']['middle_name'] = $row['middle_name'] ?? '';
$_SESSION['user']['last_name']   = $row['last_name'] ?? '';
$_SESSION['user']['fname']        = $row['first_name'] ?? '';
$_SESSION['user']['mname']        = $row['middle_name'] ?? '';
$_SESSION['user']['lname']        = $row['last_name'] ?? '';
$_SESSION['user']['email']       = $row['email'];
$_SESSION['user']['gender']      = $row['gender'];
$_SESSION['user']['position']    = $row['position'];
$_SESSION['user']['avatar']      = $row['avatar'];

addLog($user['id'], 'Updated profile information');
echo json_encode(['success' => true, 'user' => $row]);
