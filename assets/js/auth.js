// CSU-PIAT Authentication Module

const MAX_LOGIN_ATTEMPTS = 3;

function getUsers() { return JSON.parse(localStorage.getItem('csu_piat_users')) || []; }
function getSession() { return JSON.parse(localStorage.getItem('csu_piat_session')); }
function setSession(user) { localStorage.setItem('csu_piat_session', JSON.stringify(user)); }
function clearSession() { localStorage.removeItem('csu_piat_session'); }

function getAttempts() { return parseInt(localStorage.getItem('csu_piat_attempts') || '0'); }
function setAttempts(n) { localStorage.setItem('csu_piat_attempts', String(n)); }
function resetAttempts() { localStorage.removeItem('csu_piat_attempts'); }

function isLocked() {
  const lockTime = localStorage.getItem('csu_piat_locktime');
  if (!lockTime) return false;
  const diff = (Date.now() - parseInt(lockTime)) / 1000;
  if (diff > 30) { localStorage.removeItem('csu_piat_locktime'); resetAttempts(); return false; }
  return true;
}

function login(username, password) {
  if (isLocked()) return { success: false, error: 'Account temporarily locked. Please try again in 30 seconds.' };
  const users = getUsers();
  const user = users.find(u => u.username === username && u.password === password);
  if (!user) {
    const attempts = getAttempts() + 1;
    setAttempts(attempts);
    if (attempts >= MAX_LOGIN_ATTEMPTS) {
      localStorage.setItem('csu_piat_locktime', String(Date.now()));
      return { success: false, error: `Too many failed attempts. Account locked for 30 seconds.` };
    }
    return { success: false, error: `Invalid username or password. ${MAX_LOGIN_ATTEMPTS - attempts} attempt(s) remaining.` };
  }
  if (user.status === 'inactive') return { success: false, error: 'Your account has been deactivated. Contact the administrator.' };
  resetAttempts();

  // Update last login
  const users2 = getUsers();
  const idx = users2.findIndex(u => u.id === user.id);
  if (idx !== -1) {
    users2[idx].lastLogin = new Date().toLocaleString('en-PH');
    localStorage.setItem('csu_piat_users', JSON.stringify(users2));
  }

  // Log activity
  addLog(user.id, 'Logged in successfully');
  setSession(user);
  return { success: true, user };
}

function logout() {
  const session = getSession();
  if (session) addLog(session.id, 'Logged out');
  clearSession();
  window.location.href = getBasePath() + 'index.html';
}

function requireAuth(allowedRoles) {
  const session = getSession();
  if (!session) { window.location.href = getBasePath() + 'index.html'; return null; }
  if (allowedRoles && !allowedRoles.includes(session.role)) {
    redirectByRole(session.role);
    return null;
  }
  return session;
}

function redirectByRole(role) {
  const base = getBasePath();
  if (role === 'superadmin') window.location.href = base + 'views/superadmin/dashboard.html';
  else if (role === 'admin') window.location.href = base + 'views/admin/dashboard.html';
  else window.location.href = base + 'views/users/dashboard.html';
}

function getBasePath() {
  const path = window.location.pathname;
  if (path.includes('/views/superadmin/')) return '../../';
  if (path.includes('/views/admin/')) return '../../';
  if (path.includes('/views/users/')) return '../../';
  return '';
}

function addLog(userId, activity) {
  const logs = JSON.parse(localStorage.getItem('csu_piat_account_logs')) || [];
  const now = new Date();
  logs.unshift({
    userId,
    date: now.toLocaleDateString('en-PH'),
    time: now.toLocaleTimeString('en-PH'),
    activity
  });
  localStorage.setItem('csu_piat_account_logs', JSON.stringify(logs.slice(0, 100)));
}

function getUserLogs(userId) {
  const logs = JSON.parse(localStorage.getItem('csu_piat_account_logs')) || [];
  return logs.filter(l => l.userId === userId);
}

function changePassword(userId, oldPassword, newPassword) {
  const users = getUsers();
  const idx = users.findIndex(u => u.id === userId);
  if (idx === -1) return { success: false, error: 'User not found.' };
  if (users[idx].password !== oldPassword) return { success: false, error: 'Current password is incorrect.' };
  users[idx].password = newPassword;
  localStorage.setItem('csu_piat_users', JSON.stringify(users));
  addLog(userId, 'Changed password');
  return { success: true };
}

function verifyUsername(username) {
  const users = getUsers();
  return users.find(u => u.username === username) || null;
}

function verifySecurityAnswer(username, answer) {
  const users = getUsers();
  const user = users.find(u => u.username === username);
  if (!user) return false;
  return user.securityAnswer.toLowerCase() === answer.trim().toLowerCase();
}

function resetPassword(username, newPassword) {
  const users = getUsers();
  const idx = users.findIndex(u => u.username === username);
  if (idx === -1) return false;
  users[idx].password = newPassword;
  localStorage.setItem('csu_piat_users', JSON.stringify(users));
  addLog(users[idx].id, 'Reset password via Forgot Password');
  return true;
}

function registerUser(data) {
  const users = getUsers();
  if (users.find(u => u.username === data.username)) return { success: false, error: 'Username already exists.' };
  const newUser = {
    id: Date.now(),
    username: data.username,
    password: data.password,
    role: data.role || 'user',
    name: data.name,
    position: data.position || '',
    department: data.department || '',
    email: data.email || '',
    gender: data.gender || '',
    status: 'active',
    avatar: data.name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase(),
    securityQuestion: data.securityQuestion || '',
    securityAnswer: data.securityAnswer || '',
    lastLogin: '-',
    createdAt: new Date().toLocaleDateString('en-PH')
  };
  users.push(newUser);
  localStorage.setItem('csu_piat_users', JSON.stringify(users));
  return { success: true, user: newUser };
}
