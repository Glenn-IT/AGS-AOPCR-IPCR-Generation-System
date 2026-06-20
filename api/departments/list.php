<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

requireAuth();
$db = getDB();

$stmt = $db->query('SELECT id, name, code FROM departments ORDER BY name');
echo json_encode(['success' => true, 'departments' => $stmt->fetchAll()]);
