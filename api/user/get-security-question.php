<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth();
$db   = getDB();

$stmt = $db->prepare('SELECT security_question FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$row  = $stmt->fetch();

echo json_encode(['success' => true, 'security_question' => $row['security_question'] ?? '']);
