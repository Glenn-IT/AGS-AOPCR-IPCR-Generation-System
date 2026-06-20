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

$status    = $action === 'submit' ? 'pending' : 'draft';
$dept_id   = $user['department_id'];

try {
    $db->beginTransaction();

    if ($opcr_id > 0) {
        $check = $db->prepare('SELECT id, status FROM opcr_forms WHERE id=? AND admin_id=?');
        $check->execute([$opcr_id, $user['id']]);
        $existing = $check->fetch();
        if (!$existing) { $db->rollBack(); echo json_encode(['success' => false, 'error' => 'Form not found.']); exit; }
        if (in_array($existing['status'], ['approved', 'reviewed'])) {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => 'Cannot edit a reviewed or approved form.']);
            exit;
        }
        $db->prepare('UPDATE opcr_forms SET timeline_id=?,covered_period=?,status=?,date_submitted=? WHERE id=?')
           ->execute([$timeline_id, $covered_period, $status, $action==='submit'?date('Y-m-d'):null, $opcr_id]);
        $db->prepare('DELETE FROM opcr_items WHERE opcr_form_id=?')->execute([$opcr_id]);
    } else {
        // Check for existing non-disapproved form
        $dup = $db->prepare('SELECT id FROM opcr_forms WHERE admin_id=? AND timeline_id=? AND status!="disapproved"');
        $dup->execute([$user['id'], $timeline_id]);
        if ($dup->fetch()) {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => 'You already have an OPCR form for this period.']);
            exit;
        }
        $db->prepare('INSERT INTO opcr_forms (admin_id,department_id,timeline_id,covered_period,status,date_submitted) VALUES (?,?,?,?,?,?)')
           ->execute([$user['id'], $dept_id, $timeline_id, $covered_period, $status, $action==='submit'?date('Y-m-d'):null]);
        $opcr_id = $db->lastInsertId();
    }

    $insertItem = $db->prepare('INSERT INTO opcr_items (opcr_form_id,function_type,mfo,success_indicator,target,actual,budget,rating) VALUES (?,?,?,?,?,?,?,?)');
    foreach ([['core',$core],['strategic',$strategic],['support',$support]] as [$type,$items]) {
        foreach ($items as $item) {
            $insertItem->execute([
                $opcr_id, $type,
                trim($item['mfo'] ?? ''),
                trim($item['success_indicator'] ?? ''),
                trim($item['target'] ?? ''),
                trim($item['actual'] ?? ''),
                !empty($item['budget']) ? floatval($item['budget']) : 0,
                !empty($item['rating']) ? floatval($item['rating']) : null,
            ]);
        }
    }

    if ($action === 'submit') {
        // Notify superadmin
        $sa = $db->query('SELECT id FROM users WHERE role="superadmin" AND status="active" LIMIT 1')->fetch();
        if ($sa) {
            $db->prepare('INSERT INTO notifications (user_id,type,message) VALUES (?,?,?)')
               ->execute([$sa['id'], 'info', $user['name'] . ' submitted an OPCR form for ' . $covered_period . '.']);
        }
        addLog($user['id'], 'Submitted OPCR form for ' . $covered_period);
    } else {
        addLog($user['id'], 'Saved OPCR draft for ' . ($covered_period ?: 'current period'));
    }

    $db->commit();
    echo json_encode(['success' => true, 'opcr_id' => $opcr_id, 'status' => $status,
        'message' => $action === 'submit' ? 'OPCR submitted successfully!' : 'Draft saved.']);
} catch (Exception $e) {
    $db->rollBack();
    error_log('OPCR save error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error saving form.']);
}
