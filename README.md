# AOPCR/IPCR Generation System — CSU-Piat

**Automated Office Performance Commitment Rating (OPCR) and Individual Performance Commitment Rating (IPCR) Generation System**

Cagayan State University — Piat Campus | Ytawes District, Piat, Cagayan | Founded 1954

---

## Overview

A fully functional **frontend prototype** of a performance evaluation and management system for CSU-Piat faculty and staff. All data is stored using `localStorage` — no backend, no database, no PHP, no MySQL required.

---

## Tech Stack

| Technology | Purpose |
|---|---|
| HTML5 | Page structure |
| CSS3 | Custom styling and layout |
| Bootstrap 5.3 | Responsive UI components |
| Vanilla JavaScript | Logic and interactivity |
| Chart.js | Dashboard analytics |
| Font Awesome 6 | Icons |
| localStorage | Data persistence (simulated) |

---

## Project Structure

```
AGS-AOPCR-IPCR-Generation-System/
│
├── index.html                  # Login page
├── register.html               # User registration
├── forgot-password.html        # 4-step password reset
├── README.md
│
├── assets/
│   ├── css/
│   │   └── style.css           # Global styles, responsive layout
│   └── js/
│       ├── auth.js             # Authentication, session, lockout logic
│       └── components.js       # Shared sidebar, navbar, toasts, modals
│
├── data/
│   └── mock-data.js            # 20 CSU-Piat employees + all mock data
│
├── docs/
│   └── Prompt.md               # Original project prompt
│
└── views/
    ├── superadmin/
    │   ├── dashboard.html      # Stats + 3 charts
    │   ├── accounts.html       # Account management (activate/deactivate)
    │   ├── reports.html        # All OPCR/IPCR reports + CSV export
    │   └── settings.html       # Timeline + KPI management (CRUD)
    │
    ├── admin/
    │   ├── dashboard.html      # Dept stats + rating/approval charts
    │   ├── set-target.html     # OPCR form builder (editable rows)
    │   ├── accomplishments.html# Rate employee IPCR submissions
    │   └── reports.html        # Filtered IPCR reports + CSV export
    │
    └── users/
        ├── dashboard.html      # Personal stats + status chart
        ├── ipcr-form.html      # IPCR form (save draft / submit / print)
        ├── evidence.html       # Document upload (drag-and-drop)
        ├── status.html         # Submission tracking with progress steps
        └── account.html        # Profile edit, change password, activity logs
```

---

## Demo Credentials

| Role | Username | Password |
|---|---|---|
| Super Admin | `superadmin` | `admin123` |
| Admin (CEO) | `admin` | `admin123` |
| Faculty/Staff | `faculty` | `faculty123` |

Additional accounts are available in `data/mock-data.js`.

---

## System Roles & Features

### Super Admin
- Dashboard with gender distribution, approval status, and department distribution charts
- Account management — view, activate, and deactivate accounts
- System-wide OPCR/IPCR reports with CSV export and print
- Settings — manage academic year timelines and KPI indicators (Add/Edit/Delete)

### Admin (Dean / Office Head)
- Department-level dashboard with approval and rating charts
- OPCR form builder — set MFO/PAP, success indicators, targets, and measures
- Accomplishments & ratings — input actual accomplishments and rate IPCR submissions with auto-computed overall rating
- Filtered IPCR reports with CSV export

### Faculty / Staff (User)
- Personal dashboard with form status overview
- IPCR form — fill out accomplishments per KPI, save draft, and submit for review
- Evidence upload — drag-and-drop file uploader with category tagging (localStorage simulation)
- View status — progress tracker showing Submitted → Under Review → Final Decision
- Account management — edit profile, change password with strength meter, activity logs

---

## Authentication Features

- Login with username and password
- Show/hide password toggle
- Remember Me (persists username via localStorage)
- Forgot Password — 4-step flow: verify username → security question → new password → success
- Login attempt tracking — locked for 30 seconds after 3 failed attempts
- Session management via localStorage
- Logout confirmation modal
- Account activity logging

---

## Mock Data

**20 CSU-Piat Employees** across real departments:

| # | Department | Type |
|---|---|---|
| 1 | Office of the Campus Executive Officer | Administrative |
| 2 | Registrar's Office | Administrative |
| 3 | Accounting Office | Administrative |
| 4 | Human Resource Office | Administrative |
| 5 | Research, Development & Extension Office | Administrative |
| 6 | IT Office | Administrative |
| 7 | Partnership & Resource Mobilization Office | Administrative |
| 8 | College of Agriculture | Academic (AACCUP Level II) |
| 9 | College of Criminal Justice Administration | Academic |
| 10 | College of Information and Computing Sciences | Academic |
| 11 | College of Education | Academic |

Pre-loaded data includes:
- 7 sample IPCR forms with varied statuses (approved, pending, reviewed, disapproved)
- 2 sample OPCR forms
- KPI indicators across Core, Strategic, and Support functions
- 4 academic year timelines (2024-2025 and 2025-2026)
- Account activity logs and notifications

---

## About CSU-Piat

**Cagayan State University — Piat Campus** is the first state agricultural college in the Province of Cagayan, established in **1954**. Located in the Ytawes District of Piat, Cagayan, it is one of nine satellite campuses of Cagayan State University.

The campus offers programs in Agriculture, Education, Criminal Justice Administration, and Information and Computing Sciences — several of which hold Level I and Level II accreditation from AACCUP (Accrediting Agency of Chartered Colleges and Universities in the Philippines).

- **Website:** https://piat.csu.edu.ph
- **Address:** Ytawes District, Piat, Cagayan, Philippines
- **Vision:** A premier university in the Asia-Pacific region producing globally competitive graduates and research innovations for sustainable development.

---

## How to Run

1. Place the project folder inside your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\AGS-AOPCR-IPCR-Generation-System\
   ```
2. Start **Apache** in the XAMPP Control Panel.
3. Open your browser and navigate to:
   ```
   http://localhost/AGS-AOPCR-IPCR-Generation-System/
   ```
4. Log in using any of the demo credentials above.

> **Note:** No database setup is needed. All data is initialized automatically in `localStorage` on first load.

---

## Resetting Data

To reset all mock data back to defaults, open the browser console and run:

```js
localStorage.clear();
location.reload();
```

---

## License

For academic and demonstration purposes only — Cagayan State University, Piat Campus.
