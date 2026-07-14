<?php
require_once __DIR__ . '/config/session.php';

// Already logged in — go to dashboard
if (isLoggedIn()) {
    redirectByRole(getSessionUser()['role']);
}

// Load departments from DB for the form
$departments = [];
try {
    $db = getDB();
    $departments = $db->query(
        'SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name'
    )->fetchAll();
} catch (Exception $e) {
    // DB not set up yet — fall back gracefully
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | CSU-Piat AOPCR/IPCR System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="toast-container"></div>
<div class="login-wrapper" style="align-items:flex-start;padding:30px 20px">
  <div class="login-card" style="max-width:520px;margin:0 auto">
    <div class="login-header">
      <div class="login-logo"><i class="fa-solid fa-user-plus"></i></div>
      <h4>CREATE ACCOUNT</h4>
      <p>CSU-Piat AOPCR/IPCR System</p>
    </div>
    <div class="login-body">
      <form id="registerForm" novalidate>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
              <input type="text" class="form-control" id="regName" placeholder="e.g. Juan dela Cruz" required>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Username <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
              <input type="text" class="form-control" id="regUsername" placeholder="e.g. juan.delacruz" required>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
              <input type="email" class="form-control" id="regEmail" placeholder="your@email.com">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
              <input type="password" class="form-control" id="regPassword" placeholder="Min. 6 characters" required>
              <button type="button" class="btn btn-outline-secondary" onclick="togglePw('regPassword',this)"><i class="fa-solid fa-eye"></i></button>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
              <input type="password" class="form-control" id="regConfirmPw" placeholder="Re-enter password" required>
              <button type="button" class="btn btn-outline-secondary" onclick="togglePw('regConfirmPw',this)"><i class="fa-solid fa-eye"></i></button>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Gender</label>
            <select class="form-select" id="regGender">
              <option value="">Select Gender</option>
              <option>Male</option>
              <option>Female</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Department / Office <span class="text-danger">*</span></label>
            <select class="form-select" id="regDepartment" required>
              <option value="">Select Department</option>
              <?php foreach ($departments as $dept): ?>
              <option value="<?= htmlspecialchars($dept['id']) ?>"><?= htmlspecialchars($dept['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Position</label>
            <select class="form-select" id="regPosition">
              <option value="">Select Position</option>
              <optgroup label="Instructor">
                <option>Instructor I</option>
                <option>Instructor II</option>
                <option>Instructor III</option>
              </optgroup>
              <optgroup label="Assistant Professor">
                <option>Assistant Professor I</option>
                <option>Assistant Professor II</option>
                <option>Assistant Professor III</option>
                <option>Assistant Professor IV</option>
              </optgroup>
              <optgroup label="Associate Professor">
                <option>Associate Professor I</option>
                <option>Associate Professor II</option>
                <option>Associate Professor III</option>
                <option>Associate Professor IV</option>
                <option>Associate Professor V</option>
              </optgroup>
              <optgroup label="Professor">
                <option>Professor I</option>
                <option>Professor II</option>
                <option>Professor III</option>
                <option>Professor IV</option>
                <option>Professor V</option>
                <option>Professor VI</option>
              </optgroup>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Role / Designation</label>
            <select class="form-select" id="regDesignation">
              <option value="">Select Role</option>
              <option>Dean</option>
              <option>Department Head</option>
              <option>Office Head</option>
              <option>Faculty</option>
              <option>Staff</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Security Question <span class="text-danger">*</span></label>
            <select class="form-select" id="regSecQ" required>
              <option value="">Select a question</option>
              <option>What is your mother's maiden name?</option>
              <option>What city were you born in?</option>
              <option>What is your pet's name?</option>
              <option>What was the name of your first school?</option>
              <option>What is your favorite book?</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Security Answer <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="regSecA" placeholder="Your answer (case-insensitive)" required>
          </div>
        </div>

        <div class="alert alert-info mt-3 py-2" style="font-size:0.82rem">
          <i class="fa-solid fa-circle-info me-1"></i>
          Your account will be <strong>pending approval</strong> by the Super Administrator before you can log in.
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 mt-2" id="registerBtn">
          <i class="fa-solid fa-user-plus me-2"></i>Register Account
        </button>
      </form>

      <div class="text-center mt-3">
        <span style="font-size:0.82rem;color:#888">Already have an account?</span>
        <a href="index.php" class="text-primary text-decoration-none ms-1" style="font-size:0.82rem;font-weight:600">Sign in here</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/components.js"></script>
<script>
  function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash');
  }

  document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const name      = document.getElementById('regName').value.trim();
    const username  = document.getElementById('regUsername').value.trim();
    const email     = document.getElementById('regEmail').value.trim();
    const password  = document.getElementById('regPassword').value;
    const confirmPw = document.getElementById('regConfirmPw').value;
    const gender    = document.getElementById('regGender').value;
    const dept      = document.getElementById('regDepartment').value;
    const position    = document.getElementById('regPosition').value.trim();
    const designation = document.getElementById('regDesignation').value;
    const secQ      = document.getElementById('regSecQ').value;
    const secA      = document.getElementById('regSecA').value.trim();
    const btn       = document.getElementById('registerBtn');

    if (!name || !username || !password || !dept || !secQ || !secA) {
      showToast('Please fill in all required fields.', 'warning'); return;
    }
    if (password.length < 6) { showToast('Password must be at least 6 characters.', 'warning'); return; }
    if (password !== confirmPw) { showToast('Passwords do not match.', 'danger'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registering...';

    try {
      const res = await fetch('api/auth/register.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({
          name, username, email, password, confirm_password: confirmPw,
          gender, department: dept, position, designation, security_question: secQ, security_answer: secA,
        }),
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message, 'success', 'Registration Successful');
        setTimeout(() => window.location.href = 'index.php', 2000);
      } else {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-user-plus me-2"></i>Register Account';
        showToast(data.error, 'danger');
      }
    } catch (err) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-user-plus me-2"></i>Register Account';
      showToast('Server error. Make sure XAMPP is running.', 'danger');
    }
  });
</script>
</body>
</html>
