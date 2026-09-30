<?php
require_once '../../config/session.php';
$user = requireAuth(['superadmin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accomplishment & Ratings | CSU-Piat</title>
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
      <h2><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Accomplishment & Ratings</h2>
      <p>Rate the IPCR submissions of Deans and Department Heads.</p>
    </div>
    <div class="d-flex gap-2 no-print">
      <button class="btn btn-outline-primary btn-sm d-none" id="btnViewEvidence" onclick="openEvidenceModal()"><i class="fa-solid fa-paperclip me-1"></i>View Evidence <span class="badge bg-primary text-white ms-1" id="evidenceCountBadge">0</span></button>
      <button class="btn btn-primary btn-sm" onclick="saveRatings()"><i class="fa-solid fa-save me-1"></i>Save Ratings</button>
    </div>
  </div>

  <!-- Select Dean/Head/Faculty -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label fw-600">Department / College</label>
          <select class="form-select form-select-sm" id="filterDept" onchange="filterSubmissions()">
            <option value="">All Departments / Colleges</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-600">Role / Position Category</label>
          <select class="form-select form-select-sm" id="filterRole" onchange="filterSubmissions()">
            <option value="">All Roles</option>
            <option value="admin">Deans & Department Heads</option>
            <option value="user">Faculty & Staff Members</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-600">Employee IPCR Submission <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm" id="selectAdmin" onchange="loadAdmin()">
            <option value="">-- Select Employee IPCR Submission --</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">College / Office</label>
          <input type="text" class="form-control bg-light" id="accOffice" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Name of Person</label>
          <input type="text" class="form-control bg-light" id="accName" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Position</label>
          <input type="text" class="form-control bg-light" id="accPosition" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Rating Period</label>
          <input type="text" class="form-control bg-light" id="accPeriod" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Date Received / Submitted</label>
          <input type="text" class="form-control bg-light" id="accDate" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Last Edited Date</label>
          <input type="text" class="form-control bg-light text-primary fw-semibold" id="accDateEdited" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Date Reviewed</label>
          <input type="text" class="form-control bg-light" id="accDateReviewed" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Current Status</label>
          <input type="text" class="form-control bg-light" id="accStatus" readonly>
        </div>
      </div>
    </div>
  </div>

  <!-- Employee ETL Preset & Category Weights -->
  <div class="card mb-3 border-primary shadow-sm d-none" id="accEtlCard">
    <div class="card-header py-2 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#FFF4E6">
      <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-scale-balanced text-primary"></i>
        <strong style="font-size:0.85rem">WEIGHT According to ETL: <span class="badge bg-primary ms-1" id="accEtlBadge">6 ETL</span></strong>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size:0.8rem">
        <span class="badge bg-white text-dark border px-2 py-1">Core Functions: <strong id="accWeightCore">50%</strong></span>
        <span class="badge bg-white text-dark border px-2 py-1">Strategic Priorities: <strong id="accWeightStrategic">25%</strong></span>
        <span class="badge bg-white text-dark border px-2 py-1">Support Functions: <strong id="accWeightSupport">25%</strong></span>
      </div>
    </div>
  </div>

  <!-- Rating Sections -->
  <div id="formSections" class="d-none">
    <div class="mb-3">
      <div class="ipcr-section-header"><i class="fa-solid fa-star me-2"></i>A. CORE FUNCTION</div>
      <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
          <thead class="table-light"><tr><th style="width:110px">MFO/KRA</th><th>Success Indicator</th><th style="width:100px">Target</th><th>Accomplishment</th><th style="width:70px">Q</th><th style="width:70px">E</th><th style="width:70px">T</th><th style="width:80px">Average</th><th>Remarks</th><th style="width:110px;text-align:center">Evidence</th></tr></thead>
          <tbody id="coreRatingBody"></tbody>
          <tfoot>
            <tr class="bg-light">
              <td colspan="7" class="text-end fw-600" style="font-size:0.82rem">
                Average — Core Function: <span id="acc_core_avg" class="fw-700 text-dark ms-1">—</span>
                <span class="mx-2 text-muted">|</span>
                Weighted Average:
              </td>
              <td id="acc_core_weighted" class="text-center fw-700 text-primary" style="font-size:0.88rem">—</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="mb-3">
      <div class="ipcr-section-header"><i class="fa-solid fa-chess me-2"></i>B. STRATEGIC FUNCTION</div>
      <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
          <thead class="table-light"><tr><th style="width:110px">MFO/KRA</th><th>Success Indicator</th><th style="width:100px">Target</th><th>Accomplishment</th><th style="width:70px">Q</th><th style="width:70px">E</th><th style="width:70px">T</th><th style="width:80px">Average</th><th>Remarks</th><th style="width:110px;text-align:center">Evidence</th></tr></thead>
          <tbody id="strategicRatingBody"></tbody>
          <tfoot>
            <tr class="bg-light">
              <td colspan="7" class="text-end fw-600" style="font-size:0.82rem">
                Average — Strategic Function: <span id="acc_strategic_avg" class="fw-700 text-dark ms-1">—</span>
                <span class="mx-2 text-muted">|</span>
                Weighted Average:
              </td>
              <td id="acc_strategic_weighted" class="text-center fw-700 text-primary" style="font-size:0.88rem">—</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="mb-3">
      <div class="ipcr-section-header"><i class="fa-solid fa-hands-helping me-2"></i>C. SUPPORT FUNCTION</div>
      <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
          <thead class="table-light"><tr><th style="width:110px">MFO/KRA</th><th>Success Indicator</th><th style="width:100px">Target</th><th>Accomplishment</th><th style="width:70px">Q</th><th style="width:70px">E</th><th style="width:70px">T</th><th style="width:80px">Average</th><th>Remarks</th><th style="width:110px;text-align:center">Evidence</th></tr></thead>
          <tbody id="supportRatingBody"></tbody>
          <tfoot>
            <tr class="bg-light">
              <td colspan="7" class="text-end fw-600" style="font-size:0.82rem">
                Average — Support Function: <span id="acc_support_avg" class="fw-700 text-dark ms-1">—</span>
                <span class="mx-2 text-muted">|</span>
                Weighted Average:
              </td>
              <td id="acc_support_weighted" class="text-center fw-700 text-primary" style="font-size:0.88rem">—</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Overall Rating -->
    <div class="card mt-3" style="background:#FFF4E6">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-6">
            <label class="form-label fw-700">Final Status</label>
            <select class="form-select" id="finalStatus">
              <option value="reviewed">Reviewed</option>
              <option value="approved">Approved</option>
              <option value="disapproved">Disapproved</option>
            </select>
          </div>
          <div class="col-md-6 text-end">
            <div class="fw-700" style="font-size:0.9rem">Computed Overall Rating</div>
            <div id="overallRatingDisplay" style="font-size:1.5rem;font-weight:800;color:var(--primary)">-</div>
            <div id="ratingLabelDisplay"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 justify-content-end mt-3 no-print">
      <button class="btn btn-outline-primary" id="btnViewEvidence2" onclick="openEvidenceModal()"><i class="fa-solid fa-paperclip me-1"></i>View Evidence <span class="badge bg-primary text-white ms-1" id="evidenceCountBadge2">0</span></button>
      <button class="btn btn-primary" onclick="saveRatings()"><i class="fa-solid fa-save me-1"></i>Save Ratings</button>
    </div>
  </div>

  <div id="emptyState" class="empty-state mt-4">
    <i class="fa-solid fa-user-check"></i>
    <p>Select a Position and Designation above to view and rate their IPCR.</p>
  </div>
</main>

<!-- View Evidence Modal -->
<div class="modal fade" id="evidenceModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-paperclip me-2"></i>Supporting Evidence & Documents — <span id="evidenceModalUser"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
          <div id="evidenceFilterBadge" class="badge bg-primary bg-opacity-10 text-primary py-2 px-3">All Evidence</div>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="btnShowAllEv" onclick="renderEvidenceList(currentEvidence, 'All Evidence')"><i class="fa-solid fa-list me-1"></i>Show All</button>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:40px">#</th>
                <th>File Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Size</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="evidenceModalTable"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  initLayout('superadmin', 'accomplishments', [{ label: 'Accomplishment & Ratings' }]);

  const session = SESSION_USER;
  let currentForm = null;
  let allForms = [];

  async function initPage() {
    // Load departments & all IPCR submissions
    const [deptRes, ipcrRes] = await Promise.all([
      fetch(API_BASE + 'departments/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => ({ departments: [] })),
      fetch(API_BASE + 'ipcr/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => ({ forms: [] }))
    ]);

    const deptSel = document.getElementById('filterDept');
    if (deptSel && deptRes.departments) {
      deptRes.departments.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = d.name + ' (' + d.id + ')';
        deptSel.appendChild(opt);
      });
    }

    allForms = ipcrRes.forms || [];
    populateSubmissions(allForms);
  }

  function filterSubmissions() {
    const dept = document.getElementById('filterDept')?.value || '';
    const role = document.getElementById('filterRole')?.value || '';

    let filtered = allForms;
    if (dept) {
      filtered = filtered.filter(f => f.department_id === dept);
    }
    if (role) {
      filtered = filtered.filter(f => f.user_role === role);
    }

    populateSubmissions(filtered);
  }

  function populateSubmissions(forms) {
    const sel = document.getElementById('selectAdmin');
    sel.innerHTML = '<option value="">-- Select Employee IPCR Submission --</option>';

    forms.forEach(f => {
      const o = document.createElement('option');
      o.value = f.id;
      const pos = f.position ? (f.position + ' — ') : '';
      let editedText = '';
      if (f.updated_at && f.updated_at !== f.created_at) {
        editedText = ' (Edited: ' + formatDateTime(f.updated_at) + ')';
      }
      const roleTag = f.user_role === 'admin' ? '[Dean/Head]' : '[Faculty/Staff]';
      o.textContent = roleTag + ' ' + pos + f.user_name + ' (' + (f.department_name || f.department_id || 'Campus') + ')' + (f.covered_period ? ' [' + f.covered_period + ']' : '') + editedText;
      sel.appendChild(o);
    });

    if (forms.length === 0) {
      document.getElementById('emptyState').innerHTML = '<i class="fa-solid fa-inbox"></i><p>No IPCR submissions found matching selected filters.</p>';
      document.getElementById('formSections').classList.add('d-none');
      document.getElementById('accEtlCard').classList.add('d-none');
      document.getElementById('emptyState').style.display = '';
    } else {
      document.getElementById('emptyState').innerHTML = '<i class="fa-solid fa-user-check"></i><p>Select an Employee IPCR Submission above to view and rate their functions.</p>';
    }
  }

  let currentEvidence = [];
  let _evidenceModal = null;

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  async function loadAdmin() {
    const id = document.getElementById('selectAdmin').value;
    if (!id) {
      document.getElementById('formSections').classList.add('d-none');
      document.getElementById('accEtlCard').classList.add('d-none');
      document.getElementById('emptyState').style.display = '';
      document.getElementById('btnViewEvidence').classList.add('d-none');
      return;
    }
    const res = await fetch(API_BASE + 'ipcr/get.php?id=' + id).then(r => r.json()).catch(() => null);
    if (!res?.form) { showToast('Could not load form.', 'danger'); return; }
    currentForm = res.form;
    const f = res.form;

    // Real backend evidence
    let userFiles = f.evidence_files || [];
    currentEvidence = userFiles;
    const evCount = userFiles.length;

    document.getElementById('btnViewEvidence').classList.remove('d-none');
    document.getElementById('evidenceCountBadge').textContent = evCount;
    document.getElementById('evidenceCountBadge2').textContent = evCount;

    document.getElementById('accOffice').value       = f.department_name || '-';
    document.getElementById('accName').value         = f.user_name || '-';
    document.getElementById('accPosition').value     = f.position || '-';
    document.getElementById('accPeriod').value       = f.covered_period || '-';
    document.getElementById('accDate').value         = f.date_submitted ? formatDate(f.date_submitted) : (f.created_at ? formatDate(f.created_at) : '-');
    document.getElementById('accDateEdited').value   = (f.updated_at && f.updated_at !== f.created_at) ? formatDateTime(f.updated_at) : (f.date_submitted ? formatDateTime(f.date_submitted) : 'Not edited');
    document.getElementById('accDateReviewed').value = f.reviewed_at ? formatDate(f.reviewed_at) : (['reviewed','approved','disapproved'].includes(f.status) ? (f.reviewed_at || 'Reviewed') : 'Pending Review');
    document.getElementById('accStatus').value       = f.status;
    document.getElementById('finalStatus').value     = ['reviewed','approved','disapproved'].includes(f.status) ? f.status : 'reviewed';

    loadRatingRows('coreRatingBody',      f.items.core      || [], 'core');
    loadRatingRows('strategicRatingBody', f.items.strategic || [], 'strategic');
    loadRatingRows('supportRatingBody',   f.items.support   || [], 'support');

    document.getElementById('accEtlBadge').textContent = f.etl_type || '6 ETL';
    document.getElementById('accWeightCore').textContent = (f.weight_core || 50) + '%';
    document.getElementById('accWeightStrategic').textContent = (f.weight_strategic || 25) + '%';
    document.getElementById('accWeightSupport').textContent = (f.weight_support || 25) + '%';
    document.getElementById('accEtlCard').classList.remove('d-none');

    document.getElementById('formSections').classList.remove('d-none');
    document.getElementById('emptyState').style.display = 'none';
    computeOverall();
  }

  function getMatchingEvidence(categoryKey, mfoText) {
    const list = currentEvidence || [];
    const catSearch = (categoryKey || '').toLowerCase().trim();
    const mfoSearch = (mfoText || '').toLowerCase().trim();

    if (mfoSearch) {
      return list.filter(file => {
        const fCat = (file.category || '').toLowerCase().trim();
        const fMfo = (file.mfo || '').toLowerCase().trim();
        const fDesc = (file.description || '').toLowerCase().trim();

        // If file category is specified and conflicts with section, skip
        if (catSearch && fCat && fCat !== 'other' && fCat !== 'evidence' && fCat !== catSearch) {
          return false;
        }

        // Match against explicit mfo field
        if (fMfo && (fMfo === mfoSearch || fMfo.includes(mfoSearch) || mfoSearch.includes(fMfo))) {
          return true;
        }

        // Match against description if not default placeholder
        if (fDesc && fDesc !== 'uploaded evidence' && (fDesc === mfoSearch || fDesc.includes(mfoSearch) || mfoSearch.includes(fDesc))) {
          return true;
        }

        return false;
      });
    }

    if (catSearch && catSearch !== 'all') {
      return list.filter(file => {
        const fCat = (file.category || '').toLowerCase().trim();
        return fCat === catSearch || (catSearch === 'core' && fCat.includes('core')) || (catSearch === 'strategic' && fCat.includes('strat')) || (catSearch === 'support' && fCat.includes('supp'));
      });
    }

    return list;
  }

  function loadRatingRows(tbodyId, items, categoryKey) {
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    if (!items || items.length === 0) {
      tbody.innerHTML = `<tr><td colspan="10" class="text-center py-3 text-muted" style="font-size:0.82rem"><i class="fa-solid fa-inbox me-1"></i>No ${categoryKey} function items in this submission.</td></tr>`;
      return;
    }
    items.forEach(item => {
      const tr = document.createElement('tr');
      const avg = parseFloat(item.rating) || 0;
      const matchedFiles = getMatchingEvidence(categoryKey, item.mfo || '');
      const count = matchedFiles.length;
      const mfoSafe = escapeHtml(item.mfo || '');

      const evidenceBtn = count > 0
        ? `<button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" onclick="openEvidenceModalFor('${categoryKey}', '${mfoSafe}')"><i class="fa-solid fa-paperclip"></i><span>View (${count})</span></button>`
        : `<button type="button" class="btn btn-sm btn-outline-secondary opacity-75 d-inline-flex align-items-center gap-1" onclick="openEvidenceModalFor('${categoryKey}', '${mfoSafe}')"><i class="fa-solid fa-paperclip"></i><span>0 Files</span></button>`;

      tr.innerHTML = `
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${item.mfo || '-'}</td>
        <td style="font-size:0.82rem">${item.success_indicator || '-'}</td>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${item.target || '-'}</td>
        <td style="font-size:0.82rem;text-align:center">${item.accomplishment !== null && item.accomplishment !== '' ? (isNaN(item.accomplishment) ? item.accomplishment : item.accomplishment + '%') : '-'}</td>
        <td><input type="number" class="form-control form-control-sm rating-q" min="1" max="5" step="0.1" data-id="${item.id}" data-field="q_rating" value="${item.q_rating || ''}" placeholder="1-5" oninput="computeRowRating(this)"></td>
        <td><input type="number" class="form-control form-control-sm rating-e" min="1" max="5" step="0.1" data-id="${item.id}" data-field="e_rating" value="${item.e_rating || ''}" placeholder="1-5" oninput="computeRowRating(this)"></td>
        <td><input type="number" class="form-control form-control-sm rating-t" min="1" max="5" step="0.1" data-id="${item.id}" data-field="t_rating" value="${item.t_rating || ''}" placeholder="1-5" oninput="computeRowRating(this)"></td>
        <td class="text-center fw-700 row-avg" style="font-size:0.85rem;background:#fafafa">${avg > 0 ? avg.toFixed(2) : '-'}</td>
        <td><select class="form-select form-select-sm row-remarks" data-id="${item.id}" data-field="remarks" style="min-width:130px">${renderRemarksOptions(item.remarks || "")}</select></td>
        <td class="text-center">${evidenceBtn}</td>`;
      tbody.appendChild(tr);
    });
  }

  function computeRowRating(inputEl) {
    const tr = inputEl.closest('tr');
    if (!tr) return;
    const qInp = tr.querySelector('.rating-q');
    const eInp = tr.querySelector('.rating-e');
    const tInp = tr.querySelector('.rating-t');
    const avgCell = tr.querySelector('.row-avg');
    const remarksInp = tr.querySelector('.row-remarks');

    const vals = [qInp, eInp, tInp].map(i => parseFloat(i?.value)).filter(v => !isNaN(v) && v >= 1 && v <= 5);
    if (vals.length > 0) {
      const avg = vals.reduce((a, b) => a + b, 0) / vals.length;
      avgCell.textContent = avg.toFixed(2);
    } else {
      avgCell.textContent = '-';
    }
    computeOverall();
  }

  function computeOverall() {
    const f = currentForm;
    const wCore = (f && f.weight_core ? parseFloat(f.weight_core) : 50) / 100;
    const wStrat = (f && f.weight_strategic ? parseFloat(f.weight_strategic) : 25) / 100;
    const wSupp = (f && f.weight_support ? parseFloat(f.weight_support) : 25) / 100;

    function getSecAvg(tbodyId) {
      const cells = document.querySelectorAll(`#${tbodyId} .row-avg`);
      const vals = Array.from(cells).map(c => parseFloat(c.textContent)).filter(v => !isNaN(v) && v > 0);
      return vals.length ? (vals.reduce((a,b) => a+b, 0) / vals.length) : null;
    }

    const cAvg = getSecAvg('coreRatingBody');
    const sAvg = getSecAvg('strategicRatingBody');
    const supAvg = getSecAvg('supportRatingBody');

    const cW = cAvg !== null ? (cAvg * wCore) : null;
    const sW = sAvg !== null ? (sAvg * wStrat) : null;
    const supW = supAvg !== null ? (supAvg * wSupp) : null;

    const elCAvg = document.getElementById('acc_core_avg');
    const elCW = document.getElementById('acc_core_weighted');
    if (elCAvg) elCAvg.textContent = cAvg !== null ? cAvg.toFixed(2) : '—';
    if (elCW) elCW.textContent = cW !== null ? cW.toFixed(2) : '—';

    const elSAvg = document.getElementById('acc_strategic_avg');
    const elSW = document.getElementById('acc_strategic_weighted');
    if (elSAvg) elSAvg.textContent = sAvg !== null ? sAvg.toFixed(2) : '—';
    if (elSW) elSW.textContent = sW !== null ? sW.toFixed(2) : '—';

    const elSupAvg = document.getElementById('acc_support_avg');
    const elSupW = document.getElementById('acc_support_weighted');
    if (elSupAvg) elSupAvg.textContent = supAvg !== null ? supAvg.toFixed(2) : '—';
    if (elSupW) elSupW.textContent = supW !== null ? supW.toFixed(2) : '—';

    let sum = 0, weightSum = 0;
    if (cW !== null) { sum += cW; weightSum += wCore; }
    if (sW !== null) { sum += sW; weightSum += wStrat; }
    if (supW !== null) { sum += supW; weightSum += wSupp; }

    const avg = weightSum > 0 ? (sum / weightSum) : 0;
    document.getElementById('overallRatingDisplay').textContent = avg > 0 ? avg.toFixed(2) : '-';
    document.getElementById('ratingLabelDisplay').innerHTML = avg > 0 ? getRatingLabel(avg) : '';
  }

  async function saveRatings() {
    if (!currentForm) { showToast('Please select a Position and Designation first.', 'warning'); return; }

    const ratings = [];
    document.querySelectorAll('[data-field]').forEach(el => {
      const id = parseInt(el.dataset.id);
      if (!id) return;
      let entry = ratings.find(r => r.item_id === id);
      if (!entry) { entry = { item_id: id }; ratings.push(entry); }
      entry[el.dataset.field] = el.value;
    });

    try {
      const res = await fetch(API_BASE + 'ipcr/review.php', {
        method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ipcr_id: currentForm.id,
          status:  document.getElementById('finalStatus').value,
          remarks: '',
          ratings,
        }),
      });
      const data = await res.json();
      if (data.success) {
        document.getElementById('accStatus').value = data.status;
        document.getElementById('accDateReviewed').value = new Date().toLocaleDateString('en-PH');
        showToast('Ratings saved! Overall: ' + data.overall_rating.toFixed(2) + ' (' + data.status + ')', 'success');
      } else { showToast(data.error, 'danger'); }
    } catch { showToast('Server error.', 'danger'); }
  }

  function openEvidenceModalFor(categoryKey, mfoText) {
    if (!currentForm) { showToast('Please select a Position and Designation first.', 'warning'); return; }
    const filtered = getMatchingEvidence(categoryKey, mfoText);
    const label = (mfoText ? mfoText + ' (' + categoryKey.toUpperCase() + ')' : categoryKey.toUpperCase() + ' Evidence');
    renderEvidenceList(filtered, label);
    _evidenceModal = _evidenceModal || new bootstrap.Modal(document.getElementById('evidenceModal'));
    document.getElementById('evidenceModalUser').textContent = (currentForm.user_name || 'Employee');
    _evidenceModal.show();
  }

  function openEvidenceModal() {
    if (!currentForm) { showToast('Please select a Position and Designation first.', 'warning'); return; }
    renderEvidenceList(currentEvidence, 'All Evidence');
    _evidenceModal = _evidenceModal || new bootstrap.Modal(document.getElementById('evidenceModal'));
    document.getElementById('evidenceModalUser').textContent = (currentForm.user_name || 'Employee');
    _evidenceModal.show();
  }

  function renderEvidenceList(files, filterLabel) {
    document.getElementById('evidenceFilterBadge').textContent = filterLabel;
    const tbody = document.getElementById('evidenceModalTable');
    tbody.innerHTML = '';

    if (!files || files.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fa-solid fa-folder-open me-2"></i>No evidence documents found for ${filterLabel}.</td></tr>`;
      return;
    }

    files.forEach((f, i) => {
      const name = f.original_name || f.name || 'document';
      const ext = name.split('.').pop().toLowerCase();
      const iconMap = { pdf: 'fa-file-pdf text-danger', doc: 'fa-file-word text-primary', docx: 'fa-file-word text-primary', jpg: 'fa-file-image text-success', jpeg: 'fa-file-image text-success', png: 'fa-file-image text-success', xlsx: 'fa-file-excel text-success' };
      const icon = iconMap[ext] || 'fa-file text-secondary';
      const sizeStr = (f.file_size || f.size) ? formatSize(f.file_size || f.size) : '-';
      let filePath = '';
      if (f.file_path && f.file_path !== '#') {
        filePath = f.file_path.startsWith('http') ? f.file_path : (API_BASE + '../' + f.file_path.replace(/^\/+/, ''));
      } else if (f.data_url) {
        filePath = f.data_url;
      } else if (f.file_url) {
        filePath = f.file_url;
      }

      const actionHtml = filePath
        ? `<a href="${filePath}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-eye me-1"></i>View / Open</a>`
        : `<button type="button" class="btn btn-outline-secondary btn-sm" onclick="showToast('Physical file not saved on server yet. Please upload supporting evidence in the form.', 'warning')"><i class="fa-solid fa-file me-1"></i>Document Details</button>`;

      tbody.innerHTML += `<tr>
        <td>${i + 1}</td>
        <td><div class="d-flex align-items-center gap-2"><i class="fa-solid ${icon}" style="font-size:1.1rem"></i><strong>${name}</strong></div></td>
        <td>${getCategoryBadge(f.category || 'Evidence')}</td>
        <td style="font-size:0.82rem">${f.description || '-'}</td>
        <td style="font-size:0.82rem">${sizeStr}</td>
        <td>${actionHtml}</td>
      </tr>`;
    });
  }

  function formatSize(bytes) {
    if (!bytes) return '-';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
  }

  initPage();
</script>
</body>
</html>
