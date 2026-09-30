<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['superadmin']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$body    = json_decode(file_get_contents('php://input'), true);
$opcr_id = intval($body['opcr_id'] ?? 0);
$status  = $body['status'] ?? '';
$remarks = trim($body['remarks'] ?? '');
$ratings = $body['ratings'] ?? [];  // [{item_id, rating, actual, remarks}]

if (!$opcr_id || !in_array($status, ['reviewed', 'approved', 'disapproved'])) {
    echo json_encode(['success' => false, 'error' => 'opcr_id and valid status required.']); exit;
}

$db = getDB();
$stmt = $db->prepare('SELECT * FROM opcr_forms WHERE id=?');
$stmt->execute([$opcr_id]);
$form = $stmt->fetch();
if (!$form) { echo json_encode(['success' => false, 'error' => 'Form not found.']); exit; }

try {
    $db->beginTransaction();

    $updateItem = $db->prepare('UPDATE opcr_items SET rating=?,actual=?,remarks=? WHERE id=? AND opcr_form_id=?');
    foreach ($ratings as $r) {
        $actualRaw = trim($r['actual'] ?? '');
        $actual = null;
        if ($actualRaw !== '') {
            if (is_numeric($actualRaw)) {
                $actNum = intval($actualRaw);
                if ($actNum < 1) $actNum = 1;
                if ($actNum > 100) $actNum = 100;
                $actual = (string)$actNum;
            } else {
                $actual = $actualRaw;
            }
        }
        $updateItem->execute([
            !empty($r['rating']) ? floatval($r['rating']) : null,
            $actual,
            trim($r['remarks'] ?? ''),
            intval($r['item_id']),
            $opcr_id,
        ]);
    }

    // Compute overall rating using the form's ETL weights
    $formWeightStmt = $db->prepare('SELECT etl_type, weight_core, weight_strategic, weight_support FROM opcr_forms WHERE id = ?');
    $formWeightStmt->execute([$opcr_id]);
    $fw = $formWeightStmt->fetch() ?: [];

    $wCore = (isset($fw['weight_core']) && $fw['weight_core'] > 0) ? floatval($fw['weight_core']) / 100.0 : 0.70;
    $wStrat = (isset($fw['weight_strategic']) && $fw['weight_strategic'] > 0) ? floatval($fw['weight_strategic']) / 100.0 : 0.15;
    $wSupp = (isset($fw['weight_support']) && $fw['weight_support'] > 0) ? floatval($fw['weight_support']) / 100.0 : 0.15;

    $secAvgs = [];
    foreach (['core', 'strategic', 'support'] as $stype) {
        $stmtSec = $db->prepare('SELECT AVG(rating) FROM opcr_items WHERE opcr_form_id = ? AND function_type = ? AND rating IS NOT NULL AND rating > 0');
        $stmtSec->execute([$opcr_id, $stype]);
        $val = $stmtSec->fetchColumn();
        $secAvgs[$stype] = ($val !== null && $val !== false && is_numeric($val)) ? floatval($val) : null;
    }

    $weightedSum = 0;
    $activeWeightsTotal = 0;
    if ($secAvgs['core'] !== null) { $weightedSum += $secAvgs['core'] * $wCore; $activeWeightsTotal += $wCore; }
    if ($secAvgs['strategic'] !== null) { $weightedSum += $secAvgs['strategic'] * $wStrat; $activeWeightsTotal += $wStrat; }
    if ($secAvgs['support'] !== null) { $weightedSum += $secAvgs['support'] * $wSupp; $activeWeightsTotal += $wSupp; }

    if ($activeWeightsTotal > 0) {
        $avgRating = round($weightedSum / $activeWeightsTotal, 2);
    } else {
        $avg = $db->prepare('SELECT AVG(rating) FROM opcr_items WHERE opcr_form_id=? AND rating IS NOT NULL');
        $avg->execute([$opcr_id]);
        $avgRating = round(floatval($avg->fetchColumn()), 2);
    }

    $db->prepare('UPDATE opcr_forms SET status=?,overall_rating=?,remarks=?,reviewed_by=?,reviewed_at=NOW() WHERE id=?')
       ->execute([$status, $avgRating, $remarks, $user['id'], $opcr_id]);

    // Notify admin
    $typeMap = ['reviewed' => 'info', 'approved' => 'success', 'disapproved' => 'danger'];
    $msgMap  = [
        'reviewed'    => 'Your OPCR has been reviewed. Overall Rating: ' . number_format($avgRating, 2),
        'approved'    => 'Your OPCR has been approved! Overall Rating: ' . number_format($avgRating, 2),
        'disapproved' => 'Your OPCR was disapproved. Remarks: ' . ($remarks ?: 'No remarks provided.'),
    ];
    $db->prepare('INSERT INTO notifications (user_id,type,message) VALUES (?,?,?)')
       ->execute([$form['admin_id'], $typeMap[$status], $msgMap[$status]]);

    addLog($user['id'], 'Reviewed OPCR #' . $opcr_id . ' — ' . strtoupper($status) . ' (rating: ' . $avgRating . ')');

    $db->commit();
    echo json_encode(['success' => true, 'overall_rating' => $avgRating, 'status' => $status,
        'message' => 'OPCR ' . $status . ' successfully.']);
} catch (Exception $e) {
    $db->rollBack();
    error_log('OPCR review error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error.']);
}
