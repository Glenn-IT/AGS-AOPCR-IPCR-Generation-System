1. Login
   - [x] C:\xampp\htdocs\AGS-AOPCR-IPCR-Generation-System\img\CSU-Logo.png, Update the icon on the login ok use that logo (Resolved)

2. User
   IPCR Form
   - [x] Remove the header buttons (`#btnViewEvidence`, `#btnUploadEvidence`, `Print`, `#btnSaveDraft`, `#btnSubmit` in `.page-header`) (Resolved)
   - [x] Covered Period, it should not be editable (Resolved - set to readonly and auto-populated from active timeline)
   - [x] Q,E,T - it should only accept 1 to 5 (Resolved - clamped oninput and restricted invalid symbols)
   - [x] If the user submits the IPCR, provide the edit function just like on the admin side (`#editBtn2`, `setReadOnly`, and `enableEdit`) (Resolved)

3. My Profile
   - [x] Upload Profile Picture Function (Resolved - added file upload, storage in uploads/avatars/, and avatar sync across navbar, sidebar, and account profile)
