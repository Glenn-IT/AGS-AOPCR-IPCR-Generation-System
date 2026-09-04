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

$body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id = intval($body['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'File ID required']);
    exit;
}

$db = getDB();
$stmt = $db->prepare('SELECT * FROM evidence_files WHERE id = ?');
$stmt->execute([$id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    echo json_encode(['success' => false, 'error' => 'File not found']);
    exit;
}

if ($user['role'] === 'user' && $file['user_id'] != $user['id']) {
    echo json_encode(['success' => false, 'error' => 'Permission denied']);
    exit;
}

// Delete physical file
$diskPath = __DIR__ . '/../../' . $file['file_path'];
if (file_exists($diskPath)) {
    @unlink($diskPath);
}

$delStmt = $db->prepare('DELETE FROM evidence_files WHERE id = ?');
$delStmt->execute([$id]);

echo json_encode([
    'success' => true,
    'message' => 'File deleted successfully.'
]);
