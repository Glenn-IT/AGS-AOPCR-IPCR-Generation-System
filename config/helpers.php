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
    $r = round(floatval($value) * 2) / 2;
    if ($r < 1) return null;
    return $r > 5 ? 5.0 : $r;
}
