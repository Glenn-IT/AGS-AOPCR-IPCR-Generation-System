<?php

/**
 * Resolve the immediate supervisor for a department — the active admin
 * (Dean / Department Head / Office Head, in that order of precedence).
 * Returns null when the department has no assigned admin.
 */
function getImmediateSupervisor(PDO $db, ?string $departmentId): ?array {
    if (empty($departmentId)) return null;

    $stmt = $db->prepare(
        "SELECT name, position, designation FROM users
         WHERE role = 'admin' AND department_id = ? AND status = 'active'
         ORDER BY FIELD(designation, 'Dean', 'Department Head', 'Office Head'), name
         LIMIT 1"
    );
    $stmt->execute([$departmentId]);

    return $stmt->fetch() ?: null;
}

/**
 * Normalize a client-supplied IPCR rating: round to the nearest 0.5 and clamp
 * to the 1.0–5.0 range enforced by the ipcr_items CHECK constraint.
 * Anything below 1 (blank, 0, a stray 0.5) becomes null rather than an
 * out-of-range 0 that would abort the transaction.
 */
function normalizeRating($value): ?float {
    if ($value === null || $value === '') return null;
    $r = floatval($value);
    if ($r < 1) return null;
    return $r > 5 ? 5.0 : round($r, 2);
}

/**
 * Returns adjectival rating string based on 1.0 - 5.0 scale.
 */
function getAdjectivalRating($avg): string {
    if ($avg === null || $avg === '') return '';
    $v = floatval($avg);
    if ($v >= 4.5) return 'Outstanding';
    if ($v >= 3.5) return 'Very Satisfactory';
    if ($v >= 2.5) return 'Satisfactory';
    if ($v >= 1.5) return 'Unsatisfactory';
    if ($v > 0)    return 'Poor';
    return '';
}

/**
 * Ensure q_rating, e_rating, t_rating, mfo, target, budget, measure columns exist in ipcr_items table.
 */
function ensureIpcrColumns(PDO $db): void {
    static $done = false;
    if ($done) return;
    try {
        $cols = $db->query("SHOW COLUMNS FROM ipcr_items LIKE 'q_rating'")->fetchAll();
        if (empty($cols)) {
            $db->exec("ALTER TABLE ipcr_items 
                ADD COLUMN q_rating DECIMAL(3,2) DEFAULT NULL,
                ADD COLUMN e_rating DECIMAL(3,2) DEFAULT NULL,
                ADD COLUMN t_rating DECIMAL(3,2) DEFAULT NULL");
        }
        $mfoCols = $db->query("SHOW COLUMNS FROM ipcr_items LIKE 'mfo'")->fetchAll();
        if (empty($mfoCols)) {
            $db->exec("ALTER TABLE ipcr_items 
                ADD COLUMN mfo VARCHAR(100) DEFAULT NULL,
                ADD COLUMN target VARCHAR(200) DEFAULT NULL,
                ADD COLUMN budget DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                ADD COLUMN measure VARCHAR(200) DEFAULT NULL");
        }
        $etlCols = $db->query("SHOW COLUMNS FROM ipcr_forms LIKE 'etl_type'")->fetchAll();
        if (empty($etlCols)) {
            $db->exec("ALTER TABLE ipcr_forms 
                ADD COLUMN etl_type VARCHAR(20) DEFAULT '6 ETL',
                ADD COLUMN weight_core DECIMAL(5,2) DEFAULT 50.00,
                ADD COLUMN weight_strategic DECIMAL(5,2) DEFAULT 25.00,
                ADD COLUMN weight_support DECIMAL(5,2) DEFAULT 25.00");
        }
        $db->exec("ALTER TABLE ipcr_items MODIFY COLUMN rating DECIMAL(3,2) DEFAULT NULL");
    } catch (Exception $e) {
        // Silently fail if table not present
    }
    $done = true;
}

/**
 * Ensure q_rating, e_rating, t_rating, measure, remarks, rating DECIMAL exist in opcr_items table.
 */
function ensureOpcrColumns(PDO $db): void {
    static $done = false;
    if ($done) return;
    try {
        $cols = $db->query("SHOW COLUMNS FROM opcr_items LIKE 'q_rating'")->fetchAll();
        if (empty($cols)) {
            $db->exec("ALTER TABLE opcr_items 
                ADD COLUMN q_rating DECIMAL(3,2) DEFAULT NULL,
                ADD COLUMN e_rating DECIMAL(3,2) DEFAULT NULL,
                ADD COLUMN t_rating DECIMAL(3,2) DEFAULT NULL");
        }
        $mCols = $db->query("SHOW COLUMNS FROM opcr_items LIKE 'measure'")->fetchAll();
        if (empty($mCols)) {
            $db->exec("ALTER TABLE opcr_items 
                ADD COLUMN measure VARCHAR(200) DEFAULT NULL,
                ADD COLUMN remarks VARCHAR(200) DEFAULT NULL");
        }
        $etlCols = $db->query("SHOW COLUMNS FROM opcr_forms LIKE 'etl_type'")->fetchAll();
        if (empty($etlCols)) {
            $db->exec("ALTER TABLE opcr_forms 
                ADD COLUMN etl_type VARCHAR(20) DEFAULT '0 ETL',
                ADD COLUMN weight_core DECIMAL(5,2) DEFAULT 70.00,
                ADD COLUMN weight_strategic DECIMAL(5,2) DEFAULT 15.00,
                ADD COLUMN weight_support DECIMAL(5,2) DEFAULT 15.00");
        }
        // Ensure rating can store decimal values like 4.67
        $db->exec("ALTER TABLE opcr_items MODIFY COLUMN rating DECIMAL(3,2) DEFAULT NULL");
    } catch (Exception $e) {
        // Silently fail if table not present
    }
    $done = true;
}

/**
 * Check if there are newly added active KPIs for a user that are NOT part of their existing IPCR form.
 * Returns array: ['has_new' => bool, 'new_count' => int, 'new_kpi_ids' => array]
 */
function checkNewKpisForUser(PDO $db, array $user, int $ipcrFormId): array {
    $role = $user['role'] ?? 'user';
    $userId = intval($user['id']);
    $userDept = $user['department_id'] ?? null;

    $where = ['k.is_active = 1'];
    $params = [];

    if ($role === 'superadmin') {
        // Superadmin sees all active
    } elseif ($role === 'admin') {
        $where[] = '(
            (k.scope = "global")
            OR (k.scope = "department" AND (k.department_id = ? OR k.department_id IS NULL))
            OR (k.scope = "user" AND k.assigned_to = ?)
            OR (k.created_by = ?)
        )';
        $params[] = $userDept;
        $params[] = $userId;
        $params[] = $userId;
    } else {
        $where[] = '(
            (k.scope = "global" AND k.department_id IS NULL)
            OR (k.scope = "department" AND k.department_id = ?)
            OR (k.scope = "user" AND k.assigned_to = ?)
        )';
        $params[] = $userDept;
        $params[] = $userId;
    }

    $sql = "SELECT k.id FROM kpi_items k WHERE " . implode(' AND ', $where);
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $activeKpiIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($activeKpiIds)) {
        return ['has_new' => false, 'new_count' => 0, 'new_kpi_ids' => []];
    }

    // Get KPIs currently in this IPCR form (check both kpi_id and mfo+success_indicator)
    $itemStmt = $db->prepare("SELECT kpi_id, mfo, success_indicator FROM ipcr_items WHERE ipcr_form_id = ?");
    $itemStmt->execute([$ipcrFormId]);
    $formItems = $itemStmt->fetchAll();

    $formKpiMap = [];
    $formMfoMap = [];
    foreach ($formItems as $fi) {
        if (!empty($fi['kpi_id'])) {
            $formKpiMap[$fi['kpi_id']] = true;
        }
        $key = trim($fi['mfo'] ?? '') . '|||' . trim($fi['success_indicator'] ?? '');
        if ($key !== '|||') {
            $formMfoMap[$key] = true;
        }
    }

    $allKpiDetails = $db->query("SELECT id, mfo, success_indicator FROM kpi_items WHERE is_active = 1")->fetchAll();
    $kpiDetailMap = [];
    foreach ($allKpiDetails as $kd) {
        $kpiDetailMap[$kd['id']] = trim($kd['mfo'] ?? '') . '|||' . trim($kd['success_indicator'] ?? '');
    }

    $newKpis = [];
    foreach ($activeKpiIds as $kid) {
        if (isset($formKpiMap[$kid])) {
            continue;
        }
        $detailKey = $kpiDetailMap[$kid] ?? '';
        if ($detailKey !== '' && isset($formMfoMap[$detailKey])) {
            continue;
        }
        $newKpis[] = intval($kid);
    }

    return [
        'has_new'     => count($newKpis) > 0,
        'new_count'   => count($newKpis),
        'new_kpi_ids' => $newKpis
    ];
}

/**
 * Ensure mfo column exists in evidence_files table.
 */
function ensureEvidenceColumns(PDO $db): void {
    static $done = false;
    if ($done) return;
    try {
        $cols = $db->query("SHOW COLUMNS FROM evidence_files LIKE 'mfo'")->fetchAll();
        if (empty($cols)) {
            $db->exec("ALTER TABLE evidence_files ADD COLUMN mfo VARCHAR(255) DEFAULT NULL AFTER category");
        }
        $done = true;
    } catch (Exception $e) {
        // Silently continue if table does not exist or column already added
    }
}


