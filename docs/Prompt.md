# AUTOMATED OPCR/IPCR GENERATION SYSTEM FOR CSU-PIAT

You are a Senior Frontend Developer and UI/UX Designer.

Create a COMPLETE PRESENTATION-READY FRONTEND PROTOTYPE for:

AUTOMATED OFFICE PERFORMANCE COMMITMENT RATING (OPCR)
AND
INDIVIDUAL PERFORMANCE COMMITMENT RATING (IPCR)
GENERATION SYSTEM FOR CSU-PIAT

IMPORTANT:

This is a FRONTEND PROTOTYPE ONLY.

NO BACKEND.

NO DATABASE.

NO APIs.

NO PHP.

NO MYSQL.

Use localStorage and mock data to simulate all system functions.

The system must look and behave like a fully working production application.

---

## TECH STACK

- HTML5
- CSS3
- Bootstrap 5
- Vanilla JavaScript
- Chart.js
- Font Awesome

---

## PROJECT STRUCTURE

/OPCR-IPCR-SYSTEM
│
├── index.html
│
├── assets
│ ├── css
│ ├── js
│ ├── images
│ └── icons
│
├── components
│ ├── sidebar.html
│ ├── navbar.html
│ ├── footer.html
│ ├── modals.html
│ └── toast.html
│
├── data
│ └── mock-data.js
│
└── views
├── superadmin
├── admin
└── users

---

## SYSTEM ROLES

1. Super Admin
2. Admin
3. Faculty/Staff User

---

## AUTHENTICATION MODULE

Create:

LOGIN PAGE

Fields:

- Username
- Password

Features:

- Show Password
- Remember Me
- Forgot Password
- Security Question
- Change Password
- Login Attempts

Mock Accounts:

SUPER ADMIN
username: superadmin
password: admin123

ADMIN
username: admin
password: admin123

USER
username: faculty
password: faculty123

---

## FORGOT PASSWORD

Step 1:
Verify Username

Step 2:
Answer Security Question

Step 3:
Create New Password

Step 4:
Success Notification

---

## REGISTRATION MODULE

Fields:

- Full Name
- Username
- Password
- Security Question
- Security Answer
- Role
- Department/Office

Functions:

- Register
- Validate Input
- Store in localStorage

---

## SUPER ADMIN MODULE

A. DASHBOARD

Statistics Cards:

- Total Employees
- Total Department Heads
- Approved OPCR/IPCR
- Disapproved OPCR/IPCR

Charts:

- Gender Distribution
- Approval Status Distribution
- Department Distribution

B. ACCOUNT MANAGEMENT

Data Table

Columns:

- Name
- Department
- Role
- Status

Functions:

- View Account
- Activate
- Deactivate
- Search Account

C. REPORTS

Display:

- Employee
- Department
- Rating
- Status
- Last Updated

Functions:

- View Details
- Generate Reports
- Export PDF Simulation
- Print Preview

D. SETTINGS

Submodules:

1. Timeline Management

Fields:

- Academic Year
- Semester

Actions:

- Add Timeline
- Edit Timeline

2. KPI Management

Categories:

- Core Function
- Strategic Function
- Support Function

Actions:

- Add KPI
- Edit KPI
- Delete KPI

---

## ADMIN MODULE

A. DASHBOARD

Cards:

- Total Employees
- Total Department Heads
- Approved OPCR/IPCR
- Disapproved OPCR/IPCR

Charts:

- Approval Statistics
- Employee Distribution

B. SET TARGET

OPCR/IPCR Form

Sections:

- Core Function
- Strategic Function
- Support Function

Fields:

- MFO/PAP
- Success Indicators
- Target
- Measure

Functions:

- Add Row
- Edit Row
- Confirm Edit
- Save
- Print

C. SET ACTUAL ACCOMPLISHMENT & RATINGS

Fields:

- College/Office
- Name
- Position
- Covered Period
- Date

Functions:

- Input Accomplishment
- Input Ratings
- Save
- Edit
- Print

D. REPORTS

Display Submitted OPCR/IPCR Forms

Filters:

- Department
- Status
- Date Range

---

## USER (FACULTY/STAFF) MODULE

A. DASHBOARD

Cards:

- Approved Forms
- Disapproved Forms

Charts:

- Status Overview

B. IPCR FORM

Fields:

- College/Office
- Name
- Position
- Covered Period
- Date

Sections:

- Core Function
- Strategic Function
- Support Function

Functions:

- Update
- Save
- Submit
- Print

C. EVIDENCE UPLOAD

Upload Section

Functions:

- Upload Supporting Documents
- View Uploaded Files
- Delete File

Use localStorage simulation.

D. VIEW STATUS

Display:

- Covered Period
- Status

Status Values:

- Pending
- Reviewed
- Approved
- Disapproved

E. ACCOUNT MANAGEMENT

Tabs:

1. Change Password
2. Account Logs

Account Logs Table:

- Date
- Time
- User Activity

---

## LOGOUT MODULE

Create Logout Confirmation Modal

Message:

"Are you sure you want to log out?"

Buttons:

- Logout
- Cancel

---

## MOCK DATA

Generate:

50 Employees

Departments:

- Registrar
- Accounting
- Human Resource
- Research
- Extension
- IT Office
- College of Education
- College of Engineering

Generate:

- Sample OPCR
- Sample IPCR
- Sample Ratings
- Sample Reports
- Sample KPI
- Sample Timelines

Store all data in localStorage.

---

## UI/UX REQUIREMENTS

Color Theme:

Primary:
#0D6EFD

Secondary:
#FFFFFF

Accent:
#EAF3FF

Use:

- Sidebar Navigation
- Top Navbar
- Statistic Cards
- Data Tables
- Search Bars
- Bootstrap Modals
- Toast Notifications
- Chart.js Graphs

Responsive:

- Desktop
- Tablet
- Mobile

---

## OUTPUT REQUIREMENTS

Generate:

1. Complete Folder Structure
2. All HTML Pages
3. CSS Files
4. JavaScript Files
5. Reusable Components
6. Mock Data
7. Working Navigation
8. Dashboard Analytics
9. Responsive Design
10. Fully Clickable Prototype

IMPORTANT:

The prototype must appear like a fully functional OPCR/IPCR Generation System even though all operations are simulated using localStorage.
