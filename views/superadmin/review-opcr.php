<?php
require_once '../../config/session.php';
$user = requireAuth(['superadmin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review OPCR Submissions | CSU-Piat</title>
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
      <h2><i class="fa-solid fa-building-columns me-2 text-primary"></i>Review OPCR Submissions</h2>
      <p>Review and approve/disapprove Office Performance Commitment and Review forms submitted by Deans and Department Heads.</p>
    </div>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" id="filterStatus" onchange="loadForms()">
        <option value="">All Status</option>
        <option value="pending" selected>Pending Review</option>
        <option value="reviewed">Reviewed</option>
        <option value="approved">Approved</option>
        <option value="disapproved">Disapproved</option>
      </select>
    </div>
  </div>

  <!-- Status Cards -->
  <div class="row g-3 mb-4" id="statCards"></div>

  <!-- Forms Table -->
  <div class="table-wrapper">
    <div class="table-header">
      <h6><i class="fa-solid fa-list me-2"></i>OPCR Submissions</h6>
      <small class="text-muted" id="formCount"></small>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Office / Department</th>
            <th>Admin / Dean</th>
            <th>Covered Period</th>
            <th>Date Submitted</th>
            <th>Overall Rating</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="formsTable"></tbody>
      </table>
    </div>
  </div>
</main>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-building-columns me-2"></i>Review OPCR Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="reviewBody">
        <div class="text-center py-4"><span class="spinner-border text-primary"></span></div>
      </div>
      <div class="modal-footer">
        <div class="me-auto d-flex gap-2 align-items-center">
          <label class="form-label mb-0">Decision:</label>
          <select class="form-select form-select-sm" id="finalStatus" style="width:180px">
            <option value="reviewed">Reviewed</option>
            <option value="approved">Approved</option>
            <option value="disapproved">Disapproved</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="fw-700" style="font-size:0.85rem">Overall Rating:</span>
          <span id="modalRatingDisplay" class="fw-700 text-primary" style="font-size:1.2rem">-</span>
        </div>
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="saveReviewBtn" onclick="saveReview()">
          <i class="fa-solid fa-save me-1"></i>Save Decision
        </button>
      </div>
    </div>
  </div>
</div>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  initLayout('superadmin', 'review-opcr', [{ label: 'Review OPCR' }]);

  const session = SESSION_USER;
  let _currentForm = null;
  let _reviewModal = null;

  async function loadStats() {
    const res = await fetch(API_BASE + 'opcr/list.php').then(r => r.json()).catch(() => ({ forms: [] }));
    const forms = res.forms || [];
    const statusList = [
      { key: 'pending',     label: 'Pending',     color: 'warning', icon: 'fa-clock' },
      { key: 'reviewed',    label: 'Reviewed',    color: 'info',    icon: 'fa-eye' },
      { key: 'approved',    label: 'Approved',    color: 'success', icon: 'fa-check-circle' },
      { key: 'disapproved', label: 'Disapproved', color: 'danger',  icon: 'fa-times-circle' },
    ];
    const cards = document.getElementById('statCards');
    statusList.forEach(s => {
      const cnt = forms.filter(f => f.status === s.key).length;
      cards.innerHTML += `<div class="col-sm-6 col-xl-3">
        <div class="stat-card ${s.color}">
          <div class="stat-icon"><i class="fa-solid ${s.icon}"></i></div>
          <div class="stat-value">${cnt}</div>
          <div class="stat-label">${s.label}</div>
        </div></div>`;
    });
  }

  async function loadForms() {
    const status = document.getElementById('filterStatus').value;
    const url = API_BASE + 'opcr/list.php' + (status ? '?status=' + status : '');
    const res = await fetch(url).then(r => r.json()).catch(() => ({ forms: [] }));
    const forms = res.forms || [];
    document.getElementById('formCount').textContent = forms.length + ' record(s)';
    const tbody = document.getElementById('formsTable');
    if (forms.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fa-solid fa-inbox me-2"></i>No OPCR submissions found.</td></tr>`;
      return;
    }
    tbody.innerHTML = forms.map((f, i) => {
      const rating = parseFloat(f.overall_rating) || 0;
      return `<tr>
        <td>${i+1}</td>
        <td style="font-size:0.85rem"><strong>${f.department_name || f.department_id}</strong></td>
        <td style="font-size:0.82rem">${f.admin_name}</td>
        <td style="font-size:0.82rem">${f.covered_period}</td>
        <td style="font-size:0.82rem">${f.date_submitted || '-'}</td>
        <td style="font-size:0.85rem">${rating > 0 ? rating.toFixed(2) : '-'}</td>
        <td>${getStatusBadge(f.status)}</td>
        <td><button class="btn btn-outline-primary btn-sm" onclick="openReview(${f.id})"><i class="fa-solid fa-eye me-1"></i>Review</button></td>
      </tr>`;
    }).join('');
  }

  async function openReview(id) {
    _reviewModal = _reviewModal || new bootstrap.Modal(document.getElementById('reviewModal'));
    document.getElementById('reviewBody').innerHTML = '<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>';
    document.getElementById('modalRatingDisplay').textContent = '-';
    _reviewModal.show();

    const res = await fetch(API_BASE + 'opcr/get.php?id=' + id).then(r => r.json()).catch(() => null);
    if (!res?.form) {
      document.getElementById('reviewBody').innerHTML = '<div class="alert alert-danger">Could not load form.</div>';
      return;
    }
    _currentForm = res.form;
    const f = res.form;
    const rating = parseFloat(f.overall_rating) || 0;
    document.getElementById('finalStatus').value = ['reviewed','approved','disapproved'].includes(f.status) ? f.status : 'reviewed';
    document.getElementById('modalRatingDisplay').textContent = rating > 0 ? rating.toFixed(2) : '-';

    const secLabels = { core: 'A. CORE FUNCTION', strategic: 'B. STRATEGIC FUNCTION', support: 'C. SUPPORT FUNCTION' };
    let html = `<div class="row g-2 mb-3 p-3 bg-light rounded" style="font-size:0.85rem">
      <div class="col-md-4"><strong>Department:</strong> ${f.department_name || f.department_id}</div>
      <div class="col-md-4"><strong>Admin / Dean:</strong> ${f.admin_name}</div>
      <div class="col-md-4"><strong>Position:</strong> ${f.position || '-'}</div>
      <div class="col-md-4"><strong>Covered Period:</strong> ${f.covered_period}</div>
      <div class="col-md-4"><strong>Date Submitted:</strong> ${f.date_submitted || '-'}</div>
      <div class="col-md-4"><strong>Current Status:</strong> ${getStatusBadge(f.status)}</div>
    </div>
    <div class="mb-3">
      <label class="form-label fw-700">Remarks for Admin</label>
      <textarea class="form-control" id="reviewRemarks" rows="2" placeholder="Optional remarks...">${f.remarks || ''}</textarea>
    </div>`;

    ['core', 'strategic', 'support'].forEach(sec => {
      const items = f.items[sec];
      if (!items?.length) return;
      html += `<div class="ipcr-section-header">${secLabels[sec]}</div>
        <div class="table-responsive mb-3">
        <table class="table table-bordered table-sm mb-0">
          <thead class="table-light"><tr>
            <th>MFO/PAP</th><th>Success Indicator</th><th>Target</th>
            <th>Actual Accomplishment</th><th style="width:100px">Rating (1-5)</th><th>Remarks</th>
          </tr></thead>
          <tbody>
          ${items.map(item => `<tr>
            <td style="font-size:0.8rem;background:#fafafa">${item.mfo || '-'}</td>
            <td style="font-size:0.8rem;background:#fafafa">${item.success_indicator || '-'}</td>
            <td style="font-size:0.8rem;background:#fafafa">${item.target || '-'}</td>
            <td><input type="text" class="form-control form-control-sm" data-id="${item.id}" data-field="actual" value="${item.actual || ''}"></td>
            <td><input type="number" class="form-control form-control-sm rating-input" min="1" max="5" step="0.5"
                 data-id="${item.id}" data-field="rating" value="${item.rating || ''}" oninput="recompute()"></td>
            <td><input type="text" class="form-control form-control-sm" data-id="${item.id}" data-field="remarks" value="${item.remarks || ''}"></td>
          </tr>`).join('')}
          </tbody>
        </table></div>`;
    });

    document.getElementById('reviewBody').innerHTML = html;
    recompute();
  }

  function recompute() {
    const inputs = document.querySelectorAll('.rating-input');
    let total = 0, count = 0;
    inputs.forEach(inp => { const v = parseFloat(inp.value); if (!isNaN(v) && v > 0) { total += v; count++; } });
    const avg = count > 0 ? (total / count).toFixed(2) : '-';
    document.getElementById('modalRatingDisplay').textContent = avg;
  }

  async function saveReview() {
    if (!_currentForm) return;
    const btn = document.getElementById('saveReviewBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    const ratings = [];
    document.querySelectorAll('[data-field]').forEach(el => {
      const id = parseInt(el.dataset.id);
      const field = el.dataset.field;
      if (!id) return;
      let entry = ratings.find(r => r.item_id === id);
      if (!entry) { entry = { item_id: id }; ratings.push(entry); }
      entry[field] = el.value;
    });

    try {
      const res = await fetch(API_BASE + 'opcr/review.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          opcr_id: _currentForm.id,
          status:  document.getElementById('finalStatus').value,
          remarks: document.getElementById('reviewRemarks').value.trim(),
          ratings,
        }),
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
        loadForms();
        document.getElementById('statCards').innerHTML = '';
        loadStats();
      } else { showToast(data.error, 'danger'); }
    } catch { showToast('Server error.', 'danger'); }
    finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-save me-1"></i>Save Decision';
    }
  }

  loadStats();
  loadForms();
</script>
</body>
</html>
