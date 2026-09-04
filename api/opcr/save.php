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
if (!$body) { echo json_encode(['success' => false, 'error' => 'Invalid request body']); exit; }

$action         = $body['action'] ?? 'draft';  // 'draft' or 'submit'
$opcr_id        = intval($body['opcr_id'] ?? 0);
$timeline_id    = intval($body['timeline_id'] ?? 0);
$covered_period = trim($body['covered_period'] ?? '');
$core           = $body['core'] ?? [];
$strategic      = $body['strategic'] ?? [];
$support        = $body['support'] ?? [];

if (!$timeline_id) { echo json_encode(['success' => false, 'error' => 'Timeline is required']); exit; }

$db = getDB();
ensureOpcrColumns($db);

// Verify timeline is open for submit
if ($action === 'submit') {
    $tl = $db->prepare('SELECT * FROM timelines WHERE id=? AND status="open"');
    $tl->execute([$timeline_id]);
    if (!$tl->fetch()) {
        echo json_encode(['success' => false, 'error' => 'The submission period is currently closed.']);
        exit;
    }
    if (!$covered_period) {
        echo json_encode(['success' => false, 'error' => 'Covered period is required.']);
        exit;
    }
}

$status    = $action === 'submit' ? ($user['role'] === 'superadmin' ? 'approved' : 'pending') : 'draft';
$dept_id   = $user['department_id'] ?: 'CAMPUS';

// Ensure department exists if needed or get default department
if ($dept_id === 'CAMPUS') {
    $dCheck = $db->query("SELECT id FROM departments LIMIT 1")->fetch();
    if ($dCheck) $dept_id = $dCheck['id'];
}

try {
    $db->beginTransaction();

    if ($opcr_id > 0) {
        $check = $db->prepare('SELECT id, status FROM opcr_forms WHERE id=? AND admin_id=?');
        $check->execute([$opcr_id, $user['id']]);
        $existing = $check->fetch();
        if (!$existing && $user['role'] !== 'superadmin') { 
            $db->rollBack(); 
            echo json_encode(['success' => false, 'error' => 'Form not found.']); 
            exit; 
        }
        $db->prepare('UPDATE opcr_forms SET timeline_id=?,covered_period=?,status=?,date_submitted=?,updated_at=NOW() WHERE id=?')
           ->execute([$timeline_id, $covered_period, $status, $action==='submit'?date('Y-m-d'):null, $opcr_id]);
        $db->prepare('DELETE FROM opcr_items WHERE opcr_form_id=?')->execute([$opcr_id]);
    } else {
        // Check for existing form for this admin & timeline
        $dup = $db->prepare('SELECT id FROM opcr_forms WHERE admin_id=? AND timeline_id=? AND status!="disapproved"');
        $dup->execute([$user['id'], $timeline_id]);
        $existingRow = $dup->fetch();
        if ($existingRow) {
            $opcr_id = $existingRow['id'];
            $db->prepare('UPDATE opcr_forms SET timeline_id=?,covered_period=?,status=?,date_submitted=?,updated_at=NOW() WHERE id=?')
               ->execute([$timeline_id, $covered_period, $status, $action==='submit'?date('Y-m-d'):null, $opcr_id]);
            $db->prepare('DELETE FROM opcr_items WHERE opcr_form_id=?')->execute([$opcr_id]);
        } else {
            $db->prepare('INSERT INTO opcr_forms (admin_id,department_id,timeline_id,covered_period,status,date_submitted) VALUES (?,?,?,?,?,?)')
               ->execute([$user['id'], $dept_id, $timeline_id, $covered_period, $status, $action==='submit'?date('Y-m-d'):null]);
            $opcr_id = $db->lastInsertId();
        }
    }

    $insertItem = $db->prepare('INSERT INTO opcr_items (opcr_form_id,function_type,mfo,success_indicator,target,actual,budget,measure,q_rating,e_rating,t_rating,rating,remarks) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ([['core',$core],['strategic',$strategic],['support',$support]] as [$type,$items]) {
        foreach ($items as $item) {
            $actualRaw = trim($item['actual'] ?? $item['accomplishment'] ?? '');
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

            $q = !empty($item['q_rating']) ? floatval($item['q_rating']) : (!empty($item['q']) ? floatval($item['q']) : null);
            $e = !empty($item['e_rating']) ? floatval($item['e_rating']) : (!empty($item['e']) ? floatval($item['e']) : null);
            $t = !empty($item['t_rating']) ? floatval($item['t_rating']) : (!empty($item['t']) ? floatval($item['t']) : null);
            
            $rating = !empty($item['rating']) ? floatval($item['rating']) : null;
            if ($rating === null) {
                $ratings = array_filter([$q, $e, $t], fn($v) => $v !== null && $v >= 1 && $v <= 5);
                if (!empty($ratings)) {
                    $rating = round(array_sum($ratings) / count($ratings), 2);
                }
            }

            $remarks = trim($item['remarks'] ?? '');
            if (!$remarks && $rating > 0) {
                $remarks = getAdjectivalRating($rating);
            }

            $insertItem->execute([
                $opcr_id, $type,
                trim($item['mfo'] ?? ''),
                trim($item['success_indicator'] ?? $item['successIndicator'] ?? ''),
                trim($item['target'] ?? ''),
                $actual,
                !empty($item['budget']) ? floatval($item['budget']) : 0,
                trim($item['measure'] ?? ''),
                $q, $e, $t, $rating, $remarks
            ]);
        }
    }

    // Compute overall rating from items
    $avgStmt = $db->prepare('SELECT AVG(rating) FROM opcr_items WHERE opcr_form_id=? AND rating IS NOT NULL AND rating > 0');
    $avgStmt->execute([$opcr_id]);
    $calcOverall = floatval($avgStmt->fetchColumn()) ?: 0.00;

    $db->prepare('UPDATE opcr_forms SET overall_rating=? WHERE id=?')->execute([$calcOverall, $opcr_id]);

    if ($action === 'submit') {
        if ($user['role'] !== 'superadmin') {
            // Notify superadmin
            $sa = $db->query('SELECT id FROM users WHERE role="superadmin" AND status="active" LIMIT 1')->fetch();
            if ($sa) {
                $db->prepare('INSERT INTO notifications (user_id,type,message) VALUES (?,?,?)')
                   ->execute([$sa['id'], 'info', $user['name'] . ' submitted an OPCR form for ' . $covered_period . '.']);
            }
        }
        addLog($user['id'], 'Submitted OPCR form for ' . $covered_period);
    } else {
        addLog($user['id'], 'Saved OPCR draft for ' . ($covered_period ?: 'current period'));
    }

    $db->commit();
    echo json_encode([
        'success' => true, 
        'opcr_id' => $opcr_id, 
        'status' => $status,
        'overall_rating' => $calcOverall,
        'message' => $action === 'submit' ? 'OPCR submitted successfully!' : 'Draft saved successfully.'
    ]);
} catch (Exception $e) {
    $db->rollBack();
    error_log('OPCR save error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error saving form.']);
}
