// CSU-PIAT Shared Components & Utilities

function showToast(message, type = 'info', title = '') {
  const icons = { success: 'fa-check-circle text-success', danger: 'fa-times-circle text-danger', warning: 'fa-exclamation-triangle text-warning', info: 'fa-info-circle text-primary' };
  const titles = { success: 'Success', danger: 'Error', warning: 'Warning', info: 'Info' };
  const container = document.getElementById('toast-container') || (() => {
    const c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); return c;
  })();
  const t = document.createElement('div');
  t.className = `toast-item ${type}`;
  t.innerHTML = `<i class="fa-solid ${icons[type]} toast-icon"></i><div class="toast-body"><strong>${title || titles[type]}</strong><p>${message}</p></div><button onclick="this.parentElement.remove()" class="btn-close btn-sm ms-auto" style="font-size:0.6rem"></button>`;
  container.appendChild(t);
  setTimeout(() => { if (t.parentNode) { t.style.opacity = '0'; t.style.transform = 'translateX(30px)'; t.style.transition = 'all 0.3s'; setTimeout(() => t.remove(), 300); } }, 3500);
}

function confirmModal(message, title = 'Confirm Action', onConfirm) {
  const existing = document.getElementById('globalConfirmModal');
  if (existing) existing.remove();
  const modal = document.createElement('div');
  modal.className = 'modal fade'; modal.id = 'globalConfirmModal'; modal.tabIndex = -1;
  modal.innerHTML = `<div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-question-circle me-2"></i>${title}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p class="mb-0">${message}</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary btn-sm" id="confirmOkBtn">Confirm</button></div></div></div>`;
  document.body.appendChild(modal);
  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
  document.getElementById('confirmOkBtn').addEventListener('click', () => { bsModal.hide(); modal.addEventListener('hidden.bs.modal', () => { onConfirm(); modal.remove(); }, { once: true }); });
}

function formatDate(dateStr) {
  if (!dateStr || dateStr === '-') return '-';
  const d = new Date(dateStr);
  return isNaN(d) ? dateStr : d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

function getStatusBadge(status) {
  const map = {
    draft:       ['bg-secondary', 'Draft'],
    pending:     ['bg-warning text-dark', 'Pending'],
    reviewed:    ['bg-info text-dark', 'Reviewed'],
    approved:    ['bg-success', 'Approved'],
    disapproved: ['bg-danger', 'Disapproved'],
    active:      ['bg-success', 'Active'],
    inactive:    ['bg-secondary', 'Inactive'],
    open:        ['bg-success', 'Open'],
    closed:      ['bg-danger', 'Closed'],
  };
  const [cls, label] = map[status] || ['bg-secondary', status || '-'];
  return `<span class="badge ${cls}">${label}</span>`;
}

function getRatingLabel(rating) {
  const v = parseFloat(rating);
  if (!v || v <= 0) return '';
  if (v >= 4.5) return '<span class="badge bg-success">Outstanding</span>';
  if (v >= 3.5) return '<span class="badge" style="background:#E85C0D">Very Satisfactory</span>';
  if (v >= 2.5) return '<span class="badge bg-warning text-dark">Satisfactory</span>';
  if (v >= 1.5) return '<span class="badge bg-danger">Unsatisfactory</span>';
  return '<span class="badge bg-dark">Poor</span>';
}

function buildSidebar(role, activePage) {
  const menus = {
    superadmin: [
      { section: 'Main' },
      { icon: 'fa-gauge', label: 'Dashboard', href: 'dashboard.php', page: 'dashboard' },
      { section: 'OPCR / Performance' },
      { icon: 'fa-bullseye', label: 'OPCR Form', href: 'set-target.php', page: 'set-target' },
      { icon: 'fa-clipboard-check', label: 'Accomplishment & Ratings', href: 'accomplishments.php', page: 'accomplishments' },
      { section: 'Management' },
      { icon: 'fa-users', label: 'Account Management', href: 'accounts.php', page: 'accounts' },
      { icon: 'fa-chart-bar', label: 'Reports', href: 'reports.php', page: 'reports' },
      { section: 'Configuration' },
      { icon: 'fa-gear', label: 'Settings', href: 'settings.php', page: 'settings' },
      { section: 'Account' },
      { icon: 'fa-user-cog', label: 'My Profile', href: 'account.php', page: 'account' }
    ],
    admin: [
      { section: 'Main' },
      { icon: 'fa-gauge', label: 'Dashboard', href: 'dashboard.php', page: 'dashboard' },
      { section: 'IPCR / Performance' },
      { icon: 'fa-file-lines', label: 'IPCR Form', href: 'ipcr-form.php', page: 'ipcr-form' },
      { icon: 'fa-clipboard-check', label: 'Accomplishments & Ratings', href: 'accomplishments.php', page: 'accomplishments' },
      { icon: 'fa-file-alt', label: 'Reports', href: 'reports.php', page: 'reports' },
      { section: 'Account' },
      { icon: 'fa-user-cog', label: 'My Profile', href: 'account.php', page: 'account' }
    ],
    user: [
      { section: 'Main' },
      { icon: 'fa-gauge', label: 'Dashboard', href: 'dashboard.php', page: 'dashboard' },
      { section: 'Performance' },
      { icon: 'fa-file-lines', label: 'IPCR Form', href: 'ipcr-form.php', page: 'ipcr-form' },
      { icon: 'fa-paperclip', label: 'Evidence Upload', href: 'evidence.php', page: 'evidence' },
      { icon: 'fa-eye', label: 'View Status', href: 'status.php', page: 'status' },
      { section: 'Account' },
      { icon: 'fa-user-cog', label: 'My Profile', href: 'account.php', page: 'account' }
    ]
  };

  const items = menus[role] || menus.user;
  // Use PHP-injected SESSION_USER (not localStorage)
  const session = window.SESSION_USER || {};
  const roleLabels = { superadmin: 'Super Administrator', admin: 'Administrator', user: 'Faculty / Staff' };

  return `
    <div class="sidebar" id="sidebar">
      <a class="sidebar-brand" href="#">
        <div class="sidebar-logo"><i class="fa-solid fa-university"></i></div>
        <div class="sidebar-title">CSU-Piat<span>AOPCR/IPCR System</span></div>
      </a>
      <div class="sidebar-nav">
        ${items.map(item => {
          if (item.section) return `<div class="sidebar-section">${item.section}</div>`;
          return `<a href="${item.href}" class="sidebar-item ${activePage === item.page ? 'active' : ''}" data-title="${item.label}">
            <i class="fa-solid ${item.icon}"></i><span>${item.label}</span></a>`;
        }).join('')}
        <div class="sidebar-section">Session</div>
        <a href="#" onclick="handleLogout(event)" class="sidebar-item" data-title="Logout">
          <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
      </div>
      <div style="padding:12px 16px;border-top:1px solid rgba(255,255,255,0.15);margin-top:auto">
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:32px;height:32px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.7rem;font-weight:700;flex-shrink:0">${session.avatar || 'U'}</div>
          <div style="overflow:hidden;transition:all 0.3s" class="sidebar-user-info">
            <div style="color:#fff;font-size:0.75rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${session.name || 'User'}</div>
            <div style="color:rgba(255,255,255,0.6);font-size:0.65rem">${roleLabels[session.role] || 'User'}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>`;
}

function buildNavbar(pageTitle, breadcrumbs = []) {
  const session = window.SESSION_USER || {};
  const roleLabels = { superadmin: 'Super Administrator', admin: 'Administrator', user: 'Faculty / Staff' };
  const crumbHtml = breadcrumbs.map((b, i) =>
    i === breadcrumbs.length - 1
      ? `<li class="breadcrumb-item active">${b.label}</li>`
      : `<li class="breadcrumb-item"><a href="${b.href}" class="text-decoration-none">${b.label}</a></li>`
  ).join('');

  return `
    <nav class="topnav" id="topnav">
      <button class="topnav-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="breadcrumb-nav">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none"><i class="fa-solid fa-house"></i></a></li>
            ${crumbHtml}
          </ol>
        </nav>
      </div>
      <div class="topnav-right">
        <button class="topnav-btn" title="Notifications" onclick="showNotifications()">
          <i class="fa-solid fa-bell"></i>
          <span class="badge-dot"></span>
        </button>
        <div class="dropdown">
          <button class="user-avatar-btn dropdown-toggle" data-bs-toggle="dropdown" type="button">
            <div class="user-avatar">${session.avatar || 'U'}</div>
            <div class="user-info">
              <div class="user-name">${session.name || 'User'}</div>
              <div class="user-role">${roleLabels[session.role] || 'User'}</div>
            </div>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:0.85rem;border:none;border-radius:10px">
            <li><div class="px-3 py-2 border-bottom">
              <div class="fw-600" style="font-size:0.88rem">${session.name || ''}</div>
              <div class="text-muted" style="font-size:0.75rem">${session.email || ''}</div>
            </div></li>
            <li><a class="dropdown-item" href="account.php"><i class="fa-solid fa-user me-2 text-primary"></i>My Profile</a></li>
            <li><a class="dropdown-item" href="#" onclick="handleLogout(event)"><i class="fa-solid fa-right-from-bracket me-2 text-danger"></i>Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>`;
}

function buildFooter() {
  return `<footer class="main-footer" id="mainFooter">
    <span>© 2026 Cagayan State University - Piat Campus | AOPCR/IPCR Generation System | <span class="text-primary">v2.0</span></span>
  </footer>`;
}

function initLayout(role, activePage, breadcrumbs) {
  const sb = document.getElementById('sidebar-container');
  const nb = document.getElementById('navbar-container');
  const ft = document.getElementById('footer-container');
  if (sb) sb.innerHTML = buildSidebar(role, activePage);
  if (nb) nb.innerHTML = buildNavbar(activePage, breadcrumbs);
  if (ft) ft.innerHTML = buildFooter();
  initSidebarState();
}

let sidebarCollapsed = false;

function initSidebarState() {
  sidebarCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
  applySidebarState();
}

function toggleSidebar() {
  const isMobile = window.innerWidth < 992;
  if (isMobile) {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.toggle('mobile-open');
    if (overlay) overlay.classList.toggle('show');
  } else {
    sidebarCollapsed = !sidebarCollapsed;
    localStorage.setItem('sidebar_collapsed', String(sidebarCollapsed));
    applySidebarState();
  }
}

function applySidebarState() {
  const sidebar = document.getElementById('sidebar');
  const topnav = document.getElementById('topnav');
  const mainContent = document.getElementById('mainContent');
  const mainFooter = document.getElementById('mainFooter');
  if (!sidebar) return;
  if (sidebarCollapsed) {
    sidebar.classList.add('collapsed');
    if (topnav) topnav.classList.add('expanded');
    if (mainContent) mainContent.classList.add('expanded');
    if (mainFooter) mainFooter.classList.add('expanded');
  } else {
    sidebar.classList.remove('collapsed');
    if (topnav) topnav.classList.remove('expanded');
    if (mainContent) mainContent.classList.remove('expanded');
    if (mainFooter) mainFooter.classList.remove('expanded');
  }
}

function handleLogout(e) {
  e.preventDefault();
  confirmModal('Are you sure you want to log out?', 'Logout Confirmation', () => logout());
}

function showNotifications() {
  const notifs = JSON.parse(localStorage.getItem('csu_piat_notifications')) || [];
  const unread = notifs.filter(n => !n.read);
  if (unread.length === 0) { showToast('No new notifications.', 'info'); return; }
  const typeIcons = { info: 'fa-info-circle text-primary', success: 'fa-check-circle text-success', warning: 'fa-exclamation-triangle text-warning' };
  const html = unread.map(n => `<div class="d-flex gap-2 py-2 border-bottom"><i class="fa-solid ${typeIcons[n.type] || 'fa-bell'} mt-1"></i><div><div style="font-size:0.85rem">${n.message}</div><div style="font-size:0.72rem;color:#888">${n.date}</div></div></div>`).join('');
  const modal = document.createElement('div');
  modal.className = 'modal fade'; modal.id = 'notifModal'; modal.tabIndex = -1;
  modal.innerHTML = `<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-bell me-2"></i>Notifications</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">${html}</div><div class="modal-footer"><button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button></div></div></div>`;
  document.body.appendChild(modal);
  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
  modal.addEventListener('hidden.bs.modal', () => { modal.remove(); notifs.forEach(n => n.read = true); localStorage.setItem('csu_piat_notifications', JSON.stringify(notifs)); });
}

window.addEventListener('resize', () => {
  if (window.innerWidth >= 992) {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.remove('mobile-open');
    if (overlay) overlay.classList.remove('show');
  }
});
