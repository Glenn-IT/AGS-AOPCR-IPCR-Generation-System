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
 * Ensure q_rating, e_rating, t_rating columns exist in ipcr_items table.
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
    } catch (Exception $e) {
        // Silently fail if table not present
    }
    $done = true;
}
