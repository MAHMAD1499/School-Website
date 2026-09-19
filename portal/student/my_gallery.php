<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="My Personal Gallery — KSM Student Portal">
  <title>My Gallery — Student Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span class="topbar-title">My Personal Gallery</span>
      </div>
      <div class="topbar-right">
        <span id="studentNameLabel" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:var(--accent-warm);color:var(--primary-deep);" id="studentAvatar">S</div>
        <button class="btn btn-sm btn-outline" onclick="Auth.logoutStudent();window.location.href='login.php'">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">🖼️ My Personal Gallery</h1>
          <p class="page-subtitle">Private photos uploaded exclusively for you by the administration.</p>
        </div>
      </div>

      <div id="galleryGrid" class="gallery-grid"></div>
    </div>
    <div class="portal-footer">© 2026 Kindergarten Saadia's Montessori School. All rights reserved.</div>
  </div>
</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
  <button class="lightbox-close" id="lightboxClose">✕</button>
  <img src="" alt="" id="lightboxImg">
  <div class="lightbox-caption" id="lightboxCaption"></div>
</div>

<script src="../assets/portal.js?v=3"></script>
<script src="../assets/api.js?v=3"></script>
<script src="../assets/sidebar.js?v=3"></script>
<script>
  let personalPhotos = [];

  document.addEventListener('DOMContentLoaded', () => {
    const student = Auth.getStudent();
    if (!student) { window.location.href = 'login.php'; return; }
    document.getElementById('studentNameLabel').textContent = student.name;
    document.getElementById('studentAvatar').innerHTML = student.profilePic ? `<img src="${student.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : student.name.charAt(0).toUpperCase();
    buildSidebar('student');
    renderGallery(student.id);
  });

  async function renderGallery(studentId) {
    const res = await API.getStudentPersonalGallery(studentId);
    personalPhotos = res.data || [];
    const grid = document.getElementById('galleryGrid');
    
    if (personalPhotos.length === 0) {
      grid.innerHTML = `<div class="empty-state" style="column-span:all"><div class="empty-state-icon">🖼️</div><div class="empty-state-title">No photos yet</div><p class="empty-state-text">Your personal photos will appear here once added by the administration.</p></div>`;
      return;
    }
    
    grid.innerHTML = personalPhotos.map((item, i) => `
      <div class="gallery-item" onclick="openLightbox(${i})">
        <img src="${item.url}" alt="${item.caption || 'Personal photo'}" loading="lazy" onerror="this.src='https://via.placeholder.com/400x300/EFF6FF/1E3A8A?text=Photo'">
        <div class="gallery-item-caption">${item.caption || ''}</div>
      </div>
    `).join('');
  }

  function openLightbox(index) {
    const item = personalPhotos[index];
    if (!item) return;
    document.getElementById('lightboxImg').src = item.url;
    document.getElementById('lightboxImg').alt = item.caption || '';
    document.getElementById('lightboxCaption').textContent = item.caption || '';
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  document.getElementById('lightboxClose').addEventListener('click', () => {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
  });

  document.getElementById('lightbox').addEventListener('click', e => {
    if (e.target === document.getElementById('lightbox')) {
      document.getElementById('lightbox').classList.remove('open');
      document.body.style.overflow = '';
    }
  });
</script>
</body>
</html>

