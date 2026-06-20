<?php
require_once '../../../config/session.php';
header('Content-Type: application/json');

$user  = requireAuth(['superadmin']);
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$id       = intval($input['id'] ?? 0);
$year     = trim($input['academic_year'] ?? '');
$semester = trim($input['semester'] ?? '');
$start    = $input['start_date'] ?? null;
$end      = $input['end_date'] ?? null;
$deadline = $input['submission_deadline'] ?? null;
$status   = in_array($input['status'] ?? '', ['open', 'closed']) ? $input['status'] : 'open';

if (!$year || !$semester) {
    echo json_encode(['success' => false, 'error' => 'Academic year and semester are required.']);
    exit;
}

$db = getDB();

if ($id > 0) {
    $stmt = $db->prepare('UPDATE timelines SET academic_year=?, semester=?, start_date=?, end_date=?, submission_deadline=?, status=? WHERE id=?');
    $stmt->execute([$year, $semester, $start ?: null, $end ?: null, $deadline ?: null, $status, $id]);
    addLog($user['id'], "Updated timeline: $year $semester");
    echo json_encode(['success' => true, 'message' => 'Timeline updated.', 'id' => $id]);
} else {
    $stmt = $db->prepare('INSERT INTO timelines (academic_year, semester, start_date, end_date, submission_deadline, status, created_by) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$year, $semester, $start ?: null, $end ?: null, $deadline ?: null, $status, $user['id']]);
    $newId = $db->lastInsertId();
    addLog($user['id'], "Added timeline: $year $semester");
    echo json_encode(['success' => true, 'message' => 'Timeline added.', 'id' => $newId]);
}
