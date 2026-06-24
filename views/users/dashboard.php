<?php require_once '../../components/under-construction.php'; ?>
<?php
require_once '../../config/session.php';
$user = requireAuth(['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Dashboard | CSU-Piat</title>
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
    <h2><i class="fa-solid fa-gauge me-2 text-primary"></i>My Dashboard</h2>
    <p id="welcomeMsg">Welcome to CSU-Piat AOPCR/IPCR System.</p>
  </div>

  <!-- Stat Cards -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card success">
        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-value" id="approvedCount">0</div>
        <div class="stat-label">Approved Forms</div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card danger">
        <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="stat-value" id="disapprovedCount">0</div>
        <div class="stat-label">Disapproved Forms</div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card warning">
        <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-value" id="pendingCount">0</div>
        <div class="stat-label">Pending Forms</div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card info">
        <div class="stat-icon"><i class="fa-solid fa-star"></i></div>
        <div class="stat-value" id="latestRating">-</div>
        <div class="stat-label">Latest Rating</div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header"><h6><i class="fa-solid fa-chart-pie me-2 text-primary"></i>My Form Status Overview</h6></div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div class="chart-container" style="width:200px;height:200px">
            <canvas id="statusChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header"><h6><i class="fa-solid fa-info-circle me-2 text-primary"></i>My Profile Info</h6></div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush" style="font-size:0.85rem" id="profileList"></ul>
        </div>
      </div>
    </div>
  </div>

  <!-- My Forms -->
  <div class="table-wrapper mt-3">
    <div class="table-header">
      <h6><i class="fa-solid fa-file-lines me-2 text-primary"></i>My IPCR Submissions</h6>
      <a href="ipcr-form.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>New IPCR</a>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr><th>#</th><th>Covered Period</th><th>Date Submitted</th><th>Rating</th><th>Status</th><th>Action</th></tr></thead>
        <tbody id="myFormsTable"></tbody>
      </table>
    </div>
  </div>

  <!-- Active Timeline Notice -->
  <div class="alert alert-info mt-3 d-flex align-items-center gap-2" id="timelineAlert" style="display:none!important">
    <i class="fa-solid fa-calendar-check"></i>
    <div id="timelineAlertMsg"></div>
  </div>
</main>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  const session = requireAuth(['user']);
  initLayout('user', 'dashboard', [{ label: 'My Dashboard' }]);

  document.getElementById('welcomeMsg').textContent = `Welcome, ${session.name}!`;

  async function loadDashboard() {
    const res = await fetch(API_BASE + 'dashboard/user-stats.php', { credentials: 'include' }).then(r => r.json()).catch(() => null);
    if (!res?.success) { showToast('Failed to load dashboard data.', 'danger'); return; }

    const counts = res.status_counts;
    document.getElementById('approvedCount').textContent = counts.approved || 0;
    document.getElementById('disapprovedCount').textContent = counts.disapproved || 0;
    document.getElementById('pendingCount').textContent = counts.pending || 0;
    document.getElementById('latestRating').textContent = res.latest_rating ? parseFloat(res.latest_rating).toFixed(1) : '-';

    new Chart(document.getElementById('statusChart'), {
      type: 'doughnut',
      data: { labels: ['Approved', 'Pending', 'Reviewed', 'Disapproved'], datasets: [{ data: [counts.approved, counts.pending, counts.reviewed, counts.disapproved], backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#dc3545'], borderWidth: 2, borderColor: '#fff' }] },
      options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }, cutout: '65%' }
    });

    const profileList = document.getElementById('profileList');
    [
      ['fa-user', 'Full Name', session.name],
      ['fa-id-badge', 'Username', session.username],
      ['fa-briefcase', 'Position', session.position || '-'],
      ['fa-envelope', 'Email', session.email || '-'],
      ['fa-venus-mars', 'Gender', session.gender || '-'],
    ].forEach(([icon, label, value]) => {
      profileList.innerHTML += `<li class="list-group-item d-flex justify-content-between py-2"><span><i class="fa-solid ${icon} me-2 text-primary"></i>${label}</span><span class="text-muted">${value}</span></li>`;
    });

    const tbody = document.getElementById('myFormsTable');
    const forms = res.recent_forms || [];
    if (forms.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No IPCR forms submitted yet. <a href="ipcr-form.php">Submit one now.</a></td></tr>`;
    } else {
      forms.forEach((f, i) => {
        tbody.innerHTML += `<tr>
          <td>${i+1}</td>
          <td style="font-size:0.85rem">${f.covered_period || f.coveredPeriod || '-'}</td>
          <td style="font-size:0.82rem">${formatDate(f.date_submitted || f.date)}</td>
          <td style="font-size:0.85rem">${(f.overall_rating || f.rating) > 0 ? parseFloat(f.overall_rating || f.rating).toFixed(1) : '-'}</td>
          <td>${getStatusBadge(f.status)}</td>
          <td><a href="status.php" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-eye me-1"></i>View</a></td></tr>`;
      });
    }

    if (res.active_timeline) {
      const tl = res.active_timeline;
      document.getElementById('timelineAlert').style.removeProperty('display');
      document.getElementById('timelineAlertMsg').innerHTML = `<strong>Active Timeline:</strong> ${tl.academic_year} — ${tl.semester} | Submission Deadline: <strong>${formatDate(tl.submission_deadline)}</strong>`;
    }
  }

  loadDashboard();
</script>
</body>
</html>
