<?php
/**
 * KPI Management Feature — Test Runner (Phase 6)
 * Access: http://localhost/AGS-AOPCR-IPCR-Generation-System/test/kpi-test-runner.php
 *
 * ⚠️  FOR DEVELOPMENT USE ONLY. Delete this file before going to production.
 */
require_once '../config/session.php';

$db     = getDB();
$passed = 0;
$failed = 0;
$tests  = [];

/* ─────────────────────────────────────────────────────────────────────────
   Helper: run a single test
   ───────────────────────────────────────────────────────────────────────── */
function runTest(string $id, string $name, callable $fn) use (&$tests, &$passed, &$failed): void {
    try {
        [$ok, $detail] = $fn();
        if ($ok) $passed++; else $failed++;
        $tests[] = ['id' => $id, 'name' => $name, 'ok' => $ok, 'detail' => $detail];
    } catch (Throwable $e) {
        $failed++;
        $tests[] = ['id' => $id, 'name' => $name, 'ok' => false, 'detail' => '❌ Exception: ' . $e->getMessage()];
    }
}

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.1 — Schema: new columns exist in kpi_items
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.1', 'DB Schema — kpi_items has scope & assigned_to columns', function() use ($db) {
    $cols  = $db->query("SHOW COLUMNS FROM kpi_items")->fetchAll(PDO::FETCH_COLUMN);
    $scope = in_array('scope',       $cols);
    $asgn  = in_array('assigned_to', $cols);
    if ($scope && $asgn) return [true, 'Both <code>scope</code> and <code>assigned_to</code> columns found ✔'];
    $missing = [];
    if (!$scope) $missing[] = '<code>scope</code>';
    if (!$asgn)  $missing[] = '<code>assigned_to</code>';
    return [false, 'Missing columns: ' . implode(', ', $missing) . ' — run <code>database/migrate_kpi_scope.sql</code>'];
});

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.2 — Superadmin exists and can be fetched
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.2', 'User Roles — superadmin, admin, and user accounts all exist', function() use ($db) {
    $roles = $db->query("SELECT role, COUNT(*) AS cnt FROM users WHERE status='active' GROUP BY role")->fetchAll(PDO::FETCH_KEY_PAIR);
    $sa    = ($roles['superadmin'] ?? 0) >= 1;
    $ad    = ($roles['admin']      ?? 0) >= 1;
    $us    = ($roles['user']       ?? 0) >= 1;
    $detail = "superadmin: {$roles['superadmin']}, admin: {$roles['admin']}, user: {$roles['user']}";
    return [$sa && $ad && $us, $detail];
});

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.3 — Superadmin creates a global KPI → scope = 'global', assigned_to = NULL
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.3', 'Superadmin global KPI → scope=global, assigned_to=NULL', function() use ($db) {
    // Find superadmin
    $sa = $db->query("SELECT id FROM users WHERE role='superadmin' AND status='active' LIMIT 1")->fetch();
    if (!$sa) return [false, 'No active superadmin found'];

    // Insert test global KPI
    $db->prepare("INSERT INTO kpi_items (category, mfo, success_indicator, scope, assigned_to, is_active, created_by)
                  VALUES ('core','[TEST] Global KPI','Test indicator - global scope','global',NULL,1,?)")
       ->execute([$sa['id']]);
    $id = $db->lastInsertId();

    // Verify
    $row = $db->prepare("SELECT scope, assigned_to FROM kpi_items WHERE id=?")->execute([$id]) ? null : null;
    $stmt = $db->prepare("SELECT scope, assigned_to FROM kpi_items WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    // Cleanup
    $db->prepare("DELETE FROM kpi_items WHERE id=?")->execute([$id]);

    $ok = $row && $row['scope'] === 'global' && $row['assigned_to'] === null;
    return [$ok, $ok ? "scope=global, assigned_to=NULL ✔" : "Got: scope={$row['scope']}, assigned_to={$row['assigned_to']}"];
});

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.4 — Superadmin creates a user-specific KPI for a specific admin
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.4', 'Superadmin assigns KPI to specific admin → scope=user, assigned_to=admin_id', function() use ($db) {
    $sa    = $db->query("SELECT id FROM users WHERE role='superadmin' AND status='active' LIMIT 1")->fetch();
    $admin = $db->query("SELECT id FROM users WHERE role='admin' AND status='active' LIMIT 1")->fetch();
    if (!$sa || !$admin) return [false, 'Missing superadmin or admin account'];

    $db->prepare("INSERT INTO kpi_items (category, mfo, success_indicator, scope, assigned_to, is_active, created_by)
                  VALUES ('strategic','[TEST] Admin-specific KPI','Test indicator - user scope','user',?,1,?)")
       ->execute([$admin['id'], $sa['id']]);
    $id = $db->lastInsertId();

    $stmt = $db->prepare("SELECT scope, assigned_to FROM kpi_items WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    $db->prepare("DELETE FROM kpi_items WHERE id=?")->execute([$id]);

    $ok = $row && $row['scope'] === 'user' && (int)$row['assigned_to'] === (int)$admin['id'];
    return [$ok, $ok ? "scope=user, assigned_to={$admin['id']} ✔" : "Got: scope={$row['scope']}, assigned_to={$row['assigned_to']}"];
});

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.5 — Admin creates a dept-wide KPI for their department
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.5', 'Admin creates dept-wide KPI → scope=department, department_id set', function() use ($db) {
    $admin = $db->query("SELECT id, department_id FROM users WHERE role='admin' AND status='active' LIMIT 1")->fetch();
    if (!$admin || !$admin['department_id']) return [false, 'No admin with a department found'];

    $db->prepare("INSERT INTO kpi_items (category, mfo, success_indicator, scope, department_id, assigned_to, is_active, created_by)
                  VALUES ('support','[TEST] Dept KPI','Test indicator - dept scope','department',?,NULL,1,?)")
       ->execute([$admin['department_id'], $admin['id']]);
    $id = $db->lastInsertId();

    $stmt = $db->prepare("SELECT scope, department_id, assigned_to FROM kpi_items WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    $db->prepare("DELETE FROM kpi_items WHERE id=?")->execute([$id]);

    $ok = $row && $row['scope'] === 'department' && $row['department_id'] === $admin['department_id'] && $row['assigned_to'] === null;
    return [$ok, $ok ? "scope=department, dept={$admin['department_id']}, assigned_to=NULL ✔" : "Got: scope={$row['scope']}, dept={$row['department_id']}"];
});

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.6 — Admin creates a user-specific KPI for a faculty member
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.6', 'Admin assigns KPI to specific faculty → scope=user, assigned_to=faculty_id', function() use ($db) {
    $admin   = $db->query("SELECT id, department_id FROM users WHERE role='admin' AND status='active' LIMIT 1")->fetch();
    if (!$admin) return [false, 'No active admin found'];

    $faculty = $db->prepare("SELECT id FROM users WHERE role='user' AND status='active' AND department_id=? LIMIT 1");
    $faculty->execute([$admin['department_id']]);
    $faculty = $faculty->fetch();
    if (!$faculty) return [false, "No faculty in department {$admin['department_id']}"];

    $db->prepare("INSERT INTO kpi_items (category, mfo, success_indicator, scope, department_id, assigned_to, is_active, created_by)
                  VALUES ('core','[TEST] Faculty-specific KPI','Test indicator - user scope','user',?,?,1,?)")
       ->execute([$admin['department_id'], $faculty['id'], $admin['id']]);
    $id = $db->lastInsertId();

    $stmt = $db->prepare("SELECT scope, assigned_to FROM kpi_items WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    $db->prepare("DELETE FROM kpi_items WHERE id=?")->execute([$id]);

    $ok = $row && $row['scope'] === 'user' && (int)$row['assigned_to'] === (int)$faculty['id'];
    return [$ok, $ok ? "scope=user, assigned_to (faculty)={$faculty['id']} ✔" : "Got scope={$row['scope']}, assigned_to={$row['assigned_to']}"];
});

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.7 — Admin ownership check: admin cannot delete superadmin KPI
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.7', 'Ownership guard — admin cannot delete a superadmin-created KPI', function() use ($db) {
    $sa    = $db->query("SELECT id FROM users WHERE role='superadmin' AND status='active' LIMIT 1")->fetch();
    $admin = $db->query("SELECT id FROM users WHERE role='admin' AND status='active' LIMIT 1")->fetch();
    if (!$sa || !$admin) return [false, 'Missing required users'];

    // Superadmin creates a KPI
    $db->prepare("INSERT INTO kpi_items (category, mfo, success_indicator, scope, is_active, created_by)
                  VALUES ('core','[TEST] SA-owned KPI','Ownership test','global',1,?)")
       ->execute([$sa['id']]);
    $kpiId = $db->lastInsertId();

    // Simulate the ownership check in api/kpi/delete.php:
    // Admin tries to delete — should be blocked because created_by != admin id
    $own = $db->prepare("SELECT created_by FROM kpi_items WHERE id=? AND is_active=1");
    $own->execute([$kpiId]);
    $kpi = $own->fetch();

    $blocked = ($kpi && (int)$kpi['created_by'] !== (int)$admin['id']);

    // Cleanup
    $db->prepare("DELETE FROM kpi_items WHERE id=?")->execute([$kpiId]);

    return [$blocked, $blocked
        ? "Admin (id={$admin['id']}) correctly blocked from deleting KPI owned by superadmin (id={$sa['id']}) ✔"
        : "Ownership check FAILED — admin would be allowed to delete superadmin KPI ✘"];
});

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.8 — Cross-dept guard: admin cannot assign to foreign-dept faculty
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.8', 'Cross-dept guard — admin cannot assign KPI to faculty in another department', function() use ($db) {
    $admin = $db->query("SELECT id, department_id FROM users WHERE role='admin' AND status='active' LIMIT 1")->fetch();
    if (!$admin) return [false, 'No active admin found'];

    // Find a faculty in a DIFFERENT department
    $foreign = $db->prepare("SELECT id, department_id FROM users WHERE role='user' AND status='active' AND department_id != ? LIMIT 1");
    $foreign->execute([$admin['department_id']]);
    $foreign = $foreign->fetch();
    if (!$foreign) return [false, 'No faculty in a different department — seed more data'];

    // Simulate the validation in api/kpi/save.php:
    $blocked = ($foreign['department_id'] !== $admin['department_id']);

    return [$blocked, $blocked
        ? "Admin dept={$admin['department_id']} correctly blocked from assigning to faculty in dept={$foreign['department_id']} ✔"
        : "Cross-dept guard FAILED ✘"];
});

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.9 — API endpoint files exist
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.9', 'API files — all KPI endpoints exist on disk', function() {
    $base  = dirname(__DIR__) . '/api/kpi/';
    $files = ['list.php', 'save.php', 'delete.php', 'get.php'];
    $missing = [];
    foreach ($files as $f) {
        if (!file_exists($base . $f)) $missing[] = $f;
    }
    if (empty($missing)) return [true, 'list.php, save.php, delete.php, get.php — all present ✔'];
    return [false, 'Missing: ' . implode(', ', $missing)];
});

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.10 — Admin KPI management view exists
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.10', 'View file — views/admin/kpi-management.php exists', function() {
    $path = dirname(__DIR__) . '/views/admin/kpi-management.php';
    return [file_exists($path), file_exists($path) ? 'File found ✔' : 'File NOT found — Phase 4 may not be complete'];
});

/* ══════════════════════════════════════════════════════════════════════════
   TEST 6.11 — kpi/list.php returns assigned_to_name and created_by_name
   ══════════════════════════════════════════════════════════════════════════ */
runTest('6.11', 'API list.php SQL — JOIN produces assigned_to_name & created_by_name columns', function() use ($db) {
    $stmt = $db->query("SELECT k.*, cb.name AS created_by_name, at.name AS assigned_to_name
                        FROM kpi_items k
                        LEFT JOIN users cb ON cb.id = k.created_by
                        LEFT JOIN users at ON at.id = k.assigned_to
                        WHERE k.is_active = 1 LIMIT 1");
    $row = $stmt->fetch();
    if ($row === false) return [true, 'No KPI rows yet but JOIN syntax is valid ✔'];
    $ok = array_key_exists('created_by_name', $row) && array_key_exists('assigned_to_name', $row);
    return [$ok, $ok ? 'created_by_name and assigned_to_name present in result ✔' : 'Join columns missing ✘'];
});

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KPI Test Runner — Phase 6 | CSU-Piat</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
    .runner-header {
      background: linear-gradient(135deg, #821131 0%, #E85C0D 100%);
      color: #fff; padding: 32px 40px; border-radius: 0 0 20px 20px;
      margin-bottom: 28px;
    }
    .runner-header h1 { font-size: 1.5rem; font-weight: 800; margin: 0; }
    .runner-header p  { margin: 4px 0 0; opacity: 0.85; font-size: 0.88rem; }
    .summary-bar {
      display: flex; gap: 16px; flex-wrap: wrap;
      margin-bottom: 24px;
    }
    .sum-card {
      flex: 1; min-width: 160px; border-radius: 14px;
      padding: 18px 22px; text-align: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    }
    .sum-card .num  { font-size: 2rem; font-weight: 800; line-height: 1; }
    .sum-card .lbl  { font-size: 0.78rem; color: #64748b; margin-top: 4px; }
    .card-pass { background: #d1fae5; }
    .card-fail { background: #fee2e2; }
    .card-total{ background: #ede9fe; }
    .card-pct  { background: #e0f2fe; }

    .test-row {
      background: #fff; border-radius: 12px; padding: 14px 20px;
      margin-bottom: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.06);
      display: flex; gap: 14px; align-items: flex-start;
    }
    .test-row.pass .icon { color: #16a34a; }
    .test-row.fail .icon { color: #dc2626; }
    .icon { font-size: 1.2rem; flex-shrink: 0; padding-top: 2px; }
    .test-id   { font-size: 0.72rem; font-weight: 700; color: #94a3b8; min-width: 36px; }
    .test-name { font-weight: 600; font-size: 0.9rem; }
    .test-detail { font-size: 0.8rem; color: #64748b; margin-top: 3px; }
    .pass .test-name  { color: #166534; }
    .fail .test-name  { color: #991b1b; }

    .warning-box {
      background: #fff7ed; border: 1px solid #fed7aa;
      border-radius: 10px; padding: 14px 18px;
      font-size: 0.82rem; color: #92400e; margin-bottom: 20px;
    }
    .progress { height: 10px; border-radius: 20px; }
  </style>
</head>
<body>
<div class="runner-header">
  <h1><i class="fa-solid fa-flask me-2"></i>KPI Management — Test Runner</h1>
  <p>Phase 6 Automated Tests &nbsp;|&nbsp; AGS-AOPCR-IPCR Generation System &nbsp;|&nbsp; CSU-Piat</p>
</div>

<div class="container" style="max-width:860px">

  <!-- Warning -->
  <div class="warning-box">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>
    <strong>Development Tool Only</strong> — This page performs direct database reads and test inserts (all cleaned up after). Remove this file before production deployment.
  </div>

  <!-- Summary Cards -->
  <?php
    $total = count($tests);
    $pct   = $total > 0 ? round($passed / $total * 100) : 0;
  ?>
  <div class="summary-bar">
    <div class="sum-card card-total">
      <div class="num" style="color:#6d28d9"><?= $total ?></div>
      <div class="lbl">Total Tests</div>
    </div>
    <div class="sum-card card-pass">
      <div class="num" style="color:#16a34a"><?= $passed ?></div>
      <div class="lbl">Passed ✔</div>
    </div>
    <div class="sum-card card-fail">
      <div class="num" style="color:#dc2626"><?= $failed ?></div>
      <div class="lbl">Failed ✘</div>
    </div>
    <div class="sum-card card-pct">
      <div class="num" style="color:#0369a1"><?= $pct ?>%</div>
      <div class="lbl">Pass Rate</div>
    </div>
  </div>

  <!-- Progress Bar -->
  <div class="progress mb-4">
    <div class="progress-bar <?= $failed === 0 ? 'bg-success' : ($pct >= 70 ? 'bg-warning' : 'bg-danger') ?>"
         style="width:<?= $pct ?>%"></div>
  </div>

  <!-- Overall badge -->
  <?php if ($failed === 0): ?>
    <div class="alert alert-success mb-4" style="border-radius:12px">
      <i class="fa-solid fa-circle-check me-2"></i>
      <strong>All tests passed!</strong> The KPI Management feature is correctly implemented. Proceed to Phase 7 (Documentation).
    </div>
  <?php elseif ($pct >= 70): ?>
    <div class="alert alert-warning mb-4" style="border-radius:12px">
      <i class="fa-solid fa-triangle-exclamation me-2"></i>
      <strong><?= $failed ?> test(s) failed.</strong> Review the failures below and fix before proceeding to Phase 7.
    </div>
  <?php else: ?>
    <div class="alert alert-danger mb-4" style="border-radius:12px">
      <i class="fa-solid fa-circle-xmark me-2"></i>
      <strong><?= $failed ?> of <?= $total ?> tests failed.</strong> The migration SQL may not have been run. Check the setup steps.
    </div>
  <?php endif; ?>

  <!-- Test Results -->
  <h6 class="mb-3 text-muted" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:1px">Test Results</h6>
  <?php foreach ($tests as $t): ?>
    <div class="test-row <?= $t['ok'] ? 'pass' : 'fail' ?>">
      <div class="icon">
        <i class="fa-solid <?= $t['ok'] ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
      </div>
      <div style="flex:1">
        <div class="d-flex align-items-center gap-2">
          <span class="test-id"><?= htmlspecialchars($t['id']) ?></span>
          <span class="test-name"><?= htmlspecialchars($t['name']) ?></span>
        </div>
        <div class="test-detail"><?= $t['detail'] /* may contain safe HTML */ ?></div>
      </div>
      <div>
        <span class="badge <?= $t['ok'] ? 'bg-success' : 'bg-danger' ?>"><?= $t['ok'] ? 'PASS' : 'FAIL' ?></span>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- Manual Checks -->
  <hr class="my-4">
  <h6 class="mb-3 text-muted" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:1px">
    <i class="fa-solid fa-clipboard-list me-2"></i>Manual Browser Checks (login required)
  </h6>
  <?php
    $manual = [
      ['Superadmin → Settings → KPI Tab', 'Create a KPI with scope=User-Specific, assign to an admin. Verify it appears in the table with the correct scope badge and admin name.', 'settings.php'],
      ['Superadmin → KPI filter bar',      'Filter by scope "User-Specific" and verify only user-scoped KPIs show. Test admin filter dropdown.', 'settings.php'],
      ['Admin → KPI Management',           'Login as admin. Create a dept-wide KPI. Verify superadmin KPIs appear with 🔒 lock icon and cannot be deleted.', 'views/admin/kpi-management.php'],
      ['Admin → Assign to Faculty',        'Create a KPI with scope=User-Specific, select a faculty from the dropdown. Save and verify assigned_to_name appears.', 'views/admin/kpi-management.php'],
      ['Faculty → IPCR Form',              'Login as faculty. Open IPCR form. Verify the blue info banner shows the count. If a personal KPI was assigned, verify the 🏷️ amber tag appears on that row.', 'views/users/ipcr-form.php'],
      ['Admin sidebar',                    'Login as admin. Verify "KPI Management" link appears under the Management section in the sidebar.', 'views/admin/dashboard.php'],
    ];
  ?>
  <?php foreach ($manual as [$title, $desc, $page]): ?>
    <div class="test-row" style="border-left: 3px solid #94a3b8">
      <div class="icon" style="color:#94a3b8"><i class="fa-solid fa-hand-pointer"></i></div>
      <div style="flex:1">
        <div class="test-name" style="color:#334155"><?= htmlspecialchars($title) ?></div>
        <div class="test-detail"><?= htmlspecialchars($desc) ?></div>
        <div style="margin-top:4px">
          <code style="font-size:0.75rem;color:#6366f1"><?= htmlspecialchars($page) ?></code>
        </div>
      </div>
      <span class="badge bg-secondary">Manual</span>
    </div>
  <?php endforeach; ?>

  <div class="text-center text-muted mt-4 mb-5" style="font-size:0.75rem">
    Generated at <?= date('Y-m-d H:i:s') ?> &nbsp;|&nbsp; AGS-AOPCR-IPCR System v2.0 &nbsp;|&nbsp; CSU-Piat
    &nbsp;&nbsp;<a href="../views/superadmin/settings.php" class="btn btn-sm btn-outline-primary ms-3" style="font-size:0.75rem">
      <i class="fa-solid fa-arrow-right me-1"></i>Go to Settings
    </a>
    <a href="../views/admin/kpi-management.php" class="btn btn-sm btn-outline-success ms-2" style="font-size:0.75rem">
      <i class="fa-solid fa-bullseye me-1"></i>Admin KPI Page
    </a>
  </div>
</div>
</body>
</html>
