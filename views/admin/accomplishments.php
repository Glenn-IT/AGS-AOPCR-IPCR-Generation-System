<?php
require_once '../../config/session.php';
$user = requireAuth(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accomplishments & Ratings | CSU-Piat</title>
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
      <h2><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Accomplishments & Ratings</h2>
      <p>Set actual accomplishments and performance ratings for IPCR submissions.</p>
    </div>
    <div class="d-flex gap-2 no-print">
      <button class="btn btn-outline-info btn-sm d-none" id="btnViewEvidence" onclick="openEvidenceModal()"><i class="fa-solid fa-paperclip me-1"></i>View Evidence <span class="badge bg-info text-dark ms-1" id="evidenceCountBadge">0</span></button>
      <button class="btn btn-primary btn-sm" onclick="saveRatings()"><i class="fa-solid fa-save me-1"></i>Save Ratings</button>
    </div>
  </div>

  <!-- Select Employee / Position & Designation -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Position and Designation</label>
          <select class="form-select" id="selectEmployee" onchange="loadEmployee()">
            <option value="">-- Select Position and Designation --</option>
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
          <label class="form-label">Covered Period</label>
          <input type="text" class="form-control bg-light" id="accPeriod" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Date Received</label>
          <input type="text" class="form-control bg-light" id="accDate" readonly>
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

  <!-- Form Sections -->
  <div id="formSections" class="d-none">
    <!-- Core Function -->
    <div class="mb-3">
      <div class="ipcr-section-header"><i class="fa-solid fa-star me-2"></i>A. CORE FUNCTION</div>
      <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
          <thead class="table-light">
            <tr><th style="width:110px">MFO/KRA</th><th>Success Indicator</th><th style="width:100px">Target</th><th>Accomplishment</th><th style="width:70px">Q</th><th style="width:70px">E</th><th style="width:70px">T</th><th style="width:80px">Average</th><th>Remarks</th><th style="width:110px;text-align:center">Evidence</th></tr>
          </thead>
          <tbody id="coreRatingBody"></tbody>
        </table>
      </div>
    </div>

    <!-- Strategic Function -->
    <div class="mb-3">
      <div class="ipcr-section-header"><i class="fa-solid fa-chess me-2"></i>B. STRATEGIC FUNCTION</div>
      <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
          <thead class="table-light">
            <tr><th style="width:110px">MFO/KRA</th><th>Success Indicator</th><th style="width:100px">Target</th><th>Accomplishment</th><th style="width:70px">Q</th><th style="width:70px">E</th><th style="width:70px">T</th><th style="width:80px">Average</th><th>Remarks</th><th style="width:110px;text-align:center">Evidence</th></tr>
          </thead>
          <tbody id="strategicRatingBody"></tbody>
        </table>
      </div>
    </div>

    <!-- Support Function -->
    <div class="mb-3">
      <div class="ipcr-section-header"><i class="fa-solid fa-hands-helping me-2"></i>C. SUPPORT FUNCTION</div>
      <div class="table-responsive">
        <table class="table table-bordered mb-0 align-middle">
          <thead class="table-light">
            <tr><th style="width:110px">MFO/KRA</th><th>Success Indicator</th><th style="width:100px">Target</th><th>Accomplishment</th><th style="width:70px">Q</th><th style="width:70px">E</th><th style="width:70px">T</th><th style="width:80px">Average</th><th>Remarks</th><th style="width:110px;text-align:center">Evidence</th></tr>
          </thead>
          <tbody id="supportRatingBody"></tbody>
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
      <button class="btn btn-outline-info" id="btnViewEvidence2" onclick="openEvidenceModal()"><i class="fa-solid fa-paperclip me-1"></i>View Evidence <span class="badge bg-info text-dark ms-1" id="evidenceCountBadge2">0</span></button>
      <button class="btn btn-primary" onclick="saveRatings()"><i class="fa-solid fa-save me-1"></i>Save Ratings</button>
    </div>
  </div>

  <div id="emptyState" class="empty-state mt-4">
    <i class="fa-solid fa-user-check"></i>
    <p>Select a Position and Designation above to view and rate their IPCR form.</p>
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
  initLayout('admin', 'accomplishments', [{ label: 'Accomplishments & Ratings' }]);

  const session = SESSION_USER;
  let currentForm = null;

  async function initPage() {
    const res = await fetch(API_BASE + 'ipcr/list.php').then(r => r.json()).catch(() => ({ forms: [] }));
    const ipcrs = res.forms || [];
    const empSel = document.getElementById('selectEmployee');
    ipcrs.forEach(f => {
      const o = document.createElement('option');
      o.value = f.id;
      const pos = f.position ? (f.position + ' — ') : '';
      o.textContent = pos + f.user_name + (f.covered_period ? ' (' + f.covered_period + ')' : '');
      empSel.appendChild(o);
    });
    if (ipcrs.length === 0) {
      document.getElementById('emptyState').innerHTML = '<i class="fa-solid fa-inbox"></i><p>No IPCR submissions from your department yet.</p>';
    }
  }

  let currentEvidence = [];
  let _evidenceModal = null;

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  async function loadEmployee() {
    const id = document.getElementById('selectEmployee').value;
    if (!id) {
      document.getElementById('formSections').classList.add('d-none');
      document.getElementById('emptyState').style.display = '';
      document.getElementById('btnViewEvidence').classList.add('d-none');
      return;
    }
    const res = await fetch(API_BASE + 'ipcr/get.php?id=' + id).then(r => r.json()).catch(() => null);
    if (!res?.form) { showToast('Could not load form.', 'danger'); return; }
    currentForm = res.form;
    const f = res.form;

    // Load evidence from backend or local storage
    let userFiles = f.evidence_files || [];
    if (f.user_id) {
      const lsFiles = JSON.parse(localStorage.getItem('csu_piat_files_' + f.user_id)) || [];
      lsFiles.forEach(lf => {
        if (!userFiles.some(uf => (uf.original_name === lf.name || uf.name === lf.name))) {
          userFiles.push({
            id: lf.id,
            original_name: lf.name,
            category: lf.category || 'Evidence',
            description: lf.description || 'No description',
            file_size: lf.size || 0,
            uploaded_at: lf.date || '',
            file_path: lf.path || '#'
          });
        }
      });
    }
    currentEvidence = userFiles;
    const evCount = userFiles.length;

    document.getElementById('btnViewEvidence').classList.remove('d-none');
    document.getElementById('evidenceCountBadge').textContent = evCount;
    document.getElementById('evidenceCountBadge2').textContent = evCount;

    document.getElementById('accOffice').value       = f.department_name || '-';
    document.getElementById('accName').value         = f.user_name || '-';
    document.getElementById('accPosition').value     = f.position || '-';
    document.getElementById('accPeriod').value       = f.covered_period || '-';
    document.getElementById('accDate').value         = f.date_submitted || '-';
    document.getElementById('accDateReviewed').value = f.reviewed_at ? formatDate(f.reviewed_at) : (['reviewed','approved','disapproved'].includes(f.status) ? (f.reviewed_at || 'Reviewed') : 'Pending Review');
    document.getElementById('accStatus').value       = f.status;
    document.getElementById('finalStatus').value     = ['reviewed','approved','disapproved'].includes(f.status) ? f.status : 'reviewed';

    loadRatingRows('coreRatingBody',      f.items.core      || [], 'core');
    loadRatingRows('strategicRatingBody', f.items.strategic || [], 'strategic');
    loadRatingRows('supportRatingBody',   f.items.support   || [], 'support');

    document.getElementById('formSections').classList.remove('d-none');
    document.getElementById('emptyState').style.display = 'none';
    computeOverall();
  }

  function getMatchingEvidence(categoryKey, mfoText) {
    const list = currentEvidence || [];
    const catSearch = categoryKey.toLowerCase();
    const mfoSearch = (mfoText || '').toLowerCase().trim();

    return list.filter(file => {
      const fCat = (file.category || '').toLowerCase();
      const fDesc = (file.description || '').toLowerCase();
      const fName = (file.original_name || file.name || '').toLowerCase();

      if (fCat.includes(catSearch) || (catSearch === 'core' && fCat.includes('core')) || (catSearch === 'strategic' && fCat.includes('strategic')) || (catSearch === 'support' && fCat.includes('support'))) {
        return true;
      }
      if (mfoSearch && (fDesc.includes(mfoSearch) || fName.includes(mfoSearch))) {
        return true;
      }
      return false;
    });
  }

  function loadRatingRows(tbodyId, items, categoryKey) {
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    items.forEach(item => {
      const tr = document.createElement('tr');
      const avg = parseFloat(item.rating) || 0;
      const matchedFiles = getMatchingEvidence(categoryKey, item.mfo || '');
      const count = matchedFiles.length;
      const mfoSafe = escapeHtml(item.mfo || '');

      const evidenceBtn = count > 0
        ? `<button type="button" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1" onclick="openEvidenceModalFor('${categoryKey}', '${mfoSafe}')"><i class="fa-solid fa-paperclip"></i><span>View (${count})</span></button>`
        : `<button type="button" class="btn btn-sm btn-outline-secondary opacity-75 d-inline-flex align-items-center gap-1" onclick="openEvidenceModalFor('${categoryKey}', '${mfoSafe}')"><i class="fa-solid fa-paperclip"></i><span>0 Files</span></button>`;

      tr.innerHTML = `
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap" data-id="${item.id}">${item.mfo || '-'}</td>
        <td style="font-size:0.82rem">${item.success_indicator || '-'}</td>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${item.target || '-'}</td>
        <td style="font-size:0.82rem;text-align:center">${item.accomplishment !== null && item.accomplishment !== '' ? (isNaN(item.accomplishment) ? item.accomplishment : item.accomplishment + '%') : '-'}</td>
        <td><input type="number" class="form-control form-control-sm rating-q" min="1" max="5" step="0.1" data-id="${item.id}" data-field="q_rating" value="${item.q_rating || ''}" placeholder="1-5" oninput="computeRowRating(this)"></td>
        <td><input type="number" class="form-control form-control-sm rating-e" min="1" max="5" step="0.1" data-id="${item.id}" data-field="e_rating" value="${item.e_rating || ''}" placeholder="1-5" oninput="computeRowRating(this)"></td>
        <td><input type="number" class="form-control form-control-sm rating-t" min="1" max="5" step="0.1" data-id="${item.id}" data-field="t_rating" value="${item.t_rating || ''}" placeholder="1-5" oninput="computeRowRating(this)"></td>
        <td class="text-center fw-700 row-avg" style="font-size:0.85rem;background:#fafafa">${avg > 0 ? avg.toFixed(2) : '-'}</td>
        <td><input type="text" class="form-control form-control-sm row-remarks bg-light" data-id="${item.id}" data-field="remarks" value="${item.remarks || (avg > 0 ? getAdjectivalText(avg) : '')}" readonly placeholder="Auto"></td>
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
      remarksInp.value = getAdjectivalText(avg);
    } else {
      avgCell.textContent = '-';
      remarksInp.value = '';
    }
    computeOverall();
  }

  function computeOverall() {
    const avgCells = document.querySelectorAll('.row-avg');
    let total = 0, count = 0;
    avgCells.forEach(cell => {
      const v = parseFloat(cell.textContent);
      if (!isNaN(v) && v > 0) { total += v; count++; }
    });
    const avg = count > 0 ? (total / count) : 0;
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
        method: 'POST', headers: { 'Content-Type': 'application/json' },
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
    if (!currentForm) { showToast('Please select an employee first.', 'warning'); return; }
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
      const sizeStr = f.file_size ? formatSize(f.file_size) : '-';
      const filePath = f.file_path && f.file_path !== '#' ? (API_BASE + '../' + f.file_path) : '#';

      tbody.innerHTML += `<tr>
        <td>${i + 1}</td>
        <td><div class="d-flex align-items-center gap-2"><i class="fa-solid ${icon}" style="font-size:1.1rem"></i><strong>${name}</strong></div></td>
        <td><span class="badge bg-primary bg-opacity-10 text-primary">${f.category || 'Evidence'}</span></td>
        <td style="font-size:0.82rem">${f.description || '-'}</td>
        <td style="font-size:0.82rem">${sizeStr}</td>
        <td>
          ${filePath !== '#' ? `<a href="${filePath}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-eye me-1"></i>View / Open</a>` : `<button class="btn btn-outline-secondary btn-sm" onclick="showToast('File stored in client uploads.', 'info')"><i class="fa-solid fa-file me-1"></i>View Document</button>`}
        </td>
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
