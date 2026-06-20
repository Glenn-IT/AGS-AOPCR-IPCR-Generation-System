# SYSTEM AUDIT — CSU-Piat AOPCR/IPCR Generation System
**Date:** 2026-06-20  
**Auditor:** Claude Code  
**Purpose:** Pre-backend implementation audit — identify gaps, risks, and a full implementation plan to convert this frontend prototype into a production-ready PHP/MySQL system.

---

## 1. CURRENT SYSTEM STATE

### What Exists (Prototype)
The system is a **pure frontend prototype** built with HTML5, Bootstrap 5, Vanilla JavaScript, and localStorage as a mock database. It simulates a working application but has **zero real persistence, zero security, and zero backend logic**.

| Layer | Current State | Required State |
|---|---|---|
| Frontend | HTML + Bootstrap 5 + Vanilla JS | PHP templates (converted from HTML) |
| Backend | None | PHP 8.x with MVC structure |
| Database | localStorage (browser-only) | MySQL 8.x via XAMPP |
| Authentication | JS + localStorage (plain text passwords) | PHP Sessions + password_hash() |
| File Storage | Base64 in localStorage (fake) | Server filesystem (/uploads/) |
| PDF Export | window.print() only | TCPDF or FPDF library |
| Excel Export | Simulated | PhpSpreadsheet library |
| Email | Not implemented | PHPMailer or SMTP |

---

## 2. FILE INVENTORY

### Root Files
| File | Type | Status |
|---|---|---|
| `index.html` | Login page | Convert to PHP |
| `register.html` | Registration page | Convert to PHP |
| `forgot-password.html` | Password recovery | Convert to PHP |

### Assets
| Path | Status |
|---|---|
| `assets/css/style.css` | Keep as-is |
| `assets/js/auth.js` | Replace with PHP session logic |
| `assets/js/components.js` | Keep for UI utilities, remove data logic |
| `assets/images/csu-logo.png` | Keep |
| `data/mock-data.js` | Retire — replaced by MySQL seed data |

### Super Admin Views (7 pages)
| File | Convert To | Notes |
|---|---|---|
| `views/superadmin/dashboard.html` | dashboard.php | Stats from DB |
| `views/superadmin/accounts.html` | accounts.php | CRUD from users table |
| `views/superadmin/accomplishments.html` | accomplishments.php | Query ipcr/opcr tables |
| `views/superadmin/reports.html` | reports.php | Filtered queries |
| `views/superadmin/set-target.html` | set-target.php | OPCR form for superadmin |
| `views/superadmin/settings.html` | settings.php | Timeline + KPI management |
| `views/superadmin/account.html` | account.php | Profile + password |

### Admin Views (6 pages)
| File | Convert To | Notes |
|---|---|---|
| `views/admin/dashboard.html` | dashboard.php | Dept-scoped stats |
| `views/admin/ipcr-form.html` | ipcr-form.php | Review/rate IPCR of faculty |
| `views/admin/accomplishments.html` | accomplishments.php | Set actual + ratings |
| `views/admin/reports.html` | reports.php | Dept-scoped reports |
| `views/admin/set-target.html` | set-target.php | OPCR form (office targets) |
| `views/admin/account.html` | account.php | Profile + password |

### User Views (5 pages)
| File | Convert To | Notes |
|---|---|---|
| `views/users/dashboard.html` | dashboard.php | Personal IPCR stats |
| `views/users/ipcr-form.html` | ipcr-form.php | Submit personal IPCR |
| `views/users/evidence.html` | evidence.php | Real file upload |
| `views/users/status.html` | status.php | View submission status |
| `views/users/account.html` | account.php | Profile + password + logs |

---

## 3. CRITICAL SECURITY ISSUES (Must Fix Before Launch)

### 3.1 Passwords Stored in Plain Text
- **Where:** `data/mock-data.js` — all 50 users have plain text passwords in the JS file visible to anyone who opens DevTools.
- **Fix:** Use PHP `password_hash()` (BCRYPT) on registration and `password_verify()` on login.

### 3.2 Authentication Has No Server Enforcement
- **Where:** `assets/js/auth.js` — `requireAuth()` only checks localStorage. Anyone can bypass it by setting a fake session object in DevTools.
- **Fix:** Every PHP page must call `session_start()` and check `$_SESSION['user']` at the top. No client-side auth.

### 3.3 Role-Based Access Control is Purely Frontend
- **Where:** `auth.js:requireAuth()` — roles are verified only in JavaScript.
- **Fix:** PHP middleware checks role on every page load. A `user` hitting `/views/superadmin/` must get a 403 redirect from PHP, not JS.

### 3.4 localStorage Has No Data Isolation
- **Where:** All data stored in browser localStorage. Multiple users on the same machine share data.
- **Fix:** All data must live in MySQL, returned only to the authenticated user's session.

### 3.5 No CSRF Protection
- **Where:** All forms (login, registration, IPCR submit, password change).
- **Fix:** Generate a CSRF token per session, embed in forms as hidden input, validate on POST.

### 3.6 No Input Sanitization
- **Where:** All form inputs go directly into localStorage with no sanitization.
- **Fix:** Use `htmlspecialchars()` on output, `mysqli_real_escape_string()` or prepared statements on DB input.

### 3.7 Evidence Upload is Fake
- **Where:** `views/users/evidence.html` — files are converted to Base64 and stored in localStorage. 5MB total localStorage limit means this breaks immediately in production.
- **Fix:** Real PHP file upload (`$_FILES`) with MIME type validation, stored in `/uploads/evidence/{user_id}/`.

---

## 4. FUNCTIONAL GAPS

### 4.1 Missing: IPCR Review/Approval Interface for Admin
- There is an `admin/ipcr-form.html` but it appears to be a duplicate of the user IPCR form. There is **no screen where admin can view pending submissions from their department users and approve/disapprove with remarks**.
- **Required:** An `admin/review-ipcr.php` page that lists all pending IPCR submissions from users in the admin's department, with Approve / Disapprove buttons and a remarks field.

### 4.2 Missing: OPCR Review/Approval by Super Admin
- Super Admin has `set-target.html` but no dedicated view for reviewing and approving OPCR forms submitted by admins.
- **Required:** A `superadmin/review-opcr.php` page.

### 4.3 Missing: Real Notification System
- `NOTIFICATIONS` are hardcoded in `mock-data.js`. There is no mechanism to send a notification when an IPCR is approved, disapproved, or when a deadline is approaching.
- **Required:** A `notifications` MySQL table, populated server-side when status changes.

### 4.4 Missing: Real PDF Generation
- Reports use `window.print()` which is a browser print dialog, not a PDF generator. Output is not formatted to match the official IPCR/OPCR form layout.
- **Required:** TCPDF or FPDF to generate PDFs that match the official CSU-Piat IPCR/OPCR form templates (see `docs/templates/IPCR.xlsx` and `OPCR.xlsx`).

### 4.5 Missing: Real Excel Export
- No actual Excel export. Only simulated.
- **Required:** PhpSpreadsheet library to export IPCR/OPCR data into `.xlsx` format matching official templates.

### 4.6 Missing: Email for Forgot Password
- Forgot password flow (`forgot-password.html`) uses security question only — no email verification.
- **Required:** PHPMailer to send a password reset link/OTP to the user's registered email.

### 4.7 Missing: Persistent Activity Logs
- Account logs are stored in localStorage, lost when the browser is cleared, and only visible to the same browser user.
- **Required:** `activity_logs` MySQL table. All logins, logouts, password changes, form submissions, approvals logged server-side.

### 4.8 Missing: Timeline Deadline Enforcement
- Timelines exist in mock data but are never checked. Users can submit IPCR forms at any time.
- **Required:** PHP deadline check on IPCR/OPCR submission. If current date > `submission_deadline`, reject the submission and show an error.

### 4.9 Missing: Admin-User Department Scoping
- Admins should only see IPCR submissions from users in their own department. Current system has no concept of this relationship enforced anywhere.
- **Required:** Filter all admin queries by `department` field matching the logged-in admin's department.

### 4.10 Missing: Registration Approval Workflow
- `register.html` currently allows self-registration with any role. This is a security problem — a user could register as superadmin.
- **Required:** New registrations default to `status = 'pending'`. Super Admin must approve/activate the account before the user can log in. Role must NOT be user-selectable.

### 4.11 Missing: OPCR-to-IPCR Linkage
- OPCR targets set by admins should cascade down to individual IPCR forms of their faculty. Currently, KPIs in IPCR are pre-set and not linked to the department's OPCR.
- **Required:** When admin saves OPCR targets, the KPIs/targets should be accessible to users in that department when filling their IPCR.

### 4.12 Duplicate IPCR Form (Admin vs User)
- `views/admin/ipcr-form.html` and `views/users/ipcr-form.html` are identical. The admin role purpose is ambiguous — is admin filling their own IPCR, or reviewing user IPCRs?
- **Required:** Clarify role. Admin should have their own IPCR as a user PLUS a separate review interface for their department's submissions.

### 4.13 Missing: Super Admin Set Target (OPCR)
- `views/superadmin/set-target.html` exists — unclear if this is the campus-level OPCR or the same as admin OPCR.
- **Required:** Clarify: Super Admin manages campus-wide AOPCR (Automated Office PCR), Admin manages department-level OPCR, Users fill IPCR.

---

## 5. DATABASE DESIGN (Required Schema)

```sql
-- Users & Roles
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,           -- bcrypt hash
  role ENUM('superadmin','admin','user') NOT NULL,
  name VARCHAR(100) NOT NULL,
  position VARCHAR(100),
  department_id VARCHAR(10),
  email VARCHAR(100),
  gender ENUM('Male','Female','Other'),
  status ENUM('active','inactive','pending') DEFAULT 'pending',
  avatar VARCHAR(10),
  security_question VARCHAR(200),
  security_answer VARCHAR(200),             -- hashed
  last_login DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Departments
CREATE TABLE departments (
  id VARCHAR(10) PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  type ENUM('admin','academic') NOT NULL
);

-- Academic Timelines
CREATE TABLE timelines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  academic_year VARCHAR(20) NOT NULL,
  semester VARCHAR(30) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  submission_deadline DATE NOT NULL,
  status ENUM('open','closed') DEFAULT 'open',
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- KPI / Performance Indicators
CREATE TABLE kpi_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category ENUM('core','strategic','support') NOT NULL,
  mfo VARCHAR(100),
  success_indicator TEXT NOT NULL,
  target VARCHAR(200),
  measure VARCHAR(200),
  department_id VARCHAR(10),               -- NULL = applies to all
  created_by INT,
  is_active TINYINT(1) DEFAULT 1
);

-- IPCR Forms
CREATE TABLE ipcr_forms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  timeline_id INT NOT NULL,
  covered_period VARCHAR(100),
  date_submitted DATE,
  status ENUM('draft','pending','reviewed','approved','disapproved') DEFAULT 'draft',
  overall_rating DECIMAL(3,2) DEFAULT 0,
  remarks TEXT,
  reviewed_by INT,
  reviewed_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (timeline_id) REFERENCES timelines(id),
  FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

-- IPCR Line Items (core/strategic/support entries)
CREATE TABLE ipcr_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ipcr_form_id INT NOT NULL,
  kpi_id INT,
  function_type ENUM('core','strategic','support') NOT NULL,
  success_indicator TEXT,
  accomplishment TEXT,
  rating TINYINT CHECK (rating BETWEEN 1 AND 5),
  remarks VARCHAR(200),
  FOREIGN KEY (ipcr_form_id) REFERENCES ipcr_forms(id) ON DELETE CASCADE,
  FOREIGN KEY (kpi_id) REFERENCES kpi_items(id)
);

-- OPCR Forms
CREATE TABLE opcr_forms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  department_id VARCHAR(10) NOT NULL,
  timeline_id INT NOT NULL,
  covered_period VARCHAR(100),
  date_submitted DATE,
  status ENUM('draft','pending','reviewed','approved','disapproved') DEFAULT 'draft',
  overall_rating DECIMAL(3,2) DEFAULT 0,
  remarks TEXT,
  reviewed_by INT,
  reviewed_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id),
  FOREIGN KEY (timeline_id) REFERENCES timelines(id)
);

-- OPCR Line Items
CREATE TABLE opcr_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  opcr_form_id INT NOT NULL,
  function_type ENUM('core','strategic','support') NOT NULL,
  mfo VARCHAR(100),
  success_indicator TEXT,
  target VARCHAR(200),
  actual TEXT,
  budget DECIMAL(12,2) DEFAULT 0,
  rating TINYINT CHECK (rating BETWEEN 1 AND 5),
  FOREIGN KEY (opcr_form_id) REFERENCES opcr_forms(id) ON DELETE CASCADE
);

-- Evidence Files
CREATE TABLE evidence_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ipcr_form_id INT NOT NULL,
  user_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_size INT,
  mime_type VARCHAR(100),
  category ENUM('core','strategic','support','other') NOT NULL,
  description TEXT,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ipcr_form_id) REFERENCES ipcr_forms(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Notifications
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('info','success','warning','danger') DEFAULT 'info',
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Activity Logs
CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  activity TEXT NOT NULL,
  ip_address VARCHAR(45),
  user_agent VARCHAR(300),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Password Reset Tokens
CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(100) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## 6. PHP PROJECT STRUCTURE (Target Architecture)

```
AGS-AOPCR-IPCR-Generation-System/
│
├── config/
│   ├── database.php          -- PDO connection singleton
│   ├── session.php           -- session_start(), auth helpers
│   └── constants.php         -- app-wide constants (upload path, roles, etc.)
│
├── includes/
│   ├── header.php            -- HTML <head>, CSS links
│   ├── sidebar.php           -- PHP-rendered sidebar (role-aware)
│   ├── navbar.php            -- PHP-rendered navbar
│   └── footer.php            -- HTML footer + scripts
│
├── api/                      -- AJAX endpoints (return JSON)
│   ├── auth/
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── register.php
│   │   ├── forgot-password.php
│   │   └── reset-password.php
│   ├── users/
│   │   ├── list.php
│   │   ├── get.php
│   │   ├── update.php
│   │   ├── activate.php
│   │   └── deactivate.php
│   ├── ipcr/
│   │   ├── save.php          -- save draft or submit
│   │   ├── get.php
│   │   ├── list.php
│   │   ├── review.php        -- admin: approve/disapprove
│   │   └── delete.php
│   ├── opcr/
│   │   ├── save.php
│   │   ├── get.php
│   │   ├── list.php
│   │   └── review.php
│   ├── evidence/
│   │   ├── upload.php        -- real file upload handler
│   │   ├── list.php
│   │   └── delete.php
│   ├── timeline/
│   │   ├── list.php
│   │   ├── save.php
│   │   └── toggle-status.php
│   ├── kpi/
│   │   ├── list.php
│   │   ├── save.php
│   │   └── delete.php
│   ├── notifications/
│   │   ├── list.php
│   │   └── mark-read.php
│   ├── reports/
│   │   ├── summary.php
│   │   └── export-pdf.php
│   └── dashboard/
│       ├── superadmin-stats.php
│       ├── admin-stats.php
│       └── user-stats.php
│
├── views/
│   ├── superadmin/
│   │   ├── dashboard.php
│   │   ├── accounts.php
│   │   ├── accomplishments.php
│   │   ├── reports.php
│   │   ├── set-target.php
│   │   ├── review-opcr.php   -- NEW: approve/disapprove OPCR
│   │   ├── settings.php
│   │   └── account.php
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── ipcr-form.php     -- admin's own IPCR
│   │   ├── review-ipcr.php   -- NEW: review dept IPCR submissions
│   │   ├── accomplishments.php
│   │   ├── reports.php
│   │   ├── set-target.php
│   │   └── account.php
│   └── users/
│       ├── dashboard.php
│       ├── ipcr-form.php
│       ├── evidence.php
│       ├── status.php
│       └── account.php
│
├── uploads/                  -- gitignored, real file storage
│   └── evidence/
│       └── {user_id}/
│
├── lib/                      -- third-party libraries
│   ├── tcpdf/                -- PDF generation
│   └── PhpSpreadsheet/       -- Excel generation
│
├── database/
│   ├── schema.sql            -- full CREATE TABLE statements
│   └── seed.sql              -- initial data (departments, KPIs, 1 superadmin)
│
├── index.php                 -- login page
├── register.php
├── forgot-password.php
└── .htaccess                 -- URL rewrites, deny direct /api access from browser
```

---

## 7. IMPLEMENTATION PLAN (Phased)

### ✅ Phase 1 — Foundation ~~(Week 1)~~ COMPLETE
**Goal:** Database + PHP config layer working, no UI yet.

- [x] Create `database/schema.sql` — all tables from Section 5
- [x] Create `database/seed.php` — 11 departments, 50 users (bcrypt passwords), 4 timelines, 13 KPIs, sample IPCR/OPCR data
- [x] Create `config/database.php` — PDO singleton with error handling
- [x] Create `config/session.php` — `requireAuth($role)`, `isLoggedIn()`, CSRF helpers, `addLog()`
- [x] Create `config/constants.php` — UPLOAD_PATH, BASE_URL, role constants, rate-limit settings
- [x] Create `setup.php` — browser-based 4-step installer (creates DB, tables, seeds data, makes uploads dir)
- [x] Create `uploads/.htaccess` — blocks PHP execution inside uploads folder
- [x] Create root `.htaccess` — `AddType application/x-httpd-php .html`, security headers

### ✅ Phase 2 — Authentication ~~(Week 1-2)~~ COMPLETE
**Goal:** Real login/logout/register with PHP sessions.

- [x] `api/auth/login.php` — POST: `password_verify()`, rate limiting via `login_attempts` table, `$_SESSION`, return JSON
- [x] `api/auth/logout.php` — logs action, `clearSession()`, redirect to `index.php`
- [x] `api/auth/register.php` — POST: validate, `password_hash()`, insert user with `status='pending'`
- [x] `api/auth/forgot-password.php` — 3-step: verify username → verify bcrypt security answer → flag `fp_verified` in session
- [x] `api/auth/reset-password.php` — requires `fp_username` + `fp_verified` in session; bcrypt new password
- [x] `api/auth/change-password.php` — requires active session; verifies current password before updating
- [x] Convert `index.html` → `index.php` — AJAX login, session redirect, demo credential shortcuts, attempt progress bar
- [x] Convert `register.html` → `register.php` — departments loaded from MySQL, AJAX submit, pending-approval notice
- [x] Convert `forgot-password.html` → `forgot-password.php` — 4-step wizard using async/await AJAX
- [x] Add PHP auth header (`requireAuth(['role'])`) at top of all 18 view files
- [x] Inject `SESSION_USER` + `API_BASE` constants into `<head>` of all 18 view files
- [x] Rewrite `assets/js/auth.js` — `getSession()` reads `window.SESSION_USER`, all auth calls are AJAX Promises
- [x] Update `assets/js/components.js` — sidebar/navbar use `window.SESSION_USER` instead of localStorage

### ✅ Phase 3 — Core IPCR Flow ~~(Week 2-3)~~ COMPLETE
**Goal:** Users can fill, save, and submit IPCR. Admins can review and approve/disapprove.

- [x] `api/ipcr/save.php` — upsert ipcr_forms + ipcr_items; enforces open timeline + deadline; prevents duplicates; notifies admin on submit
- [x] `api/ipcr/get.php` — get IPCR form by id or by user+timeline (with access control per role)
- [x] `api/ipcr/list.php` — list filtered by status/timeline/dept; scoped per role (user=own, admin=dept, superadmin=all)
- [x] `api/ipcr/review.php` — admin sets status + remarks + individual item ratings; computes overall avg; notifies user; dept-scoped access check
- [x] `api/kpi/list.php` — returns active KPI items, optionally filtered by category/dept; grouped by type
- [x] `api/timeline/list.php` — returns timelines filtered by status
- [x] `api/notifications/list.php` + `mark-read.php` — real notifications from DB
- [x] `api/dashboard/user-stats.php` — IPCR counts, latest rating, active timeline, recent forms
- [x] `api/dashboard/admin-stats.php` — dept faculty count, IPCR counts, avg rating, pending reviews, active timeline
- [x] `api/dashboard/superadmin-stats.php` — campus-wide totals, per-dept summary, pending account approvals
- [x] Updated `views/users/ipcr-form.html` — loads KPIs + active timeline via AJAX; save/submit calls `api/ipcr/save.php`; removed all localStorage
- [x] Updated `views/users/status.html` — loads IPCR list via AJAX; detail view calls `api/ipcr/get.php`; removed all localStorage
- [x] Created `views/admin/review-ipcr.html` — lists dept IPCR submissions; inline rating inputs per line item; approve/disapprove/review with remarks

### ✅ Phase 4 — OPCR Flow ~~(Week 3)~~ COMPLETE
**Goal:** Admins set OPCR targets. Super Admin reviews and approves.

- [x] `api/opcr/save.php` — upsert opcr_forms + opcr_items; open-timeline check; duplicate guard; notifies superadmin on submit
- [x] `api/opcr/get.php` — fetch single OPCR with all line items; admin-scoped access
- [x] `api/opcr/list.php` — filterable by status/dept/timeline; admin sees own, superadmin sees all
- [x] `api/opcr/review.php` — superadmin rates each item, computes overall average, notifies admin
- [x] Updated `views/admin/set-target.html` — loads open timeline + existing OPCR via AJAX; save/submit calls `api/opcr/save.php`; removed all localStorage/mock-data
- [x] Updated `views/admin/accomplishments.html` — loads IPCR list from API; inline rating save via `api/ipcr/review.php`; removed localStorage
- [x] Created `views/superadmin/review-opcr.html` — new page closing audit gap 4.2; lists all OPCR submissions; approve/disapprove/review with per-item ratings
- [x] Updated `views/superadmin/accomplishments.html` — loads all IPCR forms from API; inline rating save via `api/ipcr/review.php`; removed localStorage

### Phase 5 — Evidence Upload (Week 3-4)
**Goal:** Real file upload with server-side storage.

- [ ] `api/evidence/upload.php` — validate MIME type, size (10MB), move_uploaded_file to `/uploads/evidence/{user_id}/`
- [ ] `api/evidence/list.php` — return user's uploaded files
- [ ] `api/evidence/delete.php` — delete file from filesystem + DB
- [ ] Convert `views/users/evidence.html` → `evidence.php`
- [ ] Add `.htaccess` to `/uploads/` to prevent direct PHP execution
- [ ] Allowed types: PDF, DOC, DOCX, JPG, PNG, XLSX

### Phase 6 — Account Management + Logs (Week 4)
**Goal:** Super Admin can manage all accounts. Logs are real.

- [ ] `api/users/list.php` — paginated, searchable, filterable
- [ ] `api/users/activate.php` + `deactivate.php` + `update.php`
- [ ] Convert all `account.html` pages → `account.php` (profile + password change + logs)
- [ ] Convert `views/superadmin/accounts.html` → `accounts.php`
- [ ] All logins, logouts, submissions, approvals write to `activity_logs` table
- [ ] `api/auth/register.php` — new users land as `pending`, superadmin activates them

### Phase 7 — Reports + Export (Week 4-5)
**Goal:** Generate real PDFs and Excel exports matching official form templates.

- [ ] Install TCPDF via Composer or manual include in `/lib/tcpdf/`
- [ ] Install PhpSpreadsheet via Composer or manual include in `/lib/PhpSpreadsheet/`
- [ ] `api/reports/export-pdf.php` — generate IPCR/OPCR PDF matching `docs/templates/IPCR.xlsx` layout
- [ ] `api/reports/export-excel.php` — generate .xlsx using PhpSpreadsheet
- [ ] Convert all `reports.html` pages → `reports.php`
- [ ] Add filter by department, status, date range

### Phase 8 — Settings + KPI/Timeline Management (Week 5)
**Goal:** Superadmin can manage timelines and KPI items from the UI.

- [ ] `api/timeline/list.php`, `save.php`, `toggle-status.php`
- [ ] `api/kpi/list.php`, `save.php`, `delete.php`
- [ ] Convert `views/superadmin/settings.html` → `settings.php`

### Phase 9 — Dashboards + Notifications (Week 5-6)
**Goal:** All dashboard stats come from real DB queries. Notifications are real.

- [ ] `api/dashboard/superadmin-stats.php` — total users, approved, disapproved, by dept
- [ ] `api/dashboard/admin-stats.php` — dept-scoped
- [ ] `api/dashboard/user-stats.php` — personal IPCR summary
- [ ] `api/notifications/list.php` — unread count + list for logged-in user
- [ ] `api/notifications/mark-read.php`
- [ ] Update all dashboard.php files to fetch stats via AJAX

### Phase 10 — Security Hardening (Week 6)
**Goal:** System is secure before any real use.

- [ ] Add CSRF token to all forms
- [ ] Sanitize all output with `htmlspecialchars()`
- [ ] Use PDO prepared statements everywhere (no raw string interpolation in SQL)
- [ ] Add rate limiting on login endpoint (store attempts in DB or session)
- [ ] Add `session_regenerate_id(true)` on successful login
- [ ] Create `.htaccess` to deny direct access to `/config/`, `/api/` from browser (force JSON responses)
- [ ] Add `X-Frame-Options`, `X-XSS-Protection` headers in PHP
- [ ] Move upload path outside web root or add .htaccess to deny direct URL access to uploads
- [ ] Validate all file uploads: MIME type check (not just extension), max size, rename with uniqid()
- [ ] Hash security answers with `password_hash()` — currently stored plain text in mock data

---

## 8. DEPENDENCIES TO INSTALL

| Library | Purpose | Install Method |
|---|---|---|
| TCPDF | PDF generation | Composer or manual `/lib/tcpdf/` |
| PhpSpreadsheet | Excel export | Composer or manual `/lib/PhpSpreadsheet/` |
| PHPMailer | Email (forgot password, notifications) | Composer or manual `/lib/PHPMailer/` |

XAMPP Requirements:
- PHP 8.0+
- MySQL 8.0+
- Apache mod_rewrite enabled
- `php.ini`: `file_uploads = On`, `upload_max_filesize = 15M`, `post_max_size = 16M`

---

## 9. WHAT CAN BE REUSED AS-IS

| Item | Can Reuse | Notes |
|---|---|---|
| `assets/css/style.css` | Yes | No changes needed |
| Bootstrap 5 CDN links | Yes | Consider local copy for offline |
| Font Awesome CDN | Yes | Consider local copy for offline |
| `assets/js/components.js` | Partial | Keep UI utilities (showToast, confirmModal, sidebar, navbar builders). Remove all localStorage data logic. |
| HTML structure of all pages | Yes | Just convert to .php, add PHP includes at top |
| Chart.js dashboard charts | Yes | Replace hardcoded data with AJAX fetch |
| All CSS classes and layout | Yes | No UI rebuild needed |
| Mock data user list | Yes | Use as `seed.sql` base data |
| IPCR/OPCR form table structure | Yes | Mirrors DB schema closely |

---

## 10. RISK REGISTER

| Risk | Severity | Mitigation |
|---|---|---|
| Evidence files fill up disk space | Medium | Set per-user quota, file size limit, periodic cleanup |
| PDF template doesn't match official IPCR form exactly | High | Get the official .xlsx template from HR, build TCPDF layout to match |
| Multiple users submitting at same time (race condition on ratings) | Low | Use MySQL transactions for rating calculations |
| Session hijacking | Medium | HTTPS required in production, `session.cookie_secure=true`, `HttpOnly` |
| Passwords from old localStorage sessions leaking | High | On first PHP login, clear old localStorage via JS one-time migration |
| Admin accidentally approves wrong department IPCR | Medium | Department scope filter on all admin queries |
| User submits after deadline | Low | Server-side deadline check in `api/ipcr/save.php` |

---

## 11. SUMMARY OF GAPS (Quick Reference)

| # | Gap | Priority |
|---|---|---|
| 1 | No PHP backend | CRITICAL |
| 2 | No MySQL database | CRITICAL |
| 3 | Passwords plain text in JS | CRITICAL |
| 4 | No server-side auth/role enforcement | CRITICAL |
| 5 | No CSRF protection | HIGH |
| 6 | File uploads are fake (localStorage) | HIGH |
| 7 | No PDF generation (only print dialog) | HIGH |
| 8 | No Excel export | HIGH |
| 9 | No admin IPCR review/approval interface | HIGH |
| 10 | No superadmin OPCR review interface | HIGH |
| 11 | Registration has no approval workflow | HIGH |
| 12 | No real activity logs (cleared with browser) | MEDIUM |
| 13 | No real notifications (hardcoded) | MEDIUM |
| 14 | No email for forgot password | MEDIUM |
| 15 | No timeline deadline enforcement | MEDIUM |
| 16 | No department scoping for admin | MEDIUM |
| 17 | Duplicate IPCR forms (admin vs user unclear) | MEDIUM |
| 18 | No OPCR-to-IPCR linkage | LOW |
| 19 | No input sanitization on forms | HIGH |
| 20 | CDN-only (no offline support) | LOW |

---

*Generated by system audit on 2026-06-20. Next step: Begin Phase 1 — database schema implementation.*
