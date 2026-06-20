<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['admin', 'superadmin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$ipcr_id = intval($body['ipcr_id'] ?? 0);
$status  = $body['status'] ?? '';
$remarks = trim($body['remarks'] ?? '');
$ratings = $body['ratings'] ?? [];  // array of {item_id, rating, accomplishment, remarks}

if (!$ipcr_id || !in_array($status, ['reviewed', 'approved', 'disapproved'])) {
    echo json_encode(['success' => false, 'error' => 'ipcr_id and valid status required.']);
    exit;
}

$db = getDB();

// Verify access — admin can only review their department's submissions
$stmt = $db->prepare('SELECT f.*, u.department_id FROM ipcr_forms f JOIN users u ON f.user_id = u.id WHERE f.id = ?');
$stmt->execute([$ipcr_id]);
$form = $stmt->fetch();

if (!$form) {
    echo json_encode(['success' => false, 'error' => 'Form not found.']);
    exit;
}
if ($user['role'] === 'admin' && $form['department_id'] !== $user['department_id']) {
    echo json_encode(['success' => false, 'error' => 'Access denied — not your department.']);
    exit;
}

try {
    $db->beginTransaction();

    // Update individual item ratings if provided
    $updateItem = $db->prepare('UPDATE ipcr_items SET rating=?, accomplishment=?, remarks=? WHERE id=? AND ipcr_form_id=?');
    foreach ($ratings as $r) {
        $updateItem->execute([
            !empty($r['rating']) ? intval($r['rating']) : null,
            trim($r['accomplishment'] ?? ''),
            trim($r['remarks'] ?? ''),
            intval($r['item_id']),
            $ipcr_id,
        ]);
    }

    // Compute overall rating from all items
    $avgStmt = $db->prepare('SELECT AVG(rating) AS avg_rating FROM ipcr_items WHERE ipcr_form_id = ? AND rating IS NOT NULL');
    $avgStmt->execute([$ipcr_id]);
    $avg = round(floatval($avgStmt->fetchColumn()), 2);

    // Update form status
    $db->prepare('UPDATE ipcr_forms SET status=?, overall_rating=?, remarks=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?')
       ->execute([$status, $avg, $remarks, $user['id'], $ipcr_id]);

    // Notify the user
    $typeMap = ['reviewed' => 'info', 'approved' => 'success', 'disapproved' => 'danger'];
    $msgMap  = [
        'reviewed'    => 'Your IPCR form has been reviewed. Overall Rating: ' . number_format($avg, 2),
        'approved'    => 'Your IPCR form has been approved! Overall Rating: ' . number_format($avg, 2),
        'disapproved' => 'Your IPCR form was disapproved. Remarks: ' . ($remarks ?: 'No remarks provided.'),
    ];
    $db->prepare('INSERT INTO notifications (user_id, type, message) VALUES (?,?,?)')
       ->execute([$form['user_id'], $typeMap[$status], $msgMap[$status]]);

    addLog($user['id'], 'Reviewed IPCR #' . $ipcr_id . ' — ' . strtoupper($status) . ' (rating: ' . $avg . ')');

    $db->commit();
    echo json_encode(['success' => true, 'overall_rating' => $avg, 'status' => $status,
        'message' => 'IPCR form ' . $status . ' successfully.']);
} catch (Exception $e) {
    $db->rollBack();
    error_log('IPCR review error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error saving review.']);
}
