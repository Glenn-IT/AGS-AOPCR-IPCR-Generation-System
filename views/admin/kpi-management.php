<?php
require_once '../../config/session.php';
$user = requireAuth(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KPI Management | CSU-Piat</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <script>
  window.SESSION_USER = <?= json_encode($user, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>;
  const API_BASE = '<?= BASE_URL ?>api/';
  </script>
  <style>
    /* ── Scope badges ─────────────────────────────── */
    .scope-dept { background: #E85C0D; color: #fff; font-size: 0.72rem; }
    .scope-user { background: #f59e0b; color: #fff; font-size: 0.72rem; }
    .scope-global { background: #821131; color: #fff; font-size: 0.72rem; }

    /* ── Ownership tags ───────────────────────────── */
    .owner-mine  { background: #d1fae5; color: #065f46; font-size: 0.7rem; border-radius: 4px; padding: 1px 6px; }
    .owner-super { background: #ede9fe; color: #5b21b6; font-size: 0.7rem; border-radius: 4px; padding: 1px 6px; }

    /* ── Stat cards ───────────────────────────────── */
    .kpi-stat { border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 14px; }
    .kpi-stat .stat-num { font-size: 1.6rem; font-weight: 800; line-height: 1; }
    .kpi-stat .stat-lbl { font-size: 0.78rem; color: #64748b; margin-top: 2px; }

    /* ── Section header ───────────────────────────── */
    .section-pill {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 4px 14px; border-radius: 20px; font-size: 0.78rem;
      font-weight: 600; margin-bottom: 6px;
    }
    .pill-dept { background: #fff4e6; color: #9a3412; }
    .pill-user { background: #fef3c7; color: #92400e; }
    .pill-from-sa { background: #ede9fe; color: #6d28d9; }

    /* ── Filter bar ───────────────────────────────── */
    .filter-bar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .filter-bar select, .filter-bar input {
      font-size: 0.82rem; padding: 5px 10px;
      border-radius: 7px; border: 1px solid #dee2e6;
    }

    /* ── Assign toggle ────────────────────────────── */
    .assign-group { display: none; }
    .assign-group.show { display: block; }

    /* ── Read-only row highlight ──────────────────── */
    tr.from-superadmin td { background: #faf5ff !important; }
    tr.from-superadmin .action-col { opacity: 0.4; pointer-events: none; }
  </style>
</head>
<body>
<div id="toast-container"></div>
<div id="sidebar-container"></div>
<div id="navbar-container"></div>

<main class="main-content" id="mainContent">

  <!-- Page Header -->
  <div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
      <h2><i class="fa-solid fa-bullseye me-2 text-primary"></i>KPI Management</h2>
      <p id="deptLabel">Manage performance indicators for your department faculty.</p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openKpiModal()">
      <i class="fa-solid fa-plus me-1"></i>Add KPI
    </button>
  </div>


  <!-- KPI Table -->
  <div class="table-wrapper">
    <div class="table-header">
      <h6><i class="fa-solid fa-table-list me-2"></i>KPI Indicators</h6>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar mb-3 px-1">
      <select id="filterScope" onchange="applyFilters()">
        <option value="">All Scopes</option>
        <option value="department">Dept-wide</option>
        <option value="user">User-Specific</option>
        <option value="global">Global (Superadmin)</option>
      </select>
      <select id="filterCategory" onchange="applyFilters()">
        <option value="">All Categories</option>
        <option value="core">Core</option>
        <option value="strategic">Strategic</option>
        <option value="support">Support</option>
      </select>
      <select id="filterFaculty" onchange="applyFilters()">
        <option value="">All Faculty</option>
      </select>
      <select id="filterOwner" onchange="applyFilters()">
        <option value="">All Sources</option>
        <option value="mine">Created by Me</option>
        <option value="superadmin">From Superadmin</option>
      </select>
      <input type="text" id="filterSearch" placeholder="Search MFO / Indicator..." oninput="applyFilters()" style="min-width:170px">
      <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
        <i class="fa-solid fa-xmark me-1"></i>Clear
      </button>
    </div>

    <!-- Category Pills -->
    <ul class="nav nav-pills mb-3" id="kpiCatTabs">
      <li class="nav-item"><a class="nav-link active" href="#tabAll"       data-bs-toggle="tab">All</a></li>
      <li class="nav-item"><a class="nav-link"        href="#tabCore"      data-bs-toggle="tab"><i class="fa-solid fa-star me-1"></i>Core</a></li>
      <li class="nav-item"><a class="nav-link"        href="#tabStrategic" data-bs-toggle="tab"><i class="fa-solid fa-chess me-1"></i>Strategic</a></li>
      <li class="nav-item"><a class="nav-link"        href="#tabSupport"   data-bs-toggle="tab"><i class="fa-solid fa-hands-helping me-1"></i>Support</a></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="tabAll"><div id="tableAll"></div></div>
      <div class="tab-pane fade" id="tabCore"><div id="tableCore"></div></div>
      <div class="tab-pane fade" id="tabStrategic"><div id="tableStrategic"></div></div>
      <div class="tab-pane fade" id="tabSupport"><div id="tableSupport"></div></div>
    </div>
  </div>

</main>

<!-- ══════════════════════════════════════════
     KPI Add / Edit Modal
═══════════════════════════════════════════ -->
<div class="modal fade" id="kpiModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="kpiModalTitle">
          <i class="fa-solid fa-bullseye me-2"></i>Add KPI
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="kpiId">

        <div class="row g-3 mb-3">
          <!-- Category -->
          <div class="col-md-6">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select class="form-select" id="kpiCategory">
              <option value="core">Core Function</option>
              <option value="strategic">Strategic Function</option>
              <option value="support">Support Function</option>
            </select>
          </div>
          <!-- Scope -->
          <div class="col-md-6">
            <label class="form-label">Target Scope <span class="text-danger">*</span></label>
            <select class="form-select" id="kpiScope" onchange="onScopeChange()">
              <option value="department">Dept-wide — All Faculty in My Department</option>
              <option value="user">User-Specific — Assign to One Faculty Member</option>
            </select>
            <div class="form-text">As an Admin, you cannot create global KPIs.</div>
          </div>
        </div>

        <!-- Assign to Faculty (shown when scope = user) -->
        <div class="mb-3 assign-group" id="facultyGroup">
          <label class="form-label">
            <i class="fa-solid fa-user-tag me-1 text-warning"></i>
            Assign To (Faculty / Staff) <span class="text-danger">*</span>
          </label>
          <select class="form-select" id="kpiAssignedTo">
            <option value="">— Select Faculty Member —</option>
          </select>
          <div class="form-text">This KPI will only appear in the selected faculty member's IPCR form.</div>
        </div>

        <!-- Batch Quantity Controls (Visible when adding new KPIs) -->
        <div id="batchKpiControls" class="card border-primary-subtle bg-light mb-3">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <label class="form-label fw-bold mb-0 text-primary">
                  <i class="fa-solid fa-layer-group me-1"></i>Number of Functions to Add:
                </label>
                <div class="text-muted" style="font-size:0.78rem">
                  Select or type the count (e.g. 5 Core Functions) to generate entry fields below.
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" class="btn btn-outline-primary count-btn" onclick="setKpiRowCount(1)">1</button>
                  <button type="button" class="btn btn-outline-primary count-btn" onclick="setKpiRowCount(3)">3</button>
                  <button type="button" class="btn btn-outline-primary count-btn active" onclick="setKpiRowCount(5)">5</button>
                  <button type="button" class="btn btn-outline-primary count-btn" onclick="setKpiRowCount(10)">10</button>
                </div>
                <div class="input-group input-group-sm" style="width: 110px;">
                  <input type="number" id="kpiRowCountInput" class="form-control text-center" min="1" max="25" value="5" onchange="setKpiRowCount(this.value)">
                  <button type="button" class="btn btn-primary" onclick="setKpiRowCount(document.getElementById('kpiRowCountInput').value)">Set</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dynamic Function Rows Container -->
        <div id="kpiRowsContainer" class="mb-3" style="max-height: 480px; overflow-y: auto; padding-right: 4px;">
          <!-- Generated function cards go here -->
        </div>

        <div id="addMoreKpiRowContainer" class="d-flex justify-content-between align-items-center mb-2 px-1">
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="addKpiRow()">
            <i class="fa-solid fa-plus me-1"></i>Add Another Function
          </button>
          <span class="text-muted small" id="kpiCountSummary"><strong>5</strong> functions to be added</span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="btnSaveKpi" onclick="saveKpi()">
          <i class="fa-solid fa-save me-1"></i><span id="saveKpiBtnText">Save KPIs</span>
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
  const session = requireAuth(['admin']);
  initLayout('admin', 'kpi-management', [{ label: 'KPI Management' }]);

  document.getElementById('deptLabel').textContent =
    `Manage performance indicators for your department faculty — ${session.name}.`;

  let allKpiRaw  = [];
  let allFaculty = [];

  // ── Helpers ──────────────────────────────────────────────────────────────
  function getScopeBadge(scope) {
    if (scope === 'department') return `<span class="badge scope-dept"><i class="fa-solid fa-building me-1"></i>Dept-wide</span>`;
    if (scope === 'user')       return `<span class="badge scope-user"><i class="fa-solid fa-user me-1"></i>User-Specific</span>`;
    if (scope === 'global')     return `<span class="badge scope-global"><i class="fa-solid fa-globe me-1"></i>Global</span>`;
    return `<span class="badge bg-secondary">—</span>`;
  }

  function getOwnerTag(item) {
    if (item.created_by == session.id) return `<span class="owner-mine"><i class="fa-solid fa-pen me-1"></i>Me</span>`;
    return `<span class="owner-super"><i class="fa-solid fa-shield-halved me-1"></i>Superadmin</span>`;
  }

  // ── Load faculty in this department ──────────────────────────────────────
  async function loadFaculty() {
    const res = await fetch(API_BASE + 'users/list.php?role=user&status=active', { credentials: 'include' })
      .then(r => r.json()).catch(() => null);
    // Filter to admin's own department
    allFaculty = (res?.users || []).filter(u => u.department_id === session.department_id);

    const filterSel = document.getElementById('filterFaculty');
    const modalSel  = document.getElementById('kpiAssignedTo');

    allFaculty.forEach(u => {
      [filterSel, modalSel].forEach(sel => {
        const opt = document.createElement('option');
        opt.value = u.id;
        opt.textContent = u.name + (u.position ? ` (${u.position})` : '');
        sel.appendChild(opt);
      });
    });
  }

  // ── Scope toggle ─────────────────────────────────────────────────────────
  function onScopeChange() {
    const scope = document.getElementById('kpiScope').value;
    document.getElementById('facultyGroup').classList.toggle('show', scope === 'user');
  }

  // ── Load KPIs ─────────────────────────────────────────────────────────────
  async function loadKpis() {
    const res = await fetch(API_BASE + 'kpi/list.php', { credentials: 'include' })
      .then(r => r.json()).catch(() => null);
    allKpiRaw = res?.items || [];
    applyFilters();
  }

  // ── Filters ───────────────────────────────────────────────────────────────
  function getFilteredItems() {
    const scope    = document.getElementById('filterScope').value;
    const category = document.getElementById('filterCategory').value;
    const faculty  = document.getElementById('filterFaculty').value;
    const owner    = document.getElementById('filterOwner').value;
    const search   = document.getElementById('filterSearch').value.toLowerCase();

    return allKpiRaw.filter(item => {
      if (scope    && item.scope    !== scope)                          return false;
      if (category && item.category !== category)                       return false;
      if (faculty  && String(item.assigned_to) !== String(faculty))     return false;
      if (owner === 'mine'       && item.created_by != session.id)      return false;
      if (owner === 'superadmin' && item.created_by == session.id)      return false;
      if (search   && !(
        item.mfo?.toLowerCase().includes(search) ||
        item.success_indicator?.toLowerCase().includes(search)
      )) return false;
      return true;
    });
  }

  function applyFilters() {
    const filtered = getFilteredItems();
    const grouped  = { core: [], strategic: [], support: [] };
    filtered.forEach(item => { grouped[item.category]?.push(item); });
    renderTable(filtered,          'tableAll');
    renderTable(grouped.core,      'tableCore');
    renderTable(grouped.strategic, 'tableStrategic');
    renderTable(grouped.support,   'tableSupport');
  }

  function clearFilters() {
    ['filterScope','filterCategory','filterFaculty','filterOwner'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('filterSearch').value = '';
    applyFilters();
  }

  // ── Render KPI table ──────────────────────────────────────────────────────
  function renderTable(items, containerId) {
    const el = document.getElementById(containerId);
    if (!items || items.length === 0) {
      el.innerHTML = `<div class="empty-state"><i class="fa-solid fa-list"></i><p>No KPIs found.</p></div>`;
      return;
    }

    el.innerHTML = `
      <div class="table-responsive">
        <table class="table table-hover align-middle" style="font-size:0.83rem">
          <thead>
            <tr>
              <th style="width:36px">#</th>
              <th>Category</th>
              <th>Scope</th>
              <th>Assigned To</th>
              <th>MFO / PAP</th>
              <th>Success Indicator</th>
              <th>Target</th>
              <th>Source</th>
              <th style="width:80px">Actions</th>
            </tr>
          </thead>
          <tbody>
            ${items.map((item, i) => {
              const isOwnedByMe = item.created_by == session.id;
              const rowClass    = isOwnedByMe ? '' : 'from-superadmin';
              const catLabel    = { core: 'Core', strategic: 'Strategic', support: 'Support' }[item.category] || item.category;
              return `
              <tr class="${rowClass}">
                <td>${i + 1}</td>
                <td><span class="badge bg-secondary" style="font-size:0.7rem">${catLabel}</span></td>
                <td>${getScopeBadge(item.scope)}</td>
                <td>${item.assigned_to_name
                  ? `<strong>${item.assigned_to_name}</strong>`
                  : `<span class="text-muted fst-italic">All Dept.</span>`}</td>
                <td class="fw-semibold">${item.mfo || '—'}</td>
                <td style="max-width:200px;white-space:normal">${item.success_indicator || '—'}</td>
                <td>${item.target || '—'}</td>
                <td>${getOwnerTag(item)}</td>
                <td class="action-col">
                  <div class="d-flex gap-1">
                    ${isOwnedByMe ? `
                      <button class="btn btn-outline-primary btn-sm" title="Edit" onclick="editKpi(${item.id})">
                        <i class="fa-solid fa-edit"></i>
                      </button>
                      <button class="btn btn-outline-danger btn-sm" title="Delete" onclick="deleteKpi(${item.id})">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    ` : `<span class="text-muted" title="Superadmin-created KPI — read only"><i class="fa-solid fa-lock"></i></span>`}
                  </div>
                </td>
              </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>`;
  }

  // ── KPI Dynamic Row Helpers ───────────────────────────────────────────────
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function createKpiRowElement(index = 1, data = {}) {
    const div = document.createElement('div');
    div.className = 'kpi-row-item card mb-2 border shadow-sm';
    div.innerHTML = `
      <div class="card-header py-1 px-3 bg-light d-flex justify-content-between align-items-center">
        <span class="fw-semibold small text-primary row-num-label">
          <i class="fa-solid fa-clipboard-check me-1"></i>Function #${index}
        </span>
        <button type="button" class="btn btn-link text-danger p-0 delete-row-btn" onclick="removeKpiRow(this)" title="Remove this function" style="text-decoration:none;font-size:0.85rem">
          <i class="fa-solid fa-trash-can"></i>
        </button>
      </div>
      <div class="card-body p-3">
        <div class="row g-2 mb-2">
          <div class="col-md-8">
            <label class="form-label small fw-semibold mb-1">MFO / PAP <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm row-mfo" value="${escapeHtml(data.mfo || '')}" placeholder="e.g. Higher Education Services / Instruction">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold mb-1">Target</label>
            <input type="text" class="form-control form-control-sm row-target" value="${escapeHtml(data.target || '')}" placeholder="e.g. 100%, 5 outputs">
          </div>
        </div>
        <div>
          <label class="form-label small fw-semibold mb-1">Success Indicator <span class="text-danger">*</span></label>
          <textarea class="form-control form-control-sm row-indicator" rows="2" placeholder="Describe the measurable success indicator for this function...">${escapeHtml(data.success_indicator || '')}</textarea>
        </div>
      </div>
    `;
    return div;
  }

  function updateRowNumbers() {
    const rows = document.querySelectorAll('#kpiRowsContainer .kpi-row-item');
    const isEdit = !!document.getElementById('kpiId').value;
    rows.forEach((row, idx) => {
      const lbl = row.querySelector('.row-num-label');
      if (lbl) lbl.innerHTML = `<i class="fa-solid fa-clipboard-check me-1"></i>Function #${idx + 1}`;
      const delBtn = row.querySelector('.delete-row-btn');
      if (delBtn) delBtn.style.display = (rows.length > 1 && !isEdit) ? 'inline-block' : 'none';
    });
    
    const count = rows.length;
    const summary = document.getElementById('kpiCountSummary');
    if (summary) summary.innerHTML = `<strong>${count}</strong> function${count === 1 ? '' : 's'} to be added`;
    
    const btnText = document.getElementById('saveKpiBtnText');
    if (btnText) {
      btnText.textContent = isEdit ? 'Save KPI' : `Save ${count > 1 ? count + ' KPIs' : 'KPI'}`;
    }

    // Update count button active states
    document.querySelectorAll('.count-btn').forEach(btn => {
      btn.classList.toggle('active', parseInt(btn.textContent) === count);
    });
    const inp = document.getElementById('kpiRowCountInput');
    if (inp && parseInt(inp.value) !== count) inp.value = count;
  }

  function setKpiRowCount(count) {
    const n = Math.max(1, Math.min(25, parseInt(count) || 1));
    const container = document.getElementById('kpiRowsContainer');
    const existingRows = container.querySelectorAll('.kpi-row-item');
    const currentCount = existingRows.length;

    if (n > currentCount) {
      for (let i = currentCount; i < n; i++) {
        container.appendChild(createKpiRowElement(i + 1));
      }
    } else if (n < currentCount) {
      for (let i = currentCount - 1; i >= n; i--) {
        existingRows[i].remove();
      }
    }
    updateRowNumbers();
  }

  function addKpiRow(data = {}) {
    const container = document.getElementById('kpiRowsContainer');
    const count = container.querySelectorAll('.kpi-row-item').length;
    container.appendChild(createKpiRowElement(count + 1, data));
    updateRowNumbers();
  }

  function removeKpiRow(btn) {
    const container = document.getElementById('kpiRowsContainer');
    const rows = container.querySelectorAll('.kpi-row-item');
    if (rows.length <= 1) {
      showToast('At least one function row is required.', 'warning');
      return;
    }
    const card = btn.closest('.kpi-row-item');
    if (card) card.remove();
    updateRowNumbers();
  }

  // ── KPI Modal ─────────────────────────────────────────────────────────────
  function openKpiModal(data = null) {
    const isEdit = !!data;
    document.getElementById('kpiId').value         = data?.id || '';
    document.getElementById('kpiCategory').value   = data?.category || 'core';
    document.getElementById('kpiScope').value      = data?.scope === 'global' ? 'department' : (data?.scope || 'department');
    document.getElementById('kpiAssignedTo').value = data?.assigned_to || '';

    const batchControls   = document.getElementById('batchKpiControls');
    const addMoreContainer = document.getElementById('addMoreKpiRowContainer');
    const container        = document.getElementById('kpiRowsContainer');
    container.innerHTML    = '';

    if (isEdit) {
      document.getElementById('kpiModalTitle').innerHTML =
        `<i class="fa-solid fa-pen-to-square me-2"></i>Edit KPI`;
      if (batchControls) batchControls.style.display = 'none';
      if (addMoreContainer) addMoreContainer.style.display = 'none';
      container.appendChild(createKpiRowElement(1, data));
    } else {
      document.getElementById('kpiModalTitle').innerHTML =
        `<i class="fa-solid fa-bullseye me-2"></i>Add KPIs`;
      if (batchControls) batchControls.style.display = 'block';
      if (addMoreContainer) addMoreContainer.style.display = 'flex';
      // Default to 5 functions ready for input as requested by user
      setKpiRowCount(5);
    }

    onScopeChange();
    updateRowNumbers();
    new bootstrap.Modal(document.getElementById('kpiModal')).show();
  }

  async function editKpi(id) {
    const res = await fetch(API_BASE + 'kpi/get.php?id=' + id, { credentials: 'include' })
      .then(r => r.json()).catch(() => null);
    if (res?.success) {
      if (res.kpi.created_by != session.id) {
        showToast('You can only edit KPIs you created.', 'warning');
        return;
      }
      openKpiModal(res.kpi);
    } else showToast(res?.error || 'Could not load KPI.', 'danger');
  }

  function deleteKpi(id) {
    confirmModal('Delete this KPI? This cannot be undone.', 'Delete KPI', async () => {
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
    const id         = document.getElementById('kpiId').value;
    const category   = document.getElementById('kpiCategory').value;
    const scope      = document.getElementById('kpiScope').value;
    const assignedTo = parseInt(document.getElementById('kpiAssignedTo').value) || null;

    if (scope === 'user' && !assignedTo) {
      showToast('Please select a faculty member to assign this KPI to.', 'warning');
      return;
    }

    const rows = document.querySelectorAll('#kpiRowsContainer .kpi-row-item');
    if (rows.length === 0) {
      showToast('Please add at least one function.', 'warning');
      return;
    }

    // Single item edit mode
    if (id) {
      const row = rows[0];
      const mfo = row.querySelector('.row-mfo').value.trim();
      const indicator = row.querySelector('.row-indicator').value.trim();
      const target = row.querySelector('.row-target').value.trim();

      if (!mfo || !indicator) {
        showToast('MFO / PAP and Success Indicator are required.', 'warning');
        return;
      }

      const payload = {
        id:                parseInt(id),
        category,
        scope,
        mfo,
        success_indicator: indicator,
        target,
        measure:           '',
        assigned_to:       scope === 'user' ? assignedTo : null,
      };

      const res = await fetch(API_BASE + 'kpi/save.php', {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      }).then(r => r.json()).catch(() => null);

      if (res?.success) {
        bootstrap.Modal.getInstance(document.getElementById('kpiModal')).hide();
        showToast('KPI updated successfully!', 'success');
        loadKpis();
      } else showToast(res?.error || 'Failed to save KPI.', 'danger');
      return;
    }

    // Batch add mode
    const items = [];
    let hasPartiallyFilled = false;
    let partialRowIndex = -1;

    rows.forEach((row, idx) => {
      const mfo = row.querySelector('.row-mfo').value.trim();
      const indicator = row.querySelector('.row-indicator').value.trim();
      const target = row.querySelector('.row-target').value.trim();

      // Skip completely blank rows
      if (mfo === '' && indicator === '' && target === '') {
        return;
      }

      if (!mfo || !indicator) {
        hasPartiallyFilled = true;
        if (partialRowIndex === -1) partialRowIndex = idx + 1;
        return;
      }

      items.push({
        category,
        mfo,
        success_indicator: indicator,
        target,
        measure: ''
      });
    });

    if (hasPartiallyFilled) {
      showToast(`Function #${partialRowIndex}: Both MFO / PAP and Success Indicator are required.`, 'warning');
      return;
    }

    if (items.length === 0) {
      showToast('Please enter MFO / PAP and Success Indicator for at least one function.', 'warning');
      return;
    }

    const payload = {
      category,
      scope,
      assigned_to: scope === 'user' ? assignedTo : null,
      items
    };

    const res = await fetch(API_BASE + 'kpi/save.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(r => r.json()).catch(() => null);

    if (res?.success) {
      bootstrap.Modal.getInstance(document.getElementById('kpiModal')).hide();
      const msg = (res.count > 1) ? `${res.count} KPIs created successfully!` : 'KPI created successfully!';
      showToast(msg, 'success');
      loadKpis();
    } else showToast(res?.error || 'Failed to save KPIs.', 'danger');
  }

  // ── Init ──────────────────────────────────────────────────────────────────
  loadFaculty();
  loadKpis();
</script>
</body>
</html>
