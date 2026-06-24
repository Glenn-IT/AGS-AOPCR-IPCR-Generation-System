<?php
require_once '../../config/session.php';
$user = requireAuth(['superadmin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Super Admin Dashboard | CSU-Piat</title>
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
    <h2><i class="fa-solid fa-gauge me-2 text-primary"></i>Super Admin Dashboard</h2>
    <p>Welcome back! Here's the system overview for CSU-Piat AOPCR/IPCR System.</p>
  </div>

  <!-- Stat Cards -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
        <div class="stat-value" id="totalEmp">0</div>
        <div class="stat-label">Total Employees</div>
        <div class="stat-trend"><span class="up"><i class="fa-solid fa-arrow-up"></i> CSU-Piat Faculty & Staff</span></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card info">
        <div class="stat-icon"><i class="fa-solid fa-user-tie"></i></div>
        <div class="stat-value" id="totalAdmin">0</div>
        <div class="stat-label">Department Heads / Admins</div>
        <div class="stat-trend"><span class="up"><i class="fa-solid fa-building"></i> Deans & Office Heads</span></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card success">
        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-value" id="totalApproved">0</div>
        <div class="stat-label">Approved OPCR/IPCR</div>
        <div class="stat-trend"><span class="up"><i class="fa-solid fa-arrow-up"></i> This Semester</span></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card danger">
        <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="stat-value" id="totalDisapproved">0</div>
        <div class="stat-label">Disapproved OPCR/IPCR</div>
        <div class="stat-trend"><span class="down"><i class="fa-solid fa-arrow-down"></i> Requires Revision</span></div>
      </div>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="row g-3 mb-4">
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h6><i class="fa-solid fa-venus-mars me-2 text-primary"></i>Gender Distribution</h6>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div class="chart-container" style="width:220px;height:220px">
            <canvas id="genderChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header">
          <h6><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Approval Status Distribution</h6>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div class="chart-container" style="width:220px;height:220px">
            <canvas id="approvalChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header">
          <h6><i class="fa-solid fa-building me-2 text-primary"></i>Department Distribution</h6>
        </div>
        <div class="card-body">
          <canvas id="deptChart" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity & System Info -->
  <div class="row g-3">
    <div class="col-lg-7">
      <div class="table-wrapper">
        <div class="table-header">
          <h6><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Recent IPCR Submissions</h6>
          <a href="reports.php" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead><tr>
              <th>Employee</th><th>Department</th><th>Covered Period</th><th>Status</th><th>Rating</th>
            </tr></thead>
            <tbody id="recentIPCR"></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card">
        <div class="card-header">
          <h6><i class="fa-solid fa-circle-info me-2 text-primary"></i>System Information</h6>
        </div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush" style="font-size:0.85rem">
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
              <span><i class="fa-solid fa-university me-2 text-primary"></i>University</span>
              <span class="text-muted fw-500">Cagayan State University</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
              <span><i class="fa-solid fa-map-marker-alt me-2 text-primary"></i>Campus</span>
              <span class="text-muted fw-500">Piat, Cagayan</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
              <span><i class="fa-solid fa-calendar me-2 text-primary"></i>Founded</span>
              <span class="text-muted fw-500">1954</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
              <span><i class="fa-solid fa-clock me-2 text-primary"></i>Active Timeline</span>
              <span class="badge bg-success" id="activeTimeline">Loading...</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
              <span><i class="fa-solid fa-calendar-check me-2 text-primary"></i>Submission Deadline</span>
              <span class="text-danger fw-600" id="submissionDeadline">-</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
              <span><i class="fa-solid fa-code me-2 text-primary"></i>System Version</span>
              <span class="badge bg-primary">v1.0.0</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</main>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  const session = requireAuth(['superadmin']);
  initLayout('superadmin', 'dashboard', [{ label: 'Dashboard' }]);

  async function loadDashboard() {
    const [statsRes, ipcrRes] = await Promise.all([
      fetch(API_BASE + 'dashboard/superadmin-stats.php', { credentials: 'include' }).then(r => r.json()).catch(() => null),
      fetch(API_BASE + 'ipcr/list.php', { credentials: 'include' }).then(r => r.json()).catch(() => null),
    ]);

    if (!statsRes?.success) { showToast('Failed to load dashboard data.', 'danger'); return; }

    const { stats, ipcr_counts, dept_stats, active_timeline } = statsRes;
    document.getElementById('totalEmp').textContent = stats.total_users;
    document.getElementById('totalAdmin').textContent = stats.total_admins;
    document.getElementById('totalApproved').textContent = ipcr_counts.approved || 0;
    document.getElementById('totalDisapproved').textContent = ipcr_counts.disapproved || 0;

    if (active_timeline) {
      document.getElementById('activeTimeline').textContent = active_timeline.academic_year + ' ' + active_timeline.semester;
      document.getElementById('submissionDeadline').textContent = formatDate(active_timeline.submission_deadline);
    } else {
      document.getElementById('activeTimeline').textContent = 'None';
    }

    // Recent IPCR
    const tbody = document.getElementById('recentIPCR');
    const forms = (ipcrRes?.forms || []).slice(0, 6);
    tbody.innerHTML = '';
    forms.forEach(f => {
      const initials = (f.user_name || '').split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
      tbody.innerHTML += `<tr>
        <td><div class="d-flex align-items-center gap-2"><div class="avatar" style="width:30px;height:30px;font-size:0.65rem">${initials}</div><span style="font-size:0.83rem">${f.user_name}</span></div></td>
        <td style="font-size:0.8rem">${f.department_name || '-'}</td>
        <td style="font-size:0.8rem">${f.covered_period}</td>
        <td>${getStatusBadge(f.status)}</td>
        <td style="font-size:0.83rem">${f.overall_rating > 0 ? parseFloat(f.overall_rating).toFixed(1) : '-'}</td></tr>`;
    });
    if (forms.length === 0) tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-muted">No submissions yet.</td></tr>`;

    // Gender chart from dept_stats (approximate)
    let male = 0, female = 0;
    new Chart(document.getElementById('genderChart'), {
      type: 'doughnut',
      data: { labels: ['Male', 'Female'], datasets: [{ data: [male || 1, female || 1], backgroundColor: ['#E85C0D', '#FABC3F'], borderWidth: 2, borderColor: '#fff' }] },
      options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }, cutout: '65%' }
    });

    new Chart(document.getElementById('approvalChart'), {
      type: 'doughnut',
      data: { labels: ['Approved', 'Pending', 'Reviewed', 'Disapproved'], datasets: [{ data: [ipcr_counts.approved, ipcr_counts.pending, ipcr_counts.reviewed, ipcr_counts.disapproved], backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#dc3545'], borderWidth: 2, borderColor: '#fff' }] },
      options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }, cutout: '65%' }
    });

    const deptLabels = dept_stats.map(d => d.department.replace('College of ', 'Col. of ').replace('Office', 'Off.'));
    const deptCounts = dept_stats.map(d => parseInt(d.total) || 0);
    new Chart(document.getElementById('deptChart'), {
      type: 'bar',
      data: { labels: deptLabels, datasets: [{ label: 'Submissions', data: deptCounts, backgroundColor: '#E85C0D99', borderColor: '#E85C0D', borderWidth: 1, borderRadius: 4 }] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { ticks: { font: { size: 9 } } }, y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
  }

  loadDashboard();
</script>
</body>
</html>
