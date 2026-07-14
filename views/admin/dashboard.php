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
  <title>Admin Dashboard | CSU-Piat</title>
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
    <h2><i class="fa-solid fa-gauge me-2 text-primary"></i>Admin Dashboard</h2>
    <p id="deptLabel">Loading department information...</p>
  </div>

  <!-- Stat Cards -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
        <div class="stat-value" id="totalEmp">0</div>
        <div class="stat-label">Total Employees in Dept.</div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card info">
        <div class="stat-icon"><i class="fa-solid fa-file-lines"></i></div>
        <div class="stat-value" id="totalForms">0</div>
        <div class="stat-label">Total IPCR Submissions</div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card success">
        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-value" id="totalApproved">0</div>
        <div class="stat-label">Approved Forms</div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card danger">
        <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="stat-value" id="totalDisapproved">0</div>
        <div class="stat-label">Disapproved Forms</div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row g-3 mb-4">
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header"><h6><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Approval Statistics</h6></div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div class="chart-container" style="width:220px;height:220px">
            <canvas id="approvalChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header"><h6><i class="fa-solid fa-chart-bar me-2 text-primary"></i>Employee Rating Overview</h6></div>
        <div class="card-body">
          <canvas id="ratingChart" height="180"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Submissions -->
  <div class="table-wrapper">
    <div class="table-header">
      <h6><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Recent IPCR Submissions</h6>
      <a href="reports.php" class="btn btn-outline-primary btn-sm">View All</a>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr><th>Employee</th><th>Position</th><th>Covered Period</th><th>Status</th><th>Rating</th><th>Date</th><th>Action</th></tr></thead>
        <tbody id="submissionsTable"></tbody>
      </table>
    </div>
  </div>
</main>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Update Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" id="reviewFormId">
        <div class="mb-3">
          <label class="form-label">New Status</label>
          <select class="form-select" id="reviewStatus">
            <option value="reviewed">Reviewed</option>
            <option value="approved">Approved</option>
            <option value="disapproved">Disapproved</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Remarks (optional)</label>
          <textarea class="form-control" id="reviewRemarks" rows="2" placeholder="Enter remarks..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" onclick="saveReview()">Save</button>
      </div>
    </div>
  </div>
</div>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  const session = requireAuth(['admin']);
  initLayout('admin', 'dashboard', [{ label: 'Dashboard' }]);

  document.getElementById('deptLabel').textContent = `Welcome, ${session.name}!`;

  async function loadDashboard() {
    const res = await fetch(API_BASE + 'dashboard/admin-stats.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    if (!res?.success) { showToast('Failed to load dashboard data.', 'danger'); return; }

    const counts = res.ipcr_counts;
    const total = Object.values(counts).reduce((a, b) => a + b, 0);
    document.getElementById('totalEmp').textContent = res.faculty_count;
    document.getElementById('totalForms').textContent = total;
    document.getElementById('totalApproved').textContent = counts.approved || 0;
    document.getElementById('totalDisapproved').textContent = counts.disapproved || 0;

    new Chart(document.getElementById('approvalChart'), {
      type: 'doughnut',
      data: { labels: ['Approved', 'Pending', 'Reviewed', 'Disapproved'], datasets: [{ data: [counts.approved, counts.pending, counts.reviewed, counts.disapproved], backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#dc3545'], borderWidth: 2, borderColor: '#fff' }] },
      options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }, cutout: '65%' }
    });

    const pending = res.pending_reviews || [];
    const ratedForms = pending.filter(f => (f.overall_rating || 0) > 0);
    new Chart(document.getElementById('ratingChart'), {
      type: 'bar',
      data: {
        labels: ratedForms.map(f => (f.user_name || '').split(' ').slice(-1)[0]),
        datasets: [{ label: 'Rating', data: ratedForms.map(f => parseFloat(f.overall_rating)), backgroundColor: ratedForms.map(f => f.overall_rating >= 4.5 ? '#198754' : f.overall_rating >= 3.5 ? '#E85C0D' : f.overall_rating >= 2.5 ? '#FABC3F' : '#C7253E'), borderRadius: 4 }]
      },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { min: 0, max: 5, ticks: { stepSize: 1 } } } }
    });

    await loadSubmissions();
  }

  async function loadSubmissions() {
    const res = await fetch(API_BASE + 'ipcr/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    const forms = res?.forms || [];
    const tbody = document.getElementById('submissionsTable');
    tbody.innerHTML = '';
    if (forms.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">No submissions found.</td></tr>`;
      return;
    }
    forms.forEach(f => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td style="font-size:0.85rem">${f.user_name}</td>
        <td style="font-size:0.8rem">${f.position || '-'}</td>
        <td style="font-size:0.8rem">${f.covered_period}</td>
        <td>${getStatusBadge(f.status)}</td>
        <td style="font-size:0.85rem">${f.overall_rating > 0 ? parseFloat(f.overall_rating).toFixed(1) : '-'}</td>
        <td style="font-size:0.78rem">${formatDate(f.date_submitted)}</td>
        <td><button class="btn btn-outline-primary btn-sm" onclick="openReview(${f.id},'${f.status}')"><i class="fa-solid fa-pen-to-square"></i></button></td>`;
      tbody.appendChild(tr);
    });
  }

  function openReview(id, currentStatus) {
    document.getElementById('reviewFormId').value = id;
    document.getElementById('reviewStatus').value = currentStatus !== 'pending' ? currentStatus : 'reviewed';
    document.getElementById('reviewRemarks').value = '';
    new bootstrap.Modal(document.getElementById('reviewModal')).show();
  }

  async function saveReview() {
    const id = document.getElementById('reviewFormId').value;
    const status = document.getElementById('reviewStatus').value;
    const remarks = document.getElementById('reviewRemarks').value;
    const res = await fetch(API_BASE + 'ipcr/review.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ipcr_id: id, status, remarks })
    }).then(r => r.json()).catch(() => null);
    if (res?.success) {
      bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
      showToast('Form status updated to ' + status + '.', 'success');
      setTimeout(() => location.reload(), 800);
    } else {
      showToast(res?.error || 'Failed to update status.', 'danger');
    }
  }

  loadDashboard();
</script>
</body>
</html>
