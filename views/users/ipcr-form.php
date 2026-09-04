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
    <div class="d-flex gap-2 no-print flex-wrap">
      <button class="btn btn-outline-info btn-sm" id="btnViewEvidence" onclick="openEvidenceModal()"><i class="fa-solid fa-paperclip me-1"></i>View Evidence <span class="badge bg-info text-dark ms-1" id="evidenceCountBadge">0</span></button>
      <button class="btn btn-outline-primary btn-sm" id="btnUploadEvidence" onclick="openUploadModal()"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload Evidence</button>
      <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
      <button class="btn btn-outline-primary btn-sm" id="btnSaveDraft" onclick="saveIPCR('draft')"><i class="fa-solid fa-floppy-disk me-1"></i>Save Draft</button>
      <button class="btn btn-success btn-sm" id="btnSubmit" onclick="submitIPCR()"><i class="fa-solid fa-paper-plane me-1"></i>Submit</button>
    </div>
  </div>

  <div id="noTimelineAlert" class="alert alert-warning d-none no-print" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>
    <strong>No active submission period.</strong> The Super Admin has not opened a submission period yet. You can view and fill in the form, but saving and submitting are disabled until a period is opened.
  </div>

  <!-- KPI source info banner (populated by JS) -->
  <div id="kpiInfoBanner" class="alert alert-info d-none no-print mb-3 py-2" role="alert" style="font-size:0.84rem">
    <i class="fa-solid fa-circle-info me-2"></i>
    <span id="kpiInfoText"></span>
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
        <thead class="table-light" style="font-size:0.8rem"><tr><th style="width:110px">MFO / KRA</th><th>Success Indicators</th><th style="width:100px">Target</th><th style="width:110px">Actual Accomplishment</th><th style="width:70px">Q</th><th style="width:70px">E</th><th style="width:70px">T</th><th style="width:80px">Average</th><th>Remarks</th><th style="width:140px;text-align:center">Evidence</th></tr></thead>
        <tbody id="coreBody"></tbody>
      </table>
    </div>
  </div>

  <!-- Strategic Function -->
  <div class="mb-3">
    <div class="ipcr-section-header"><i class="fa-solid fa-chess me-2"></i>B. STRATEGIC FUNCTION</div>
    <div class="table-responsive">
      <table class="table table-bordered mb-0">
        <thead class="table-light" style="font-size:0.8rem"><tr><th style="width:110px">MFO / KRA</th><th>Success Indicators</th><th style="width:100px">Target</th><th style="width:110px">Actual Accomplishment</th><th style="width:70px">Q</th><th style="width:70px">E</th><th style="width:70px">T</th><th style="width:80px">Average</th><th>Remarks</th><th style="width:140px;text-align:center">Evidence</th></tr></thead>
        <tbody id="strategicBody"></tbody>
      </table>
    </div>
  </div>

  <!-- Support Function -->
  <div class="mb-3">
    <div class="ipcr-section-header"><i class="fa-solid fa-hands-helping me-2"></i>C. SUPPORT FUNCTION</div>
    <div class="table-responsive">
      <table class="table table-bordered mb-0">
        <thead class="table-light" style="font-size:0.8rem"><tr><th style="width:110px">MFO / KRA</th><th>Success Indicators</th><th style="width:100px">Target</th><th style="width:110px">Actual Accomplishment</th><th style="width:70px">Q</th><th style="width:70px">E</th><th style="width:70px">T</th><th style="width:80px">Average</th><th>Remarks</th><th style="width:140px;text-align:center">Evidence</th></tr></thead>
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
        <strong id="sigSupervisor">&nbsp;</strong><br><span class="text-muted">Immediate Supervisor / Rater</span>
      </div>
    </div>
    <div class="col-4 text-center">
      <div style="border-top:1px solid #333;margin-top:40px;padding-top:5px;font-size:0.82rem">
        <strong>DR. MARIA SANTOS</strong><br><span class="text-muted">Campus Executive Officer</span>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 justify-content-end mt-3 no-print flex-wrap">
    <button class="btn btn-outline-info" id="btnViewEvidence2" onclick="openEvidenceModal()"><i class="fa-solid fa-paperclip me-1"></i>View Evidence <span class="badge bg-info text-dark ms-1" id="evidenceCountBadge2">0</span></button>
    <button class="btn btn-outline-primary" id="btnUploadEvidence2" onclick="openUploadModal()"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload Evidence</button>
    <button class="btn btn-outline-secondary" onclick="showPrintPreview()"><i class="fa-solid fa-print me-1"></i>Print Preview</button>
    <button class="btn btn-outline-primary" id="btnSaveDraft2" onclick="saveIPCR('draft')"><i class="fa-solid fa-floppy-disk me-1"></i>Save Draft</button>
    <button class="btn btn-success" id="btnSubmit2" onclick="submitIPCR()"><i class="fa-solid fa-paper-plane me-1"></i>Submit for Review</button>
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
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnShowAllEv" onclick="renderEvidenceList(currentEvidence, 'All Evidence')"><i class="fa-solid fa-list me-1"></i>Show All</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="openUploadModalFromEvidence()"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload Document</button>
          </div>
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

<!-- Upload Evidence Modal -->
<div class="modal fade" id="uploadEvidenceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-cloud-arrow-up me-2 text-primary"></i>Upload Supporting Evidence</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="quickUploadForm" onsubmit="submitQuickUpload(event)">
          <div class="mb-3">
            <label class="form-label fw-600">Category</label>
            <select class="form-select form-select-sm" id="quickUploadCategory">
              <option value="core">Core Function</option>
              <option value="strategic">Strategic Function</option>
              <option value="support">Support Function</option>
              <option value="other">Other / Miscellaneous</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">MFO / Document Description</label>
            <input type="text" class="form-control form-control-sm" id="quickUploadDesc" placeholder="e.g. Syllabus, Class Record, Research Output...">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Choose File(s)</label>
            <input type="file" class="form-control form-control-sm" id="quickUploadFiles" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls,.gif,.txt,.zip,.csv" required>
            <small class="text-muted">Accepted: PDF, DOC, DOCX, JPG, PNG, XLSX (Max: 20MB per file)</small>
          </div>
          <div id="quickUploadStatus" class="alert alert-info py-2 d-none" style="font-size:0.83rem"></div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitQuickUpload"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload & Attach</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  initLayout('user', 'ipcr-form', [{ label: 'IPCR Form' }]);

  const session = SESSION_USER;
  let kpi = { core: [], strategic: [], support: [] };   // KPI catalogue (kpi_items) — never the saved rows
  let kpiRaw = [];  // flat list — used to detect personally-assigned KPIs
  let activeTimeline = null;
  let existingIpcrId = null;
  let supervisor = null;
  let currentEvidence = [];
  let _evidenceModal = null;

  document.getElementById('ipcrName').value = session.name;
  document.getElementById('ipcrPosition').value = session.position || '-';
  document.getElementById('ipcrDate').value = new Date().toISOString().split('T')[0];
  document.getElementById('sigName').textContent = session.name.toUpperCase();

  function getMatchingEvidence(categoryKey, mfoText) {
    const list = currentEvidence || [];
    const catSearch = (categoryKey || '').toLowerCase();
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

  function openEvidenceModalFor(categoryKey, mfoText) {
    const filtered = getMatchingEvidence(categoryKey, mfoText);
    const label = (mfoText ? mfoText + ' (' + (categoryKey||'').toUpperCase() + ')' : (categoryKey||'').toUpperCase() + ' Evidence');
    renderEvidenceList(filtered, label);
    _evidenceModal = _evidenceModal || new bootstrap.Modal(document.getElementById('evidenceModal'));
    document.getElementById('evidenceModalUser').textContent = (session.name || 'Employee');
    _evidenceModal.show();
  }

  function openEvidenceModal() {
    renderEvidenceList(currentEvidence, 'All Evidence');
    _evidenceModal = _evidenceModal || new bootstrap.Modal(document.getElementById('evidenceModal'));
    document.getElementById('evidenceModalUser').textContent = (session.name || 'Employee');
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
        : `<button type="button" class="btn btn-outline-secondary btn-sm" onclick="showToast('Physical file not saved on server yet. Please upload via Evidence Upload.', 'warning')"><i class="fa-solid fa-file me-1"></i>Document Details</button>`;

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

  let _uploadModal = null;

  function openUploadModal() {
    openUploadModalFor('core', '');
  }

  function openUploadModalFor(categoryKey = 'core', mfoText = '') {
    _uploadModal = _uploadModal || new bootstrap.Modal(document.getElementById('uploadEvidenceModal'));
    const catSelect = document.getElementById('quickUploadCategory');
    if (catSelect) {
      const normCat = (categoryKey || '').toLowerCase();
      if (normCat.includes('core')) catSelect.value = 'core';
      else if (normCat.includes('strat')) catSelect.value = 'strategic';
      else if (normCat.includes('supp')) catSelect.value = 'support';
      else catSelect.value = 'other';
    }
    const descInput = document.getElementById('quickUploadDesc');
    if (descInput) descInput.value = mfoText || '';
    const fileInput = document.getElementById('quickUploadFiles');
    if (fileInput) fileInput.value = '';
    const st = document.getElementById('quickUploadStatus');
    if (st) st.classList.add('d-none');
    _uploadModal.show();
  }

  function openUploadModalFromEvidence() {
    if (_evidenceModal) _evidenceModal.hide();
    openUploadModal();
  }

  function updateAllEvidenceButtons() {
    document.querySelectorAll('.evidence-cell').forEach(cell => {
      const cat = cell.dataset.cat || 'core';
      const mfo = cell.dataset.mfo || '';
      cell.innerHTML = getEvidenceBtn(cat, mfo);
    });
    const evCount = currentEvidence.length;
    const b1 = document.getElementById('evidenceCountBadge');
    const b2 = document.getElementById('evidenceCountBadge2');
    if (b1) b1.textContent = evCount;
    if (b2) b2.textContent = evCount;
  }

  async function submitQuickUpload(e) {
    e.preventDefault();
    const filesInput = document.getElementById('quickUploadFiles');
    const fileList = filesInput?.files;
    if (!fileList || fileList.length === 0) {
      showToast('Please select at least one file to upload.', 'warning');
      return;
    }

    const category = document.getElementById('quickUploadCategory').value;
    const desc = document.getElementById('quickUploadDesc').value.trim();
    const btn = document.getElementById('btnSubmitQuickUpload');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';

    const formData = new FormData();
    formData.append('category', category);
    formData.append('description', desc);
    formData.append('user_id', session.id);
    if (existingIpcrId) formData.append('ipcr_form_id', existingIpcrId);

    Array.from(fileList).forEach(f => formData.append('files[]', f));

    // Also cache Data URL locally
    Array.from(fileList).forEach(file => {
      const reader = new FileReader();
      reader.onload = function(evt) {
        const localEntry = {
          id: Date.now() + Math.random(),
          name: file.name,
          original_name: file.name,
          size: file.size,
          file_size: file.size,
          category,
          description: desc || 'Uploaded evidence',
          ext: file.name.split('.').pop().toLowerCase(),
          data_url: evt.target.result,
          date: new Date().toLocaleDateString('en-PH')
        };
        const current = JSON.parse(localStorage.getItem('csu_piat_files_' + session.id)) || [];
        current.unshift(localEntry);
        localStorage.setItem('csu_piat_files_' + session.id, JSON.stringify(current));
      };
      reader.readAsDataURL(file);
    });

    try {
      const res = await fetch(API_BASE + 'evidence/upload.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success && data.files) {
        data.files.forEach(nf => {
          currentEvidence.unshift(nf);
        });
        showToast(data.message || 'Evidence uploaded successfully!', 'success');
        updateAllEvidenceButtons();
        _uploadModal.hide();
      } else {
        showToast(data.error || 'Upload error.', 'danger');
      }
    } catch (err) {
      showToast('Uploaded to local workspace.', 'info');
      updateAllEvidenceButtons();
      _uploadModal.hide();
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload & Attach';
    }
  }

  async function initForm() {
    // Resolve the immediate supervisor for the signature blocks
    const [supRes, evRes] = await Promise.all([
      fetch(API_BASE + 'user/supervisor.php', { credentials: 'include' }).then(r => r.json()).catch(() => null),
      fetch(API_BASE + 'evidence/list.php?user_id=' + session.id, { credentials: 'include' }).then(r => r.json()).catch(() => null),
    ]);
    supervisor = supRes?.supervisor || null;
    document.getElementById('sigSupervisor').textContent = supervisor ? supervisor.name.toUpperCase() : ' ';

    // Load user's evidence from server and localStorage
    let userFiles = (evRes && evRes.files) ? [...evRes.files] : [];
    const lsFiles = JSON.parse(localStorage.getItem('csu_piat_files_' + session.id)) || [];
    lsFiles.forEach(lf => {
      if (!userFiles.some(uf => uf.id === lf.id || uf.original_name === lf.name || uf.name === lf.name)) {
        userFiles.push({
          id: lf.id,
          original_name: lf.name || lf.original_name,
          name: lf.name || lf.original_name,
          category: lf.category || 'Evidence',
          description: lf.description || 'No description',
          file_size: lf.size || lf.file_size || 0,
          uploaded_at: lf.date || lf.uploaded_at || '',
          file_path: lf.file_path || lf.path || '',
          data_url: lf.data_url || lf.file_url || ''
        });
      }
    });

    // The KPI catalogue supplies MFO / Success Indicator / Target for every row,
    // whether the form is fresh or reloaded from a saved draft.
    // Pass department_id explicitly so the server can apply scope filtering correctly
    const kpiRes = await fetch(
      `${API_BASE}kpi/list.php?department_id=${encodeURIComponent(session.department_id || '')}`,
      { credentials: 'include' }
    ).then(r => r.json()).catch(() => null);
    if (kpiRes?.grouped) kpi = kpiRes.grouped;
    if (kpiRes?.items)   kpiRaw = kpiRes.items;
    showKpiBanner();

    // Load open timeline
    const tlRes = await fetch(API_BASE + 'timeline/list.php?status=open', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    if (tlRes?.error) { showToast(tlRes.error, 'danger'); return; }
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
      const existRes = await fetch(`${API_BASE}ipcr/get.php?timeline_id=${activeTimeline.id}`, { credentials: 'include' }).then(r => r.json()).catch(() => null);
      if (existRes?.form) {
        const f = existRes.form;
        existingIpcrId = f.id;
        document.getElementById('ipcrOffice').value = f.department_name || session.department_id || '';
        document.getElementById('ipcrPeriod').value = f.covered_period;
        document.getElementById('ipcrStatus').value = f.status;

        // Merge backend evidence files
        (f.evidence_files || []).forEach(bf => {
          if (!userFiles.some(uf => uf.original_name === bf.original_name || uf.name === bf.original_name)) {
            userFiles.push(bf);
          }
        });
        currentEvidence = userFiles;

        loadSection('coreBody', f.items.core, kpi.core, 'core');
        loadSection('strategicBody', f.items.strategic, kpi.strategic, 'strategic');
        loadSection('supportBody', f.items.support, kpi.support, 'support');
      } else {
        currentEvidence = userFiles;
        loadKpiSection('coreBody', kpi.core, 'core');
        loadKpiSection('strategicBody', kpi.strategic, 'strategic');
        loadKpiSection('supportBody', kpi.support, 'support');
        // Load dept name
        document.getElementById('ipcrOffice').value = session.department_name || session.department || '';
      }
    } else {
      currentEvidence = userFiles;
      // No open timeline — show the KPI catalogue for reference but disable save/submit
      loadKpiSection('coreBody', kpi.core, 'core');
      loadKpiSection('strategicBody', kpi.strategic, 'strategic');
      loadKpiSection('supportBody', kpi.support, 'support');
      document.getElementById('ipcrOffice').value = session.department_name || session.department || '';
      document.getElementById('ipcrStatus').value = 'No open timeline';
      document.getElementById('noTimelineAlert').classList.remove('d-none');
      ['btnSaveDraft', 'btnSubmit', 'btnSaveDraft2', 'btnSubmit2'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.disabled = true; el.title = 'No active submission period is open.'; }
      });
    }

    const evCount = currentEvidence.length;
    const b1 = document.getElementById('evidenceCountBadge');
    const b2 = document.getElementById('evidenceCountBadge2');
    if (b1) b1.textContent = evCount;
    if (b2) b2.textContent = evCount;

    computeOverallRating();
  }

  initForm();

  // ── KPI info banner ─────────────────────────────────────────────────────
  function showKpiBanner() {
    const total    = kpiRaw.length;
    const personal = kpiRaw.filter(k => k.scope === 'user' && String(k.assigned_to) === String(session.id));
    const banner   = document.getElementById('kpiInfoBanner');
    const text     = document.getElementById('kpiInfoText');
    if (!banner || total === 0) return;
    let msg = `<strong>${total}</strong> KPI indicator${total !== 1 ? 's' : ''} loaded for your IPCR form.`;
    if (personal.length > 0) {
      msg += ` &nbsp;<span class="badge" style="background:#f59e0b;color:#fff;font-size:0.72rem"><i class="fa-solid fa-user-tag me-1"></i>${personal.length} personally assigned to you</span>`;
    }
    text.innerHTML = msg;
    banner.classList.remove('d-none');
  }

  // Returns a small badge tag when this KPI is personally assigned to the current user
  function personalTag(item) {
    if (item.scope === 'user' && String(item.assigned_to) === String(session.id)) {
      return `<span title="Assigned specifically to you" style="font-size:0.65rem;background:#f59e0b;color:#fff;border-radius:4px;padding:1px 5px;margin-left:4px"><i class="fa-solid fa-user-tag"></i></span>`;
    }
    return '';
  }

  function validateAccInput(input) {
    if (!input) return;
    let v = input.value;
    if (v === '') return;
    let num = parseInt(v, 10);
    if (isNaN(num)) {
      input.value = '';
      return;
    }
    if (num > 100) input.value = 100;
    else if (num < 1) input.value = 1;
    else input.value = num;
  }

  function enforceDigitsOnly(e) {
    if (['e', 'E', '+', '-', '.'].includes(e.key)) {
      e.preventDefault();
    }
  }

  function getEvidenceBtn(categoryKey, mfoText) {
    const matchedFiles = getMatchingEvidence(categoryKey, mfoText || '');
    const count = matchedFiles.length;
    const mfoSafe = (mfoText || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    const viewBtn = count > 0
      ? `<button type="button" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1" onclick="openEvidenceModalFor('${categoryKey}', '${mfoSafe}')"><i class="fa-solid fa-paperclip"></i><span>View (${count})</span></button>`
      : `<button type="button" class="btn btn-sm btn-outline-secondary opacity-75 d-inline-flex align-items-center gap-1" onclick="openEvidenceModalFor('${categoryKey}', '${mfoSafe}')"><i class="fa-solid fa-paperclip"></i><span>0 Files</span></button>`;
    const uploadBtn = `<button type="button" class="btn btn-sm btn-outline-primary" title="Upload Evidence for this MFO" onclick="openUploadModalFor('${categoryKey}', '${mfoSafe}')"><i class="fa-solid fa-cloud-arrow-up"></i></button>`;
    return `<div class="d-inline-flex align-items-center justify-content-center gap-1">${viewBtn}${uploadBtn}</div>`;
  }

  function loadKpiSection(tbodyId, items, categoryKey = 'core') {
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    (items || []).forEach(item => {
      const evidenceBtn = getEvidenceBtn(categoryKey, item.mfo);
      const mfoAttr = (item.mfo || '').replace(/"/g, '&quot;');
      tbody.innerHTML += `<tr>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${item.mfo}${personalTag(item)}</td>
        <td style="font-size:0.82rem;background:#fafafa">${item.success_indicator}</td>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${item.target || '—'}</td>
        <td><input type="number" class="form-control form-control-sm acc-input" min="1" max="100" step="1" placeholder="1-100" oninput="validateAccInput(this)" onkeydown="enforceDigitsOnly(event)"></td>
        <td><input type="number" class="form-control form-control-sm rating-q" min="1" max="5" step="0.1" placeholder="1-5" data-kpi="${item.id}" oninput="computeRowRating(this)"></td>
        <td><input type="number" class="form-control form-control-sm rating-e" min="1" max="5" step="0.1" placeholder="1-5" data-kpi="${item.id}" oninput="computeRowRating(this)"></td>
        <td><input type="number" class="form-control form-control-sm rating-t" min="1" max="5" step="0.1" placeholder="1-5" data-kpi="${item.id}" oninput="computeRowRating(this)"></td>
        <td class="text-center fw-700 row-avg" style="font-size:0.85rem;background:#fafafa">-</td>
        <td><input type="text" class="form-control form-control-sm row-remarks bg-light" placeholder="Auto" readonly></td>
        <td class="text-center evidence-cell" data-cat="${categoryKey}" data-mfo="${mfoAttr}">${evidenceBtn}</td></tr>`;
    });
  }

  function loadSection(tbodyId, items, sectionKpi, categoryKey = 'core') {
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    const allKpi = [...(kpi.core || []), ...(kpi.strategic || []), ...(kpi.support || [])];
    (items || []).forEach(item => {
      const kpiItem = allKpi.find(k => String(k.id) === String(item.kpi_id)) || {};
      const avg = parseFloat(item.rating) || 0;
      const mfo = kpiItem.mfo || item.mfo || '-';
      const evidenceBtn = getEvidenceBtn(categoryKey, mfo);
      const mfoAttr = (mfo || '').replace(/"/g, '&quot;');
      tbody.innerHTML += `<tr>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${mfo}</td>
        <td style="font-size:0.82rem;background:#fafafa">${kpiItem.success_indicator || item.success_indicator || '-'}</td>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${kpiItem.target || item.target || '-'}</td>
        <td><input type="number" class="form-control form-control-sm acc-input" min="1" max="100" step="1" placeholder="1-100" value="${item.accomplishment || ''}" oninput="validateAccInput(this)" onkeydown="enforceDigitsOnly(event)"></td>
        <td><input type="number" class="form-control form-control-sm rating-q" min="1" max="5" step="0.1" value="${item.q_rating || ''}" data-kpi="${item.kpi_id || ''}" oninput="computeRowRating(this)"></td>
        <td><input type="number" class="form-control form-control-sm rating-e" min="1" max="5" step="0.1" value="${item.e_rating || ''}" data-kpi="${item.kpi_id || ''}" oninput="computeRowRating(this)"></td>
        <td><input type="number" class="form-control form-control-sm rating-t" min="1" max="5" step="0.1" value="${item.t_rating || ''}" data-kpi="${item.kpi_id || ''}" oninput="computeRowRating(this)"></td>
        <td class="text-center fw-700 row-avg" style="font-size:0.85rem;background:#fafafa">${avg > 0 ? avg.toFixed(2) : '-'}</td>
        <td><input type="text" class="form-control form-control-sm row-remarks bg-light" value="${item.remarks || (avg > 0 ? getAdjectivalText(avg) : '')}" readonly placeholder="Auto"></td>
        <td class="text-center evidence-cell" data-cat="${categoryKey}" data-mfo="${mfoAttr}">${evidenceBtn}</td></tr>`;
    });
    // Surface KPIs added after this form was first saved (no ipcr_items row yet)
    (sectionKpi || []).forEach(k => {
      const already = (items || []).some(item => String(item.kpi_id) === String(k.id));
      if (already) return;
      const evidenceBtn = getEvidenceBtn(categoryKey, k.mfo);
      const mfoAttr = (k.mfo || '').replace(/"/g, '&quot;');
      tbody.innerHTML += `<tr>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${k.mfo || '—'}${personalTag(k)}</td>
        <td style="font-size:0.82rem;background:#fafafa">${k.success_indicator || '—'}</td>
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap">${k.target || '—'}</td>
        <td><input type="number" class="form-control form-control-sm acc-input" min="1" max="100" step="1" placeholder="1-100" oninput="validateAccInput(this)" onkeydown="enforceDigitsOnly(event)"></td>
        <td><input type="number" class="form-control form-control-sm rating-q" min="1" max="5" step="0.1" placeholder="1-5" data-kpi="${k.id}" oninput="computeRowRating(this)"></td>
        <td><input type="number" class="form-control form-control-sm rating-e" min="1" max="5" step="0.1" placeholder="1-5" data-kpi="${k.id}" oninput="computeRowRating(this)"></td>
        <td><input type="number" class="form-control form-control-sm rating-t" min="1" max="5" step="0.1" placeholder="1-5" data-kpi="${k.id}" oninput="computeRowRating(this)"></td>
        <td class="text-center fw-700 row-avg" style="font-size:0.85rem;background:#fafafa">-</td>
        <td><input type="text" class="form-control form-control-sm row-remarks bg-light" placeholder="Auto" readonly></td>
        <td class="text-center evidence-cell" data-cat="${categoryKey}" data-mfo="${mfoAttr}">${evidenceBtn}</td></tr>`;
    });
  }

  function getRows(tbodyId) {
    const rows = [];
    document.getElementById(tbodyId).querySelectorAll('tr').forEach(tr => {
      const accInp     = tr.querySelector('.acc-input') || tr.querySelector('textarea');
      const qInp       = tr.querySelector('.rating-q');
      const eInp       = tr.querySelector('.rating-e');
      const tInp       = tr.querySelector('.rating-t');
      const avgCell    = tr.querySelector('.row-avg');
      const remarksInp = tr.querySelector('.row-remarks');

      const q = parseFloat(qInp?.value) || 0;
      const e = parseFloat(eInp?.value) || 0;
      const t = parseFloat(tInp?.value) || 0;
      const a = parseFloat(avgCell?.textContent) || 0;

      rows.push({
        kpi_id:            qInp?.dataset?.kpi || eInp?.dataset?.kpi || tInp?.dataset?.kpi || '',
        success_indicator: tr.cells[1]?.textContent?.trim() || '',
        accomplishment:    accInp?.value !== undefined ? accInp.value.trim() : '',
        q_rating:          q,
        e_rating:          e,
        t_rating:          t,
        rating:            a,
        remarks:           remarksInp?.value || ''
      });
    });
    return rows;
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
    computeOverallRating();
  }

  function computeOverallRating() {
    const avgCells = document.querySelectorAll('.row-avg');
    let total = 0, count = 0;
    avgCells.forEach(cell => {
      const v = parseFloat(cell.textContent);
      if (!isNaN(v) && v > 0) { total += v; count++; }
    });
    const avg = count > 0 ? (total / count) : 0;
    const el = document.getElementById('overallRatingDisplay');
    const labelEl = document.getElementById('overallRatingLabel');
    if (el) el.textContent = avg > 0 ? avg.toFixed(2) : '-';
    if (labelEl) labelEl.innerHTML = avg > 0 ? getRatingLabel(avg) : '';
  }

  async function saveIPCR(action = 'draft') {
    const period = document.getElementById('ipcrPeriod').value.trim();
    if (!period) { showToast('Please enter the covered period.', 'warning'); return false; }
    if (!activeTimeline) { showToast('No open submission period found.', 'warning'); return false; }

    const payload = {
      action,
      ipcr_id: existingIpcrId || 0,
      timeline_id: activeTimeline.id,
      covered_period: period,
      core:      getRows('coreBody'),
      strategic: getRows('strategicBody'),
      support:   getRows('supportBody'),
    };

    try {
      const res = await fetch(API_BASE + 'ipcr/save.php', {
        method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (data.success) {
        existingIpcrId = data.ipcr_id;
        document.getElementById('ipcrStatus').value = data.status;
        showToast(data.message, 'success');
        return true;
      }
      showToast(data.error, 'danger');
      return false;
    } catch {
      showToast('Server error. Make sure XAMPP is running.', 'danger');
      return false;
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

    // Blank when the department has no assigned admin — the ruled line still prints.
    const supName = supervisor ? esc(supervisor.name) : '';

    function getFormRows(tbodyId) {
      const rows = [];
      document.getElementById(tbodyId).querySelectorAll('tr').forEach(tr => {
        const tds = tr.querySelectorAll('td');
        const accInp     = tr.querySelector('.acc-input') || tr.querySelector('textarea');
        const qInp       = tr.querySelector('.rating-q');
        const eInp       = tr.querySelector('.rating-e');
        const tInp       = tr.querySelector('.rating-t');
        const avgCell    = tr.querySelector('.row-avg');
        const remarksInp = tr.querySelector('.row-remarks');
        rows.push({
          mfo:     tds[0]?.textContent?.trim() || '',
          si:      tds[1]?.textContent?.trim() || '',
          target:  tds[2]?.textContent?.trim() || '',
          actual:  accInp?.value !== undefined ? accInp.value.trim() : '',
          q:       qInp?.value || '',
          e:       eInp?.value || '',
          t:       tInp?.value || '',
          a:       avgCell?.textContent !== '-' ? avgCell?.textContent : '',
          remarks: remarksInp?.value || ''
        });
      });
      return rows;
    }

    const core      = getFormRows('coreBody');
    const strategic = getFormRows('strategicBody');
    const support   = getFormRows('supportBody');
    const allRows   = [...core, ...strategic, ...support];
    const avgs      = allRows.map(r => parseFloat(r.a)).filter(v => v > 0);
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
        const formattedActual = r.actual ? (isNaN(r.actual) ? r.actual : r.actual + '%') : '';
        html += `<tr class="data-row">
          <td>${esc(r.mfo)}</td>
          <td>${esc(r.si)}</td>
          <td class="tc">${esc(r.target)}</td>
          <td>${esc(name)}</td>
          <td class="tc">${esc(formattedActual)}</td>
          <td class="tc">${esc(r.q)}</td>
          <td class="tc">${esc(r.e)}</td>
          <td class="tc">${esc(r.t)}</td>
          <td class="tc b">${esc(r.a)}</td>
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
        <div class="rev-name">${supName || '&nbsp;'}</div>
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
      <td class="sig-name-cell" style="border-top:1px solid #aaa">${supName || ''}<div class="rev-role">(immediate supervisor)</div></td>
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
      // Only leave the page if the submission actually went through, so a failure
      // message stays on screen instead of flashing past.
      if (await saveIPCR('submit')) {
        setTimeout(() => window.location.href = 'status.php', 1000);
      }
    });
  }
</script>
</body>
</html>
