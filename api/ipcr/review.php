<?php
require_once '../../config/session.php';
require_once '../../config/helpers.php';
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

    ensureIpcrColumns($db);

    // Update individual item ratings if provided
    $updateItem = $db->prepare('UPDATE ipcr_items SET q_rating=?, e_rating=?, t_rating=?, rating=?, remarks=? WHERE id=? AND ipcr_form_id=?');
    $updateItemWithAcc = $db->prepare('UPDATE ipcr_items SET q_rating=?, e_rating=?, t_rating=?, rating=?, accomplishment=?, remarks=? WHERE id=? AND ipcr_form_id=?');

    foreach ($ratings as $r) {
        $itemId = intval($r['item_id'] ?? 0);
        if (!$itemId) continue;

        $q = normalizeRating($r['q_rating'] ?? $r['q'] ?? null);
        $e = normalizeRating($r['e_rating'] ?? $r['e'] ?? null);
        $t = normalizeRating($r['t_rating'] ?? $r['t'] ?? null);

        $qet = array_filter([$q, $e, $t], fn($v) => $v !== null && $v > 0);
        $avgRating = count($qet) > 0 ? round(array_sum($qet) / count($qet), 2) : normalizeRating($r['rating'] ?? null);

        $remarks = trim($r['remarks'] ?? '');
        if (($remarks === '' || in_array($remarks, ['Outstanding','Very Satisfactory','Satisfactory','Unsatisfactory','Poor'])) && $avgRating !== null) {
            $remarks = getAdjectivalRating($avgRating);
        }

        if (array_key_exists('accomplishment', $r)) {
            $accRaw = trim($r['accomplishment'] ?? '');
            $acc = null;
            if ($accRaw !== '') {
                if (is_numeric($accRaw)) {
                    $accNum = intval($accRaw);
                    if ($accNum < 1) $accNum = 1;
                    if ($accNum > 100) $accNum = 100;
                    $acc = (string)$accNum;
                } else {
                    $acc = $accRaw;
                }
            }
            $updateItemWithAcc->execute([
                $q, $e, $t, $avgRating,
                $acc,
                $remarks,
                $itemId,
                $ipcr_id,
            ]);
        } else {
            $updateItem->execute([
                $q, $e, $t, $avgRating,
                $remarks,
                $itemId,
                $ipcr_id,
            ]);
        }
    }

    // Compute overall rating using the form's ETL weights
    $formWeightStmt = $db->prepare('SELECT etl_type, weight_core, weight_strategic, weight_support FROM ipcr_forms WHERE id = ?');
    $formWeightStmt->execute([$ipcr_id]);
    $fw = $formWeightStmt->fetch() ?: [];

    $wCore = (isset($fw['weight_core']) && $fw['weight_core'] > 0) ? floatval($fw['weight_core']) / 100.0 : 0.50;
    $wStrat = (isset($fw['weight_strategic']) && $fw['weight_strategic'] > 0) ? floatval($fw['weight_strategic']) / 100.0 : 0.25;
    $wSupp = (isset($fw['weight_support']) && $fw['weight_support'] > 0) ? floatval($fw['weight_support']) / 100.0 : 0.25;

    $secAvgs = [];
    foreach (['core', 'strategic', 'support'] as $stype) {
        $stmtSec = $db->prepare('SELECT AVG(rating) FROM ipcr_items WHERE ipcr_form_id = ? AND function_type = ? AND rating IS NOT NULL');
        $stmtSec->execute([$ipcr_id, $stype]);
        $val = $stmtSec->fetchColumn();
        $secAvgs[$stype] = ($val !== null && $val !== false && is_numeric($val)) ? floatval($val) : null;
    }

    $weightedSum = 0;
    $activeWeights = 0;
    if ($secAvgs['core'] !== null) { $weightedSum += $secAvgs['core'] * $wCore; $activeWeights += $wCore; }
    if ($secAvgs['strategic'] !== null) { $weightedSum += $secAvgs['strategic'] * $wStrat; $activeWeights += $wStrat; }
    if ($secAvgs['support'] !== null) { $weightedSum += $secAvgs['support'] * $wSupp; $activeWeights += $wSupp; }

    if ($activeWeights > 0) {
        $avg = round($weightedSum / $activeWeights, 2);
    } else {
        $avgStmt = $db->prepare('SELECT AVG(rating) AS avg_rating FROM ipcr_items WHERE ipcr_form_id = ? AND rating IS NOT NULL');
        $avgStmt->execute([$ipcr_id]);
        $avgVal = $avgStmt->fetchColumn();
        $avg = ($avgVal !== null && $avgVal !== false && is_numeric($avgVal)) ? round(floatval($avgVal), 2) : 0.00;
    }

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
