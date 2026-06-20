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
              <th>#</th><th>Academic Year</th><th>Semester</th><th>Start Date</th><th>End Date</th><th>Submission Deadline</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody id="timelineTable"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- KPI Tab -->
    <div class="tab-pane fade" id="kpi">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">KPI / Performance Indicators</h6>
        <button class="btn btn-primary btn-sm" onclick="openKpiModal()"><i class="fa-solid fa-plus me-1"></i>Add KPI</button>
      </div>
      <ul class="nav nav-pills mb-3" id="kpiTabs">
        <li class="nav-item"><a class="nav-link active" href="#kpiCore" data-bs-toggle="tab">Core Function</a></li>
        <li class="nav-item"><a class="nav-link" href="#kpiStrategic" data-bs-toggle="tab">Strategic Function</a></li>
        <li class="nav-item"><a class="nav-link" href="#kpiSupport" data-bs-toggle="tab">Support Function</a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="kpiCore"><div class="table-wrapper" id="kpiCoreTable"></div></div>
        <div class="tab-pane fade" id="kpiStrategic"><div class="table-wrapper" id="kpiStrategicTable"></div></div>
        <div class="tab-pane fade" id="kpiSupport"><div class="table-wrapper" id="kpiSupportTable"></div></div>
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
          <input type="text" class="form-control" id="tlYear" placeholder="e.g. 2026-2027">
        </div>
        <div class="mb-3">
          <label class="form-label">Semester <span class="text-danger">*</span></label>
          <select class="form-select" id="tlSem">
            <option>1st Semester</option>
            <option>2nd Semester</option>
            <option>Summer</option>
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
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="kpiModalTitle"><i class="fa-solid fa-bullseye me-2"></i>Add KPI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="kpiId">
        <div class="mb-3">
          <label class="form-label">Category <span class="text-danger">*</span></label>
          <select class="form-select" id="kpiCategory">
            <option value="core">Core Function</option>
            <option value="strategic">Strategic Function</option>
            <option value="support">Support Function</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">MFO/PAP <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="kpiMfo" placeholder="e.g. Instruction, Research">
        </div>
        <div class="mb-3">
          <label class="form-label">Success Indicator <span class="text-danger">*</span></label>
          <textarea class="form-control" id="kpiIndicator" rows="2" placeholder="Describe the success indicator..."></textarea>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Target</label>
            <input type="text" class="form-control" id="kpiTarget" placeholder="e.g. 100%">
          </div>
          <div class="col-6">
            <label class="form-label">Measure</label>
            <input type="text" class="form-control" id="kpiMeasure" placeholder="e.g. Quality/Timeliness">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" onclick="saveKpi()"><i class="fa-solid fa-save me-1"></i>Save</button>
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

  let allKpi = {};

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

  async function loadKpis() {
    const res = await fetch(API_BASE + 'kpi/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    allKpi = res?.grouped || {};
    renderKpis();
  }

  function renderKpiTable(category, containerId) {
    const items = allKpi[category] || [];
    const el = document.getElementById(containerId);
    if (items.length === 0) { el.innerHTML = '<div class="empty-state"><i class="fa-solid fa-list"></i><p>No KPIs yet.</p></div>'; return; }
    el.innerHTML = `<table class="table"><thead><tr><th>#</th><th>MFO/PAP</th><th>Success Indicator</th><th>Target</th><th>Measure</th><th>Action</th></tr></thead>
      <tbody>${items.map((item, i) => `<tr>
        <td>${i+1}</td><td style="font-size:0.83rem">${item.mfo}</td>
        <td style="font-size:0.83rem">${item.success_indicator}</td>
        <td style="font-size:0.83rem">${item.target}</td>
        <td style="font-size:0.83rem">${item.measure}</td>
        <td><div class="d-flex gap-1">
          <button class="btn btn-outline-primary btn-sm" onclick='editKpi("${category}",${item.id})'><i class="fa-solid fa-edit"></i></button>
          <button class="btn btn-outline-danger btn-sm" onclick='deleteKpi(${item.id})'><i class="fa-solid fa-trash"></i></button>
        </div></td></tr>`).join('')}</tbody></table>`;
  }

  function renderKpis() {
    renderKpiTable('core', 'kpiCoreTable');
    renderKpiTable('strategic', 'kpiStrategicTable');
    renderKpiTable('support', 'kpiSupportTable');
  }

  function openTimelineModal(data = null) {
    document.getElementById('tlId').value = data?.id || '';
    document.getElementById('tlYear').value = data?.academic_year || '';
    document.getElementById('tlSem').value = data?.semester || '1st Semester';
    document.getElementById('tlStart').value = data?.start_date || '';
    document.getElementById('tlEnd').value = data?.end_date || '';
    document.getElementById('tlDeadline').value = data?.submission_deadline || '';
    document.getElementById('tlStatus').value = data?.status || 'open';
    document.getElementById('timelineModalTitle').innerHTML = `<i class="fa-solid fa-calendar me-2"></i>${data ? 'Edit' : 'Add'} Timeline`;
    new bootstrap.Modal(document.getElementById('timelineModal')).show();
  }

  function editTimeline(t) { openTimelineModal(t); }

  async function saveTimeline() {
    const id = document.getElementById('tlId').value;
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

  function openKpiModal(category = 'core', data = null) {
    document.getElementById('kpiId').value = data?.id || '';
    document.getElementById('kpiCategory').value = category;
    document.getElementById('kpiMfo').value = data?.mfo || '';
    document.getElementById('kpiIndicator').value = data?.success_indicator || '';
    document.getElementById('kpiTarget').value = data?.target || '';
    document.getElementById('kpiMeasure').value = data?.measure || '';
    document.getElementById('kpiModalTitle').innerHTML = `<i class="fa-solid fa-bullseye me-2"></i>${data ? 'Edit' : 'Add'} KPI`;
    new bootstrap.Modal(document.getElementById('kpiModal')).show();
  }

  function editKpi(category, id) {
    const item = (allKpi[category] || []).find(x => x.id === id);
    if (item) openKpiModal(category, item);
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
    const id = document.getElementById('kpiId').value;
    const cat = document.getElementById('kpiCategory').value;
    const mfo = document.getElementById('kpiMfo').value.trim();
    const indicator = document.getElementById('kpiIndicator').value.trim();
    if (!mfo || !indicator) { showToast('MFO and Success Indicator are required.', 'warning'); return; }
    const catLabels = { core: 'Core Function', strategic: 'Strategic Function', support: 'Support Function' };
    const res = await fetch(API_BASE + 'kpi/save.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id || undefined, category: catLabels[cat], mfo, success_indicator: indicator, target: document.getElementById('kpiTarget').value, measure: document.getElementById('kpiMeasure').value })
    }).then(r => r.json()).catch(() => null);
    if (res?.success) {
      bootstrap.Modal.getInstance(document.getElementById('kpiModal')).hide();
      showToast('KPI saved successfully!', 'success');
      loadKpis();
    } else showToast(res?.error || 'Failed to save KPI.', 'danger');
  }

  loadTimelines();
  loadKpis();
</script>
</body>
</html>
