<?php
require_once __DIR__ . '/config/session.php';

if (isLoggedIn()) {
    redirectByRole(getSessionUser()['role']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | CSU-Piat AOPCR/IPCR System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="toast-container"></div>
<div class="login-wrapper">
  <div class="login-card" style="max-width:460px">
    <div class="login-header">
      <div class="login-logo"><i class="fa-solid fa-key"></i></div>
      <h4>FORGOT PASSWORD</h4>
      <p>CSU-Piat AOPCR/IPCR System</p>
    </div>
    <div class="login-body">

      <!-- Progress Steps -->
      <div class="status-steps mb-4">
        <div class="step-item">
          <div class="step-circle active" id="sc1">1</div>
          <div class="step-label active" id="sl1">Verify Username</div>
        </div>
        <div class="step-item">
          <div class="step-circle" id="sc2">2</div>
          <div class="step-label" id="sl2">Security Question</div>
        </div>
        <div class="step-item">
          <div class="step-circle" id="sc3">3</div>
          <div class="step-label" id="sl3">New Password</div>
        </div>
        <div class="step-item">
          <div class="step-circle" id="sc4">4</div>
          <div class="step-label" id="sl4">Done</div>
        </div>
      </div>

      <!-- Step 1: Verify Username -->
      <div class="step-card active" id="step1">
        <h6 class="fw-700 mb-1">Step 1: Verify Your Username</h6>
        <p class="text-muted mb-3" style="font-size:0.83rem">Enter your registered username to proceed.</p>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
            <input type="text" class="form-control" id="fpUsername" placeholder="Enter your username">
          </div>
        </div>
        <button class="btn btn-primary w-100" id="step1Btn" onclick="step1Next()">
          <i class="fa-solid fa-arrow-right me-2"></i>Next
        </button>
      </div>

      <!-- Step 2: Security Question -->
      <div class="step-card" id="step2">
        <h6 class="fw-700 mb-1">Step 2: Answer Security Question</h6>
        <p class="text-muted mb-3" style="font-size:0.83rem">Select the security question you registered with and enter your answer.</p>
        <div class="mb-3">
          <label class="form-label">Security Question</label>
          <select class="form-select" id="fpQuestion">
            <option value="">-- Select your security question --</option>
            <option>What is your mother's maiden name?</option>
            <option>What city were you born in?</option>
            <option>What is your pet's name?</option>
            <option>What was the name of your first school?</option>
            <option>What is your favorite book?</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Your Answer</label>
          <input type="text" class="form-control" id="fpAnswer" placeholder="Enter your answer">
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary" onclick="goStep(1)"><i class="fa-solid fa-arrow-left me-2"></i>Back</button>
          <button class="btn btn-primary flex-fill" id="step2Btn" onclick="step2Next()"><i class="fa-solid fa-arrow-right me-2"></i>Next</button>
        </div>
      </div>

      <!-- Step 3: New Password -->
      <div class="step-card" id="step3">
        <h6 class="fw-700 mb-1">Step 3: Create New Password</h6>
        <p class="text-muted mb-3" style="font-size:0.83rem">Enter and confirm your new password.</p>
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            <input type="password" class="form-control" id="fpNewPw" placeholder="Enter new password">
            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('fpNewPw',this)"><i class="fa-solid fa-eye"></i></button>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm New Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            <input type="password" class="form-control" id="fpConfirmPw" placeholder="Confirm new password">
            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('fpConfirmPw',this)"><i class="fa-solid fa-eye"></i></button>
          </div>
        </div>
        <div id="pwStrength" class="mb-3 d-none">
          <div class="progress mb-1" style="height:5px"><div class="progress-bar" id="pwBar"></div></div>
          <small id="pwLabel" class="text-muted"></small>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary" onclick="goStep(2)"><i class="fa-solid fa-arrow-left me-2"></i>Back</button>
          <button class="btn btn-primary flex-fill" id="step3Btn" onclick="step3Next()"><i class="fa-solid fa-check me-2"></i>Reset Password</button>
        </div>
      </div>

      <!-- Step 4: Success -->
      <div class="step-card" id="step4">
        <div class="text-center py-3">
          <div style="width:70px;height:70px;background:#d1e7dd;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:2rem;color:#198754">
            <i class="fa-solid fa-check"></i>
          </div>
          <h5 class="fw-700 text-success">Password Reset Successful!</h5>
          <p class="text-muted" style="font-size:0.85rem">Your password has been updated. You can now log in with your new password.</p>
          <a href="index.php" class="btn btn-primary mt-2"><i class="fa-solid fa-right-to-bracket me-2"></i>Back to Login</a>
        </div>
      </div>

      <div class="text-center mt-3">
        <a href="index.php" class="text-muted text-decoration-none" style="font-size:0.82rem">
          <i class="fa-solid fa-arrow-left me-1"></i>Back to Login
        </a>
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

  function goStep(n) {
    document.querySelectorAll('.step-card').forEach(c => c.classList.remove('active'));
    document.getElementById('step' + n).classList.add('active');
    for (let i = 1; i <= 4; i++) {
      const sc = document.getElementById('sc' + i);
      const sl = document.getElementById('sl' + i);
      sc.classList.remove('active', 'done');
      sl.classList.remove('active');
      if (i < n) { sc.classList.add('done'); sc.innerHTML = '<i class="fa-solid fa-check"></i>'; }
      else if (i === n) { sc.classList.add('active'); sc.textContent = i; sl.classList.add('active'); }
      else { sc.textContent = i; }
    }
  }

  async function step1Next() {
    const uname = document.getElementById('fpUsername').value.trim();
    if (!uname) { showToast('Please enter your username.', 'warning'); return; }

    const btn = document.getElementById('step1Btn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking...';

    try {
      const res = await fetch('api/auth/forgot-password.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ action: 'verify_username', username: uname }),
      });
      const data = await res.json();

      if (data.success) {
        document.getElementById('fpQuestion').value = '';
        document.getElementById('fpAnswer').value = '';
        goStep(2);
      } else {
        showToast(data.error, 'danger');
      }
    } catch {
      showToast('Server error. Make sure XAMPP is running.', 'danger');
    } finally {
      btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-arrow-right me-2"></i>Next';
    }
  }

  async function step2Next() {
    const question = document.getElementById('fpQuestion').value;
    const answer   = document.getElementById('fpAnswer').value.trim();
    if (!question) { showToast('Please select your security question.', 'warning'); return; }
    if (!answer)   { showToast('Please enter your answer.', 'warning'); return; }

    const btn = document.getElementById('step2Btn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';

    try {
      const res = await fetch('api/auth/forgot-password.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ action: 'verify_answer', question, answer }),
      });
      const data = await res.json();

      if (data.success) { goStep(3); }
      else { showToast(data.error, 'danger'); }
    } catch {
      showToast('Server error. Make sure XAMPP is running.', 'danger');
    } finally {
      btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-arrow-right me-2"></i>Next';
    }
  }

  async function step3Next() {
    const newPw    = document.getElementById('fpNewPw').value;
    const confirmPw = document.getElementById('fpConfirmPw').value;
    if (!newPw || newPw.length < 6) { showToast('Password must be at least 6 characters.', 'warning'); return; }
    if (newPw !== confirmPw) { showToast('Passwords do not match.', 'danger'); return; }

    const btn = document.getElementById('step3Btn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Resetting...';

    try {
      const res = await fetch('api/auth/reset-password.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ password: newPw, confirm_password: confirmPw }),
      });
      const data = await res.json();

      if (data.success) { goStep(4); }
      else { showToast(data.error, 'danger'); }
    } catch {
      showToast('Server error. Make sure XAMPP is running.', 'danger');
    } finally {
      btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Reset Password';
    }
  }

  document.getElementById('fpNewPw').addEventListener('input', function() {
    const val = this.value;
    const bar = document.getElementById('pwBar');
    const label = document.getElementById('pwLabel');
    const strength = document.getElementById('pwStrength');
    if (!val) { strength.classList.add('d-none'); return; }
    strength.classList.remove('d-none');
    let score = 0;
    if (val.length >= 6) score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = ['', 'Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
    const colors = ['', 'bg-danger', 'bg-danger', 'bg-warning', 'bg-success', 'bg-success'];
    bar.style.width = (score * 20) + '%';
    bar.className = 'progress-bar ' + (colors[score] || '');
    label.textContent = levels[score] || '';
  });
</script>
</body>
</html>
