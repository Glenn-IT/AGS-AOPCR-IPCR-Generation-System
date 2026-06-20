<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

requireAuth(['superadmin']);
$db = getDB();

$role   = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

$where  = ['u.role != "superadmin"'];
$params = [];

if ($role !== '') { $where[] = 'u.role = ?'; $params[] = $role; }
if ($status !== '') { $where[] = 'u.status = ?'; $params[] = $status; }

$sql = 'SELECT u.id, u.name, u.username, u.email, u.role, u.status, u.position, u.designation,
        u.gender, u.avatar, u.last_login, u.created_at,
        d.name AS department_name, d.id AS department_id
        FROM users u LEFT JOIN departments d ON u.department_id = d.id
        WHERE ' . implode(' AND ', $where) . ' ORDER BY u.name';

$stmt = $db->prepare($sql);
$stmt->execute($params);

echo json_encode(['success' => true, 'users' => $stmt->fetchAll()]);
