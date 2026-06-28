// CSU-PIAT Auth Module — PHP/MySQL backend version

// ----------------------------------------------------------------
// Session helpers (PHP session is the source of truth)
// SESSION_USER is injected by PHP into each protected page.
// ----------------------------------------------------------------

function getSession() {
  return window.SESSION_USER || null;
}

function requireAuth(allowedRoles) {
  // PHP already enforces this server-side.
  // This JS stub just returns the session for UI use and acts as
  // a last-resort client-side safety net.
  const session = window.SESSION_USER;
  if (!session) {
    window.location.href = getBasePath() + 'index.php';
    return null;
  }
  if (allowedRoles && !allowedRoles.includes(session.role)) {
    redirectByRole(session.role);
    return null;
  }
  return session;
}

function logout() {
  sessionStorage.removeItem('csu_login_attempts');
  sessionStorage.removeItem('csu_lockout_until');
  window.location.href = getBasePath() + 'api/auth/logout.php';
}

function redirectByRole(role) {
  const base = getBasePath();
  if (role === 'superadmin') window.location.href = base + 'views/superadmin/dashboard.php';
  else if (role === 'admin') window.location.href = base + 'views/admin/dashboard.php';
  else window.location.href = base + 'views/users/dashboard.php';
}

function getBasePath() {
  const path = window.location.pathname;
  if (path.includes('/views/superadmin/')) return '../../';
  if (path.includes('/views/admin/'))      return '../../';
  if (path.includes('/views/users/'))      return '../../';
  return '';
}

// ----------------------------------------------------------------
// Activity log — now handled server-side, this is a no-op
// ----------------------------------------------------------------
function addLog(userId, activity) { /* handled by PHP */ }

// ----------------------------------------------------------------
// Password change — AJAX to backend
// ----------------------------------------------------------------
function changePassword(userId, oldPassword, newPassword) {
  // Returns a Promise. Callers that expect synchronous result
  // (account pages) must be updated to handle the Promise.
  return fetch(getBasePath() + 'api/auth/change-password.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({
      current_password: oldPassword,
      new_password:     newPassword,
      confirm_password: newPassword,
    }),
  }).then(r => r.json());
}

// ----------------------------------------------------------------
// Forgot password helpers (used by forgot-password.html inline JS)
// These are AJAX wrappers — return Promises.
// ----------------------------------------------------------------
function verifyUsername(username) {
  return fetch('api/auth/forgot-password.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ action: 'verify_username', username }),
  }).then(r => r.json());
}

function verifySecurityAnswer(username, answer) {
  return fetch('api/auth/forgot-password.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ action: 'verify_answer', answer }),
  }).then(r => r.json());
}

function resetPassword(username, newPassword) {
  return fetch('api/auth/reset-password.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ password: newPassword, confirm_password: newPassword }),
  }).then(r => r.json());
}

// ----------------------------------------------------------------
// Registration (used by register.html inline JS)
// ----------------------------------------------------------------
function registerUser(data) {
  return fetch('api/auth/register.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({
      name:              data.name,
      username:          data.username,
      password:          data.password,
      confirm_password:  data.password,
      email:             data.email || '',
      gender:            data.gender || '',
      department:        data.department,
      position:          data.position || '',
      security_question: data.securityQuestion,
      security_answer:   data.securityAnswer,
    }),
  }).then(r => r.json());
}

// ----------------------------------------------------------------
// Login attempt counter display (client-side only)
// Real rate limiting is server-side (login_attempts table).
// ----------------------------------------------------------------
const MAX_LOGIN_ATTEMPTS = 3;

function getAttempts() {
  return parseInt(sessionStorage.getItem('csu_login_attempts') || '0');
}
function setAttempts(n) {
  sessionStorage.setItem('csu_login_attempts', String(n));
}
function resetAttempts() {
  sessionStorage.removeItem('csu_login_attempts');
}
