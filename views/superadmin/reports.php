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
  <title>Reports | CSU-Piat</title>
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
    <h2><i class="fa-solid fa-chart-bar me-2 text-primary"></i>Reports</h2>
    <p>View and export OPCR/IPCR performance reports for CSU-Piat.</p>
  </div>

  <!-- Filters -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Report Type</label>
          <select class="form-select form-select-sm" id="reportType">
            <option value="all">All Reports</option>
            <option value="ipcr">IPCR Only</option>
            <option value="opcr">OPCR Only</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select form-select-sm" id="statusFilter">
            <option value="">All Status</option>
            <option value="approved">Approved</option>
            <option value="pending">Pending</option>
            <option value="reviewed">Reviewed</option>
            <option value="disapproved">Disapproved</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Department</label>
          <select class="form-select form-select-sm" id="deptFilter">
            <option value="">All Departments</option>
          </select>
        </div>
        <div class="col-md-3">
          <button class="btn btn-primary btn-sm w-100" onclick="applyFilters()"><i class="fa-solid fa-filter me-1"></i>Apply Filter</button>
        </div>
      </div>
    </div>
  </div>

  <div class="table-wrapper">
    <div class="table-header">
      <h6><i class="fa-solid fa-file-alt me-2"></i>OPCR/IPCR Submissions</h6>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-success btn-sm" onclick="exportReport()"><i class="fa-solid fa-file-excel me-1"></i>Export</button>
        <button class="btn btn-outline-secondary btn-sm" onclick="printReport()"><i class="fa-solid fa-print me-1"></i>Print</button>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr>
          <th>#</th><th>Type</th><th>Name</th><th>Department</th><th>Covered Period</th><th>Rating</th><th>Status</th><th>Date</th><th class="no-print">Action</th>
        </tr></thead>
        <tbody id="reportsTable"></tbody>
      </table>
    </div>
  </div>
</main>

<!-- View Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-file-lines me-2"></i>Form Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailModalBody"></div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary btn-sm" onclick="showPrintPreview()"><i class="fa-solid fa-print me-1"></i>Print Preview</button>
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
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
  initLayout('superadmin', 'reports', [{ label: 'Reports' }]);

  let allForms = [];

  async function loadReports() {
    const [ipcrRes, opcrRes] = await Promise.all([
      fetch(API_BASE + 'ipcr/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null),
      fetch(API_BASE + 'opcr/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null),
    ]);
    const ipcrs = (ipcrRes?.forms || []).map(f => ({ ...f, type: 'IPCR', name: f.user_name }));
    const opcrs = (opcrRes?.forms || []).map(f => ({ ...f, type: 'OPCR', name: f.admin_name }));
    allForms = [...ipcrs, ...opcrs];
    const deptSel = document.getElementById('deptFilter');
    const depts = [...new Set(allForms.map(f => f.department_name).filter(Boolean))];
    depts.forEach(d => { const o = document.createElement('option'); o.value = d; o.textContent = d; deptSel.appendChild(o); });
    renderTable();
  }

  function applyFilters() { renderTable(); }

  function renderTable() {
    const typeF = document.getElementById('reportType').value;
    const statusF = document.getElementById('statusFilter').value;
    const deptF = document.getElementById('deptFilter').value;
    const filtered = allForms.filter(f =>
      (typeF === 'all' || f.type === typeF.toUpperCase()) &&
      (!statusF || f.status === statusF) &&
      (!deptF || f.department_name === deptF)
    );
    const tbody = document.getElementById('reportsTable');
    tbody.innerHTML = '';
    if (filtered.length === 0) {
      tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-muted">No reports found.</td></tr>`;
      return;
    }
    filtered.forEach((f, i) => {
      const rating = parseFloat(f.overall_rating) || 0;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${i+1}</td>
        <td><span class="badge ${f.type === 'IPCR' ? 'bg-info' : 'bg-warning text-dark'}">${f.type}</span></td>
        <td style="font-size:0.85rem">${f.name}</td>
        <td style="font-size:0.8rem">${f.department_name || '-'}</td>
        <td style="font-size:0.8rem">${f.covered_period}</td>
        <td style="font-size:0.85rem">${rating > 0 ? rating.toFixed(1) : '-'}</td>
        <td>${getStatusBadge(f.status)}</td>
        <td style="font-size:0.78rem">${formatDate(f.date_submitted)}</td>
        <td class="no-print"><button class="btn btn-outline-primary btn-sm" onclick='viewDetail(${JSON.stringify(f).replace(/'/g,"&#39;")})'><i class="fa-solid fa-eye"></i></button></td>`;
      tbody.appendChild(tr);
    });
  }

  function showPrintPreview() {
    showToast('Use the dedicated review page for full print preview.', 'info');
  }

  function viewDetail(form) {
    const rating = parseFloat(form.overall_rating) || 0;
    document.getElementById('detailModalBody').innerHTML = `
      <div class="row g-2 mb-3 p-2 bg-light rounded" style="font-size:0.85rem">
        <div class="col-6"><strong>Type:</strong> <span class="badge ${form.type === 'IPCR' ? 'bg-info' : 'bg-warning text-dark'}">${form.type}</span></div>
        <div class="col-6"><strong>Name:</strong> ${form.name}</div>
        <div class="col-6"><strong>Department:</strong> ${form.department_name || '-'}</div>
        <div class="col-6"><strong>Position:</strong> ${form.position || '-'}</div>
        <div class="col-6"><strong>Covered Period:</strong> ${form.covered_period}</div>
        <div class="col-6"><strong>Submitted:</strong> ${formatDate(form.date_submitted)}</div>
        <div class="col-6"><strong>Academic Year:</strong> ${form.academic_year || '-'} ${form.semester || ''}</div>
        <div class="col-6"><strong>Status:</strong> ${getStatusBadge(form.status)}</div>
        <div class="col-6"><strong>Overall Rating:</strong> ${rating > 0 ? rating.toFixed(2) + ' — ' : ''}${getRatingLabel(rating)}</div>
        ${form.remarks ? `<div class="col-12"><strong>Remarks:</strong> ${form.remarks}</div>` : ''}
      </div>`;
    new bootstrap.Modal(document.getElementById('detailModal')).show();
  }

  function exportReport() {
    const rows = [['Type', 'Name', 'Department', 'Covered Period', 'Rating', 'Status', 'Date']];
    allForms.forEach(f => rows.push([f.type, f.name, f.department_name, f.covered_period, f.overall_rating || '-', f.status, f.date_submitted]));
    const csv = rows.map(r => r.map(v => `"${String(v||'').replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = 'CSU-Piat-Reports.csv'; a.click();
    showToast('Report exported successfully!', 'success');
  }

  function printReport() { window.print(); }

  loadReports();
</script>
</body>
</html>
