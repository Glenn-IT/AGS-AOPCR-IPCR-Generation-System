<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

requireAuth();
$db = getDB();

$category  = $_GET['category'] ?? '';
$dept_id   = $_GET['department_id'] ?? '';

$where  = ['is_active = 1'];
$params = [];

if ($category !== '') {
    $where[]  = 'category = ?';
    $params[] = $category;
}
if ($dept_id !== '') {
    $where[]  = '(department_id = ? OR department_id IS NULL)';
    $params[] = $dept_id;
}

$stmt = $db->prepare('SELECT * FROM kpi_items WHERE ' . implode(' AND ', $where) . ' ORDER BY category, id');
$stmt->execute($params);
$items = $stmt->fetchAll();

$grouped = ['core' => [], 'strategic' => [], 'support' => []];
foreach ($items as $item) {
    $grouped[$item['category']][] = $item;
}

echo json_encode(['success' => true, 'items' => $items, 'grouped' => $grouped]);
