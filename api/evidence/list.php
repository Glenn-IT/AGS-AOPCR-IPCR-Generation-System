<?php
require_once '../../config/session.php';
require_once '../../config/helpers.php';
header('Content-Type: application/json');

$user = requireAuth(['user', 'admin', 'superadmin']);

$targetUserId = !empty($_GET['user_id']) ? intval($_GET['user_id']) : $user['id'];
$ipcrId = !empty($_GET['ipcr_id']) ? intval($_GET['ipcr_id']) : null;
$opcrId = !empty($_GET['opcr_id']) ? intval($_GET['opcr_id']) : null;
$category = !empty($_GET['category']) ? trim($_GET['category']) : null;
$mfo = !empty($_GET['mfo']) ? trim($_GET['mfo']) : null;

$db = getDB();
ensureEvidenceColumns($db);

$where = [];
$params = [];

if ($ipcrId) {
    $where[] = 'ipcr_form_id = ?';
    $params[] = $ipcrId;
} elseif ($opcrId) {
    $where[] = 'opcr_form_id = ?';
    $params[] = $opcrId;
} else {
    // If not superadmin/admin requesting other user, restrict to targetUserId
    if ($user['role'] === 'user' || $targetUserId === $user['id']) {
        $where[] = 'user_id = ?';
        $params[] = $user['id'];
    } else {
        $where[] = 'user_id = ?';
        $params[] = $targetUserId;
    }
}

if ($category) {
    $where[] = 'category = ?';
    $params[] = $category;
}

if ($mfo) {
    $where[] = '(mfo = ? OR description = ?)';
    $params[] = $mfo;
    $params[] = $mfo;
}

$sql = 'SELECT id, ipcr_form_id, opcr_form_id, user_id, original_name, original_name AS name, stored_name, file_path, file_size, file_size AS size, mime_type, category, mfo, description, uploaded_at, DATE_FORMAT(uploaded_at, "%m/%d/%Y") AS date FROM evidence_files';
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY id DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add file extension and normalize fields
foreach ($files as &$f) {
    $f['ext'] = strtolower(pathinfo($f['original_name'], PATHINFO_EXTENSION));
    $f['id'] = intval($f['id']);
    $f['file_size'] = intval($f['file_size']);
    $f['mfo'] = $f['mfo'] ?? '';
}

echo json_encode([
    'success' => true,
    'files' => $files
]);
