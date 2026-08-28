<?php
require_once '../../config/session.php';
$user = requireAuth(['superadmin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings | CSU-Piat</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <script>
  window.SESSION_USER = <?= json_encode($user, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>;
  const API_BASE = '<?= BASE_URL ?>api/';
  </script>
  <style>
    .scope-badge-global     { background: #6366f1; color: #fff; }
    .scope-badge-department { background: #0ea5e9; color: #fff; }
    .scope-badge-user       { background: #f59e0b; color: #fff; }
    .kpi-assign-group       { display: none; }
    .kpi-assign-group.show  { display: block; }
    .kpi-filter-bar         { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .kpi-filter-bar select, .kpi-filter-bar input { font-size: 0.82rem; padding: 4px 8px; border-radius: 6px; border: 1px solid #dee2e6; }
  </style>
</head>
<body>
<div id="toast-container"></div>
<div id="sidebar-container"></div>
<div id="navbar-container"></div>

<main class="main-content" id="mainContent">
  <div class="page-header">
    <h2><i class="fa-solid fa-gear me-2 text-primary"></i>Settings</h2>
    <p>Manage system timelines, KPI categories, and configurations.</p>
  </div>

  <!-- Nav Tabs -->
  <ul class="nav nav-tabs mb-3" id="settingsTabs">
    <li class="nav-item"><a class="nav-link active" href="#timeline" data-bs-toggle="tab"><i class="fa-solid fa-calendar me-2"></i>Timeline Management</a></li>
    <li class="nav-item"><a class="nav-link" href="#kpi" data-bs-toggle="tab"><i class="fa-solid fa-bullseye me-2"></i>KPI Management</a></li>
  </ul>

  <div class="tab-content">
    <!-- Timeline Tab -->
    <div class="tab-pane fade show active" id="timeline">
      <div class="table-wrapper">
        <div class="table-header">
          <h6><i class="fa-solid fa-calendar-days me-2"></i>Academic Year Timelines</h6>
          <button class="btn btn-primary btn-sm" onclick="openTimelineModal()"><i class="fa-solid fa-plus me-1"></i>Add Timeline</button>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead><tr>
              <th>#</th><th>Academic Year</th><th>Covered Period</th><th>Start Date</th><th>End Date</th><th>Submission Deadline</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody id="timelineTable"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- KPI Tab -->
    <div class="tab-pane fade" id="kpi">
      <div class="table-wrapper">
        <div class="table-header">
          <h6><i class="fa-solid fa-bullseye me-2"></i>KPI / Performance Indicators</h6>
          <button class="btn btn-primary btn-sm" onclick="openKpiModal()"><i class="fa-solid fa-plus me-1"></i>Add KPI</button>
        </div>

        <!-- Filter Bar -->
        <div class="kpi-filter-bar mb-3 px-1">
          <select id="filterScope" onchange="applyFilters()">
            <option value="">All Scopes</option>
            <option value="global">Global</option>
            <option value="department">Department</option>
            <option value="user">User-Specific</option>
          </select>
          <select id="filterCategory" onchange="applyFilters()">
            <option value="">All Categories</option>
            <option value="core">Core Function</option>
            <option value="strategic">Strategic Function</option>
            <option value="support">Support Function</option>
          </select>
          <select id="filterAdmin" onchange="applyFilters()">
            <option value="">All Admins</option>
          </select>
          <input type="text" id="filterSearch" placeholder="Search MFO / Indicator..." oninput="applyFilters()" style="min-width:180px">
          <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()"><i class="fa-solid fa-xmark me-1"></i>Clear</button>
        </div>

        <!-- KPI table by category pills -->
        <ul class="nav nav-pills mb-3" id="kpiTabs">
          <li class="nav-item"><a class="nav-link active" href="#kpiAll"     data-bs-toggle="tab">All</a></li>
          <li class="nav-item"><a class="nav-link"        href="#kpiCore"    data-bs-toggle="tab">Core Function</a></li>
          <li class="nav-item"><a class="nav-link"        href="#kpiStrategic" data-bs-toggle="tab">Strategic Function</a></li>
          <li class="nav-item"><a class="nav-link"        href="#kpiSupport" data-bs-toggle="tab">Support Function</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="kpiAll"><div id="kpiAllTable"></div></div>
          <div class="tab-pane fade" id="kpiCore"><div id="kpiCoreTable"></div></div>
          <div class="tab-pane fade" id="kpiStrategic"><div id="kpiStrategicTable"></div></div>
          <div class="tab-pane fade" id="kpiSupport"><div id="kpiSupportTable"></div></div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Timeline Modal -->
<div class="modal fade" id="timelineModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="timelineModalTitle"><i class="fa-solid fa-calendar me-2"></i>Add Timeline</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="tlId">
        <div class="mb-3">
          <label class="form-label">Academic Year <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="tlYear" placeholder="e.g. 2026 or 2026-2027">
        </div>
        <div class="mb-3">
          <label class="form-label">Covered Period <span class="text-danger">*</span></label>
          <select class="form-select" id="tlSem">
            <option value="January to June">January to June</option>
            <option value="July to December">July to December</option>
          </select>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" id="tlStart">
          </div>
          <div class="col-6">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" id="tlEnd">
          </div>
        </div>
        <div class="mb-3 mt-2">
          <label class="form-label">Submission Deadline</label>
          <input type="date" class="form-control" id="tlDeadline">
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select class="form-select" id="tlStatus">
            <option value="open">Open</option>
            <option value="closed">Closed</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" onclick="saveTimeline()"><i class="fa-solid fa-save me-1"></i>Save</button>
      </div>
    </div>
  </div>
</div>

<!-- KPI Modal -->
<div class="modal fade" id="kpiModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="kpiModalTitle"><i class="fa-solid fa-bullseye me-2"></i>Add KPI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="kpiId">

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select class="form-select" id="kpiCategory">
              <option value="core">Core Function</option>
              <option value="strategic">Strategic Function</option>
              <option value="support">Support Function</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Scope <span class="text-danger">*</span></label>
            <select class="form-select" id="kpiScope" onchange="onScopeChange()">
              <option value="global">Global — All Admins (System-wide)</option>
              <option value="department">Department — All Faculty in a Dept</option>
              <option value="user">User-Specific — Assign to one Admin</option>
            </select>
          </div>
        </div>

        <!-- Department selector (shown when scope = department) -->
        <div class="mb-3 kpi-assign-group" id="deptGroup">
          <label class="form-label">Department <span class="text-danger">*</span></label>
          <select class="form-select" id="kpiDeptId">
            <option value="">— Select Department —</option>
          </select>
          <div class="form-text">This KPI will apply to all faculty in the selected department.</div>
        </div>

        <!-- Admin selector (shown when scope = user) -->
        <div class="mb-3 kpi-assign-group" id="adminGroup">
          <label class="form-label">Assign To (Admin / Dean / Head) <span class="text-danger">*</span></label>
          <select class="form-select" id="kpiAssignedTo">
            <option value="">— Select Admin —</option>
          </select>
          <div class="form-text">This KPI will be visible only to the selected Admin.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">MFO / PAP <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="kpiMfo" placeholder="e.g. Instruction, Research, Extension">
        </div>
        <div class="mb-3">
          <label class="form-label">Success Indicator <span class="text-danger">*</span></label>
          <textarea class="form-control" id="kpiIndicator" rows="2" placeholder="Describe the measurable success indicator..."></textarea>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Target</label>
            <input type="text" class="form-control" id="kpiTarget" placeholder="e.g. 100%">
          </div>
          <div class="col-6">
            <label class="form-label">Measure</label>
            <input type="text" class="form-control" id="kpiMeasure" placeholder="e.g. Quality / Timeliness">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" onclick="saveKpi()"><i class="fa-solid fa-save me-1"></i>Save KPI</button>
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
  initLayout('superadmin', 'settings', [{ label: 'Settings' }]);

  let allKpiRaw   = [];   // flat array of all KPI items
  let allKpiGrouped = {}; // grouped by category
  let allAdmins   = [];   // list of active admin users
  let allDepts    = [];   // list of all departments

  // ─── Scope badge helper ──────────────────────────────────────────────────
  function getScopeBadge(scope, assignedName) {
    if (scope === 'global')     return `<span class="badge scope-badge-global"><i class="fa-solid fa-globe me-1"></i>Global</span>`;
    if (scope === 'department') return `<span class="badge scope-badge-department"><i class="fa-solid fa-building me-1"></i>Dept</span>`;
    if (scope === 'user')       return `<span class="badge scope-badge-user"><i class="fa-solid fa-user me-1"></i>User</span>`;
    return `<span class="badge bg-secondary">—</span>`;
  }

  // ─── Load admins for dropdown ────────────────────────────────────────────
  async function loadAdmins() {
    const res = await fetch(API_BASE + 'users/list.php?role=admin&status=active', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    allAdmins = res?.users || [];
    // Populate filter dropdown
    const sel = document.getElementById('filterAdmin');
    allAdmins.forEach(a => {
      const opt = document.createElement('option');
      opt.value = a.id;
      opt.textContent = a.name + ' (' + (a.department_name || '—') + ')';
      sel.appendChild(opt);
    });
    // Populate modal dropdown
    const kpiSel = document.getElementById('kpiAssignedTo');
    allAdmins.forEach(a => {
      const opt = document.createElement('option');
      opt.value = a.id;
      opt.textContent = a.name + ' — ' + (a.department_name || '—');
      kpiSel.appendChild(opt);
    });
  }

  // ─── Load departments for dropdown ──────────────────────────────────────
  async function loadDepts() {
    const res = await fetch(API_BASE + 'departments/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    allDepts = res?.departments || [];
    const sel = document.getElementById('kpiDeptId');
    allDepts.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.id;
      opt.textContent = d.name;
      sel.appendChild(opt);
    });
  }

  // ─── Scope change handler ────────────────────────────────────────────────
  function onScopeChange() {
    const scope = document.getElementById('kpiScope').value;
    document.getElementById('deptGroup').classList.toggle('show', scope === 'department');
    document.getElementById('adminGroup').classList.toggle('show', scope === 'user');
  }

  // ─── Timeline functions ──────────────────────────────────────────────────
  async function loadTimelines() {
    const res = await fetch(API_BASE + 'timeline/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    const timelines = res?.timelines || [];
    const tbody = document.getElementById('timelineTable');
    tbody.innerHTML = '';
    if (timelines.length === 0) { tbody.innerHTML = `<tr><td colspan="8" class="text-center py-3 text-muted">No timelines found.</td></tr>`; return; }
    timelines.forEach((t, i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${i+1}</td><td>${t.academic_year}</td><td>${t.semester}</td>
        <td>${formatDate(t.start_date)}</td><td>${formatDate(t.end_date)}</td>
        <td>${formatDate(t.submission_deadline)}</td><td>${getStatusBadge(t.status)}</td>
        <td><button class="btn btn-outline-primary btn-sm" onclick='editTimeline(${JSON.stringify(t)})'><i class="fa-solid fa-edit"></i></button></td>`;
      tbody.appendChild(tr);
    });
  }

  function openTimelineModal(data = null) {
    document.getElementById('tlId').value = data?.id || '';
    document.getElementById('tlYear').value = data?.academic_year || '';
    document.getElementById('tlSem').value = data?.semester || 'January to June';
    document.getElementById('tlStart').value = data?.start_date || '';
    document.getElementById('tlEnd').value = data?.end_date || '';
    document.getElementById('tlDeadline').value = data?.submission_deadline || '';
    document.getElementById('tlStatus').value = data?.status || 'open';
    document.getElementById('timelineModalTitle').innerHTML = `<i class="fa-solid fa-calendar me-2"></i>${data ? 'Edit' : 'Add'} Timeline`;
    new bootstrap.Modal(document.getElementById('timelineModal')).show();
  }

  function editTimeline(t) { openTimelineModal(t); }

  async function saveTimeline() {
    const id   = document.getElementById('tlId').value;
    const year = document.getElementById('tlYear').value.trim();
    if (!year) { showToast('Academic Year is required.', 'warning'); return; }
    const res = await fetch(API_BASE + 'timeline/save.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id || undefined, academic_year: year, semester: document.getElementById('tlSem').value, start_date: document.getElementById('tlStart').value, end_date: document.getElementById('tlEnd').value, submission_deadline: document.getElementById('tlDeadline').value, status: document.getElementById('tlStatus').value })
    }).then(r => r.json()).catch(() => null);
    if (res?.success) {
      bootstrap.Modal.getInstance(document.getElementById('timelineModal')).hide();
      showToast('Timeline saved successfully!', 'success');
      loadTimelines();
    } else showToast(res?.error || 'Failed to save timeline.', 'danger');
  }

  // ─── KPI load & render ───────────────────────────────────────────────────
  async function loadKpis() {
    const res = await fetch(API_BASE + 'kpi/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    allKpiRaw    = res?.items   || [];
    allKpiGrouped = res?.grouped || {};
    applyFilters();
  }

  function getFilteredItems() {
    const scope    = document.getElementById('filterScope').value;
    const category = document.getElementById('filterCategory').value;
    const adminId  = document.getElementById('filterAdmin').value;
    const search   = document.getElementById('filterSearch').value.toLowerCase();

    return allKpiRaw.filter(item => {
      if (scope    && item.scope    !== scope)                        return false;
      if (category && item.category !== category)                     return false;
      if (adminId  && String(item.assigned_to) !== String(adminId))  return false;
      if (search   && !(item.mfo?.toLowerCase().includes(search) || item.success_indicator?.toLowerCase().includes(search))) return false;
      return true;
    });
  }

  function applyFilters() {
    const filtered = getFilteredItems();
    const grouped  = { core: [], strategic: [], support: [] };
    filtered.forEach(item => { grouped[item.category]?.push(item); });
    renderKpiTable(filtered,         'kpiAllTable',       'All');
    renderKpiTable(grouped.core,     'kpiCoreTable',      'Core');
    renderKpiTable(grouped.strategic,'kpiStrategicTable', 'Strategic');
    renderKpiTable(grouped.support,  'kpiSupportTable',   'Support');
  }

  function clearFilters() {
    document.getElementById('filterScope').value    = '';
    document.getElementById('filterCategory').value = '';
    document.getElementById('filterAdmin').value    = '';
    document.getElementById('filterSearch').value   = '';
    applyFilters();
  }

  function renderKpiTable(items, containerId, label) {
    const el = document.getElementById(containerId);
    if (!items || items.length === 0) {
      el.innerHTML = `<div class="empty-state"><i class="fa-solid fa-list"></i><p>No ${label} KPIs found.</p></div>`;
      return;
    }
    el.innerHTML = `
      <div class="table-responsive">
        <table class="table table-hover align-middle" style="font-size:0.83rem">
          <thead>
            <tr>
              <th style="width:36px">#</th>
              <th>Scope</th>
              <th>Assigned To</th>
              <th>MFO / PAP</th>
              <th>Success Indicator</th>
              <th>Target</th>
              <th>Measure</th>
              <th>Created By</th>
              <th style="width:80px">Actions</th>
            </tr>
          </thead>
          <tbody>
            ${items.map((item, i) => `
              <tr>
                <td>${i+1}</td>
                <td>${getScopeBadge(item.scope, item.assigned_to_name)}</td>
                <td>${item.assigned_to_name
                      ? `<span class="text-dark fw-semibold">${item.assigned_to_name}</span>`
                      : `<span class="text-muted fst-italic">All</span>`}</td>
                <td class="fw-semibold">${item.mfo || '—'}</td>
                <td style="max-width:220px;white-space:normal">${item.success_indicator}</td>
                <td>${item.target || '—'}</td>
                <td>${item.measure || '—'}</td>
                <td class="text-muted">${item.created_by_name || '—'}</td>
                <td>
                  <div class="d-flex gap-1">
                    <button class="btn btn-outline-primary btn-sm" title="Edit" onclick='editKpi(${item.id})'><i class="fa-solid fa-edit"></i></button>
                    <button class="btn btn-outline-danger btn-sm"  title="Delete" onclick='deleteKpi(${item.id})'><i class="fa-solid fa-trash"></i></button>
                  </div>
                </td>
              </tr>`).join('')}
          </tbody>
        </table>
      </div>`;
  }

  // ─── KPI Modal ───────────────────────────────────────────────────────────
  function openKpiModal(data = null) {
    document.getElementById('kpiId').value          = data?.id || '';
    document.getElementById('kpiCategory').value    = data?.category || 'core';
    document.getElementById('kpiScope').value       = data?.scope || 'global';
    document.getElementById('kpiAssignedTo').value  = data?.assigned_to || '';
    document.getElementById('kpiDeptId').value      = data?.department_id || '';
    document.getElementById('kpiMfo').value         = data?.mfo || '';
    document.getElementById('kpiIndicator').value   = data?.success_indicator || '';
    document.getElementById('kpiTarget').value      = data?.target || '';
    document.getElementById('kpiMeasure').value     = data?.measure || '';
    document.getElementById('kpiModalTitle').innerHTML = `<i class="fa-solid fa-bullseye me-2"></i>${data ? 'Edit' : 'Add'} KPI`;
    onScopeChange();
    new bootstrap.Modal(document.getElementById('kpiModal')).show();
  }

  async function editKpi(id) {
    const res = await fetch(API_BASE + 'kpi/get.php?id=' + id, { credentials: 'include' }).then(r => r.json()).catch(() => null);
    if (res?.success) openKpiModal(res.kpi);
    else showToast(res?.error || 'Could not load KPI.', 'danger');
  }

  function deleteKpi(id) {
    confirmModal('Are you sure you want to delete this KPI?', 'Delete KPI', async () => {
      const res = await fetch(API_BASE + 'kpi/delete.php', {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      }).then(r => r.json()).catch(() => null);
      if (res?.success) { showToast('KPI deleted.', 'success'); loadKpis(); }
      else showToast(res?.error || 'Failed to delete KPI.', 'danger');
    });
  }

  async function saveKpi() {
    const id        = document.getElementById('kpiId').value;
    const cat       = document.getElementById('kpiCategory').value;
    const scope     = document.getElementById('kpiScope').value;
    const mfo       = document.getElementById('kpiMfo').value.trim();
    const indicator = document.getElementById('kpiIndicator').value.trim();
    const assignedTo = parseInt(document.getElementById('kpiAssignedTo').value) || null;
    const deptId     = document.getElementById('kpiDeptId').value || null;

    if (!mfo || !indicator) { showToast('MFO and Success Indicator are required.', 'warning'); return; }
    if (scope === 'user' && !assignedTo) { showToast('Please select an Admin to assign this KPI to.', 'warning'); return; }
    if (scope === 'department' && !deptId) { showToast('Please select a Department.', 'warning'); return; }

    const payload = {
      id:               id || undefined,
      category:         cat,
      scope,
      mfo,
      success_indicator: indicator,
      target:           document.getElementById('kpiTarget').value,
      measure:          document.getElementById('kpiMeasure').value,
      assigned_to:      scope === 'user'       ? assignedTo : null,
      department_id:    scope === 'department' ? deptId     : null,
    };

    const res = await fetch(API_BASE + 'kpi/save.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(r => r.json()).catch(() => null);

    if (res?.success) {
      bootstrap.Modal.getInstance(document.getElementById('kpiModal')).hide();
      showToast('KPI saved successfully!', 'success');
      loadKpis();
    } else showToast(res?.error || 'Failed to save KPI.', 'danger');
  }

  // ─── Init ────────────────────────────────────────────────────────────────
  loadTimelines();
  loadAdmins();
  loadDepts();
  loadKpis();
</script>
</body>
</html>
