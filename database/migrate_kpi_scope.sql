-- ============================================================
-- Migration: KPI Management — Phase 1
-- Adds `scope` and `assigned_to` columns to kpi_items
-- Run this on existing databases that already have kpi_items
-- ============================================================

-- Step 1: Add `scope` column
-- Defines how broadly this KPI applies:
--   'global'     → Superadmin KPI for all Admins (system-wide)
--   'department' → Admin KPI for all faculty in their department
--   'user'       → KPI assigned to a single specific user
ALTER TABLE kpi_items
  ADD COLUMN scope ENUM('global','department','user') NOT NULL DEFAULT 'global'
  AFTER department_id;

-- Step 2: Add `assigned_to` column
-- Points to a specific user when scope = 'user'. NULL otherwise.
ALTER TABLE kpi_items
  ADD COLUMN assigned_to INT DEFAULT NULL
  AFTER scope;

-- Step 3: Add index on the new column
ALTER TABLE kpi_items
  ADD KEY fk_kpi_assigned (assigned_to);

-- Step 4: Add foreign key constraint
ALTER TABLE kpi_items
  ADD CONSTRAINT fk_kpi_assigned
    FOREIGN KEY (assigned_to) REFERENCES users (id) ON DELETE SET NULL;

-- Step 5: Backfill existing rows
-- All existing KPIs were created by superadmin without a specific target,
-- so they are treated as 'global' scope. department_id may already be set
-- on some rows — leave those as-is (scope stays 'global' meaning
-- superadmin created it; department_id just narrows which dept it applies to).
UPDATE kpi_items
  SET scope = 'global', assigned_to = NULL
  WHERE scope IS NULL OR scope = '';

-- ============================================================
-- Verification query — run after migration to confirm
-- ============================================================
-- SELECT id, category, mfo, department_id, scope, assigned_to, created_by
-- FROM kpi_items
-- ORDER BY id;
