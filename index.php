<?php
require_once __DIR__ . '/config/session.php';

// Already logged in — go straight to dashboard
if (isLoggedIn()) {
    redirectByRole(getSessionUser()['role']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | CSU-Piat AOPCR/IPCR System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="toast-container"></div>

<div class="login-wrapper">
  <div class="login-card">
    <div class="login-header">
      <div class="login-logo"><i class="fa-solid fa-university"></i></div>
      <h4>CAGAYAN STATE UNIVERSITY</h4>
      <p>Piat Campus &bull; Ytawes District, Piat, Cagayan</p>
      <div style="margin-top:10px;padding:6px 14px;background:rgba(255,255,255,0.15);border-radius:20px;display:inline-block;font-size:0.78rem;letter-spacing:1px">
        AOPCR/IPCR Generation System
      </div>
    </div>
    <div class="login-body">
      <div id="lockAlert" class="alert alert-danger d-none py-2 mb-3" role="alert">
        <i class="fa-solid fa-lock me-2"></i><span id="lockMsg"></span>
      </div>

      <form id="loginForm" novalidate>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
            <input type="text" class="form-control" id="username" placeholder="Enter username" required autocomplete="username">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            <input type="password" class="form-control" id="password" placeholder="Enter password" required autocomplete="current-password">
            <button type="button" class="btn btn-outline-secondary" id="togglePw" tabindex="-1">
              <i class="fa-solid fa-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label" for="rememberMe" style="font-size:0.83rem">Remember Me</label>
          </div>
          <a href="forgot-password.php" class="text-primary text-decoration-none" style="font-size:0.83rem">
            <i class="fa-solid fa-key me-1"></i>Forgot Password?
          </a>
        </div>

        <div id="attemptsBar" class="mb-3 d-none">
          <div class="d-flex justify-content-between mb-1">
            <small class="text-danger">Login Attempts</small>
            <small id="attemptsText" class="text-danger"></small>
          </div>
          <div class="progress" style="height:5px">
            <div class="progress-bar bg-danger" id="attemptsProgress"></div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2" id="loginBtn">
          <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
        </button>
      </form>

      <div class="text-center mt-3">
        <span style="font-size:0.82rem;color:#888">Don't have an account?</span>
        <a href="register.php" class="text-primary text-decoration-none ms-1" style="font-size:0.82rem;font-weight:600">Register here</a>
      </div>

      <hr class="my-3">
      <div class="text-center">
        <small class="text-muted d-block mb-2" style="font-size:0.75rem">Demo Credentials</small>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
          <button class="btn btn-outline-primary btn-sm" onclick="fillDemo('superadmin','admin123')">Super Admin</button>
          <button class="btn btn-outline-success btn-sm" onclick="fillDemo('admin','admin123')">Admin</button>
          <button class="btn btn-outline-secondary btn-sm" onclick="fillDemo('faculty','faculty123')">Faculty</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/components.js"></script>
<script>
  const MAX_ATTEMPTS = 3;
  let localAttempts = parseInt(sessionStorage.getItem('csu_login_attempts') || '0');

  // Remember Me
  const remembered = localStorage.getItem('csu_piat_remember');
  if (remembered) document.getElementById('username').value = remembered;

  // Show/hide password
  document.getElementById('togglePw').addEventListener('click', () => {
    const pw = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    pw.type = pw.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
  });

  function updateAttemptsBar(count) {
    if (count > 0) {
      document.getElementById('attemptsBar').classList.remove('d-none');
      document.getElementById('attemptsText').textContent = count + ' / ' + MAX_ATTEMPTS;
      document.getElementById('attemptsProgress').style.width = ((count / MAX_ATTEMPTS) * 100) + '%';
    }
  }

  updateAttemptsBar(localAttempts);

  document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const rememberMe = document.getElementById('rememberMe').checked;
    const btn = document.getElementById('loginBtn');
    const lockAlert = document.getElementById('lockAlert');

    if (!username || !password) {
      showToast('Please enter your username and password.', 'warning');
      return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
    lockAlert.classList.add('d-none');

    try {
      const res = await fetch('api/auth/login.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ username, password }),
      });
      const data = await res.json();

      if (data.success) {
        sessionStorage.removeItem('csu_login_attempts');
        if (rememberMe) localStorage.setItem('csu_piat_remember', username);
        else localStorage.removeItem('csu_piat_remember');

        showToast('Welcome, ' + data.user.name + '!', 'success', 'Login Successful');

        const roleMap = {
          superadmin: 'views/superadmin/dashboard.php',
          admin:      'views/admin/dashboard.php',
          user:       'views/users/dashboard.php',
        };
        setTimeout(() => { window.location.href = roleMap[data.user.role] || 'index.php'; }, 800);

      } else {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-right-to-bracket me-2"></i>Sign In';
        lockAlert.classList.remove('d-none');
        document.getElementById('lockMsg').textContent = data.error;

        if (!data.locked) {
          localAttempts = data.attempts || (localAttempts + 1);
          sessionStorage.setItem('csu_login_attempts', String(localAttempts));
          updateAttemptsBar(localAttempts);
        }
      }
    } catch (err) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-right-to-bracket me-2"></i>Sign In';
      showToast('Server error. Make sure XAMPP is running.', 'danger');
    }
  });

  function fillDemo(u, p) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = p;
  }
</script>
</body>
</html>
