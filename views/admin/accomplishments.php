<?php require_once '../../components/under-construction.php'; ?>
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
      <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
      <button class="btn btn-primary btn-sm" onclick="saveRatings()"><i class="fa-solid fa-save me-1"></i>Save Ratings</button>
    </div>
  </div>

  <!-- Select Employee -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Select Employee</label>
          <select class="form-select" id="selectEmployee" onchange="loadEmployee()">
            <option value="">-- Select Employee --</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">College / Office</label>
          <input type="text" class="form-control bg-light" id="accOffice" readonly>
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
          <label class="form-label">Date Submitted</label>
          <input type="text" class="form-control bg-light" id="accDate" readonly>
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
        <table class="table table-bordered mb-0">
          <thead class="table-light">
            <tr><th style="width:110px">MFO/KRA</th><th>Success Indicator</th><th>Accomplishment</th><th style="width:90px">Rating (1-5)</th><th>Remarks</th></tr>
          </thead>
          <tbody id="coreRatingBody"></tbody>
        </table>
      </div>
    </div>

    <!-- Strategic Function -->
    <div class="mb-3">
      <div class="ipcr-section-header"><i class="fa-solid fa-chess me-2"></i>B. STRATEGIC FUNCTION</div>
      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <thead class="table-light">
            <tr><th style="width:110px">MFO/KRA</th><th>Success Indicator</th><th>Accomplishment</th><th style="width:90px">Rating (1-5)</th><th>Remarks</th></tr>
          </thead>
          <tbody id="strategicRatingBody"></tbody>
        </table>
      </div>
    </div>

    <!-- Support Function -->
    <div class="mb-3">
      <div class="ipcr-section-header"><i class="fa-solid fa-hands-helping me-2"></i>C. SUPPORT FUNCTION</div>
      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <thead class="table-light">
            <tr><th style="width:110px">MFO/KRA</th><th>Success Indicator</th><th>Accomplishment</th><th style="width:90px">Rating (1-5)</th><th>Remarks</th></tr>
          </thead>
          <tbody id="supportRatingBody"></tbody>
        </table>
      </div>
    </div>

    <!-- Overall Rating -->
    <div class="card mt-3">
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
      <button class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
      <button class="btn btn-primary" onclick="saveRatings()"><i class="fa-solid fa-save me-1"></i>Save Ratings</button>
    </div>
  </div>

  <div id="emptyState" class="empty-state mt-4">
    <i class="fa-solid fa-user-check"></i>
    <p>Select an employee above to view and rate their IPCR form.</p>
  </div>
</main>

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
      o.textContent = f.user_name + ' (' + f.covered_period + ')';
      empSel.appendChild(o);
    });
    if (ipcrs.length === 0) {
      document.getElementById('emptyState').innerHTML = '<i class="fa-solid fa-inbox"></i><p>No IPCR submissions from your department yet.</p>';
    }
  }

  async function loadEmployee() {
    const id = document.getElementById('selectEmployee').value;
    if (!id) {
      document.getElementById('formSections').classList.add('d-none');
      document.getElementById('emptyState').style.display = '';
      return;
    }
    const res = await fetch(API_BASE + 'ipcr/get.php?id=' + id).then(r => r.json()).catch(() => null);
    if (!res?.form) { showToast('Could not load form.', 'danger'); return; }
    currentForm = res.form;
    const f = res.form;

    document.getElementById('accOffice').value   = f.department_name || '-';
    document.getElementById('accPosition').value = f.position || '-';
    document.getElementById('accPeriod').value   = f.covered_period;
    document.getElementById('accDate').value     = f.date_submitted || '-';
    document.getElementById('accStatus').value   = f.status;
    document.getElementById('finalStatus').value = ['reviewed','approved','disapproved'].includes(f.status) ? f.status : 'reviewed';

    loadRatingRows('coreRatingBody',      f.items.core      || []);
    loadRatingRows('strategicRatingBody', f.items.strategic || []);
    loadRatingRows('supportRatingBody',   f.items.support   || []);

    document.getElementById('formSections').classList.remove('d-none');
    document.getElementById('emptyState').style.display = 'none';
    computeOverall();
  }

  function loadRatingRows(tbodyId, items) {
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    items.forEach(item => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td style="font-size:0.82rem;background:#fafafa;white-space:nowrap" data-id="${item.id}">${item.mfo || '-'}</td>
        <td style="font-size:0.82rem">${item.success_indicator || '-'}</td>
        <td><input type="text" class="form-control form-control-sm" data-id="${item.id}" data-field="accomplishment" value="${item.accomplishment || ''}" placeholder="Actual accomplishment..."></td>
        <td><input type="number" class="form-control form-control-sm rating-input" min="1" max="5" step="0.5" data-id="${item.id}" data-field="rating" value="${item.rating || ''}" oninput="computeOverall()"></td>
        <td><input type="text" class="form-control form-control-sm" data-id="${item.id}" data-field="remarks" value="${item.remarks || ''}" placeholder="Remarks..."></td>`;
      tbody.appendChild(tr);
    });
  }

  function computeOverall() {
    const allRatings = document.querySelectorAll('.rating-input');
    let total = 0, count = 0;
    allRatings.forEach(inp => { const v = parseFloat(inp.value); if (!isNaN(v) && v > 0) { total += v; count++; } });
    const avg = count > 0 ? (total / count) : 0;
    document.getElementById('overallRatingDisplay').textContent = avg > 0 ? avg.toFixed(2) : '-';
    document.getElementById('ratingLabelDisplay').innerHTML = avg > 0 ? getRatingLabel(avg) : '';
  }

  async function saveRatings() {
    if (!currentForm) { showToast('Please select an employee first.', 'warning'); return; }

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
        showToast('Ratings saved! Overall: ' + data.overall_rating.toFixed(2) + ' (' + data.status + ')', 'success');
      } else { showToast(data.error, 'danger'); }
    } catch { showToast('Server error.', 'danger'); }
  }

  initPage();
</script>
</body>
</html>
