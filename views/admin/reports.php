<?php
require_once '../../config/session.php';
$user = requireAuth(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Reports | CSU-Piat</title>
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
  <div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
      <h2><i class="fa-solid fa-file-alt me-2 text-primary"></i>IPCR Reports</h2>
      <p>View, filter, and export submitted IPCR forms.</p>
    </div>
    <div class="d-flex gap-2 no-print">
      <button class="btn btn-outline-success btn-sm" onclick="exportCSV()"><i class="fa-solid fa-file-excel me-1"></i>Export</button>
      <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
    </div>
  </div>

  <!-- Filters -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select form-select-sm" id="statusFilter" onchange="applyFilter()">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="reviewed">Reviewed</option>
            <option value="approved">Approved</option>
            <option value="disapproved">Disapproved</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Department</label>
          <select class="form-select form-select-sm" id="deptFilter" onchange="applyFilter()">
            <option value="">All Departments</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Search</label>
          <div class="search-box w-100">
            <i class="fa-solid fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search name..." oninput="applyFilter()" style="padding-left:32px">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Date From</label>
          <input type="date" class="form-control form-control-sm" id="dateFrom" onchange="applyFilter()">
        </div>
        <div class="col-md-3">
          <label class="form-label">Date To</label>
          <input type="date" class="form-control form-control-sm" id="dateTo" onchange="applyFilter()">
        </div>
      </div>
    </div>
  </div>

  <div class="table-wrapper">
    <div class="table-header">
      <h6><i class="fa-solid fa-list me-2"></i>Submitted IPCR Forms</h6>
      <small class="text-muted" id="tableInfo"></small>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr>
          <th>#</th><th>Employee</th><th>Department</th><th>Covered Period</th><th>Submission Date</th><th>Rating</th><th>Status</th><th class="no-print">Action</th>
        </tr></thead>
        <tbody id="reportsTable"></tbody>
      </table>
    </div>
  </div>
</main>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-file-lines me-2"></i>IPCR Form Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailBody"></div>
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
  const session = requireAuth(['admin']);
  initLayout('admin', 'reports', [{ label: 'Reports' }]);

  let allForms = [];

  async function loadReports() {
    const res = await fetch(API_BASE + 'ipcr/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    allForms = res?.forms || [];
    const deptSel = document.getElementById('deptFilter');
    const depts = [...new Set(allForms.map(f => f.department_name).filter(Boolean))];
    depts.forEach(d => { const o = document.createElement('option'); o.value = d; o.textContent = d; deptSel.appendChild(o); });
    applyFilter();
  }

  function applyFilter() {
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchInput').value.toLowerCase();
    const from = document.getElementById('dateFrom').value;
    const to = document.getElementById('dateTo').value;
    const dept = document.getElementById('deptFilter').value;
    const filtered = allForms.filter(f =>
      (!status || f.status === status) &&
      (!dept || f.department_name === dept) &&
      (!search || (f.user_name || '').toLowerCase().includes(search)) &&
      (!from || (f.date_submitted || '') >= from) &&
      (!to || (f.date_submitted || '') <= to)
    );
    const tbody = document.getElementById('reportsTable');
    tbody.innerHTML = '';
    if (filtered.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fa-solid fa-inbox me-2"></i>No records found.</td></tr>`;
    } else {
      filtered.forEach((f, i) => {
        const initials = (f.user_name || '').split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${i+1}</td>
          <td><div class="d-flex align-items-center gap-2"><div class="avatar" style="width:30px;height:30px;font-size:0.65rem">${initials}</div><span style="font-size:0.83rem">${f.user_name}</span></div></td>
          <td style="font-size:0.8rem">${f.department_name || '-'}</td>
          <td style="font-size:0.8rem">${f.covered_period}</td>
          <td style="font-size:0.8rem">${formatDate(f.date_submitted)}</td>
          <td style="font-size:0.83rem">${f.overall_rating > 0 ? parseFloat(f.overall_rating).toFixed(1) : '-'}</td>
          <td>${getStatusBadge(f.status)}</td>
          <td class="no-print"><button class="btn btn-outline-primary btn-sm" onclick='showDetail(${JSON.stringify(f).replace(/'/g,"&#39;")})'><i class="fa-solid fa-eye"></i></button></td>`;
        tbody.appendChild(tr);
      });
    }
    document.getElementById('tableInfo').textContent = `${filtered.length} of ${allForms.length} records`;
  }

  function showPrintPreview() {
    showToast('Use the dedicated IPCR review page for full print preview.', 'info');
  }

  function showDetail(f) {
    const rating = parseFloat(f.overall_rating) || 0;
    const html = `<div class="row g-2 mb-3 p-2 bg-light rounded" style="font-size:0.85rem">
      <div class="col-6"><strong>Name:</strong> ${f.user_name}</div>
      <div class="col-6"><strong>Department:</strong> ${f.department_name || '-'}</div>
      <div class="col-6"><strong>Position:</strong> ${f.position || '-'}</div>
      <div class="col-6"><strong>Period:</strong> ${f.covered_period}</div>
      <div class="col-6"><strong>Submitted:</strong> ${formatDate(f.date_submitted)}</div>
      <div class="col-6"><strong>Academic Year:</strong> ${f.academic_year || '-'} ${f.semester || ''}</div>
      <div class="col-6"><strong>Status:</strong> ${getStatusBadge(f.status)}</div>
      <div class="col-6"><strong>Rating:</strong> ${rating > 0 ? rating.toFixed(2) + ' — ' : ''}${getRatingLabel(rating)}</div>
      ${f.remarks ? `<div class="col-12"><strong>Remarks:</strong> ${f.remarks}</div>` : ''}
      ${f.reviewed_by_name ? `<div class="col-12"><strong>Reviewed by:</strong> ${f.reviewed_by_name}</div>` : ''}
    </div>`;
    document.getElementById('detailBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('detailModal')).show();
  }

  function exportCSV() {
    const rows = [['Name', 'Department', 'Position', 'Covered Period', 'Date Submitted', 'Rating', 'Status']];
    allForms.forEach(f => rows.push([f.user_name, f.department_name, f.position, f.covered_period, f.date_submitted, f.overall_rating || '-', f.status]));
    const csv = rows.map(r => r.map(v => `"${String(v||'').replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = 'IPCR-Reports.csv'; a.click();
    showToast('Report exported!', 'success');
  }

  loadReports();
</script>
</body>
</html>
