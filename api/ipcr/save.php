<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['user', 'admin']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    echo json_encode(['success' => false, 'error' => 'Invalid request body']);
    exit;
}

$action        = $body['action'] ?? 'draft';   // 'draft' or 'submit'
$ipcr_id       = intval($body['ipcr_id'] ?? 0);
$timeline_id   = intval($body['timeline_id'] ?? 0);
$covered_period = trim($body['covered_period'] ?? '');
$core          = $body['core'] ?? [];
$strategic     = $body['strategic'] ?? [];
$support       = $body['support'] ?? [];

if (!$timeline_id) {
    echo json_encode(['success' => false, 'error' => 'Timeline is required']);
    exit;
}

$db = getDB();

// Check timeline exists and is open
$tl = $db->prepare('SELECT * FROM timelines WHERE id = ? AND status = "open"');
$tl->execute([$timeline_id]);
$timeline = $tl->fetch();

if ($action === 'submit') {
    if (!$timeline) {
        echo json_encode(['success' => false, 'error' => 'The submission period is currently closed.']);
        exit;
    }
    if ($covered_period === '') {
        echo json_encode(['success' => false, 'error' => 'Covered period is required.']);
        exit;
    }
    // Enforce deadline
    if (!empty($timeline['submission_deadline']) && date('Y-m-d') > $timeline['submission_deadline']) {
        echo json_encode(['success' => false, 'error' => 'Submission deadline has passed (' . $timeline['submission_deadline'] . ').']);
        exit;
    }
}

$status = $action === 'submit' ? 'pending' : 'draft';

try {
    $db->beginTransaction();

    if ($ipcr_id > 0) {
        // Update existing — only if it belongs to this user and is still draft/pending
        $check = $db->prepare('SELECT id, status FROM ipcr_forms WHERE id = ? AND user_id = ?');
        $check->execute([$ipcr_id, $user['id']]);
        $existing = $check->fetch();

        if (!$existing) {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => 'Form not found or access denied.']);
            exit;
        }
        if (in_array($existing['status'], ['approved', 'reviewed'])) {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => 'Cannot edit a form that has already been reviewed or approved.']);
            exit;
        }

        $db->prepare('UPDATE ipcr_forms SET timeline_id=?, covered_period=?, status=?, date_submitted=? WHERE id=?')
           ->execute([$timeline_id, $covered_period, $status, $action === 'submit' ? date('Y-m-d') : null, $ipcr_id]);

        $db->prepare('DELETE FROM ipcr_items WHERE ipcr_form_id = ?')->execute([$ipcr_id]);
    } else {
        // Check no existing non-disapproved form for this user+timeline
        $dup = $db->prepare('SELECT id FROM ipcr_forms WHERE user_id=? AND timeline_id=? AND status != "disapproved"');
        $dup->execute([$user['id'], $timeline_id]);
        if ($dup->fetch()) {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => 'You already have an IPCR form for this period.']);
            exit;
        }

        $db->prepare('INSERT INTO ipcr_forms (user_id, timeline_id, covered_period, status, date_submitted) VALUES (?,?,?,?,?)')
           ->execute([$user['id'], $timeline_id, $covered_period, $status, $action === 'submit' ? date('Y-m-d') : null]);

        $ipcr_id = $db->lastInsertId();
    }

    // Insert items
    $insertItem = $db->prepare('INSERT INTO ipcr_items (ipcr_form_id, kpi_id, function_type, success_indicator, accomplishment, rating, remarks) VALUES (?,?,?,?,?,?,?)');

    foreach ([['core', $core], ['strategic', $strategic], ['support', $support]] as [$type, $items]) {
        foreach ($items as $item) {
            $insertItem->execute([
                $ipcr_id,
                !empty($item['kpi_id']) ? intval($item['kpi_id']) : null,
                $type,
                trim($item['success_indicator'] ?? ''),
                trim($item['accomplishment'] ?? ''),
                !empty($item['rating']) ? intval($item['rating']) : null,
                trim($item['remarks'] ?? ''),
            ]);
        }
    }

    // Notify admin on submit
    if ($action === 'submit') {
        $adminQ = $db->prepare('SELECT id FROM users WHERE role="admin" AND department_id=(SELECT department_id FROM users WHERE id=?) AND status="active" LIMIT 1');
        $adminQ->execute([$user['id']]);
        $admin = $adminQ->fetch();
        if ($admin) {
            $db->prepare('INSERT INTO notifications (user_id, type, message) VALUES (?,?,?)')
               ->execute([$admin['id'], 'info', $user['name'] . ' submitted an IPCR form for ' . $covered_period . '.']);
        }
        addLog($user['id'], 'Submitted IPCR form for ' . $covered_period);
    } else {
        addLog($user['id'], 'Saved IPCR draft for ' . ($covered_period ?: 'current period'));
    }

    $db->commit();
    echo json_encode(['success' => true, 'ipcr_id' => $ipcr_id, 'status' => $status,
        'message' => $action === 'submit' ? 'IPCR submitted successfully!' : 'Draft saved.']);
} catch (Exception $e) {
    $db->rollBack();
    error_log('IPCR save error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error saving form.']);
}
