<?php require_once '../../components/under-construction.php'; ?>
<?php
require_once '../../config/session.php';
$user = requireAuth(['superadmin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Management | CSU-Piat</title>
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
    <h2><i class="fa-solid fa-users me-2 text-primary"></i>Account Management</h2>
    <p>Manage CSU-Piat faculty and staff accounts.</p>
  </div>

  <div class="table-wrapper">
    <div class="table-header">
      <h6><i class="fa-solid fa-list me-2"></i>All Accounts</h6>
      <div class="d-flex gap-2 flex-wrap">
        <div class="search-box">
          <i class="fa-solid fa-search search-icon"></i>
          <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search name, username...">
        </div>
        <select class="form-select form-select-sm" id="roleFilter" style="width:130px">
          <option value="">All Roles</option>
          <option value="admin">Admin</option>
          <option value="user">User</option>
        </select>
        <select class="form-select form-select-sm" id="statusFilter" style="width:130px">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr>
          <th>#</th><th>Name</th><th>Username</th><th>Department</th><th>Position</th><th>Role</th><th>Status</th><th>Last Login</th><th class="no-print">Actions</th>
        </tr></thead>
        <tbody id="accountsTable"></tbody>
      </table>
    </div>
    <div class="px-3 pb-3 d-flex justify-content-between align-items-center">
      <small class="text-muted" id="tableInfo"></small>
    </div>
  </div>
</main>

<!-- View Account Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-user me-2"></i>Account Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewModalBody"></div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-pen me-2"></i>Edit Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editUserId">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" id="editName">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control form-control-sm" id="editEmail">
          </div>
          <div class="col-md-6">
            <label class="form-label">Gender</label>
            <select class="form-select form-select-sm" id="editGender">
              <option value="">Select Gender</option>
              <option>Male</option>
              <option>Female</option>
              <option>Other</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Department / Office</label>
            <select class="form-select form-select-sm" id="editDepartment">
              <option value="">Select Department</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Position</label>
            <input type="text" class="form-control form-control-sm" id="editPosition" placeholder="e.g. Instructor I">
          </div>
          <div class="col-md-6">
            <label class="form-label">Role / Designation</label>
            <select class="form-select form-select-sm" id="editDesignation">
              <option value="">Select Role</option>
              <option>Dean</option>
              <option>Department Head</option>
              <option>Office Head</option>
              <option>Faculty</option>
              <option>Staff</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">System Role</label>
            <select class="form-select form-select-sm" id="editRole">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select form-select-sm" id="editStatus">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="saveEditBtn" onclick="saveEdit()">
          <i class="fa-solid fa-floppy-disk me-1"></i>Save Changes
        </button>
      </div>
    </div>
  </div>
</div>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  requireAuth(['superadmin']);
  initLayout('superadmin', 'accounts', [{ label: 'Account Management' }]);

  let allUsers = [];

  async function loadUsers() {
    const res = await fetch(API_BASE + 'users/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    allUsers = res?.users || [];
    renderTable();
  }

  function renderTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const roleF = document.getElementById('roleFilter').value;
    const statusF = document.getElementById('statusFilter').value;
    const filtered = allUsers.filter(u =>
      (!search || u.name.toLowerCase().includes(search) || u.username.toLowerCase().includes(search)) &&
      (!roleF || u.role === roleF) &&
      (!statusF || u.status === statusF)
    );
    const tbody = document.getElementById('accountsTable');
    tbody.innerHTML = '';
    if (filtered.length === 0) {
      tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-muted">No accounts found.</td></tr>`;
    } else {
      const roleLabel = { admin: '<span class="badge bg-primary">Admin</span>', user: '<span class="badge bg-secondary">User</span>' };
      filtered.forEach((u, i) => {
        const initials = (u.avatar || u.name.split(' ').map(n => n[0]).join('')).slice(0, 2).toUpperCase();
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${i+1}</td>
          <td><div class="d-flex align-items-center gap-2">
            <div class="avatar" style="width:32px;height:32px;font-size:0.7rem">${initials}</div>
            <div><div style="font-size:0.85rem;font-weight:600">${u.name}</div><div style="font-size:0.75rem;color:#888">${u.email || ''}</div></div></div></td>
          <td style="font-size:0.83rem">${u.username}</td>
          <td style="font-size:0.8rem">${u.department_name || '-'}</td>
          <td style="font-size:0.8rem">${u.position || '-'}</td>
          <td>${roleLabel[u.role] || u.role}</td>
          <td>${getStatusBadge(u.status)}</td>
          <td style="font-size:0.78rem">${u.last_login || '-'}</td>
          <td class="no-print"><div class="d-flex gap-1">
            <button class="btn btn-outline-primary btn-sm" onclick="viewAccount(${u.id})" title="View"><i class="fa-solid fa-eye"></i></button>
            <button class="btn btn-outline-warning btn-sm" onclick="openEditModal(${u.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
            <button class="btn btn-${u.status === 'active' ? 'outline-danger' : 'outline-success'} btn-sm" onclick="toggleStatus(${u.id},'${u.name}','${u.status}')" title="${u.status === 'active' ? 'Deactivate' : 'Activate'}">
              <i class="fa-solid fa-${u.status === 'active' ? 'ban' : 'check'}"></i>
            </button></div></td>`;
        tbody.appendChild(tr);
      });
    }
    document.getElementById('tableInfo').textContent = `Showing ${filtered.length} of ${allUsers.length} accounts`;
  }

  function viewAccount(id) {
    const u = allUsers.find(x => x.id === id);
    if (!u) return;
    const initials = (u.avatar || u.name.split(' ').map(n => n[0]).join('')).slice(0, 2).toUpperCase();
    document.getElementById('viewModalBody').innerHTML = `
      <div class="text-center mb-3">
        <div class="avatar mx-auto mb-2" style="width:64px;height:64px;font-size:1.2rem">${initials}</div>
        <h6 class="mb-0">${u.name}</h6><small class="text-muted">${[u.designation, u.position].filter(Boolean).join(' · ') || 'N/A'}</small>
      </div>
      <table class="table table-sm table-borderless" style="font-size:0.85rem">
        <tr><td class="text-muted fw-500" style="width:40%">Username</td><td>${u.username}</td></tr>
        <tr><td class="text-muted fw-500">Email</td><td>${u.email || '-'}</td></tr>
        <tr><td class="text-muted fw-500">Gender</td><td>${u.gender || '-'}</td></tr>
        <tr><td class="text-muted fw-500">Designation</td><td>${u.designation || '-'}</td></tr>
        <tr><td class="text-muted fw-500">Role</td><td>${u.role}</td></tr>
        <tr><td class="text-muted fw-500">Department</td><td>${u.department_name || '-'}</td></tr>
        <tr><td class="text-muted fw-500">Status</td><td>${getStatusBadge(u.status)}</td></tr>
        <tr><td class="text-muted fw-500">Last Login</td><td>${u.last_login || '-'}</td></tr>
        <tr><td class="text-muted fw-500">Date Registered</td><td>${u.created_at || '-'}</td></tr>
      </table>`;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
  }

  async function toggleStatus(id, name, currentStatus) {
    const action = currentStatus === 'active' ? 'deactivate' : 'activate';
    confirmModal(`Are you sure you want to ${action} <strong>${name}</strong>'s account?`, 'Confirm Action', async () => {
      const res = await fetch(API_BASE + 'users/toggle-status.php', {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: id })
      }).then(r => r.json()).catch(() => null);
      if (res?.success) {
        const u = allUsers.find(x => x.id === id);
        if (u) u.status = res.new_status;
        renderTable();
        showToast(res.message || 'Status updated.', 'success');
      } else showToast(res?.error || 'Failed to update status.', 'danger');
    });
  }

  let allDepts = [];

  async function loadDepartments() {
    if (allDepts.length) return;
    const res = await fetch(API_BASE + 'departments/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    allDepts = res?.departments || [];
    const sel = document.getElementById('editDepartment');
    allDepts.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.id; opt.textContent = d.name;
      sel.appendChild(opt);
    });
  }

  const adminDesignations = ['Dean', 'Department Head', 'Office Head'];

  function syncRoleFromDesignation() {
    const desig = document.getElementById('editDesignation').value;
    if (desig) {
      document.getElementById('editRole').value = adminDesignations.includes(desig) ? 'admin' : 'user';
    }
  }

  document.getElementById('editDesignation').addEventListener('change', syncRoleFromDesignation);

  function openEditModal(id) {
    const u = allUsers.find(x => x.id === id);
    if (!u) return;
    loadDepartments();
    document.getElementById('editUserId').value       = u.id;
    document.getElementById('editName').value         = u.name || '';
    document.getElementById('editEmail').value        = u.email || '';
    document.getElementById('editGender').value       = u.gender || '';
    document.getElementById('editDepartment').value   = u.department_id || '';
    document.getElementById('editPosition').value     = u.position || '';
    document.getElementById('editDesignation').value  = u.designation || '';
    syncRoleFromDesignation();
    document.getElementById('editRole').value         = u.role || 'user';
    document.getElementById('editStatus').value       = u.status || 'active';
    new bootstrap.Modal(document.getElementById('editModal')).show();
  }

  async function saveEdit() {
    const btn = document.getElementById('saveEditBtn');
    const name = document.getElementById('editName').value.trim();
    if (!name) { showToast('Name is required.', 'warning'); return; }
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    const res = await fetch(API_BASE + 'users/update.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id:          document.getElementById('editUserId').value,
        name,
        email:       document.getElementById('editEmail').value.trim(),
        gender:      document.getElementById('editGender').value,
        department:  document.getElementById('editDepartment').value,
        position:    document.getElementById('editPosition').value.trim(),
        designation: document.getElementById('editDesignation').value,
        role:        document.getElementById('editRole').value,
        status:      document.getElementById('editStatus').value,
      })
    }).then(r => r.json()).catch(() => null);
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Save Changes';
    if (res?.success) {
      bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
      showToast(res.message, 'success');
      await loadUsers();
    } else {
      showToast(res?.error || 'Failed to save changes.', 'danger');
    }
  }

  document.getElementById('searchInput').addEventListener('input', renderTable);
  document.getElementById('roleFilter').addEventListener('change', renderTable);
  document.getElementById('statusFilter').addEventListener('change', renderTable);
  loadUsers();
</script>
</body>
</html>
