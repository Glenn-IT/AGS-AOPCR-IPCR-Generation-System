# AGS-AOPCR-IPCR Generation System — System Overview

**Automated Office Performance Commitment Rating (OPCR) and Individual Performance Commitment Rating (IPCR) Generation System**  
Cagayan State University — Piat Campus

---

## Table of Contents

1. [What Is This System?](#1-what-is-this-system)
2. [Technology Stack](#2-technology-stack)
3. [Project Structure](#3-project-structure)
4. [Database Schema](#4-database-schema)
5. [How the System Works](#5-how-the-system-works)
6. [System Roles & Features](#6-system-roles--features)
7. [API Endpoints](#7-api-endpoints)
8. [Authentication & Security](#8-authentication--security)
9. [Setup & Installation](#9-setup--installation)
10. [Demo Credentials](#10-demo-credentials)
11. [Implementation Status](#11-implementation-status)

---

## 1. What Is This System?

This is a **performance evaluation and management system** for Cagayan State University — Piat Campus. It digitizes and automates the university's annual performance review cycle using three core forms:

| Form | Full Name | Who Fills It | Purpose |
|------|-----------|--------------|---------|
| **IPCR** | Individual Performance Commitment and Review | Faculty & Staff | Documents individual accomplishments and self-ratings per KPI |
| **OPCR** | Office Performance Commitment Rating | Department Heads / Admins | Sets office-level targets, MFOs, and budget per department |
| **AOPCR** | Automated Office Performance Commitment Rating | System-generated | System-wide coordination and reporting across all departments |

**Performance cycle per academic semester:**
1. Superadmin opens a Timeline (submission period)
2. Department heads (Admins) submit OPCR targets → Superadmin reviews
3. Faculty/Staff submit IPCR accomplishments → Admin reviews
4. System generates reports and overall ratings for individual and departmental performance

---

## 2. Technology Stack

| Layer | Technology | Version | Purpose |
|-------|-----------|---------|---------|
| **Backend** | PHP | 8.x | Business logic, authentication, database queries, API endpoints |
| **Database** | MySQL | 8.x | Persistent storage for users, forms, ratings, logs |
| **Web Server** | Apache (XAMPP) | 2.4.x | Local development server |
| **Frontend** | HTML5 + Bootstrap | 5.3 | Responsive UI templates |
| **JavaScript** | Vanilla JS (ES6+) | — | AJAX calls, dynamic UI, form handling |
| **Charts** | Chart.js | 4.x | Dashboard analytics (pie, bar, doughnut) |
| **Icons** | Font Awesome | 6.x | UI icons throughout the system |
| **Styling** | Custom CSS + Bootstrap | — | CSU-Piat brand theme (`#E85C0D`, `#821131`) |

---

## 3. Project Structure

```
AGS-AOPCR-IPCR-Generation-System/
│
├── index.php                        ← Login page (entry point)
├── register.php                     ← Self-registration (creates pending account)
├── forgot-password.php              ← 3-step password recovery
├── setup.php                        ← One-time database installer (browser UI)
├── README.md
│
├── config/                          ← Core system configuration
│   ├── constants.php                ← DB credentials, app settings, role constants, upload paths
│   ├── database.php                 ← PDO singleton connection class
│   └── session.php                  ← Session management, auth middleware, CSRF, activity logging
│
├── database/                        ← Schema and seed data
│   ├── schema.sql                   ← CREATE TABLE statements for all 11 tables
│   └── seed.php                     ← 50 users + 11 departments + timelines + KPIs
│
├── api/                             ← 30 JSON endpoints (PHP → JSON responses)
│   ├── auth/
│   │   ├── login.php                ← POST: authenticate user, start session
│   │   ├── logout.php               ← Destroy session, log activity
│   │   ├── register.php             ← POST: create pending account
│   │   ├── forgot-password.php      ← 3-step: verify username → security question → flag session
│   │   ├── reset-password.php       ← POST: set new password (bcrypt)
│   │   └── change-password.php      ← POST: authenticated password change
│   │
│   ├── ipcr/
│   │   ├── save.php                 ← POST: upsert IPCR draft or submit (enforces deadline)
│   │   ├── get.php                  ← GET: fetch single IPCR form + all line items
│   │   ├── list.php                 ← GET: filtered IPCR list by role/dept/status/timeline
│   │   └── review.php               ← POST: admin rates items, computes avg rating, notifies user
│   │
│   ├── opcr/
│   │   ├── save.php                 ← POST: upsert OPCR targets + budget
│   │   ├── get.php                  ← GET: fetch single OPCR form
│   │   ├── list.php                 ← GET: filtered OPCR list for admin/superadmin scope
│   │   └── review.php               ← POST: superadmin rates OPCR items, notifies admin
│   │
│   ├── kpi/
│   │   ├── list.php                 ← GET: active KPIs by category/department
│   │   ├── save.php                 ← POST: superadmin creates/updates a KPI
│   │   └── delete.php               ← POST: retire/deactivate a KPI
│   │
│   ├── timeline/
│   │   ├── list.php                 ← GET: timelines (open/closed filter)
│   │   └── save.php                 ← POST: create timeline or toggle open/closed
│   │
│   ├── dashboard/
│   │   ├── user-stats.php           ← GET: personal IPCR counts, latest rating, active timeline
│   │   ├── admin-stats.php          ← GET: department-scoped employee + form stats
│   │   └── superadmin-stats.php     ← GET: campus-wide counts and per-department summary
│   │
│   ├── notifications/
│   │   ├── list.php                 ← GET: unread notifications for logged-in user
│   │   └── mark-read.php            ← POST: mark notification(s) as read
│   │
│   ├── users/
│   │   ├── list.php                 ← GET: paginated user list (superadmin only)
│   │   └── toggle-status.php        ← POST: activate or deactivate a user account
│   │
│   ├── departments/
│   │   └── list.php                 ← GET: all 11 departments (used in dropdowns)
│   │
│   └── user/
│       ├── update-profile.php       ← POST: update name, email, position
│       └── logs.php                 ← GET: activity log for current user
│
├── views/                           ← PHP-rendered page templates
│   ├── superadmin/
│   │   ├── dashboard.php            ← Stats cards + 3 analytics charts
│   │   ├── accounts.php             ← All users table, activate/deactivate
│   │   ├── accomplishments.php      ← View + rate all IPCR forms system-wide
│   │   ├── review-opcr.php          ← Review and approve OPCR submissions from admins
│   │   ├── reports.php              ← System-wide IPCR/OPCR reports + PDF/Excel export
│   │   ├── set-target.php           ← Create/edit campus-level OPCR targets
│   │   ├── settings.php             ← Timeline + KPI management (CRUD)
│   │   └── account.php              ← Profile, change password, activity logs
│   │
│   ├── admin/
│   │   ├── dashboard.php            ← Department-scoped stats + approval/rating charts
│   │   ├── ipcr-form.php            ← Admin's own IPCR form
│   │   ├── review-ipcr.php          ← Rate faculty IPCR submissions from own department
│   │   ├── accomplishments.php      ← Inline rating of faculty IPCR items
│   │   ├── reports.php              ← Department-scoped reports + export
│   │   ├── set-target.php           ← Create/edit department OPCR targets
│   │   └── account.php              ← Profile, password, activity logs
│   │
│   └── users/
│       ├── dashboard.php            ← Personal IPCR stats + status chart
│       ├── ipcr-form.php            ← Fill IPCR form, save draft, submit
│       ├── status.php               ← Track submission status with progress steps
│       ├── evidence.php             ← File upload for supporting documents (in progress)
│       └── account.php              ← Profile, password change, activity logs
│
├── assets/
│   ├── css/
│   │   └── style.css                ← Global styles: sidebar, cards, forms, print, brand colors
│   └── js/
│       ├── components.js            ← UI utilities: toasts, modals, badges, sidebar/navbar builders
│       └── auth.js                  ← Session helpers, password change, logout, forgot-password flows
│
├── uploads/
│   └── evidence/                    ← Stored supporting documents for IPCR submissions
│
└── docs/
    ├── system-overview.md           ← This file
    ├── audit.md                     ← Phase-by-phase implementation audit
    ├── issues.md                    ← Known issues tracker
    ├── Prompt.md                    ← Original project requirements
    └── templates/                   ← Official CSU-Piat IPCR/OPCR Excel form templates
```

---

## 4. Database Schema

The system uses **11 MySQL tables**:

### users
Stores all system accounts across 3 roles.

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK | Auto-increment |
| username | VARCHAR(50) | Unique login identifier |
| password | VARCHAR(255) | bcrypt hash |
| role | ENUM | `superadmin`, `admin`, `user` |
| name | VARCHAR(100) | Full name |
| position | VARCHAR(100) | Job title/position |
| department_id | INT FK | Links to `departments` |
| email | VARCHAR(100) | Contact email |
| gender | ENUM | `male`, `female` |
| status | ENUM | `active`, `inactive`, `pending` |
| security_question | TEXT | For password recovery |
| security_answer | VARCHAR(255) | bcrypt hash |
| failed_login_attempts | INT | Rate limiting counter |
| locked_until | DATETIME | Lockout expiry timestamp |
| last_login | DATETIME | Last successful login |
| created_at | DATETIME | Account creation time |

### departments
The 11 real CSU-Piat departments.

| ID | Department | Type |
|----|-----------|------|
| CEO | Office of the Campus Executive Officer | Administrative |
| REG | Registrar's Office | Administrative |
| ACCT | Accounting Office | Administrative |
| HR | Human Resource Office | Administrative |
| RDE | Research, Development & Extension Office | Administrative |
| ITO | IT Office | Administrative |
| PRMO | Partnership & Resource Mobilization Office | Administrative |
| CAGRI | College of Agriculture | Academic |
| CCJA | College of Criminal Justice Administration | Academic |
| CICS | College of Information and Computing Sciences | Academic |
| CED | College of Education | Academic |

### timelines
Academic year/semester submission periods.

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK | — |
| academic_year | VARCHAR(20) | e.g., `2025-2026` |
| semester | ENUM | `1st`, `2nd`, `summer` |
| start_date | DATE | Period start |
| end_date | DATE | Period end |
| submission_deadline | DATE | Last day to submit forms |
| status | ENUM | `open` or `closed` |
| created_by | INT FK | Superadmin who created it |

### kpi_items
Performance indicators used to populate IPCR/OPCR forms.

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK | — |
| category | ENUM | `core`, `strategic`, `support` |
| mfo | TEXT | Major Final Output |
| success_indicator | TEXT | Measurable target |
| target | VARCHAR(255) | Expected value/output |
| measure | VARCHAR(100) | Unit of measurement |
| department_id | INT FK | NULL = applies to all departments |
| is_active | TINYINT | Soft delete flag |
| created_by | INT FK | User who created it |

### ipcr_forms
One IPCR submission per user per timeline.

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK | — |
| user_id | INT FK | Faculty/staff who submitted |
| timeline_id | INT FK | Which academic period |
| covered_period | VARCHAR(100) | Readable period label |
| date_submitted | DATETIME | Submission timestamp |
| status | ENUM | `draft`, `pending`, `reviewed`, `approved`, `disapproved` |
| overall_rating | DECIMAL(3,2) | Auto-computed average of item ratings |
| remarks | TEXT | Reviewer's overall comments |
| reviewed_by | INT FK | Admin who reviewed |
| reviewed_at | DATETIME | Review timestamp |

### ipcr_items
Individual line items inside an IPCR form.

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK | — |
| ipcr_form_id | INT FK | Parent IPCR form |
| kpi_id | INT FK | Linked KPI indicator |
| function_type | ENUM | `core`, `strategic`, `support` |
| success_indicator | TEXT | Copied from KPI (editable) |
| accomplishment | TEXT | What the employee actually did |
| rating | DECIMAL(2,1) | Score 1–5 given by reviewer |
| remarks | TEXT | Reviewer's per-item comment |

### opcr_forms
One OPCR submission per department per timeline.

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK | — |
| admin_id | INT FK | Department head who submitted |
| department_id | INT FK | Which department |
| timeline_id | INT FK | Which academic period |
| covered_period | VARCHAR(100) | Readable period label |
| date_submitted | DATETIME | Submission timestamp |
| status | ENUM | `draft`, `pending`, `reviewed`, `approved`, `disapproved` |
| overall_rating | DECIMAL(3,2) | Auto-computed from item ratings |
| remarks | TEXT | Superadmin's overall comments |
| reviewed_by | INT FK | Superadmin who reviewed |
| reviewed_at | DATETIME | Review timestamp |

### opcr_items
Individual line items inside an OPCR form.

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK | — |
| opcr_form_id | INT FK | Parent OPCR form |
| function_type | ENUM | `core`, `strategic`, `support` |
| mfo | TEXT | Major Final Output |
| success_indicator | TEXT | Target statement |
| target | VARCHAR(255) | Expected output |
| actual | VARCHAR(255) | What was achieved |
| budget | DECIMAL(12,2) | Allocated budget |
| rating | DECIMAL(2,1) | Score 1–5 given by reviewer |

### evidence_files
Supporting documents uploaded with IPCR submissions.

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK | — |
| ipcr_form_id | INT FK | Linked IPCR form |
| user_id | INT FK | Uploader |
| original_name | VARCHAR(255) | Original filename |
| stored_name | VARCHAR(255) | Renamed file on disk |
| file_path | VARCHAR(500) | Path under `/uploads/evidence/` |
| file_size | INT | Size in bytes |
| mime_type | VARCHAR(100) | e.g., `application/pdf` |
| category | ENUM | `core`, `strategic`, `support`, `other` |
| description | TEXT | Optional description |
| uploaded_at | DATETIME | Upload timestamp |

### notifications
System-generated in-app notifications.

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK | — |
| user_id | INT FK | Recipient |
| type | ENUM | `info`, `success`, `warning`, `danger` |
| message | TEXT | Notification text |
| is_read | TINYINT | 0 = unread, 1 = read |
| created_at | DATETIME | — |

### activity_logs
Audit trail for all user actions.

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK | — |
| user_id | INT FK | Who performed the action |
| activity | VARCHAR(255) | Description string |
| ip_address | VARCHAR(45) | Requester IP |
| user_agent | TEXT | Browser/client info |
| created_at | DATETIME | Timestamp |

---

## 5. How the System Works

### Full Performance Cycle

```
[Superadmin]
    │
    ├── 1. Creates Timeline (academic year, semester, deadline)
    │
    ↓
[Admin / Department Head]
    │
    ├── 2. Fills OPCR Form
    │       └── Defines: MFO, Success Indicators, Targets, Budget
    │               (3 sections: Core, Strategic, Support functions)
    │
    ├── 3. Submits OPCR → status: "pending"
    │
    ↓
[Superadmin]
    │
    ├── 4. Reviews OPCR → rates each item (1–5 scale)
    │       └── Sets: overall_rating, remarks, status → "approved" or "disapproved"
    │       └── Admin receives notification
    │
    ↓
[User / Faculty / Staff]
    │
    ├── 5. Fills IPCR Form
    │       └── KPIs auto-loaded from database
    │       └── Fills: accomplishment per KPI, self-rating
    │               (3 sections: Core, Strategic, Support functions)
    │
    ├── 6. Saves Draft → editable, not yet reviewed
    │       OR
    │       Submits IPCR → status: "pending", locked for editing
    │
    ↓
[Admin / Department Head]
    │
    ├── 7. Reviews IPCR submissions from own department
    │       └── Rates each item (1–5 scale)
    │       └── Overall rating = average of all item ratings
    │       └── Sets: overall_rating, remarks, status → "approved" or "disapproved"
    │       └── User receives notification
    │
    ↓
[Superadmin / Reports]
    │
    └── 8. Views system-wide reports, exports PDF/Excel
            └── Per-department OPCR performance
            └── Individual IPCR ratings
            └── Campus-wide analytics
```

### Data Flow (Frontend → Backend)

```
Browser JS (Fetch API)
    │
    ├── POST/GET → /api/{module}/{action}.php
    │
    ↓
PHP API Endpoint
    ├── session.php → requireAuth(['role'])    ← Gate: must be logged in + correct role
    ├── Validates input (type-checks, required fields)
    ├── database.php → PDO prepared statement  ← Safe parameterized query
    ├── Business logic (deadlines, rating avg, notifications)
    └── echo json_encode(['success' => true, 'data' => ...])
    │
    ↓
Browser JS receives JSON
    └── Updates DOM (table rows, chart data, status badges, toast notifications)
```

### Authentication Flow

```
Login Page (index.php)
    │
    POST → /api/auth/login.php
    │
    ├── Check: username exists?
    ├── Check: account status = "active"?
    ├── Check: not locked (failed_login_attempts < 3)?
    ├── password_verify($input, $bcryptHash)
    │
    ├── ON FAIL → increment failed_login_attempts
    │             if attempts ≥ 3: set locked_until = NOW() + 30s
    │             return error JSON
    │
    └── ON SUCCESS → session_regenerate_id(true)
                     $_SESSION['user_id'], ['role'], ['dept_id'] set
                     Reset failed_login_attempts = 0
                     Log activity: "User logged in"
                     return redirect JSON → role-based dashboard
```

---

## 6. System Roles & Features

### Superadmin

The system administrator for the entire CSU-Piat campus.

| Feature | Description |
|---------|-------------|
| **Dashboard** | Campus-wide stats: total users, IPCR counts, approval rates. Charts: gender distribution, approval status, department distribution. |
| **Accounts Management** | View all 50+ users in a paginated table. Activate or deactivate accounts. Approve pending self-registrations. |
| **OPCR Review** | View all OPCR submissions from all department heads. Rate individual OPCR line items (1–5). Approve or disapprove with remarks. Overall rating auto-computed. |
| **IPCR Accomplishments** | View and rate any IPCR form across the entire campus. |
| **Reports** | System-wide IPCR/OPCR reports filtered by department, status, date, academic year. Export to PDF or Excel (official template). |
| **Set Campus Targets** | Create and edit campus-level OPCR targets that cascade down to departments. |
| **Settings** | Manage academic year timelines (create, open/close). Manage KPI indicators (create, edit, deactivate). |
| **Account** | Edit own profile, change password with strength meter, view personal activity logs. |

### Admin (Dean / Department Head / Office Head)

One Admin per department. Manages their department's forms and reviews faculty submissions.

| Feature | Description |
|---------|-------------|
| **Dashboard** | Department-scoped stats: total faculty, IPCR submissions, pending reviews, approval rate. Charts: approval status, rating distribution. |
| **OPCR Form** | Create the department's OPCR: define MFO/PAPs, success indicators, targets, actual output, and budget per function type. Save draft or submit for superadmin review. |
| **Own IPCR Form** | Admin is also a university employee and must submit their own personal IPCR. |
| **Review IPCR** | View all IPCR submissions from faculty/staff in own department. Rate each line item (1–5). Set overall rating + remarks. Approve or disapprove. User notified automatically. |
| **Accomplishments** | Inline rating interface for bulk review of employee submissions. |
| **Reports** | Department-filtered IPCR/OPCR reports. Export to PDF or Excel. |
| **Account** | Edit profile, change password, view activity logs. |

### User (Faculty / Staff / Academic Personnel)

Regular university employees who submit their individual IPCR.

| Feature | Description |
|---------|-------------|
| **Dashboard** | Personal stats: IPCR status breakdown (draft/pending/reviewed/approved), latest overall rating, active timeline. Status distribution pie chart. |
| **IPCR Form** | Fill out the individual performance form with accomplishments per KPI item. Organized into Core, Strategic, and Support function sections. Save as draft (editable) or submit for admin review (locks form). |
| **Status Tracker** | Visual progress indicator: Draft → Submitted → Under Review → Final Decision. Shows current rating and reviewer remarks when available. |
| **Evidence Upload** | Upload supporting documents (PDF, images, Word files) for each IPCR category. Each file tagged with category and optional description. |
| **Account** | Edit profile (name, email, position), change password with strength meter, view personal activity log. |

---

## 7. API Endpoints

All endpoints return JSON: `{ "success": true/false, "data": ..., "message": "..." }`

### Authentication

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| POST | `/api/auth/login.php` | No | Login with username + password |
| POST | `/api/auth/logout.php` | Yes | Destroy session |
| POST | `/api/auth/register.php` | No | Create pending account |
| POST | `/api/auth/forgot-password.php` | No | Step 1–2: verify user + security question |
| POST | `/api/auth/reset-password.php` | No | Step 3: set new password |
| POST | `/api/auth/change-password.php` | Yes | Authenticated password change |

### IPCR

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| POST | `/api/ipcr/save.php` | user/admin | Save draft or submit IPCR form |
| GET | `/api/ipcr/get.php?id=` | Yes | Get single IPCR with items |
| GET | `/api/ipcr/list.php` | Yes | List IPCR forms (role-scoped) |
| POST | `/api/ipcr/review.php` | admin/superadmin | Rate items, set status, notify user |

### OPCR

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| POST | `/api/opcr/save.php` | admin | Save draft or submit OPCR form |
| GET | `/api/opcr/get.php?id=` | admin/superadmin | Get single OPCR with items |
| GET | `/api/opcr/list.php` | admin/superadmin | List OPCR forms (role-scoped) |
| POST | `/api/opcr/review.php` | superadmin | Rate items, set status, notify admin |

### Supporting

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/api/kpi/list.php` | Yes | KPIs filtered by category/department |
| POST | `/api/kpi/save.php` | superadmin | Create or update a KPI |
| POST | `/api/kpi/delete.php` | superadmin | Deactivate a KPI |
| GET | `/api/timeline/list.php` | Yes | List timelines |
| POST | `/api/timeline/save.php` | superadmin | Create timeline or toggle open/closed |
| GET | `/api/dashboard/user-stats.php` | user | Personal stats |
| GET | `/api/dashboard/admin-stats.php` | admin | Department stats |
| GET | `/api/dashboard/superadmin-stats.php` | superadmin | Campus-wide stats |
| GET | `/api/notifications/list.php` | Yes | Unread notifications |
| POST | `/api/notifications/mark-read.php` | Yes | Mark as read |
| GET | `/api/users/list.php` | superadmin | Paginated user list |
| POST | `/api/users/toggle-status.php` | superadmin | Activate/deactivate user |
| GET | `/api/departments/list.php` | Yes | All 11 departments |
| POST | `/api/user/update-profile.php` | Yes | Update own profile |
| GET | `/api/user/logs.php` | Yes | Own activity logs |

---

## 8. Authentication & Security

### What's Implemented

| Feature | Implementation |
|---------|---------------|
| **Password hashing** | `password_hash()` with `PASSWORD_BCRYPT` (cost factor 10) |
| **Password verification** | `password_verify()` — timing-safe comparison |
| **Session management** | PHP native sessions, `httponly`, `samesite=strict` cookie flags |
| **Session fixation prevention** | `session_regenerate_id(true)` on every successful login |
| **Rate limiting** | 3 failed attempts triggers 30-second account lockout (stored in DB) |
| **Role enforcement** | `requireAuth(['role'])` called at the top of every protected endpoint |
| **Department scoping** | Admin API endpoints filter all queries to `department_id = $_SESSION['dept_id']` |
| **Activity audit trail** | All logins, logouts, form submissions, approvals, password changes logged with IP |
| **CSRF token generation** | Framework in place in `session.php` |
| **Prepared statements** | All DB queries use PDO with parameterized inputs |

### Forgot Password Flow

```
Step 1: POST username → verify user exists + is active
Step 2: POST security_answer → verify against stored bcrypt hash
Step 3: SESSION flag set ['password_reset_allowed'] = true
         → POST new password → bcrypt hash + store → clear session flag
```

---

## 9. Setup & Installation

### Prerequisites

- XAMPP (Apache + MySQL) installed
- PHP 8.0+
- MySQL 8.0+

### Steps

1. **Place project** in XAMPP htdocs:
   ```
   C:\xampp\htdocs\AGS-AOPCR-IPCR-Generation-System\
   ```

2. **Start XAMPP**: Open XAMPP Control Panel → Start **Apache** and **MySQL**

3. **Open Setup Page** in browser:
   ```
   http://localhost/AGS-AOPCR-IPCR-Generation-System/setup.php
   ```

4. **Run all 4 steps** in order:
   - Step 1: Create Database (`csu_piat_performance`)
   - Step 2: Create Tables (all 11 tables from `schema.sql`)
   - Step 3: Create Upload Directories
   - Step 4: Seed Data (50 users, 11 departments, 4 timelines, 13 KPIs)

5. **Delete `setup.php`** after completion (security measure)

6. **Access the system**:
   ```
   http://localhost/AGS-AOPCR-IPCR-Generation-System/
   ```

### Database Configuration

Edit `/config/constants.php` if your credentials differ from defaults:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'csu_piat_performance');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP default: empty password
define('DB_CHARSET', 'utf8mb4');
```

---

## 10. Demo Credentials

| Role | Username | Password | Department |
|------|----------|----------|-----------|
| Super Admin | `superadmin` | `admin123` | — (system-wide) |
| Admin (CEO) | `admin` | `admin123` | Office of the Campus Executive Officer |
| Faculty | `faculty` | `faculty123` | Any assigned department |

> 50 additional user accounts are available from the seed data. See `/database/seed.php` for the full list.

---

## 11. Implementation Status

### Complete

| Module | Status |
|--------|--------|
| Database schema (11 tables) | Done |
| PDO singleton + setup page | Done |
| Authentication (login, logout, register, forgot password, change password) | Done |
| Rate limiting + account lockout | Done |
| Session management + role enforcement | Done |
| Activity logging | Done |
| IPCR form (save draft + submit) | Done |
| IPCR review by admin (rate items, approve/disapprove, notify) | Done |
| OPCR form (save draft + submit) | Done |
| OPCR review by superadmin (rate items, approve/disapprove, notify) | Done |
| Dashboard stats for all 3 roles | Done |
| Dashboard charts (Chart.js) | Done |
| KPI management (list, create, deactivate) | Done |
| Timeline management (create, open/close) | Done |
| Notification system (create + mark read) | Done |
| Department scoping for admin role | Done |
| Seed data (50 users, all departments, timelines, KPIs) | Done |

### In Progress

| Module | Status |
|--------|--------|
| Evidence file upload (real disk storage + validation) | In Progress |
| Reports page with PDF export (TCPDF integration) | In Progress |
| Reports page with Excel export (PhpSpreadsheet integration) | In Progress |
| Superadmin accounts management UI | In Progress |
| User profile update UI | In Progress |

### Planned

| Module | Status |
|--------|--------|
| Email notifications (PHPMailer — forgot password, approval alerts) | Planned |
| CSRF token validation on all POST forms | Planned |
| File upload MIME type + size validation | Planned |
| OPCR targets → IPCR auto-population (dept targets cascade to individuals) | Planned |
| Batch IPCR approval (approve multiple at once) | Planned |
| Profile picture upload | Planned |
| Admin drill-down reports (click stat → filtered table) | Planned |
R
---

## About CSU-Piat

**Cagayan State University — Piat Campus** is the first state agricultural college in the Province of Cagayan, established in **1954**. Located in the Ytawes District of Piat, Cagayan, it is one of nine satellite campuses of Cagayan State University.

Programs offered: Agriculture, Education, Criminal Justice Administration, Information and Computing Sciences.

- **Website:** https://piat.csu.edu.ph
- **Address:** Ytawes District, Piat, Cagayan, Philippines

---

*For academic and administrative use only — Cagayan State University, Piat Campus.*
