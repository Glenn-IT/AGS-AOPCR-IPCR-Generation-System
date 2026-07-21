<?php
require_once '../../config/session.php';
$user = requireAuth(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile | CSU-Piat</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <script>
  window.SESSION_USER = <?= json_encode($user, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>;
  const API_BASE = '<?= BASE_URL ?>api/';
  </script>
</head>
<body>
<div id="toast-container"></div>
<div id="sidebar-container"></div>
<div id="navbar-container"></div>

<main class="main-content" id="mainContent">
  <div class="page-header">
    <h2><i class="fa-solid fa-user-cog me-2 text-primary"></i>My Profile</h2>
    <p>Manage your profile, change password, and view activity logs.</p>
  </div>

  <ul class="nav nav-tabs mb-3" id="accountTabs">
    <li class="nav-item"><a class="nav-link active" href="#profile" data-bs-toggle="tab"><i class="fa-solid fa-user me-2"></i>My Profile</a></li>
    <li class="nav-item"><a class="nav-link" href="#changepw" data-bs-toggle="tab"><i class="fa-solid fa-lock me-2"></i>Change Password</a></li>
    <li class="nav-item"><a class="nav-link" href="#securityq" data-bs-toggle="tab"><i class="fa-solid fa-shield-halved me-2"></i>Security Question</a></li>
    <li class="nav-item"><a class="nav-link" href="#logs" data-bs-toggle="tab"><i class="fa-solid fa-list-check me-2"></i>Activity Logs</a></li>
  </ul>

  <div class="tab-content">
    <!-- Profile Tab -->
    <div class="tab-pane fade show active" id="profile">
      <div class="row g-3">
        <div class="col-lg-4">
          <div class="card text-center">
            <div class="card-body p-4">
              <div class="avatar mx-auto mb-3" style="width:80px;height:80px;font-size:1.5rem" id="profileAvatar"></div>
              <h5 class="fw-700" id="profileName"></h5>
              <p class="text-muted mb-1" style="font-size:0.85rem" id="profilePosition"></p>
              <span class="badge bg-warning text-dark" id="profileRole"></span>
              <hr>
              <div class="text-start" style="font-size:0.85rem">
                <div class="mb-2"><i class="fa-solid fa-building me-2 text-primary"></i><span id="profileDept"></span></div>
                <div class="mb-2"><i class="fa-solid fa-envelope me-2 text-primary"></i><span id="profileEmail"></span></div>
                <div><i class="fa-solid fa-clock me-2 text-primary"></i><small class="text-muted">Last login: </small><span id="profileLastLogin"></span></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header"><h6>Edit Profile Information</h6></div>
            <div class="card-body">
              <form id="profileForm">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="editName">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control bg-light" id="editUsername" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="editEmail">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <select class="form-select" id="editGender">
                      <option value="">Select</option>
                      <option>Male</option>
                      <option>Female</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Position</label>
                    <input type="text" class="form-control" id="editPosition">
                  </div>
                </div>
                <div class="mt-3">
                  <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-save me-1"></i>Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Change Password Tab -->
    <div class="tab-pane fade" id="changepw">
      <div class="card" style="max-width:480px">
        <div class="card-header"><h6><i class="fa-solid fa-key me-2"></i>Change Password</h6></div>
        <div class="card-body">
          <form id="changePwForm" novalidate>
            <div class="mb-3">
              <label class="form-label">Current Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" id="currentPw" placeholder="Enter current password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePw('currentPw', this)"><i class="fa-solid fa-eye"></i></button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">New Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" id="newPw" placeholder="Min. 6 characters">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePw('newPw', this)"><i class="fa-solid fa-eye"></i></button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" id="confirmPw" placeholder="Re-enter new password">
              </div>
            </div>
            <div id="pwStrength" class="mb-3 d-none">
              <div class="progress mb-1" style="height:5px"><div class="progress-bar" id="pwBar"></div></div>
              <small id="pwLabel" class="text-muted"></small>
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-save me-1"></i>Update Password</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Security Question Tab -->
    <div class="tab-pane fade" id="securityq">
      <div class="card" style="max-width:480px">
        <div class="card-header"><h6><i class="fa-solid fa-shield-halved me-2"></i>Update Security Question</h6></div>
        <div class="card-body">
          <div class="alert alert-info py-2 mb-3" style="font-size:0.85rem"><i class="fa-solid fa-circle-info me-1"></i>Your security question is used to recover your account if you forget your password.</div>
          <div class="mb-3">
            <label class="form-label">Current Security Question</label>
            <input type="text" class="form-control bg-light" id="currentSecurityQ" readonly placeholder="Loading...">
          </div>
          <form id="securityQForm" novalidate>
            <div class="mb-3">
              <label class="form-label">Current Answer <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="sqCurrentAnswer" placeholder="Enter your current answer">
            </div>
            <hr>
            <div class="mb-3">
              <label class="form-label">New Security Question <span class="text-danger">*</span></label>
              <select class="form-select" id="sqNewQuestion">
                <option value="">-- Select a question --</option>
                <option>What is your mother's maiden name?</option>
                <option>What city were you born in?</option>
                <option>What is your pet's name?</option>
                <option>What was the name of your first school?</option>
                <option>What is your favorite book?</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">New Answer <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="sqNewAnswer" placeholder="Enter new answer">
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm New Answer <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="sqConfirmAnswer" placeholder="Re-enter new answer">
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-save me-1"></i>Update Security Question</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Activity Logs Tab -->
    <div class="tab-pane fade" id="logs">
      <div class="table-wrapper">
        <div class="table-header">
          <h6><i class="fa-solid fa-clock-rotate-left me-2"></i>Activity Logs</h6>
          <small class="text-muted" id="logsCount"></small>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>#</th><th>Date</th><th>Time</th><th>Activity</th></tr></thead>
            <tbody id="logsTable"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  const session = requireAuth(['admin']);
  initLayout('admin', 'account', [{ label: 'My Profile' }]);

  const roleLabels = { superadmin: 'Super Administrator', admin: 'Administrator', user: 'Faculty / Staff' };
  document.getElementById('profileAvatar').textContent = session.avatar || '?';
  document.getElementById('profileName').textContent = session.name;
  document.getElementById('profilePosition').textContent = session.position || '-';
  document.getElementById('profileRole').textContent = roleLabels[session.role] || session.role;
  document.getElementById('profileDept').textContent = session.department || '-';
  document.getElementById('profileEmail').textContent = session.email || '-';
  document.getElementById('profileLastLogin').textContent = session.lastLogin || '-';
  document.getElementById('editName').value = session.name;
  document.getElementById('editUsername').value = session.username;
  document.getElementById('editEmail').value = session.email || '';
  document.getElementById('editGender').value = session.gender || '';
  document.getElementById('editPosition').value = session.position || '';

  document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const res = await fetch(API_BASE + 'user/update-profile.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: document.getElementById('editName').value.trim(), email: document.getElementById('editEmail').value.trim(), gender: document.getElementById('editGender').value, position: document.getElementById('editPosition').value.trim() })
    }).then(r => r.json()).catch(() => null);
    if (res?.success) { showToast('Profile updated successfully!', 'success'); setTimeout(() => location.reload(), 1000); }
    else showToast(res?.error || 'Failed to update profile.', 'danger');
  });

  document.getElementById('newPw').addEventListener('input', function() {
    const val = this.value;
    const bar = document.getElementById('pwBar'); const label = document.getElementById('pwLabel'); const strength = document.getElementById('pwStrength');
    if (!val) { strength.classList.add('d-none'); return; }
    strength.classList.remove('d-none');
    let score = 0;
    if (val.length >= 6) score++; if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++; if (/[0-9]/.test(val)) score++; if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['', 'bg-danger', 'bg-danger', 'bg-warning', 'bg-success', 'bg-success'];
    const labels = ['', 'Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
    bar.style.width = (score * 20) + '%'; bar.className = 'progress-bar ' + (colors[score] || ''); label.textContent = labels[score] || '';
  });

  document.getElementById('changePwForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const cur = document.getElementById('currentPw').value; const np = document.getElementById('newPw').value; const cp = document.getElementById('confirmPw').value;
    if (!cur || !np || !cp) { showToast('Please fill in all fields.', 'warning'); return; }
    if (np.length < 6) { showToast('New password must be at least 6 characters.', 'warning'); return; }
    if (np !== cp) { showToast('Passwords do not match.', 'danger'); return; }
    const result = await changePassword(session.id, cur, np);
    if (result.success) { showToast('Password changed! Please log in again.', 'success'); setTimeout(() => logout(), 1500); }
    else showToast(result.error || 'Failed to change password.', 'danger');
  });

  async function loadLogs() {
    const res = await fetch(API_BASE + 'user/logs.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    const logs = res?.logs || [];
    const logsTable = document.getElementById('logsTable');
    document.getElementById('logsCount').textContent = logs.length + ' records';
    logsTable.innerHTML = logs.length === 0 ? '<tr><td colspan="4" class="text-center py-3 text-muted">No activity logs found.</td></tr>' :
      logs.map((log, i) => `<tr><td>${i+1}</td><td style="font-size:0.83rem">${log.date}</td><td style="font-size:0.83rem">${log.time}</td><td style="font-size:0.83rem">${log.activity}</td></tr>`).join('');
  }
  document.querySelector('a[href="#logs"]').addEventListener('click', loadLogs, { once: true });

  document.querySelector('a[href="#securityq"]').addEventListener('click', async function() {
    const res = await fetch(API_BASE + 'user/get-security-question.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    document.getElementById('currentSecurityQ').value = res?.security_question || 'Not set';
  }, { once: true });

  document.getElementById('securityQForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const curAns  = document.getElementById('sqCurrentAnswer').value.trim();
    const newQ    = document.getElementById('sqNewQuestion').value;
    const newAns  = document.getElementById('sqNewAnswer').value.trim();
    const confAns = document.getElementById('sqConfirmAnswer').value.trim();
    if (!curAns || !newQ || !newAns || !confAns) { showToast('Please fill in all fields.', 'warning'); return; }
    if (newAns !== confAns) { showToast('New answers do not match.', 'danger'); return; }
    const res = await fetch(API_BASE + 'user/update-security-question.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ current_answer: curAns, security_question: newQ, new_answer: newAns, confirm_answer: confAns })
    }).then(r => r.json()).catch(() => null);
    if (res?.success) {
      showToast('Security question updated successfully!', 'success');
      document.getElementById('currentSecurityQ').value = newQ;
      this.reset();
    } else showToast(res?.error || 'Failed to update security question.', 'danger');
  });

  function togglePw(id, btn) {
    const input = document.getElementById(id); const icon = btn.querySelector('i');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash');
  }
</script>
</body>
</html>
