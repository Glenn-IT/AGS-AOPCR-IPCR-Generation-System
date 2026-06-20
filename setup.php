<?php
// ============================================================
// CSU-Piat AOPCR/IPCR System — Setup / Installation Page
// Visit: http://localhost/AGS-AOPCR-IPCR-Generation-System/setup.php
// DELETE or RENAME this file after setup is complete.
// ============================================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'csu_piat_aopcr_ipcr');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

$step   = $_POST['step']   ?? 'check';
$errors = [];
$logs   = [];

// --- Helpers ---

function connectRaw(): PDO {
    return new PDO(
        'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET,
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}

function connectDB(): PDO {
    return new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}

// --- Actions ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Create Database
    if ($action === 'create_db') {
        try {
            $pdo = connectRaw();
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $logs[] = ['ok', 'Database <strong>' . DB_NAME . '</strong> created (or already exists).'];
        } catch (PDOException $e) {
            $errors[] = 'Could not create database: ' . $e->getMessage();
        }
    }

    // 2. Run Schema
    if ($action === 'run_schema') {
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (!file_exists($schemaFile)) {
            $errors[] = 'schema.sql not found at: ' . $schemaFile;
        } else {
            try {
                $pdo = connectDB();
                $sql = file_get_contents($schemaFile);
                // Split on semicolons, skip empty statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                $count = 0;
                foreach ($statements as $stmt) {
                    if (!empty($stmt) && $stmt !== '--') {
                        $pdo->exec($stmt);
                        $count++;
                    }
                }
                $logs[] = ['ok', "Schema executed: <strong>$count</strong> statements run successfully."];
            } catch (PDOException $e) {
                $errors[] = 'Schema error: ' . $e->getMessage();
            }
        }
    }

    // 3. Run Seed
    if ($action === 'run_seed') {
        $seedFile = __DIR__ . '/database/seed.php';
        if (!file_exists($seedFile)) {
            $errors[] = 'seed.php not found at: ' . $seedFile;
        } else {
            try {
                $pdo = connectDB();
                require_once $seedFile;
                $seedLogs = runSeed($pdo);
                foreach ($seedLogs as $l) {
                    $logs[] = ['ok', $l];
                }
            } catch (PDOException $e) {
                $errors[] = 'Seed error: ' . $e->getMessage();
            } catch (Throwable $e) {
                $errors[] = 'Seed error: ' . $e->getMessage();
            }
        }
    }

    // 4. Create Uploads Directory
    if ($action === 'create_uploads') {
        $path = __DIR__ . '/uploads/evidence';
        if (!is_dir($path)) {
            if (mkdir($path, 0755, true)) {
                $logs[] = ['ok', 'Created directory: <code>/uploads/evidence/</code>'];
            } else {
                $errors[] = 'Could not create /uploads/evidence/. Create it manually.';
            }
        } else {
            $logs[] = ['ok', 'Directory <code>/uploads/evidence/</code> already exists.'];
        }
        // Protect uploads from direct PHP execution
        $htaccess = __DIR__ . '/uploads/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Options -Indexes\n<Files *.php>\n  Deny from all\n</Files>\n");
            $logs[] = ['ok', 'Created <code>/uploads/.htaccess</code> to block PHP execution.'];
        }
    }

    // Redirect to avoid re-POST on refresh
    $_SESSION['setup_logs']   = $logs;
    $_SESSION['setup_errors'] = $errors;
    session_write_close();
    header('Location: setup.php');
    exit;
}

// Pull logs from session after redirect
session_start();
$logs   = $_SESSION['setup_logs']   ?? [];
$errors = $_SESSION['setup_errors'] ?? [];
unset($_SESSION['setup_logs'], $_SESSION['setup_errors']);

// --- Status Checks ---
$checks = [];

// PHP version
$checks['php'] = [
    'label' => 'PHP Version (8.0+)',
    'ok'    => version_compare(PHP_VERSION, '8.0.0', '>='),
    'value' => PHP_VERSION,
];

// PDO MySQL
$checks['pdo'] = [
    'label' => 'PDO MySQL Extension',
    'ok'    => extension_loaded('pdo_mysql'),
    'value' => extension_loaded('pdo_mysql') ? 'Enabled' : 'MISSING',
];

// MySQL connection
try {
    connectRaw();
    $checks['mysql'] = ['label' => 'MySQL Connection', 'ok' => true, 'value' => 'Connected'];
} catch (PDOException $e) {
    $checks['mysql'] = ['label' => 'MySQL Connection', 'ok' => false, 'value' => $e->getMessage()];
}

// Database exists
try {
    $pdo = connectDB();
    $checks['db'] = ['label' => 'Database: ' . DB_NAME, 'ok' => true, 'value' => 'Found'];
} catch (PDOException $e) {
    $checks['db'] = ['label' => 'Database: ' . DB_NAME, 'ok' => false, 'value' => 'Not found — create it first'];
}

// Tables
$tableNames = ['departments','users','timelines','kpi_items','ipcr_forms','ipcr_items','opcr_forms','opcr_items','evidence_files','notifications','activity_logs','login_attempts'];
$tableStatus = [];
if ($checks['db']['ok']) {
    try {
        $pdo = connectDB();
        foreach ($tableNames as $t) {
            $exists = $pdo->query("SHOW TABLES LIKE '$t'")->rowCount() > 0;
            $count  = $exists ? $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn() : 0;
            $tableStatus[$t] = ['exists' => $exists, 'count' => $count];
        }
    } catch (PDOException $e) {}
}

// Uploads dir
$checks['uploads'] = [
    'label' => '/uploads/evidence/ Directory',
    'ok'    => is_dir(__DIR__ . '/uploads/evidence'),
    'value' => is_dir(__DIR__ . '/uploads/evidence') ? 'Exists' : 'Missing',
];

// File uploads
$checks['file_uploads'] = [
    'label' => 'PHP File Uploads',
    'ok'    => ini_get('file_uploads') == '1',
    'value' => ini_get('file_uploads') == '1' ? 'On (max: ' . ini_get('upload_max_filesize') . ')' : 'DISABLED',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Setup | CSU-Piat AOPCR/IPCR System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body { background: #f4f7fb; font-family: 'Segoe UI', sans-serif; }
  .setup-card { max-width: 860px; margin: 40px auto; }
  .header-band { background: linear-gradient(135deg,#821131,#C7253E,#E85C0D); color:#fff; border-radius:12px 12px 0 0; padding:24px 28px; }
  .check-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #eee; font-size:.88rem; }
  .check-row:last-child { border:none; }
  table.table th { background:#f8f9fa; font-size:.8rem; }
  code { background:#f0f4ff; padding:2px 6px; border-radius:4px; }
  .warning-box { background:#fff3cd; border:1px solid #ffc107; border-radius:8px; padding:14px; font-size:.85rem; }
</style>
</head>
<body>
<div class="setup-card">
  <div class="card shadow-sm border-0">
    <div class="header-band">
      <h4 class="mb-0"><i class="fa-solid fa-gear me-2"></i>CSU-Piat AOPCR/IPCR System — Setup</h4>
      <small>Phase 1: Database & Configuration</small>
    </div>
    <div class="card-body p-4">

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <strong><i class="fa-solid fa-circle-xmark me-1"></i>Errors:</strong>
          <ul class="mb-0 mt-1"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <?php if ($logs): ?>
        <div class="alert alert-success">
          <strong><i class="fa-solid fa-circle-check me-1"></i>Done:</strong>
          <ul class="mb-0 mt-1"><?php foreach ($logs as $l): ?><li><?= $l[1] ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <!-- System Checks -->
      <h6 class="mb-3"><i class="fa-solid fa-list-check me-2 text-primary"></i>System Requirements</h6>
      <div class="mb-4">
        <?php foreach ($checks as $c): ?>
          <div class="check-row">
            <span><?= htmlspecialchars($c['label']) ?></span>
            <span class="<?= $c['ok'] ? 'text-success' : 'text-danger' ?>">
              <i class="fa-solid <?= $c['ok'] ? 'fa-check-circle' : 'fa-times-circle' ?> me-1"></i>
              <?= htmlspecialchars($c['value']) ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Setup Steps -->
      <h6 class="mb-3"><i class="fa-solid fa-list-ol me-2 text-primary"></i>Setup Steps (run in order)</h6>
      <div class="row g-2 mb-4">
        <div class="col-md-6">
          <form method="POST">
            <input type="hidden" name="action" value="create_db">
            <button class="btn btn-primary w-100" type="submit">
              <i class="fa-solid fa-database me-1"></i> Step 1: Create Database
            </button>
          </form>
        </div>
        <div class="col-md-6">
          <form method="POST">
            <input type="hidden" name="action" value="run_schema">
            <button class="btn btn-outline-primary w-100" type="submit" <?= $checks['db']['ok'] ? '' : 'disabled' ?>>
              <i class="fa-solid fa-table me-1"></i> Step 2: Create Tables (schema.sql)
            </button>
          </form>
        </div>
        <div class="col-md-6">
          <form method="POST">
            <input type="hidden" name="action" value="create_uploads">
            <button class="btn btn-outline-secondary w-100" type="submit">
              <i class="fa-solid fa-folder me-1"></i> Step 3: Create Uploads Directory
            </button>
          </form>
        </div>
        <div class="col-md-6">
          <form method="POST">
            <input type="hidden" name="action" value="run_seed">
            <button class="btn btn-success w-100" type="submit" <?= ($checks['db']['ok'] && !empty($tableStatus['users']['exists'])) ? '' : 'disabled' ?>>
              <i class="fa-solid fa-seedling me-1"></i> Step 4: Seed Data (50 users + sample forms)
            </button>
          </form>
        </div>
      </div>

      <!-- Table Status -->
      <?php if (!empty($tableStatus)): ?>
      <h6 class="mb-3"><i class="fa-solid fa-table me-2 text-primary"></i>Database Tables</h6>
      <div class="table-responsive mb-4">
        <table class="table table-sm table-hover mb-0">
          <thead><tr><th>Table</th><th>Status</th><th>Rows</th></tr></thead>
          <tbody>
            <?php foreach ($tableStatus as $name => $info): ?>
            <tr>
              <td><code><?= $name ?></code></td>
              <td>
                <?php if ($info['exists']): ?>
                  <span class="badge bg-success">Created</span>
                <?php else: ?>
                  <span class="badge bg-danger">Missing</span>
                <?php endif; ?>
              </td>
              <td><?= $info['exists'] ? number_format($info['count']) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- Credentials -->
      <h6 class="mb-3"><i class="fa-solid fa-key me-2 text-primary"></i>Default Login Credentials</h6>
      <div class="table-responsive mb-4">
        <table class="table table-sm table-bordered mb-0">
          <thead><tr><th>Role</th><th>Username</th><th>Password</th></tr></thead>
          <tbody>
            <tr><td><span class="badge bg-danger">Super Admin</span></td><td><code>superadmin</code></td><td><code>admin123</code></td></tr>
            <tr><td><span class="badge bg-warning text-dark">Admin</span></td><td><code>admin</code></td><td><code>admin123</code></td></tr>
            <tr><td><span class="badge bg-secondary">Faculty/Staff</span></td><td><code>faculty</code></td><td><code>faculty123</code></td></tr>
          </tbody>
        </table>
      </div>

      <div class="warning-box">
        <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
        <strong>Security reminder:</strong> Delete or rename <code>setup.php</code> after setup is complete. This file exposes database credentials and allows anyone to reset your data.
      </div>

      <div class="mt-3 text-center">
        <a href="index.php" class="btn btn-primary px-4" <?= ($checks['db']['ok'] && !empty($tableStatus['users']['exists'])) ? '' : 'disabled' ?>>
          <i class="fa-solid fa-right-to-bracket me-2"></i>Go to Login Page
        </a>
      </div>

    </div>
  </div>
  <p class="text-center text-muted mt-3" style="font-size:.78rem">CSU-Piat AOPCR/IPCR Generation System — Setup v1.0</p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
