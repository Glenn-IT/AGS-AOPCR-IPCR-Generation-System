<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$db   = getDB();

if (!empty($body['id'])) {
    // Mark single notification
    $db->prepare('UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?')
       ->execute([intval($body['id']), $user['id']]);
} else {
    // Mark all
    $db->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute([$user['id']]);
}

echo json_encode(['success' => true]);
