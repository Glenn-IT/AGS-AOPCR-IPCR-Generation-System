<?php
require_once '../../config/session.php';
header('Content-Type: application/json');

$user = requireAuth(['superadmin']);
$db   = getDB();

// Total users/admins
$totals = $db->query('SELECT role, status, COUNT(*) AS cnt FROM users GROUP BY role, status')->fetchAll();
$stats  = ['total_users' => 0, 'active_users' => 0, 'pending_users' => 0, 'total_admins' => 0];
foreach ($totals as $r) {
    if ($r['role'] === 'user') {
        $stats['total_users'] += intval($r['cnt']);
        if ($r['status'] === 'active') $stats['active_users'] += intval($r['cnt']);
        if ($r['status'] === 'pending') $stats['pending_users'] += intval($r['cnt']);
    }
    if ($r['role'] === 'admin') $stats['total_admins'] += intval($r['cnt']);
}

// IPCR counts overall
$ipcr = $db->query('SELECT status, COUNT(*) AS cnt FROM ipcr_forms GROUP BY status')->fetchAll();
$ipcrCounts = ['draft' => 0, 'pending' => 0, 'reviewed' => 0, 'approved' => 0, 'disapproved' => 0];
foreach ($ipcr as $row) {
    $ipcrCounts[$row['status']] = intval($row['cnt']);
}

// Per-department submission summary
$deptStats = $db->query('SELECT d.name AS department, COUNT(f.id) AS total,
        SUM(f.status="approved") AS approved,
        SUM(f.status="pending") AS pending,
        ROUND(AVG(NULLIF(f.overall_rating,0)),2) AS avg_rating
        FROM departments d
        LEFT JOIN users u ON d.id = u.department_id AND u.role="user"
        LEFT JOIN ipcr_forms f ON u.id = f.user_id
        GROUP BY d.id, d.name ORDER BY d.name')->fetchAll();

// Recent pending account approvals
$pendingAccounts = $db->query('SELECT id, name, username, email, position, department_id, created_at
        FROM users WHERE status="pending" ORDER BY created_at DESC LIMIT 10')->fetchAll();

// Active timeline
$tl = $db->query('SELECT * FROM timelines WHERE status="open" ORDER BY created_at DESC LIMIT 1')->fetch();

echo json_encode([
    'success'          => true,
    'stats'            => $stats,
    'ipcr_counts'      => $ipcrCounts,
    'dept_stats'       => $deptStats,
    'pending_accounts' => $pendingAccounts,
    'active_timeline'  => $tl ?: null,
]);
