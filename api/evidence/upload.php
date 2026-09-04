<?php
require_once '../../config/session.php';
require_once '../../config/helpers.php';
header('Content-Type: application/json');

$user = requireAuth(['user', 'admin', 'superadmin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$rawCategory = trim($_POST['category'] ?? 'other');
$category = 'other';
$catLower = strtolower($rawCategory);
if (strpos($catLower, 'core') !== false) {
    $category = 'core';
} elseif (strpos($catLower, 'strategic') !== false) {
    $category = 'strategic';
} elseif (strpos($catLower, 'support') !== false) {
    $category = 'support';
}

$description = trim($_POST['description'] ?? '');
$ipcr_form_id = !empty($_POST['ipcr_form_id']) ? intval($_POST['ipcr_form_id']) : null;
$opcr_form_id = !empty($_POST['opcr_form_id']) ? intval($_POST['opcr_form_id']) : null;
$targetUserId = (!empty($_POST['user_id']) && in_array($user['role'], ['admin', 'superadmin'])) ? intval($_POST['user_id']) : $user['id'];

// Check files
$uploadedFiles = [];
if (!empty($_FILES['files'])) {
    $uploadedFiles = $_FILES['files'];
} elseif (!empty($_FILES['file'])) {
    $uploadedFiles = $_FILES['file'];
}

if (empty($uploadedFiles) || empty($uploadedFiles['name'])) {
    echo json_encode(['success' => false, 'error' => 'No files uploaded.']);
    exit;
}

$uploadDir = __DIR__ . '/../../uploads/evidence/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Normalize multiple files vs single file
$names = is_array($uploadedFiles['name']) ? $uploadedFiles['name'] : [$uploadedFiles['name']];
$tmpNames = is_array($uploadedFiles['tmp_name']) ? $uploadedFiles['tmp_name'] : [$uploadedFiles['tmp_name']];
$sizes = is_array($uploadedFiles['size']) ? $uploadedFiles['size'] : [$uploadedFiles['size']];
$errors = is_array($uploadedFiles['error']) ? $uploadedFiles['error'] : [$uploadedFiles['error']];
$types = is_array($uploadedFiles['type']) ? $uploadedFiles['type'] : [$uploadedFiles['type']];

$allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx', 'xls', 'gif', 'txt', 'zip', 'csv'];
$db = getDB();
$savedFiles = [];

for ($i = 0; $i < count($names); $i++) {
    if ($errors[$i] !== UPLOAD_ERR_OK) {
        continue;
    }

    $origName = $names[$i];
    $tmpName = $tmpNames[$i];
    $fileSize = $sizes[$i];
    $mimeType = $types[$i] ?? 'application/octet-stream';

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        continue;
    }

    // 20MB limit
    if ($fileSize > 20 * 1024 * 1024) {
        continue;
    }

    $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
    $storedName = time() . '_' . uniqid() . '_' . $cleanName;
    $targetPath = $uploadDir . $storedName;
    $relPath = 'uploads/evidence/' . $storedName;

    if (move_uploaded_file($tmpName, $targetPath)) {
        $stmt = $db->prepare('INSERT INTO evidence_files (ipcr_form_id, opcr_form_id, user_id, original_name, stored_name, file_path, file_size, mime_type, category, description, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $ipcr_form_id,
            $opcr_form_id,
            $targetUserId,
            $origName,
            $storedName,
            $relPath,
            $fileSize,
            $mimeType,
            $category,
            $description ?: 'Uploaded evidence'
        ]);

        $newId = $db->lastInsertId();
        $savedFiles[] = [
            'id' => intval($newId),
            'original_name' => $origName,
            'name' => $origName,
            'stored_name' => $storedName,
            'file_path' => $relPath,
            'file_size' => $fileSize,
            'size' => $fileSize,
            'mime_type' => $mimeType,
            'category' => $category,
            'description' => $description ?: 'Uploaded evidence',
            'uploaded_at' => date('Y-m-d H:i:s'),
            'date' => date('m/d/Y'),
            'ext' => $ext
        ];
    }
}

if (empty($savedFiles)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save files or file type/size disallowed.']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => count($savedFiles) . ' file(s) uploaded successfully.',
    'files' => $savedFiles
]);
