<?php
require_once '../../config/session.php';
$user = requireAuth(['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Evidence Upload | CSU-Piat</title>
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
    <h2><i class="fa-solid fa-paperclip me-2 text-primary"></i>Evidence Upload</h2>
    <p>Upload supporting documents for your IPCR submission.</p>
  </div>

  <!-- Upload Card -->
  <div class="card mb-4">
    <div class="card-header"><h6><i class="fa-solid fa-upload me-2 text-primary"></i>Upload Supporting Documents</h6></div>
    <div class="card-body">
      <div class="border-2 border-dashed rounded-3 p-4 text-center mb-3" id="dropZone"
           style="border:2px dashed #E85C0D;background:var(--accent);cursor:pointer"
           onclick="document.getElementById('fileInput').click()"
           ondragover="event.preventDefault();this.style.background='#d0e8ff'"
           ondragleave="this.style.background='var(--accent)'"
           ondrop="handleDrop(event)">
        <i class="fa-solid fa-cloud-arrow-up text-primary mb-2" style="font-size:2.5rem"></i>
        <h6 class="text-primary">Click to Upload or Drag & Drop</h6>
        <p class="text-muted mb-0" style="font-size:0.82rem">Accepted: PDF, DOC, DOCX, JPG, PNG, XLSX (Max: 10MB per file)</p>
        <input type="file" id="fileInput" class="d-none" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx"
               onchange="handleFiles(this.files)">
      </div>
      <div class="row g-2 mb-3">
        <div class="col-md-6">
          <label class="form-label">Category</label>
          <select class="form-select form-select-sm" id="fileCategory">
            <option value="Core Function">Core Function Evidence</option>
            <option value="Strategic Function">Strategic Function Evidence</option>
            <option value="Support Function">Support Function Evidence</option>
            <option value="Other">Other / Miscellaneous</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Description</label>
          <input type="text" class="form-control form-control-sm" id="fileDesc" placeholder="Brief description of the document...">
        </div>
      </div>
    </div>
  </div>

  <!-- Uploaded Files -->
  <div class="table-wrapper">
    <div class="table-header">
      <h6><i class="fa-solid fa-folder-open me-2"></i>Uploaded Documents</h6>
      <small class="text-muted" id="fileCount">0 files</small>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr><th>#</th><th>File Name</th><th>Category</th><th>Description</th><th>Size</th><th>Date Uploaded</th><th>Actions</th></tr></thead>
        <tbody id="filesTable"></tbody>
      </table>
    </div>
  </div>
</main>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  const session = requireAuth(['user']);
  initLayout('user', 'evidence', [{ label: 'Evidence Upload' }]);

  const storageKey = `csu_piat_files_${session.id}`;
  let fileListCache = [];

  function getLocalFiles() { return JSON.parse(localStorage.getItem(storageKey)) || []; }
  function saveLocalFiles(files) { localStorage.setItem(storageKey, JSON.stringify(files)); }

  const fileIcons = { pdf: 'fa-file-pdf text-danger', doc: 'fa-file-word text-primary', docx: 'fa-file-word text-primary', jpg: 'fa-file-image text-success', jpeg: 'fa-file-image text-success', png: 'fa-file-image text-success', xlsx: 'fa-file-excel text-success' };

  function formatSize(bytes) {
    if (!bytes) return '-';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
  }

  async function loadFiles() {
    try {
      const res = await fetch(API_BASE + 'evidence/list.php?user_id=' + session.id).then(r => r.json());
      const serverFiles = (res && res.files) ? res.files : [];
      const localFiles = getLocalFiles();

      // Merge server and local files
      const merged = [...serverFiles];
      localFiles.forEach(lf => {
        if (!merged.some(mf => mf.id === lf.id || (mf.original_name === lf.name && mf.file_size === lf.size))) {
          merged.push(lf);
        }
      });
      fileListCache = merged;
      saveLocalFiles(fileListCache);
    } catch (e) {
      fileListCache = getLocalFiles();
    }
    renderTable();
  }

  async function handleFiles(fileList) {
    if (!fileList || fileList.length === 0) return;
    const category = document.getElementById('fileCategory').value;
    const desc = document.getElementById('fileDesc').value.trim();

    const formData = new FormData();
    formData.append('category', category);
    formData.append('description', desc);
    formData.append('user_id', session.id);

    let validCount = 0;
    Array.from(fileList).forEach(file => {
      if (file.size > 20 * 1024 * 1024) {
        showToast(`${file.name} exceeds 20MB limit.`, 'warning');
        return;
      }
      formData.append('files[]', file);
      validCount++;
    });

    if (validCount === 0) return;

    // Also read base64 for immediate offline fallback
    Array.from(fileList).forEach(file => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const localEntry = {
          id: Date.now() + Math.random(),
          name: file.name,
          original_name: file.name,
          size: file.size,
          file_size: file.size,
          category,
          description: desc || 'Uploaded evidence',
          ext: file.name.split('.').pop().toLowerCase(),
          data_url: e.target.result,
          date: new Date().toLocaleDateString('en-PH')
        };
        const current = getLocalFiles();
        current.unshift(localEntry);
        saveLocalFiles(current);
      };
      reader.readAsDataURL(file);
    });

    try {
      showToast('Uploading file(s)...', 'info');
      const res = await fetch(API_BASE + 'evidence/upload.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.message || 'Files uploaded successfully!', 'success');
        await loadFiles();
      } else {
        showToast(data.error || 'Upload failed.', 'danger');
        renderTable();
      }
    } catch (err) {
      showToast('Uploaded to local workspace.', 'info');
      renderTable();
    }

    document.getElementById('fileInput').value = '';
    document.getElementById('fileDesc').value = '';
  }

  function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.background = 'var(--accent)';
    handleFiles(e.dataTransfer.files);
  }

  async function deleteFile(id) {
    confirmModal('Are you sure you want to delete this file?', 'Delete File', async () => {
      try {
        await fetch(API_BASE + 'evidence/delete.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id })
        });
      } catch (e) {}

      const local = getLocalFiles().filter(f => f.id !== id);
      saveLocalFiles(local);
      fileListCache = fileListCache.filter(f => f.id !== id);
      renderTable();
      showToast('File deleted.', 'success');
    });
  }

  function getFileViewUrl(f) {
    if (f.file_path && f.file_path !== '#') {
      return f.file_path.startsWith('http') ? f.file_path : (API_BASE + '../' + f.file_path.replace(/^\/+/, ''));
    }
    if (f.data_url) return f.data_url;
    if (f.file_url) return f.file_url;
    return '';
  }

  function renderTable() {
    const files = fileListCache.length > 0 ? fileListCache : getLocalFiles();
    const tbody = document.getElementById('filesTable');
    tbody.innerHTML = '';
    document.getElementById('fileCount').textContent = files.length + ' file(s)';
    if (files.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="py-4 text-center"><div class="empty-state"><i class="fa-solid fa-folder-open"></i><p>No files uploaded yet.</p></div></td></tr>`;
      return;
    }
    files.forEach((f, i) => {
      const name = f.original_name || f.name || 'Document';
      const ext = f.ext || name.split('.').pop().toLowerCase();
      const icon = fileIcons[ext] || 'fa-file text-secondary';
      const viewUrl = getFileViewUrl(f);

      tbody.innerHTML += `<tr>
        <td>${i+1}</td>
        <td><div class="d-flex align-items-center gap-2">
          <i class="fa-solid ${icon}" style="font-size:1.2rem"></i>
          <span style="font-size:0.83rem"><strong>${name}</strong></span></div></td>
        <td>${getCategoryBadge(f.category)}</td>
        <td style="font-size:0.82rem">${f.description || '-'}</td>
        <td style="font-size:0.82rem">${formatSize(f.file_size || f.size)}</td>
        <td style="font-size:0.82rem">${f.date || f.uploaded_at || '-'}</td>
        <td>
          <div class="d-flex gap-1">
            ${viewUrl ? `<a href="${viewUrl}" target="_blank" class="btn btn-outline-primary btn-sm" title="View / Open"><i class="fa-solid fa-eye"></i></a>` : `<button type="button" class="btn btn-outline-secondary btn-sm" title="No link" disabled><i class="fa-solid fa-eye"></i></button>`}
            <button class="btn btn-outline-danger btn-sm" onclick="deleteFile(${f.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>
          </div>
        </td></tr>`;
    });
  }

  loadFiles();
</script>
</body>
</html>
