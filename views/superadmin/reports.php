<?php
require_once '../../config/session.php';
$user = requireAuth(['superadmin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Superadmin Reports | CSU-Piat</title>
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
      <h2><i class="fa-solid fa-chart-bar me-2 text-primary"></i>OPCR / IPCR Reports & Ratings Summary</h2>
      <p>View campus performance ratings summary, filter, print, and export OPCR/IPCR reports for CSU-Piat.</p>
    </div>
    <div class="d-flex gap-2 no-print">
      <button class="btn btn-outline-primary btn-sm" onclick="printReport()"><i class="fa-solid fa-print me-1"></i>Print Report</button>
      <button class="btn btn-outline-success btn-sm" onclick="exportReport()"><i class="fa-solid fa-file-excel me-1"></i>Export CSV</button>
    </div>
  </div>

  <!-- Filters -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Report Type</label>
          <select class="form-select form-select-sm" id="reportType" onchange="applyFilters()">
            <option value="all">All Reports (OPCR & IPCR)</option>
            <option value="ipcr">IPCR Only</option>
            <option value="opcr">OPCR Only</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select form-select-sm" id="statusFilter" onchange="applyFilters()">
            <option value="">All Status</option>
            <option value="approved">Approved</option>
            <option value="pending">Pending</option>
            <option value="reviewed">Reviewed</option>
            <option value="disapproved">Disapproved</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Department / Office</label>
          <select class="form-select form-select-sm" id="deptFilter" onchange="applyFilters()">
            <option value="">All Departments</option>
          </select>
        </div>
        <div class="col-md-3">
          <div class="search-box w-100">
            <i class="fa-solid fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search name..." oninput="applyFilters()" style="padding-left:32px">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Summary of Ratings Stat Cards -->
  <div class="row g-3 mb-3" id="ratingsSummaryCards">
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-star"></i></div>
        <div class="stat-value" id="summaryCampusAvg">-</div>
        <div class="stat-label">Campus Average Rating</div>
        <div class="stat-trend" id="summaryCampusBadge"></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="stat-card success">
        <div class="stat-icon"><i class="fa-solid fa-award"></i></div>
        <div class="stat-value" id="summaryOutCount">0</div>
        <div class="stat-label">Outstanding (5)</div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="stat-card info">
        <div class="stat-icon"><i class="fa-solid fa-thumbs-up"></i></div>
        <div class="stat-value" id="summaryVSCount">0</div>
        <div class="stat-label">Very Satisfactory (4)</div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="stat-card warning">
        <div class="stat-icon"><i class="fa-solid fa-check"></i></div>
        <div class="stat-value" id="summarySatCount">0</div>
        <div class="stat-label">Satisfactory (3)</div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card danger">
        <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-value" id="summaryBelowCount">0</div>
        <div class="stat-label">Unsatisfactory / Poor</div>
      </div>
    </div>
  </div>

  <!-- Summary of Ratings Breakdown Table -->
  <div class="card mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
      <h6 class="mb-0"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Summary of Ratings Breakdown</h6>
      <small class="text-muted">Adjectival performance breakdown for all campus submissions</small>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th>Adjectival Rating</th>
              <th>Score Range</th>
              <th class="text-center">Submissions Count</th>
              <th class="text-center">Percentage</th>
              <th>Performance Status</th>
            </tr>
          </thead>
          <tbody id="summaryBreakdownTable"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Table Wrapper -->
  <div class="table-wrapper">
    <div class="table-header">
      <h6><i class="fa-solid fa-file-alt me-2"></i>OPCR/IPCR Submissions</h6>
      <small class="text-muted" id="tableInfo"></small>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr>
          <th>#</th><th>Type</th><th>Name</th><th>Department</th><th>Covered Period</th><th>Rating</th><th>Adjectival Rating</th><th>Status</th><th>Date</th><th class="no-print">Action</th>
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
        <button class="btn btn-outline-primary btn-sm" onclick="printReport()"><i class="fa-solid fa-print me-1"></i>Print Report</button>
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
  const session = requireAuth(['superadmin']);
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
    deptSel.innerHTML = '<option value="">All Departments</option>';
    const depts = [...new Set(allForms.map(f => f.department_name).filter(Boolean))];
    depts.forEach(d => { const o = document.createElement('option'); o.value = d; o.textContent = d; deptSel.appendChild(o); });
    
    renderTable();
  }

  function applyFilters() { renderTable(); }

  function renderTable() {
    const typeF = document.getElementById('reportType').value;
    const statusF = document.getElementById('statusFilter').value;
    const deptF = document.getElementById('deptFilter').value;
    const searchF = (document.getElementById('searchInput').value || '').toLowerCase();

    const filtered = allForms.filter(f =>
      (typeF === 'all' || f.type === typeF.toUpperCase()) &&
      (!statusF || f.status === statusF) &&
      (!deptF || f.department_name === deptF) &&
      (!searchF || (f.name || '').toLowerCase().includes(searchF))
    );

    // Update Analytics Summary
    updateRatingsSummary(filtered);

    // Render Table
    const tbody = document.getElementById('reportsTable');
    tbody.innerHTML = '';
    if (filtered.length === 0) {
      tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted"><i class="fa-solid fa-inbox me-2"></i>No reports found.</td></tr>`;
      document.getElementById('tableInfo').textContent = '0 records';
      return;
    }
    filtered.forEach((f, i) => {
      const rating = parseFloat(f.overall_rating) || 0;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${i+1}</td>
        <td><span class="badge ${f.type === 'IPCR' ? 'bg-info' : 'bg-warning text-dark'}">${f.type}</span></td>
        <td style="font-size:0.85rem"><strong>${f.name}</strong></td>
        <td style="font-size:0.8rem">${f.department_name || '-'}</td>
        <td style="font-size:0.8rem">${f.covered_period}</td>
        <td style="font-size:0.85rem;font-weight:700">${rating > 0 ? rating.toFixed(2) : '-'}</td>
        <td>${rating > 0 ? getRatingLabel(rating) : '<span class="text-muted">-</span>'}</td>
        <td>${getStatusBadge(f.status)}</td>
        <td style="font-size:0.78rem">${formatDate(f.date_submitted)}</td>
        <td class="no-print"><button class="btn btn-outline-primary btn-sm" onclick='viewDetail(${JSON.stringify(f).replace(/'/g,"&#39;")})'><i class="fa-solid fa-eye"></i></button></td>`;
      tbody.appendChild(tr);
    });
    document.getElementById('tableInfo').textContent = `${filtered.length} of ${allForms.length} records`;
  }

  function updateRatingsSummary(forms) {
    const rated = forms.map(f => parseFloat(f.overall_rating) || 0).filter(v => v > 0);
    const avg = rated.length ? (rated.reduce((a,b) => a+b, 0) / rated.length) : 0;
    
    document.getElementById('summaryCampusAvg').textContent = avg > 0 ? avg.toFixed(2) : '-';
    document.getElementById('summaryCampusBadge').innerHTML = avg > 0 ? getRatingLabel(avg) : '<span class="text-muted">No rated submissions</span>';

    const countOut   = forms.filter(f => (parseFloat(f.overall_rating)||0) >= 4.5).length;
    const countVS    = forms.filter(f => (parseFloat(f.overall_rating)||0) >= 3.5 && (parseFloat(f.overall_rating)||0) < 4.5).length;
    const countSat   = forms.filter(f => (parseFloat(f.overall_rating)||0) >= 2.5 && (parseFloat(f.overall_rating)||0) < 3.5).length;
    const countUnsat = forms.filter(f => (parseFloat(f.overall_rating)||0) >= 1.5 && (parseFloat(f.overall_rating)||0) < 2.5).length;
    const countPoor  = forms.filter(f => (parseFloat(f.overall_rating)||0) > 0 && (parseFloat(f.overall_rating)||0) < 1.5).length;
    const countBelow = countUnsat + countPoor;

    document.getElementById('summaryOutCount').textContent = countOut;
    document.getElementById('summaryVSCount').textContent  = countVS;
    document.getElementById('summarySatCount').textContent = countSat;
    document.getElementById('summaryBelowCount').textContent = countBelow;

    // Summary Breakdown Table
    const total = forms.length;
    const breakdownData = [
      { label: 'Outstanding', range: '4.50 – 5.00', cnt: countOut, badge: '<span class="badge bg-success">Outstanding</span>' },
      { label: 'Very Satisfactory', range: '3.50 – 4.49', cnt: countVS, badge: '<span class="badge" style="background:#E85C0D">Very Satisfactory</span>' },
      { label: 'Satisfactory', range: '2.50 – 3.49', cnt: countSat, badge: '<span class="badge bg-warning text-dark">Satisfactory</span>' },
      { label: 'Unsatisfactory', range: '1.50 – 2.49', cnt: countUnsat, badge: '<span class="badge bg-danger">Unsatisfactory</span>' },
      { label: 'Poor', range: '1.00 – 1.49', cnt: countPoor, badge: '<span class="badge bg-dark">Poor</span>' },
    ];

    const breakdownTbody = document.getElementById('summaryBreakdownTable');
    breakdownTbody.innerHTML = breakdownData.map(row => {
      const pct = total > 0 ? ((row.cnt / total) * 100).toFixed(1) : '0.0';
      return `<tr>
        <td style="font-weight:600">${row.label}</td>
        <td style="font-size:0.82rem;color:#555">${row.range}</td>
        <td class="text-center fw-700">${row.cnt}</td>
        <td class="text-center fw-700">${pct}%</td>
        <td>${row.badge}</td>
      </tr>`;
    }).join('');
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
    const rows = [['Type', 'Name', 'Department', 'Covered Period', 'Rating', 'Adjectival Rating', 'Status', 'Date']];
    allForms.forEach(f => {
      const r = parseFloat(f.overall_rating) || 0;
      const adj = r > 0 ? getAdjectivalText(r) : '-';
      rows.push([f.type, f.name, f.department_name, f.covered_period, r > 0 ? r.toFixed(2) : '-', adj, f.status, f.date_submitted]);
    });
    const csv = rows.map(r => r.map(v => `"${String(v||'').replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = 'CSU-Piat-Ratings-Summary.csv'; a.click();
    showToast('Report exported successfully!', 'success');
  }

  function printReport() {
    const typeF = document.getElementById('reportType').value;
    const statusF = document.getElementById('statusFilter').value;
    const deptF = document.getElementById('deptFilter').value || 'All Departments';
    const searchF = (document.getElementById('searchInput').value || '').toLowerCase();
    const dateStr = new Date().toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });

    const filtered = allForms.filter(f =>
      (typeF === 'all' || f.type === typeF.toUpperCase()) &&
      (!statusF || f.status === statusF) &&
      (deptF === 'All Departments' || !deptF || f.department_name === deptF) &&
      (!searchF || (f.name || '').toLowerCase().includes(searchF))
    );

    const ratings = filtered.map(f => parseFloat(f.overall_rating) || 0).filter(v => v > 0);
    const campusAvg = ratings.length ? (ratings.reduce((a,b) => a+b, 0) / ratings.length).toFixed(2) : '-';

    const countOut   = filtered.filter(f => (parseFloat(f.overall_rating)||0) >= 4.5).length;
    const countVS    = filtered.filter(f => (parseFloat(f.overall_rating)||0) >= 3.5 && (parseFloat(f.overall_rating)||0) < 4.5).length;
    const countSat   = filtered.filter(f => (parseFloat(f.overall_rating)||0) >= 2.5 && (parseFloat(f.overall_rating)||0) < 3.5).length;
    const countUnsat = filtered.filter(f => (parseFloat(f.overall_rating)||0) >= 1.5 && (parseFloat(f.overall_rating)||0) < 2.5).length;
    const countPoor  = filtered.filter(f => (parseFloat(f.overall_rating)||0) > 0 && (parseFloat(f.overall_rating)||0) < 1.5).length;

    function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    let rowsHtml = '';
    filtered.forEach((f, i) => {
      const r = parseFloat(f.overall_rating) || 0;
      const adj = r > 0 ? getAdjectivalText(r) : '-';
      rowsHtml += `<tr>
        <td style="text-align:center">${i+1}</td>
        <td style="text-align:center;font-weight:bold">${f.type}</td>
        <td><strong>${esc(f.name)}</strong></td>
        <td>${esc(f.department_name || '-')}</td>
        <td>${esc(f.covered_period)}</td>
        <td style="text-align:center">${formatDate(f.date_submitted)}</td>
        <td style="text-align:center;font-weight:bold">${r > 0 ? r.toFixed(2) : '-'}</td>
        <td style="text-align:center">${adj}</td>
        <td style="text-align:center;text-transform:capitalize">${f.status}</td>
      </tr>`;
    });

    const html = `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Campus OPCR/IPCR Performance & Ratings Report</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:9pt; color:#000; padding:0.4in; }
@page { size:letter portrait; margin:0.4in; }
@media print { .no-print { display:none!important; } }
.no-print { position:fixed; top:12px; right:16px; z-index:999; display:flex; gap:8px; }
.no-print button { padding:8px 16px; font-size:12px; border:none; border-radius:4px; cursor:pointer; font-weight:600; font-family:sans-serif; }
.btn-p { background:#E85C0D; color:#fff; } .btn-c { background:#555; color:#fff; }
.header { text-align:center; border-bottom:2px solid #821131; padding-bottom:10px; margin-bottom:15px; }
.header h3 { font-size:14pt; color:#821131; font-weight:bold; margin-bottom:2px; }
.header h4 { font-size:11pt; color:#2d3436; margin-bottom:4px; }
.header p { font-size:8.5pt; color:#555; }
.summary-box { display:flex; gap:10px; margin-bottom:15px; }
.stat-box { flex:1; border:1px solid #ddd; border-radius:6px; padding:8px; text-align:center; background:#fafafa; }
.stat-val { font-size:14pt; font-weight:bold; color:#821131; }
.stat-lbl { font-size:7.5pt; color:#555; text-transform:uppercase; margin-top:2px; }
table { width:100%; border-collapse:collapse; margin-bottom:15px; font-size:8.5pt; }
th, td { border:1px solid #333; padding:5px 7px; vertical-align:middle; }
th { background:#f0f0f0; font-weight:bold; text-transform:uppercase; font-size:8pt; }
.sig-row { display:flex; justify-content:space-between; margin-top:35px; page-break-inside:avoid; }
.sig-box { width:45%; text-align:center; }
.sig-line { border-top:1px solid #000; margin-top:35px; padding-top:4px; font-weight:bold; font-size:8.5pt; }
</style>
</head>
<body>
<div class="no-print">
  <button class="btn-p" onclick="window.print()">Print / Save as PDF</button>
  <button class="btn-c" onclick="window.close()">Close</button>
</div>

<div class="header">
  <h3>CAGAYAN STATE UNIVERSITY — PIAT CAMPUS</h3>
  <h4>CAMPUS SUMMARY OF OPCR/IPCR RATINGS & PERFORMANCE REPORT</h4>
  <p>Scope: <strong>${esc(deptF)} (${esc(typeF.toUpperCase())})</strong> | Date Generated: <strong>${dateStr}</strong></p>
</div>

<div class="summary-box">
  <div class="stat-box"><div class="stat-val">${campusAvg}</div><div class="stat-lbl">Campus Avg Rating</div></div>
  <div class="stat-box"><div class="stat-val">${filtered.length}</div><div class="stat-lbl">Total Submissions</div></div>
  <div class="stat-box"><div class="stat-val">${countOut}</div><div class="stat-lbl">Outstanding (5)</div></div>
  <div class="stat-box"><div class="stat-val">${countVS}</div><div class="stat-lbl">Very Satisfactory (4)</div></div>
  <div class="stat-box"><div class="stat-val">${countSat + countUnsat + countPoor}</div><div class="stat-lbl">Satisfactory / Below</div></div>
</div>

<h5 style="margin-bottom:6px;font-size:9.5pt;font-weight:bold;color:#821131">I. RATINGS BREAKDOWN</h5>
<table>
  <thead>
    <tr>
      <th>Adjectival Rating</th>
      <th>Rating Range</th>
      <th style="text-align:center">Count</th>
      <th style="text-align:center">Percentage</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Outstanding</td><td>4.50 – 5.00</td><td style="text-align:center">${countOut}</td><td style="text-align:center">${filtered.length ? ((countOut/filtered.length)*100).toFixed(1) : 0}%</td></tr>
    <tr><td>Very Satisfactory</td><td>3.50 – 4.49</td><td style="text-align:center">${countVS}</td><td style="text-align:center">${filtered.length ? ((countVS/filtered.length)*100).toFixed(1) : 0}%</td></tr>
    <tr><td>Satisfactory</td><td>2.50 – 3.49</td><td style="text-align:center">${countSat}</td><td style="text-align:center">${filtered.length ? ((countSat/filtered.length)*100).toFixed(1) : 0}%</td></tr>
    <tr><td>Unsatisfactory</td><td>1.50 – 2.49</td><td style="text-align:center">${countUnsat}</td><td style="text-align:center">${filtered.length ? ((countUnsat/filtered.length)*100).toFixed(1) : 0}%</td></tr>
    <tr><td>Poor</td><td>1.00 – 1.49</td><td style="text-align:center">${countPoor}</td><td style="text-align:center">${filtered.length ? ((countPoor/filtered.length)*100).toFixed(1) : 0}%</td></tr>
  </tbody>
</table>

<h5 style="margin-bottom:6px;font-size:9.5pt;font-weight:bold;color:#821131">II. CAMPUS SUBMISSIONS & RATINGS</h5>
<table>
  <thead>
    <tr>
      <th style="width:30px">#</th>
      <th>Type</th>
      <th>Name</th>
      <th>Department</th>
      <th>Covered Period</th>
      <th>Submitted</th>
      <th>Rating</th>
      <th>Adjectival Rating</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    ${rowsHtml || '<tr><td colspan="9" style="text-align:center">No records found.</td></tr>'}
  </tbody>
</table>

<div class="sig-row">
  <div class="sig-box">
    <div class="sig-line">${esc(session.name)}<br><span style="font-weight:normal;font-size:7.5pt;color:#555">Prepared by (Superadmin)</span></div>
  </div>
  <div class="sig-box">
    <div class="sig-line">Campus Executive Officer<br><span style="font-weight:normal;font-size:7.5pt;color:#555">Approved by</span></div>
  </div>
</div>

<script>setTimeout(()=>window.print(), 700);<\/script>
</body>
</html>`;

    const w = window.open('', '_blank');
    if (!w) { showToast('Please allow popups to print report.', 'warning'); return; }
    w.document.write(html);
    w.document.close();
  }

  loadReports();
</script>
</body>
</html>
