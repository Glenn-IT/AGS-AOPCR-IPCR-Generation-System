<?php
require_once '../../config/session.php';
$user = requireAuth(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Status | CSU-Piat</title>
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
    <h2><i class="fa-solid fa-eye me-2 text-primary"></i>View Status</h2>
    <p>Track the status of your submitted IPCR forms as Administrator / Dean.</p>
  </div>

  <!-- Status Summary -->
  <div class="row g-3 mb-4" id="statusCards"></div>

  <!-- Timeline Status -->
  <div class="card mb-4" id="submissionCard" style="display:none">
    <div class="card-header"><h6><i class="fa-solid fa-timeline me-2 text-primary"></i>Submission Progress</h6></div>
    <div class="card-body">
      <div class="status-steps" id="statusSteps"></div>
      <div class="mt-3 p-3 rounded" id="statusMessage" style="background:var(--accent)"></div>
    </div>
  </div>

  <!-- Forms Table -->
  <div class="table-wrapper">
    <div class="table-header">
      <h6><i class="fa-solid fa-list me-2"></i>My IPCR Submissions</h6>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr><th>#</th><th>Rating Period</th><th>Date Submitted</th><th>Overall Rating</th><th>Rating Label</th><th>Status</th><th>Action</th></tr></thead>
        <tbody id="statusTable"></tbody>
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
        <a href="ipcr-form.php" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-edit me-1"></i>Edit Form</a>
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
  initLayout('admin', 'status', [{ label: 'View Status' }]);

  const session = SESSION_USER;

  // Preload logo for print preview
  let _printLogo = '';
  fetch('../../assets/images/csu-logo.png')
    .then(r => r.blob()).then(b => {
      const rd = new FileReader();
      rd.onload = ev => { _printLogo = ev.target.result; };
      rd.readAsDataURL(b);
    }).catch(() => {});

  let _currentIPCR = null;

  async function loadStatus() {
    const [listRes] = await Promise.all([
      fetch(API_BASE + 'ipcr/list.php?my_submissions=1', { credentials: 'include' }).then(r => r.json()).catch(() => ({ forms: [] })),
    ]);
    const ipcrs = listRes.forms || [];

    // Status Cards
    const statuses = [
      { key: 'pending',     label: 'Pending',     color: 'warning', icon: 'fa-clock' },
      { key: 'reviewed',    label: 'Reviewed',    color: 'info',    icon: 'fa-eye' },
      { key: 'approved',    label: 'Approved',    color: 'success', icon: 'fa-check-circle' },
      { key: 'disapproved', label: 'Disapproved', color: 'danger',  icon: 'fa-times-circle' },
    ];
    const cardsContainer = document.getElementById('statusCards');
    cardsContainer.innerHTML = '';
    statuses.forEach(s => {
      const count = ipcrs.filter(f => f.status === s.key).length;
      cardsContainer.innerHTML += `<div class="col-sm-6 col-xl-3">
        <div class="stat-card ${s.color}">
          <div class="stat-icon"><i class="fa-solid ${s.icon}"></i></div>
          <div class="stat-value">${count}</div>
          <div class="stat-label">${s.label} Forms</div>
        </div></div>`;
    });

    // Latest form progress
    if (ipcrs.length > 0) {
      const latest = ipcrs[0];
      const statusOrder = ['pending', 'reviewed', 'approved'];
      const disapproved = latest.status === 'disapproved';
      document.getElementById('submissionCard').style.display = '';

      const stepDefs = ['Submitted', 'Under Review', 'Final Decision'];
      const currentIdx = disapproved ? 2 : statusOrder.indexOf(latest.status);
      document.getElementById('statusSteps').innerHTML = stepDefs.map((label, i) => {
        const isDone = i < currentIdx, isActive = i === currentIdx;
        const icon = isDone ? '<i class="fa-solid fa-check"></i>' : (i + 1);
        return `<div class="step-item">
          <div class="step-circle ${isDone ? 'done' : isActive ? 'active' : ''}">${icon}</div>
          <div class="step-label ${isActive ? 'active' : ''}">${label}</div>
        </div>`;
      }).join('');

      const rating = parseFloat(latest.overall_rating) || 0;
      const msgMap = {
        pending:     `<i class="fa-solid fa-clock text-warning me-2"></i><strong>Your IPCR is pending review.</strong> Submitted for review by the Campus Executive Officer. Period: <strong>${latest.covered_period}</strong>`,
        reviewed:    `<i class="fa-solid fa-search text-primary me-2"></i><strong>Your IPCR is under review.</strong> Please wait for final approval.`,
        approved:    `<i class="fa-solid fa-check-circle text-success me-2"></i><strong>Your IPCR has been approved!</strong> Overall Rating: <strong>${rating.toFixed(2)}</strong>. ${getRatingLabel(rating)}`,
        disapproved: `<i class="fa-solid fa-times-circle text-danger me-2"></i><strong>Your IPCR was disapproved.</strong> Remarks: ${latest.remarks || 'No remarks.'}`,
      };
      document.getElementById('statusMessage').innerHTML = msgMap[latest.status] || '';
    } else {
      document.getElementById('submissionCard').style.display = 'none';
    }

    // Table
    const tbody = document.getElementById('statusTable');
    if (ipcrs.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fa-solid fa-inbox me-2"></i>No IPCR forms submitted yet. <a href="ipcr-form.php">Submit one now.</a></td></tr>`;
    } else {
      tbody.innerHTML = '';
      ipcrs.forEach((f, i) => {
        const rating = parseFloat(f.overall_rating) || 0;
        tbody.innerHTML += `<tr>
          <td>${i+1}</td>
          <td style="font-size:0.85rem">${f.covered_period}</td>
          <td style="font-size:0.82rem">${f.date_submitted || '-'}</td>
          <td style="font-size:0.85rem">${rating > 0 ? rating.toFixed(2) : '-'}</td>
          <td>${rating > 0 ? getRatingLabel(rating) : '-'}</td>
          <td>${getStatusBadge(f.status)}</td>
          <td><button class="btn btn-outline-primary btn-sm" onclick="viewDetail(${f.id})"><i class="fa-solid fa-eye"></i></button></td></tr>`;
      });
    }
  }

  loadStatus();

  async function viewDetail(id) {
    const res = await fetch(API_BASE + 'ipcr/get.php?id=' + id, { credentials: 'include' }).then(r => r.json()).catch(() => null);
    if (!res?.form) { showToast('Could not load form details.', 'danger'); return; }
    _currentIPCR = res.form;
    const f = res.form;
    const rating = parseFloat(f.overall_rating) || 0;
    const secLabels = { core: 'Core Function', strategic: 'Strategic Function', support: 'Support Function' };
    let html = `<div class="row g-2 mb-3 p-2 bg-light rounded" style="font-size:0.85rem">
      <div class="col-6"><strong>Name:</strong> ${f.user_name}</div>
      <div class="col-6"><strong>Department:</strong> ${f.department_name || '-'}</div>
      <div class="col-6"><strong>Position:</strong> ${f.position || '-'}</div>
      <div class="col-6"><strong>Rating Period:</strong> ${f.covered_period}</div>
      <div class="col-6"><strong>Status:</strong> ${getStatusBadge(f.status)}</div>
      <div class="col-6"><strong>Rating:</strong> ${rating > 0 ? rating.toFixed(2) : '-'} ${rating > 0 ? '— ' + getRatingLabel(rating) : ''}</div>
    </div>
    <div class="card mb-3 border-primary shadow-sm">
      <div class="card-header py-2 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#FFF4E6">
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-scale-balanced text-primary"></i>
          <strong style="font-size:0.85rem">WEIGHT According to ETL: <span class="badge bg-primary ms-1">${f.etl_type || "6 ETL"}</span></strong>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size:0.8rem">
          <span class="badge bg-white text-dark border px-2 py-1">Core: <strong>${f.weight_core || 50}%</strong> (× ${((f.weight_core || 50)/100).toFixed(2)})</span>
          <span class="badge bg-white text-dark border px-2 py-1">Strategic: <strong>${f.weight_strategic || 25}%</strong> (× ${((f.weight_strategic || 25)/100).toFixed(2)})</span>
          <span class="badge bg-white text-dark border px-2 py-1">Support: <strong>${f.weight_support || 25}%</strong> (× ${((f.weight_support || 25)/100).toFixed(2)})</span>
        </div>
      </div>
    </div>`;
    ['core', 'strategic', 'support'].forEach(sec => {
      const items = f.items[sec];
      if (!items?.length) return;
      html += `<div class="ipcr-section-header mb-0">${secLabels[sec]}</div>
        <table class="table table-sm mb-3"><thead><tr><th>Indicator</th><th>Accomplishment</th><th>Rating</th><th>Remarks</th></tr></thead><tbody>
        ${items.map(item => `<tr>
          <td style="font-size:0.8rem">${item.success_indicator || '-'}</td>
          <td style="font-size:0.8rem;text-align:center">${item.accomplishment !== null && item.accomplishment !== '' ? (isNaN(item.accomplishment) ? item.accomplishment : item.accomplishment + '%') : '-'}</td>
          <td style="font-size:0.8rem;text-align:center">${item.rating || '-'}</td>
          <td style="font-size:0.8rem">${item.remarks || '-'}</td></tr>`).join('')}
        </tbody></table>`;
    });
    document.getElementById('detailBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('detailModal')).show();
  }

  function showPrintPreview() {
    const f = _currentIPCR;
    if (!f) { showToast('No IPCR selected.', 'warning'); return; }

    function ep(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function adj(v) {
      if(v>=4.5)return'Outstanding';if(v>=3.5)return'Very Satisfactory';
      if(v>=2.5)return'Satisfactory';if(v>=1.5)return'Unsatisfactory';
      if(v>0)return'Poor';return'';
    }

    function buildRows(items, minRows) {
      let html = '';
      const total = Math.max((items||[]).length, minRows);
      for (let i = 0; i < total; i++) {
        const item = (items||[])[i] || {};
        const q = item.q_rating || '';
        const e = item.e_rating || '';
        const t = item.t_rating || '';
        let formattedAcc = '';
        if (item.accomplishment !== null && item.accomplishment !== undefined && String(item.accomplishment).trim() !== '') {
          const accStr = String(item.accomplishment).trim();
          if (accStr.includes('%')) {
            formattedAcc = accStr;
          } else if (/%/.test(String(item.target || '')) && !isNaN(accStr)) {
            formattedAcc = accStr + '%';
          } else {
            formattedAcc = accStr;
          }
        html += `<tr class="data-row">
          <td>${ep(item.mfo)}</td>
          <td>${ep(item.success_indicator)}</td>
          <td class="tc">${ep(item.target)}</td>
          <td class="tc">${item.budget !== undefined && item.budget !== null ? item.budget : ''}</td>
          <td>${ep(f.user_name)}</td>
          <td class="tc">${ep(formattedAcc)}</td>
          <td class="tc">${q}</td><td class="tc">${e}</td><td class="tc">${t}</td><td class="tc b">${avg}</td>
          <td>${ep(item.remarks)}</td>
        </tr>`;
      }
      return html;
    }

    const finalAvg = parseFloat(f.overall_rating) || 0;
    const etlName  = f.etl_type || '6 ETL';
    const wCore    = (f.weight_core ? parseFloat(f.weight_core) : 50) / 100;
    const wStrat   = (f.weight_strategic ? parseFloat(f.weight_strategic) : 25) / 100;
    const wSupp    = (f.weight_support ? parseFloat(f.weight_support) : 25) / 100;

    const coreItems = f.items?.core || [];
    const stratItems = f.items?.strategic || [];
    const suppItems = f.items?.support || [];

    const cAvgs = coreItems.map(r => parseFloat(r.rating)).filter(v => v > 0);
    const coreAvg = cAvgs.length ? (cAvgs.reduce((a,b) => a+b, 0) / cAvgs.length) : null;

    const sAvgs = stratItems.map(r => parseFloat(r.rating)).filter(v => v > 0);
    const stratAvg = sAvgs.length ? (sAvgs.reduce((a,b) => a+b, 0) / sAvgs.length) : null;

    const supAvgs = suppItems.map(r => parseFloat(r.rating)).filter(v => v > 0);
    const suppAvg = supAvgs.length ? (supAvgs.reduce((a,b) => a+b, 0) / supAvgs.length) : null;

    const coreWeighted = coreAvg !== null ? (coreAvg * wCore) : null;
    const stratWeighted = stratAvg !== null ? (stratAvg * wStrat) : null;
    const suppWeighted = suppAvg !== null ? (suppAvg * wSupp) : null;
    const logoTag = _printLogo
      ? `<img src="${_printLogo}" class="logo" alt="CSU Logo">`
      : `<div class="logo-ph"></div>`;

    const html = `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IPCR — ${ep(f.user_name)}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Times New Roman',Times,serif; font-size:7.8pt; color:#000; background:#fff; }
@page { size:letter landscape; margin:.35in .3in; }
@media print { .no-print{display:none!important;} }
.no-print { position:fixed;top:10px;right:14px;z-index:999;display:flex;gap:8px; }
.no-print button { padding:7px 16px;font-size:12px;border:none;border-radius:4px;cursor:pointer;font-family:sans-serif;font-weight:600; }
.btn-pdf { background:#c0392b;color:#fff; } .btn-cls { background:#555;color:#fff; }
.form-outer { border:1.5px solid #000;width:100%; }
table { width:100%; border-collapse:collapse; }
td, th { border:1px solid #000; padding:1.5px 3px; vertical-align:middle; font-size:7.8pt; }
.tc { text-align:center; } .b { font-weight:700; }
.hdr-row { padding:5px 8px 4px; position:relative; border-bottom:1px solid #000; }
.annex { position:absolute;top:5px;right:8px;font-size:7.5pt; }
.hdr-inner { display:flex;align-items:center;justify-content:center;gap:8px; }
.logo { width:46px;height:46px;object-fit:contain; } .logo-ph { width:46px;height:46px;background:#ddd;border-radius:50%; }
.univ-text { text-align:center;line-height:1.5; }
.univ-text .republic { font-size:7.5pt; } .univ-text .univ { font-size:9.5pt;font-weight:700; } .univ-text .campus { font-size:7.5pt; }
.form-title { text-align:center;font-weight:700;font-size:9pt;text-decoration:underline;margin-top:4px;padding-bottom:2px; }
.div-field { text-align:center;padding:3px 0 2px;border-bottom:1px solid #000; }
.uline { display:inline-block;border-bottom:1px solid #000;min-width:180px;font-size:7.8pt; }
.field-lbl { font-size:6.5pt;display:block;margin-top:1px; }
.commit-wrap { display:table;width:100%;border-bottom:1px solid #000; }
.commit-left { display:table-cell;width:73%;padding:4px 8px;vertical-align:top;line-height:1.7;font-size:7.8pt; }
.commit-right { display:table-cell;width:27%;padding:4px 8px;vertical-align:bottom;border-left:1px solid #000;text-align:center; }
.sig-line { display:block;border-top:1px solid #000;margin:28px auto 1px;width:80%;font-size:7pt; }
.date-line { font-size:7.5pt;margin-top:4px; }
.rev-table th { background:#fff;font-weight:700;font-size:7.5pt;text-align:center;padding:2px 4px; }
.rev-table td { font-size:7.5pt;padding:3px 5px;vertical-align:bottom; }
.rev-name { font-weight:700;font-size:7.8pt; } .rev-role { font-size:6.5pt;font-style:italic; }
.legend-wrap { display:table;width:100%;border-top:none; }
.legend-blank { display:table-cell;width:38%;border-right:1px solid #000; }
.legend-right { display:table-cell;width:62%; }
.legend-right table { border:none; }
.legend-right td { border:none;border-bottom:1px solid #ccc;font-size:7.3pt;padding:1px 3px; }
.legend-right td:first-child { font-weight:700;text-align:center;border-right:1px solid #000;width:20px;border-left:1px solid #000; }
.legend-right tr:first-child td { border-top:1px solid #000; }
.legend-right tr:last-child td { border-bottom:1px solid #000; }
.data-table { border-top:1px solid #000; }
.data-table th { background:#d9d9d9;font-weight:700;text-align:center;font-size:7.3pt;padding:2px 3px; }
.data-table thead { display:table-header-group; }
.data-table .sec-row td { background:#fed7aa;font-weight:700;font-size:7.8pt;text-align:left;padding:2px 5px;page-break-after:avoid; }
.data-table .data-row td { height:18px;font-size:7.5pt;padding:1px 3px;vertical-align:top; }
.data-table .data-row { page-break-inside:avoid; }
.summary-table td { border:1px solid #000;padding:1.5px 5px;font-size:7.5pt; }
.summary-table .lbl { font-weight:700; } .summary-table .val { text-align:center;font-weight:700; }
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
    <span class="uline">&nbsp;${ep(f.department_name)}&nbsp;</span>
    <span class="field-lbl">Division/Office/College</span>
  </div>
  <div class="commit-wrap">
    <div class="commit-left">
      I,&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${ep(f.user_name)}</span>,&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${ep(f.position)}</span>,
      commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measures for<br>
      the period&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${ep(f.covered_period)}</span>.
    </div>
    <div class="commit-right">
      <span class="sig-line">${ep(f.user_name)}<br><span style="font-size:6.5pt;font-style:italic">(name of employee)</span></span>
      <div class="date-line">Date:&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${ep(f.date_submitted)}</span></div>
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
      <td style="height:32px;vertical-align:bottom;text-align:center">
        <div class="rev-name">HITLER C. DANGATAN, Ph.D.</div><div class="rev-role">(Campus Executive Officer)</div>
      </td>
      <td>&nbsp;</td>
      <td style="text-align:center;vertical-align:middle">
        <div class="rev-name">University President</div><div class="rev-role">CSU System Administration</div>
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
      <col style="width:16%"><col style="width:18%"><col style="width:8%">
      <col style="width:8%"><col style="width:10%"><col style="width:15%">
      <col style="width:3%"><col style="width:3%"><col style="width:3%"><col style="width:3%">
      <col style="width:13%">
    </colgroup>
    <thead>
      <tr>
        <th rowspan="2">MFO/PAP</th>
        <th colspan="2" rowspan="2">SUCCESS INDICATORS<br>(TARGET + MEASURES)</th>
        <th rowspan="2">ALLOTTED<br>BUDGET</th>
        <th rowspan="2">INDIVIDUALS ACCOUNTABLE</th>
        <th rowspan="2">ACTUAL<br>ACCOMPLISHMENTS</th>
        <th colspan="4">RATING</th>
        <th rowspan="2">REMARKS</th>
      </tr>
      <tr><th>Q<sup>1</sup></th><th>E<sup>2</sup></th><th>T<sup>3</sup></th><th>A<sup>4</sup></th></tr>
    </thead>
    <tbody>
      <tr class="sec-row"><td colspan="11">A. CORE FUNCTION</td></tr>
      ${buildRows(f.items?.core, 4)}
      <tr class="data-row">
        <td colspan="9" style="font-weight:700;text-align:left;padding-left:8px;">Average Rating</td>
        <td class="tc b">${coreAvg !== null ? coreAvg.toFixed(2) : ''}</td>
        <td>&nbsp;</td>
      </tr>
      <tr class="data-row">
        <td colspan="9" style="font-weight:700;text-align:left;padding-left:8px;">Weighted Average Rating</td>
        <td class="tc b">${coreWeighted !== null ? coreWeighted.toFixed(2) : ''}</td>
        <td>&nbsp;</td>
      </tr>
      <tr class="sec-row"><td colspan="11">B. STRATEGIC FUNCTION</td></tr>
      ${buildRows(f.items?.strategic, 3)}
      <tr class="data-row">
        <td colspan="9" style="font-weight:700;text-align:left;padding-left:8px;">Average Rating</td>
        <td class="tc b">${stratAvg !== null ? stratAvg.toFixed(2) : ''}</td>
        <td>&nbsp;</td>
      </tr>
      <tr class="data-row">
        <td colspan="9" style="font-weight:700;text-align:left;padding-left:8px;">Weighted Average Rating</td>
        <td class="tc b">${stratWeighted !== null ? stratWeighted.toFixed(2) : ''}</td>
        <td>&nbsp;</td>
      </tr>
      <tr class="sec-row"><td colspan="11">C. SUPPORT FUNCTION</td></tr>
      ${buildRows(f.items?.support, 3)}
      <tr class="data-row">
        <td colspan="9" style="font-weight:700;text-align:left;padding-left:8px;">Average Rating</td>
        <td class="tc b">${suppAvg !== null ? suppAvg.toFixed(2) : ''}</td>
        <td>&nbsp;</td>
      </tr>
      <tr class="data-row">
        <td colspan="9" style="font-weight:700;text-align:left;padding-left:8px;">Weighted Average Rating</td>
        <td class="tc b">${suppWeighted !== null ? suppWeighted.toFixed(2) : ''}</td>
        <td>&nbsp;</td>
      </tr>
    </tbody>
  </table>
  <table class="summary-table" style="page-break-inside:avoid">
    <tr>
      <td class="lbl" style="width:40%">AVERAGE RATING:</td>
      <td class="val" style="width:60%" colspan="3">${finalAvg > 0 ? finalAvg.toFixed(2) : ''}</td>
    </tr>
    <tr>
      <td class="lbl">FINAL AVERAGE RATING:</td>
      <td class="val" colspan="3">${finalAvg > 0 ? finalAvg.toFixed(2) : ''}</td>
    </tr>
    <tr style="background:#f4f4f4">
      <td class="lbl">ADJECTIVAL RATING:</td>
      <td class="val" colspan="3" style="font-size:8.5pt">${finalAvg > 0 ? adj(finalAvg) : ''}</td>
    </tr>
    <tr>
      <td class="lbl" colspan="4">COMMENTS AND RECOMMENDATION:</td>
    </tr>
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
      <td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    <tr>
      <td class="sig-name-cell" style="border-top:1px solid #aaa;text-align:center">
        <div class="rev-name">${ep(f.user_name)}</div>
        <div class="rev-role">(name of employee)</div>
      </td>
      <td>&nbsp;</td>
      <td class="sig-name-cell" style="border-top:1px solid #aaa;text-align:center">
        <div class="rev-name">HITLER C. DANGATAN, Ph.D.</div>
        <div class="rev-role">(Campus Executive Officer)</div>
      </td>
      <td>&nbsp;</td>
      <td class="sig-name-cell" style="border-top:1px solid #aaa;text-align:center">
        <div class="rev-name">University President</div>
        <div class="rev-role" style="font-style:normal;text-transform:uppercase">CSU SYSTEM ADMINISTRATION</div>
      </td>
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

</script>
</body>
</html>
