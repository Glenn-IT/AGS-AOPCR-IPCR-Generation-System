# AOPCR/IPCR Generation System — CSU-Piat

**Automated Office Performance Commitment Rating (OPCR) and Individual Performance Commitment Rating (IPCR) Generation System**

Cagayan State University — Piat Campus | Ytawes District, Piat, Cagayan | Founded 1954

---

## Overview

A **PHP/MySQL performance evaluation system** for CSU-Piat faculty and staff. The system manages the full academic performance review cycle — from individual IPCR submissions by faculty, to department-level OPCR targets by office heads, to system-wide review and reporting by the campus administrator.

> Full system documentation: [`docs/system-overview.md`](docs/system-overview.md)

---

## Tech Stack

| Technology | Purpose |
|---|---|
| PHP 8.x | Backend logic, authentication, API endpoints |
| MySQL 8.x | Persistent data storage (11 tables) |
| Apache (XAMPP) | Local web server |
| Bootstrap 5.3 | Responsive UI components |
| Vanilla JavaScript (ES6+) | AJAX, dynamic UI, form handling |
| Chart.js | Dashboard analytics |
| Font Awesome 6 | Icons |

---

## Quick Start

1. Place the project in your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\AGS-AOPCR-IPCR-Generation-System\
   ```

2. Start **Apache** and **MySQL** in the XAMPP Control Panel.

3. Run the database setup at:
   ```
   http://localhost/AGS-AOPCR-IPCR-Generation-System/setup.php
   ```
   Complete all 4 steps: Create Database → Create Tables → Create Upload Dirs → Seed Data.

4. Delete `setup.php` after setup is complete (security).

5. Open the system:
   ```
   http://localhost/AGS-AOPCR-IPCR-Generation-System/
   ```

---

## Demo Credentials

| Role | Username | Password |
|---|---|---|
| Super Admin | `superadmin` | `admin123` |
| Admin (CEO) | `admin` | `admin123` |
| Faculty/Staff | `faculty` | `faculty123` |

50 additional accounts are available from the seed data (`database/seed.php`).

---

## System Roles

### Super Admin
- Campus-wide dashboard with gender, approval, and department distribution charts
- Account management — view all users, activate/deactivate accounts
- OPCR review — rate and approve/disapprove department OPCR submissions
- System-wide IPCR/OPCR reports with PDF and Excel export
- Settings — manage academic year timelines and KPI indicators

### Admin (Dean / Department Head / Office Head)
- Department-scoped dashboard with approval and rating charts
- OPCR form builder — set MFO/PAPs, success indicators, targets, and budget
- IPCR review — rate faculty submissions from own department, approve/disapprove
- Department-filtered reports with export
- Own personal IPCR form

### Faculty / Staff (User)
- Personal dashboard with IPCR status breakdown and latest rating
- IPCR form — fill accomplishments per KPI, save draft, or submit for review
- Status tracker — progress steps from draft to final decision
- Evidence upload — supporting documents per IPCR category
- Account management — profile, password change, activity logs

---

## Project Structure

```
AGS-AOPCR-IPCR-Generation-System/
├── index.php                   ← Login page
├── register.php                ← Self-registration
├── forgot-password.php         ← 3-step password recovery
├── setup.php                   ← One-time database installer
│
├── config/                     ← DB config, session, constants
├── database/                   ← schema.sql + seed.php
├── api/                        ← 30 JSON API endpoints
│   ├── auth/                   ← login, logout, register, password
│   ├── ipcr/                   ← save, get, list, review
│   ├── opcr/                   ← save, get, list, review
│   ├── kpi/                    ← list, save, delete
│   ├── timeline/               ← list, save
│   ├── dashboard/              ← stats per role
│   ├── notifications/          ← list, mark-read
│   ├── users/                  ← list, toggle-status
│   └── departments/            ← list
│
├── views/
│   ├── superadmin/             ← dashboard, accounts, review-opcr, reports, settings
│   ├── admin/                  ← dashboard, ipcr-form, review-ipcr, set-target, reports
│   └── users/                  ← dashboard, ipcr-form, status, evidence, account
│
├── assets/
│   ├── css/style.css
│   └── js/
│       ├── components.js
│       └── auth.js
│
├── uploads/evidence/           ← Stored IPCR supporting documents
│
└── docs/
    ├── system-overview.md      ← Full system documentation
    ├── audit.md
    └── templates/              ← Official CSU-Piat IPCR/OPCR Excel templates
```

---

## Authentication Features

- Login with bcrypt password verification
- Show/hide password toggle
- Remember Me (persists username)
- Forgot password — 3-step flow: verify username → security question → new password
- Rate limiting — 30-second lockout after 3 failed attempts (stored in DB)
- Session management with `httponly` + `samesite=strict` cookie flags
- Session regeneration on login
- Full activity audit trail (logins, approvals, password changes)

---

## About CSU-Piat

**Cagayan State University — Piat Campus** is the first state agricultural college in the Province of Cagayan, established in **1954**.

- **Website:** https://piat.csu.edu.ph
- **Address:** Ytawes District, Piat, Cagayan, Philippines
- 11 departments: 7 administrative offices + 4 academic colleges

---

## License

For academic and administrative use only — Cagayan State University, Piat Campus.
