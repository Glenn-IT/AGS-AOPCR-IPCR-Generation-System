<?php
require_once '../../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth();
$db   = getDB();

$stmt = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$user['id']]);
$notifications = $stmt->fetchAll();

$unread = array_filter($notifications, fn($n) => !$n['is_read']);

echo json_encode([
    'success'       => true,
    'notifications' => $notifications,
    'unread_count'  => count($unread),
]);
