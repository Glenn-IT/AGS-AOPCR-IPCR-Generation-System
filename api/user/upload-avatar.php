<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (empty($_FILES['avatar']) || empty($_FILES['avatar']['name'])) {
    echo json_encode(['success' => false, 'error' => 'No image file selected.']);
    exit;
}

$file = $_FILES['avatar'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload error code: ' . $file['error']]);
    exit;
}

$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File size exceeds maximum limit of 5MB.']);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
if (!in_array($ext, $allowedExts)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, WEBP, GIF.']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
if (!in_array($mime, $allowedMimes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid image content.']);
    exit;
}

$uploadDir = __DIR__ . '/../../uploads/avatars/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
$targetPath = $uploadDir . $filename;
$relativeDbPath = 'uploads/avatars/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file.']);
    exit;
}

$db = getDB();
$stmt = $db->prepare('UPDATE users SET avatar = ?, profile_picture = ? WHERE id = ?');
$stmt->execute([$relativeDbPath, $relativeDbPath, $user['id']]);

$_SESSION['user']['avatar'] = $relativeDbPath;
if (isset($_SESSION['user']['profile_picture'])) {
    $_SESSION['user']['profile_picture'] = $relativeDbPath;
}

addLog($user['id'], 'Updated profile picture');

echo json_encode([
    'success' => true,
    'message' => 'Profile picture updated successfully!',
    'avatar' => $relativeDbPath,
    'avatar_url' => BASE_URL . $relativeDbPath
]);
