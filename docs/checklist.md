# KPI Management Feature — Implementation Checklist

> Based on `issues.md` requirements.
> **Goal:** Superadmin creates KPIs for Admins (and specific Admins). Admin creates KPIs for their department faculty (and specific faculty members).

---

## Phase 1 — Database Schema Changes

- [x] **1.1** Add `assigned_to` column to `kpi_items` table
  - Nullable `INT` FK referencing `users(id)`
  - NULL = applies to all users in that scope (dept-wide or system-wide)
  - Non-null = KPI assigned to that specific user only
- [x] **1.2** Add `scope` column to `kpi_items` table
  - ENUM: `'global'`, `'department'`, `'user'`
  - `global` = superadmin KPI for all admins
  - `department` = admin KPI for all faculty in their dept
  - `user` = KPI targeted at a specific individual
- [x] **1.3** Update `schema.sql` with the new columns
- [x] **1.4** Write a migration SQL snippet (ALTER TABLE) for existing databases → `database/migrate_kpi_scope.sql`

---

## Phase 2 — Backend API Changes

### `api/kpi/save.php`
- [x] **2.1** Allow `admin` role to create/update KPIs (currently superadmin-only)
  - Admin can only create KPIs scoped to their own `department_id`
  - Admin cannot create global KPIs
- [x] **2.2** Accept `assigned_to` (user ID) and `scope` in POST body
- [x] **2.3** Validate `assigned_to` user belongs to the correct department
  - Superadmin assigning to admin → validate target is an `admin` role user
  - Admin assigning to faculty → validate target is a `user` role in their dept
- [x] **2.4** Log activity: `"Admin created KPI for [user name]"` or `"Superadmin created KPI for [dept]"`

### `api/kpi/list.php`
- [x] **2.5** Update query to return KPIs scoped to the logged-in user
  - Superadmin → sees ALL KPIs (global + dept-level + user-specific)
  - Admin → sees KPIs created by superadmin for their dept/themselves + KPIs they created for their faculty
  - User → sees only KPIs assigned to them or their department
- [x] **2.6** Add `assigned_to_name` (joined from `users`) in the returned data
- [x] **2.7** Add `created_by_name` (joined from `users`) in the returned data

### `api/kpi/delete.php`
- [x] **2.8** Allow admin to delete KPIs they created (admin cannot delete superadmin KPIs)
- [x] **2.9** Enforce ownership check before deletion

### New: `api/kpi/get.php` *(optional but useful)*
- [x] **2.10** GET endpoint to fetch a single KPI by ID (for edit pre-fill) → `api/kpi/get.php`

---

## Phase 3 — Superadmin UI Changes

### `views/superadmin/settings.php` — KPI Tab
- [x] **3.1** Add **"Assign To"** dropdown in the KPI modal
  - Options: `All Admins (Global)` + list of all active Admin users (Deans/Heads)
- [x] **3.2** Display `Assigned To` column in the KPI table
  - Show `All Admins` if global, or the specific admin's name
- [x] **3.3** Display `Scope` badge (global / department / user) in KPI table
- [x] **3.4** Filter/search KPIs by assigned user or scope in the table
- [x] **3.5** Update `saveKpi()` JS function to send `assigned_to` and `scope` in payload
- [x] **3.6** Update `renderKpiTable()` to show the new columns

---

## Phase 4 — Admin UI (New Page)

- [x] **4.1** Create `views/admin/kpi-management.php`
  - New page for Admin to manage KPIs for their department faculty
- [x] **4.2** Add sidebar navigation link for `KPI Management` (admin sidebar)
- [x] **4.3** Build KPI table showing:
  - KPIs created by Superadmin assigned to this admin or their dept
  - KPIs created by this Admin for their faculty (with assigned user column)
- [x] **4.4** Build **Add KPI modal** for Admin
  - Fields: Category, MFO, Success Indicator, Target, Measure
  - **"Assign To"** dropdown: `All Faculty (Dept-wide)` + list of faculty in their dept
- [x] **4.5** Implement edit/delete for KPIs the admin created (read-only for superadmin KPIs — lock icon)
- [x] **4.6** Wire up JS fetch calls to the updated `api/kpi/save.php`, `list.php`, `delete.php`

---

## Phase 5 — User-Side KPI Visibility

- [x] **5.1** Update `views/users/ipcr-form.php` to load KPIs filtered to:
  - Global KPIs (no `assigned_to`)
  - Dept-wide KPIs for user's department
  - KPIs specifically assigned to this user
- [x] **5.2** Update `api/kpi/list.php` user-scope query to apply this filter → already done in Phase 2

---

## Phase 6 — Testing & Validation

- [x] **6.1** Test: Superadmin creates a global KPI → visible to all admins  
- [x] **6.2** Test: Superadmin creates a KPI assigned to a specific Admin → only that admin sees it  
- [x] **6.3** Test: Admin creates a dept-wide KPI → all faculty in dept see it in their IPCR form  
- [x] **6.4** Test: Admin creates a KPI for a specific faculty → only that faculty sees it  
- [x] **6.5** Test: Admin cannot delete superadmin-created KPIs  
- [x] **6.6** Test: Admin cannot create a KPI assigned to a user from another department  
- [x] **6.7** Test: KPI list API returns correct data per role/scope  

> 🧪 **Test runner created:** `test/kpi-test-runner.php`  
> Access: `http://localhost/AGS-AOPCR-IPCR-Generation-System/test/kpi-test-runner.php`  
> Auto-runs 11 checks (DB schema, inserts, ownership guards, file existence, API JOIN).  
> **Manual browser checks** for UI flows are listed on the test runner page.  
> ⚠️ Delete `kpi-test-runner.php` after testing is complete.

---

## Phase 7 — Documentation

- [x] **7.1** Update `docs/system-overview.md` — KPI Management section
- [x] **7.2** Update `docs/audit.md` with implementation phase entry
- [x] **7.3** Update `database/schema.sql` with final column definitions
- [x] **7.4** Mark this checklist items as done as we progress ✅

---

## Summary of Files to Create / Modify

| File | Action |
|------|--------|
| `database/schema.sql` | Modify — add `assigned_to`, `scope` columns |
| `api/kpi/save.php` | Modify — allow admin, add assignment fields |
| `api/kpi/list.php` | Modify — scope-aware filtering + join user names |
| `api/kpi/delete.php` | Modify — ownership check |
| `api/kpi/get.php` | Create — single KPI fetch |
| `views/superadmin/settings.php` | Modify — add Assign To in KPI modal & table |
| `views/admin/kpi-management.php` | **Create** — new admin KPI management page |
| `assets/js/components.js` | Possibly modify — sidebar nav link for admin |
| `views/users/ipcr-form.php` | Modify — filter KPIs by assignment scope |
| `docs/system-overview.md` | Update |
| `docs/audit.md` | Update |
