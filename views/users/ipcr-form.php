<?php
require_once '../../config/session.php';
$user = requireAuth(['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IPCR Form | CSU-Piat</title>
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
      <h2><i class="fa-solid fa-file-lines me-2 text-primary"></i>IPCR Form</h2>
      <p>Individual Performance Commitment and Review | CSU-Piat</p>
    </div>
    <div class="d-flex gap-2 no-print">
      <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
      <button class="btn btn-outline-primary btn-sm" onclick="saveIPCR('draft')"><i class="fa-solid fa-floppy-disk me-1"></i>Save Draft</button>
      <button class="btn btn-success btn-sm" onclick="submitIPCR()"><i class="fa-solid fa-paper-plane me-1"></i>Submit</button>
    </div>
  </div>

  <!-- Print Header (visible on print only) -->
  <div class="d-none d-print-block text-center mb-3">
    <h5 class="fw-700">CAGAYAN STATE UNIVERSITY — PIAT CAMPUS</h5>
    <h6>INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)</h6>
    <p style="font-size:0.85rem">Ytawes District, Piat, Cagayan | Founded 1954</p>
  </div>

  <!-- Form Header -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">College / Office</label>
          <input type="text" class="form-control bg-light" id="ipcrOffice" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Name</label>
          <input type="text" class="form-control bg-light" id="ipcrName" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Position</label>
          <input type="text" class="form-control bg-light" id="ipcrPosition" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Covered Period <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="ipcrPeriod" placeholder="e.g. January - June 2026">
        </div>
        <div class="col-md-4">
          <label class="form-label">Date</label>
          <input type="date" class="form-control" id="ipcrDate">
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <input type="text" class="form-control bg-light" id="ipcrStatus" readonly>
        </div>
      </div>
    </div>
  </div>

  <!-- Legend -->
  <div class="card mb-3 no-print">
    <div class="card-body py-2">
      <small class="text-muted"><strong>Rating Scale:</strong>
        <span class="text-success ms-2">5 — Outstanding</span>
        <span class="text-primary ms-2">4 — Very Satisfactory</span>
        <span class="text-warning ms-2">3 — Satisfactory</span>
        <span class="text-danger ms-2">2 — Unsatisfactory</span>
        <span class="text-danger ms-2">1 — Poor</span>
      </small>
    </div>
  </div>

  <!-- Core Function -->
  <div class="mb-3">
    <div class="ipcr-section-header"><i class="fa-solid fa-star me-2"></i>A. CORE FUNCTION</div>
    <div class="table-responsive">
      <table class="table table-bordered mb-0">
        <thead class="table-light" style="font-size:0.8rem"><tr><th style="width:110px">MFO / KRA</th><th>Success Indicators</th><th style="width:100px">Target</th><th>Actual Accomplishment</th><th style="width:90px">Rating (1-5)</th><th>Remarks</th></tr></thead>
        <tbody id="coreBody"></tbody>
      </table>
    </div>
  </div>

  <!-- Strategic Function -->
  <div class="mb-3">
    <div class="ipcr-section-header"><i class="fa-solid fa-chess me-2"></i>B. STRATEGIC FUNCTION</div>
    <div class="table-responsive">
      <table class="table table-bordered mb-0">
        <thead class="table-light" style="font-size:0.8rem"><tr><th style="width:110px">MFO / KRA</th><th>Success Indicators</th><th style="width:100px">Target</th><th>Actual Accomplishment</th><th style="width:90px">Rating (1-5)</th><th>Remarks</th></tr></thead>
        <tbody id="strategicBody"></tbody>
      </table>
    </div>
  </div>

  <!-- Support Function -->
  <div class="mb-3">
    <div class="ipcr-section-header"><i class="fa-solid fa-hands-helping me-2"></i>C. SUPPORT FUNCTION</div>
    <div class="table-responsive">
      <table class="table table-bordered mb-0">
        <thead class="table-light" style="font-size:0.8rem"><tr><th style="width:110px">MFO / KRA</th><th>Success Indicators</th><th style="width:100px">Target</th><th>Actual Accomplishment</th><th style="width:90px">Rating (1-5)</th><th>Remarks</th></tr></thead>
        <tbody id="supportBody"></tbody>
      </table>
    </div>
  </div>

  <!-- Overall Rating Summary -->
  <div class="card mb-3" id="overallRatingCard" style="background:#FFF4E6">
    <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
      <div>
        <h6 class="mb-0 fw-700"><i class="fa-solid fa-calculator me-2 text-primary"></i>Computed Overall Rating</h6>
        <small class="text-muted">Average of all rated performance indicators</small>
      </div>
      <div class="d-flex align-items-center gap-3">
        <span class="fs-4 fw-700 text-primary" id="overallRatingDisplay">-</span>
        <span id="overallRatingLabel"></span>
      </div>
    </div>
  </div>

  <!-- Signature Block (for print) -->
  <div class="row g-3 mt-3">
    <div class="col-4 text-center">
      <div style="border-top:1px solid #333;margin-top:40px;padding-top:5px;font-size:0.82rem">
        <strong id="sigName"></strong><br><span class="text-muted">Ratee</span>
      </div>
    </div>
    <div class="col-4 text-center">
      <div style="border-top:1px solid #333;margin-top:40px;padding-top:5px;font-size:0.82rem">
        <strong>Immediate Supervisor</strong><br><span class="text-muted">Rater</span>
      </div>
    </div>
    <div class="col-4 text-center">
      <div style="border-top:1px solid #333;margin-top:40px;padding-top:5px;font-size:0.82rem">
        <strong>DR. MARIA SANTOS</strong><br><span class="text-muted">Campus Executive Officer</span>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 justify-content-end mt-3 no-print">
    <button class="btn btn-outline-secondary" onclick="showPrintPreview()"><i class="fa-solid fa-print me-1"></i>Print Preview</button>
    <button class="btn btn-outline-primary" onclick="saveIPCR('draft')"><i class="fa-solid fa-floppy-disk me-1"></i>Save Draft</button>
    <button class="btn btn-success" onclick="submitIPCR()"><i class="fa-solid fa-paper-plane me-1"></i>Submit for Review</button>
  </div>
</main>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  initLayout('user', 'ipcr-form', [{ label: 'IPCR Form' }]);

  const session = SESSION_USER;
  let kpi = { core: [], strategic: [], support: [] };
  let activeTimeline = null;
  let existingIpcrId = null;

  document.getElementById('ipcrName').value = session.name;
  document.getElementById('ipcrPosition').value = session.position || '-';
  document.getElementById('ipcrDate').value = new Date().toISOString().split('T')[0];
  document.getElementById('sigName').textContent = session.name.toUpperCase();

  async function initForm() {
    // Load open timeline
    const tlRes = await fetch(API_BASE + 'timeline/list.php?status=open').then(r => r.json()).catch(() => null);
    activeTimeline = tlRes?.timelines?.[0] || null;

    if (activeTimeline) {
      const deadline = new Date(activeTimeline.submission_deadline);
      const daysLeft = Math.ceil((deadline - new Date()) / 86400000);
      if (daysLeft > 0) {
        showToast(`Deadline: ${activeTimeline.submission_deadline} (${daysLeft} day(s) left)`, 'info');
      } else {
        showToast('Submission deadline has passed.', 'warning');
      }
      document.getElementById('ipcrPeriod').value = activeTimeline.semester + ' ' + activeTimeline.academic_year;
      document.getElementById('ipcrStatus').value = 'Draft';

      // Check for existing IPCR on this timeline
      const existRes = await fetch(`${API_BASE}ipcr/get.php?timeline_id=${activeTimeline.id}`).then(r => r.json()).catch(() => null);
      if (existRes?.form) {
        const f = existRes.form;
        existingIpcrId = f.id;
        document.getElementById('ipcrOffice').value = f.department_name || session.department_id || '';
        document.getElementById('ipcrPeriod').value = f.covered_period;
        document.getElementById('ipcrStatus').value = f.status;
        kpi = { core: f.items.core, strategic: f.items.strategic, support: f.items.support };
        loadSection('coreBody', f.items.core);
        loadSection('strategicBody', f.items.strategic);
        loadSection('supportBody', f.items.support);
      } else {
        // Load KPIs for fresh form
        const kpiRes = await fetch(`${API_BASE}kpi/list.php`).then(r => r.json()).catch(() => null);
        if (kpiRes?.grouped) {
          kpi = kpiRes.grouped;
          loadKpiSection('coreBody', kpi.core);
          loadKpiSection('strategicBody', kpi.strategic);
          loadKpiSection('supportBody', kpi.support);
        }
        // Load dept name
        document.getElementById('ipcrOffice').value = session.department_name || session.department_id || '';
      }
    } else {
      // No open timeline — load KPIs anyway for reference
      const kpiRes = await fetch(`${API_BASE}kpi/list.php`).then(r => r.json()).catch(() => null);
      if (kpiRes?.grouped) {
        kpi = kpiRes.grouped;
        loadKpiSection('coreBody', kpi.core);
        loadKpiSection('strategicBody', kpi.strategic);
        loadKpiSection('supportBody', kpi.support);
      }
      document.getElementById('ipcrOffice').value = session.department_name || session.department_id || '';
      document.getElementById('ipcrStatus').value = 'No open timeline';
      showToast('No active submission period is currently open.', 'warning');
    }
    computeOverallRating();
  }

  initForm();

  function loadKpiSection(tbodyId, items) {
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    items.forEach(item => {
      tbody.innerHTML += `<tr>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${item.mfo}</td>
        <td style="font-size:0.82rem;background:#fafafa">${item.successIndicator}</td>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${item.target}</td>
        <td><textarea class="form-control form-control-sm" rows="2" placeholder="Describe your actual accomplishment..."></textarea></td>
        <td><input type="number" class="form-control form-control-sm rating-input" min="1" max="5" step="0.5" placeholder="1-5" data-kpi="${item.id}" oninput="computeOverallRating()"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="Outstanding/VS/Satisfactory..."></td></tr>`;
    });
  }

  function loadSection(tbodyId, items, fromSaved) {
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    const allKpi = [...(kpi.core || []), ...(kpi.strategic || []), ...(kpi.support || [])];
    items.forEach(item => {
      const kpiItem = allKpi.find(k => k.id === item.kpiId) || {};
      tbody.innerHTML += `<tr>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${kpiItem.mfo || '-'}</td>
        <td style="font-size:0.82rem;background:#fafafa">${kpiItem.successIndicator || item.successIndicator || '-'}</td>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${kpiItem.target || '-'}</td>
        <td><textarea class="form-control form-control-sm" rows="2">${item.accomplishment || ''}</textarea></td>
        <td><input type="number" class="form-control form-control-sm rating-input" min="1" max="5" step="0.5" value="${item.rating || ''}" data-kpi="${item.kpiId || ''}" oninput="computeOverallRating()"></td>
        <td><input type="text" class="form-control form-control-sm" value="${item.remarks || ''}"></td></tr>`;
    });
  }

  function getRows(tbodyId, kpiList) {
    const rows = [];
    document.getElementById(tbodyId).querySelectorAll('tr').forEach((tr, i) => {
      const inputs = tr.querySelectorAll('input, textarea');
      // inputs[0]=textarea(accomplishment), inputs[1]=number(rating, has data-kpi), inputs[2]=text(remarks)
      rows.push({
        kpiId: kpiList[i]?.id || inputs[1]?.dataset?.kpi || '',
        accomplishment: inputs[0]?.value || '',
        rating: parseFloat(inputs[1]?.value) || 0,
        remarks: inputs[2]?.value || ''
      });
    });
    return rows;
  }

  function computeOverallRating() {
    const allRatings = document.querySelectorAll('.rating-input');
    let total = 0, count = 0;
    allRatings.forEach(inp => { const v = parseFloat(inp.value); if (!isNaN(v) && v >= 1 && v <= 5) { total += v; count++; } });
    const avg = count > 0 ? (total / count) : 0;
    const el = document.getElementById('overallRatingDisplay');
    const labelEl = document.getElementById('overallRatingLabel');
    if (el) el.textContent = avg > 0 ? avg.toFixed(2) : '-';
    if (labelEl) labelEl.innerHTML = avg > 0 ? getRatingLabel(avg) : '';
  }

  async function saveIPCR(action = 'draft') {
    const period = document.getElementById('ipcrPeriod').value.trim();
    if (!period) { showToast('Please enter the covered period.', 'warning'); return; }
    if (!activeTimeline) { showToast('No open submission period found.', 'warning'); return; }

    const payload = {
      action,
      ipcr_id: existingIpcrId || 0,
      timeline_id: activeTimeline.id,
      covered_period: period,
      core:      getRows('coreBody',      kpi.core      || []),
      strategic: getRows('strategicBody', kpi.strategic || []),
      support:   getRows('supportBody',   kpi.support   || []),
    };

    try {
      const res = await fetch(API_BASE + 'ipcr/save.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (data.success) {
        existingIpcrId = data.ipcr_id;
        document.getElementById('ipcrStatus').value = data.status;
        showToast(data.message, 'success');
      } else {
        showToast(data.error, 'danger');
      }
    } catch {
      showToast('Server error. Make sure XAMPP is running.', 'danger');
    }
  }

  // Preload logo for print preview
  let _printLogo = '';
  fetch('../../assets/images/csu-logo.png')
    .then(r => r.blob()).then(b => {
      const rd = new FileReader();
      rd.onload = ev => { _printLogo = ev.target.result; };
      rd.readAsDataURL(b);
    }).catch(() => {});

  function showPrintPreview() {
    const name   = document.getElementById('ipcrName').value.trim();
    const pos    = document.getElementById('ipcrPosition').value.trim();
    const office = document.getElementById('ipcrOffice').value.trim();
    const period = document.getElementById('ipcrPeriod').value.trim();
    const date   = document.getElementById('ipcrDate').value;

    if (!period) { showToast('Please enter the covered period before previewing.', 'warning'); return; }

    function esc(s) {
      return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function getFormRows(tbodyId) {
      const rows = [];
      document.getElementById(tbodyId).querySelectorAll('tr').forEach(tr => {
        const tds = tr.querySelectorAll('td');
        const inputs = tr.querySelectorAll('input, textarea');
        rows.push({
          mfo:     tds[0]?.textContent?.trim() || '',
          si:      tds[1]?.textContent?.trim() || '',
          target:  tds[2]?.textContent?.trim() || '',
          actual:  inputs[0]?.value || '',
          rating:  parseFloat(inputs[1]?.value) || '',
          remarks: inputs[2]?.value || ''
        });
      });
      return rows;
    }

    const core      = getFormRows('coreBody');
    const strategic = getFormRows('strategicBody');
    const support   = getFormRows('supportBody');
    const allRows   = [...core, ...strategic, ...support];
    const avgs      = allRows.map(r => r.rating).filter(v => v > 0);
    const finalAvg  = avgs.length ? parseFloat((avgs.reduce((a,b) => a+b,0) / avgs.length).toFixed(2)) : 0;

    function adj(avg) {
      if (avg >= 4.5) return 'Outstanding';
      if (avg >= 3.5) return 'Very Satisfactory';
      if (avg >= 2.5) return 'Satisfactory';
      if (avg >= 1.5) return 'Unsatisfactory';
      if (avg > 0)    return 'Poor';
      return '';
    }

    function buildRows(rows, minRows) {
      let html = '';
      const total = Math.max(rows.length, minRows);
      for (let i = 0; i < total; i++) {
        const r = rows[i] || {};
        const rat = r.rating || '';
        html += `<tr class="data-row">
          <td>${esc(r.mfo)}</td>
          <td>${esc(r.si)}</td>
          <td class="tc">${esc(r.target)}</td>
          <td>${esc(name)}</td>
          <td>${esc(r.actual)}</td>
          <td class="tc">${rat}</td>
          <td class="tc">${rat}</td>
          <td class="tc">${rat}</td>
          <td class="tc b">${rat}</td>
          <td>${esc(r.remarks)}</td>
        </tr>`;
      }
      return html;
    }

    const logoTag = _printLogo
      ? `<img src="${_printLogo}" class="logo" alt="CSU Logo">`
      : `<div class="logo-ph"></div>`;

    const html = `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IPCR — ${esc(name)}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Times New Roman',Times,serif; font-size:7.8pt; color:#000; background:#fff; }
@page { size:letter landscape; margin:.35in .3in; }
@media print { .no-print{display:none!important;} }
.no-print { position:fixed;top:10px;right:14px;z-index:999;display:flex;gap:8px; }
.no-print button { padding:7px 16px;font-size:12px;border:none;border-radius:4px;cursor:pointer;font-family:sans-serif;font-weight:600; }
.btn-pdf { background:#c0392b;color:#fff; }
.btn-cls { background:#555;color:#fff; }
.form-outer { border:1.5px solid #000;width:100%; }
table { width:100%; border-collapse:collapse; }
td, th { border:1px solid #000; padding:1.5px 3px; vertical-align:middle; font-size:7.8pt; }
.tc { text-align:center; } .b { font-weight:700; }
.hdr-row { padding:5px 8px 4px; position:relative; border-bottom:1px solid #000; }
.annex { position:absolute;top:5px;right:8px;font-size:7.5pt; }
.hdr-inner { display:flex;align-items:center;justify-content:center;gap:8px; }
.logo { width:46px;height:46px;object-fit:contain; }
.logo-ph { width:46px;height:46px;background:#ddd;border-radius:50%; }
.univ-text { text-align:center;line-height:1.5; }
.univ-text .republic { font-size:7.5pt; }
.univ-text .univ { font-size:9.5pt;font-weight:700; }
.univ-text .campus { font-size:7.5pt; }
.form-title { text-align:center;font-weight:700;font-size:9pt;text-decoration:underline;margin-top:4px;padding-bottom:2px; }
.div-field { text-align:center;padding:3px 0 2px;border-bottom:1px solid #000; }
.uline { display:inline-block;border-bottom:1px solid #000;min-width:180px;font-size:7.8pt; }
.field-lbl { font-size:6.5pt;display:block;margin-top:1px; }
.commit-wrap { display:table;width:100%;border-bottom:1px solid #000; }
.commit-left { display:table-cell;width:73%;padding:4px 8px;vertical-align:top;line-height:1.7;font-size:7.8pt; }
.commit-right { display:table-cell;width:27%;padding:4px 8px;vertical-align:bottom;border-left:1px solid #000;text-align:center; }
.sig-line { display:block;border-top:1px solid #000;margin:28px auto 1px;width:80%;font-size:7pt; }
.date-line { font-size:7.5pt;margin-top:4px; }
.rev-table { border-top:none; }
.rev-table th { background:#fff;font-weight:700;font-size:7.5pt;text-align:center;padding:2px 4px; }
.rev-table td { font-size:7.5pt;padding:3px 5px;vertical-align:bottom; }
.rev-name { font-weight:700;font-size:7.8pt; }
.rev-role { font-size:6.5pt;font-style:italic; }
.legend-wrap { display:table;width:100%;border-top:none;border-bottom:none; }
.legend-blank { display:table-cell;width:38%;border-right:1px solid #000; }
.legend-right { display:table-cell;width:62%; }
.legend-right table { border:none; }
.legend-right td { border:none;border-bottom:1px solid #ccc;font-size:7.3pt;padding:1px 3px; }
.legend-right td:first-child { font-weight:700;text-align:center;border-right:1px solid #000;width:20px;border-left:1px solid #000; }
.legend-right tr:first-child td { border-top:1px solid #000; }
.legend-right tr:last-child td { border-bottom:1px solid #000; }
.data-table { border-top:1px solid #000; }
.data-table th { background:#d9d9d9;font-weight:700;text-align:center;font-size:7.3pt;padding:2px 3px; }
.data-table .sec-row td { background:#bdd7ee;font-weight:700;font-size:7.8pt;text-align:left;padding:2px 5px; }
.data-table .data-row td { height:18px;font-size:7.5pt;padding:1px 3px;vertical-align:top; }
.summary-table td { border:1px solid #000;padding:1.5px 5px;font-size:7.5pt; }
.summary-table .lbl { font-weight:700;font-size:7.5pt; }
.summary-table .val { text-align:center;font-weight:700; }
.sig-tbl th { background:#fff;font-weight:700;text-align:center;font-size:7.3pt;border:1px solid #000;padding:2px 4px; }
.sig-tbl td { border:1px solid #000;padding:2px 4px;font-size:7.3pt;vertical-align:top; }
.sig-tbl .certify { font-style:italic;font-size:7pt;text-align:center; }
.sig-tbl .sig-name-cell { font-weight:700;text-align:center; }
.legend-note { font-size:6.5pt;padding:2px 5px;font-style:italic; }
</style>
</head>
<body>
<div class="no-print">
  <button class="btn-pdf" onclick="window.print()">&#128438; Print / Save as PDF</button>
  <button class="btn-cls" onclick="window.close()">&#x2715; Close</button>
</div>
<div class="form-outer">
  <div class="hdr-row">
    <div class="annex">ANNEX A</div>
    <div class="hdr-inner">
      ${logoTag}
      <div class="univ-text">
        <div class="republic">Republic of the Philippines</div>
        <div class="univ">CAGAYAN STATE UNIVERSITY</div>
        <div class="campus">Piat Campus, Piat, Cagayan</div>
      </div>
    </div>
    <div class="form-title">INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW FORM (IPCR)</div>
  </div>
  <div class="div-field">
    <span class="uline">&nbsp;${esc(office)}&nbsp;</span>
    <span class="field-lbl">Division/Office/College</span>
  </div>
  <div class="commit-wrap">
    <div class="commit-left">
      I,&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${esc(name)}</span>,&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${esc(pos)}</span>,
      commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measures for<br>
      the period&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${esc(period)}</span>.
    </div>
    <div class="commit-right">
      <span class="sig-line">${esc(name)}<br><span style="font-size:6.5pt;font-style:italic">(name of employee)</span></span>
      <div class="date-line">Date:&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${esc(date)}</span></div>
    </div>
  </div>
  <table class="rev-table">
    <tr>
      <th style="width:35%">REVIEWED BY</th>
      <th style="width:10%">DATE</th>
      <th style="width:45%">APPROVED BY</th>
      <th style="width:10%">DATE</th>
    </tr>
    <tr>
      <td style="height:32px;vertical-align:bottom">
        <div class="rev-name">&nbsp;</div>
        <div class="rev-role">(immediate supervisor)</div>
      </td>
      <td>&nbsp;</td>
      <td style="text-align:center;vertical-align:middle">
        <div class="rev-name">HITLER C. DANGATAN, Ph.D.</div>
        <div class="rev-role">Campus Executive Officer</div>
      </td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <div class="legend-wrap" style="border-top:1px solid #000;">
    <div class="legend-blank">&nbsp;</div>
    <div class="legend-right">
      <table>
        <tr><td>R</td><td>5 – Outstanding &nbsp;- performance exceeded expectation by 30% and above of planned target</td></tr>
        <tr><td>A</td><td>4 – Very Satisfactory &nbsp;- performance exceeded expectations by 15% to 29% of planned targets</td></tr>
        <tr><td>T</td><td>3 – Satisfactory &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- performance met 90% to 114% of the planned targets</td></tr>
        <tr><td>I</td><td>2 – Unsatisfactory &nbsp;&nbsp;- performance only met 51% to 89% of planned targets and failed to deliver one or</td></tr>
        <tr><td>N</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;more critical aspects of the targets</td></tr>
        <tr><td>G</td><td>1 – Poor &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- performance failed to deliver most of the targets by 50% and below</td></tr>
      </table>
    </div>
  </div>
  <table class="data-table">
    <colgroup>
      <col style="width:18%"><col style="width:20%"><col style="width:8%">
      <col style="width:10%"><col style="width:17%">
      <col style="width:3%"><col style="width:3%"><col style="width:3%"><col style="width:3%">
      <col style="width:15%">
    </colgroup>
    <thead>
      <tr>
        <th rowspan="2">MFO/KRA</th>
        <th rowspan="2">SUCCESS INDICATORS<br>(TARGET + MEASURE)</th>
        <th rowspan="2">TARGET</th>
        <th rowspan="2">INDIVIDUALS<br>ACCOUNTABLE</th>
        <th rowspan="2">ACTUAL<br>ACCOMPLISHMENTS</th>
        <th colspan="4">RATING</th>
        <th rowspan="2">REMARKS</th>
      </tr>
      <tr>
        <th>Q<sup>1</sup></th><th>E<sup>2</sup></th><th>T<sup>3</sup></th><th>A<sup>4</sup></th>
      </tr>
    </thead>
    <tbody>
      <tr class="sec-row"><td colspan="10">A. CORE FUNCTION</td></tr>
      ${buildRows(core, 4)}
      <tr class="sec-row"><td colspan="10">B. STRATEGIC FUNCTION</td></tr>
      ${buildRows(strategic, 3)}
      <tr class="sec-row"><td colspan="10">C. SUPPORT FUNCTION</td></tr>
      ${buildRows(support, 3)}
    </tbody>
  </table>
  <table class="summary-table">
    <tr><td class="lbl" style="width:20%">AVERAGE RATING:</td><td class="val">${finalAvg||''}</td></tr>
    <tr><td class="lbl">FINAL AVERAGE RATING:</td><td class="val">${finalAvg||''}</td></tr>
    <tr><td class="lbl">ADJECTIVAL RATING:</td><td class="val">${finalAvg ? adj(finalAvg) : ''}</td></tr>
  </table>
  <table class="sig-tbl">
    <tr>
      <th style="width:18%">DISCUSSED WITH</th>
      <th style="width:9%">DATE</th>
      <th style="width:28%">ASSESSED BY</th>
      <th style="width:9%">DATE</th>
      <th style="width:27%">FINAL RATING BY</th>
      <th style="width:9%">DATE</th>
    </tr>
    <tr style="height:52px">
      <td>&nbsp;</td><td>&nbsp;</td>
      <td class="certify">I certify that I discussed my assessment of the performance with the employee</td>
      <td>&nbsp;</td>
      <td class="sig-name-cell">HITLER C. DANGATAN, Ph.D.</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="sig-name-cell" style="border-top:1px solid #aaa">${esc(name)}</td>
      <td>&nbsp;</td>
      <td class="sig-name-cell" style="border-top:1px solid #aaa">(immediate supervisor)</td>
      <td>&nbsp;</td>
      <td class="sig-name-cell" style="border-top:1px solid #aaa">Campus Executive Officer</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="6" class="legend-note">Legend: 1:Quality &nbsp; 2:Efficiency &nbsp; 3:Timeliness &nbsp; 4:Average</td>
    </tr>
  </table>
</div>
<script>setTimeout(()=>window.print(),700);<\/script>
</body>
</html>`;

    const w = window.open('', '_blank');
    if (!w) { showToast('Please allow popups for this site to use Print Preview.', 'warning'); return; }
    w.document.write(html);
    w.document.close();
  }

  function submitIPCR() {
    confirmModal('Are you sure you want to submit your IPCR for review?', 'Submit IPCR', async () => {
      await saveIPCR('submit');
      setTimeout(() => window.location.href = 'status.php', 1000);
    });
  }
</script>
</body>
</html>
