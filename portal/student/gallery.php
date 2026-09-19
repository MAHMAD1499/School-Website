<?php
$_ksm=['host'=>'localhost','user'=>'root','pass'=>'','name'=>'ksm_database'];
function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
header('Access-Control-Allow-Origin: *');header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){$r=ksm_db()->query("SELECT * FROM gallery ORDER BY id ASC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Gallery — KSM Student Portal">
  <title>Gallery — Student Portal</title>
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
        <span class="topbar-title">Photo Gallery</span>
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
          <h1 class="page-title">🖼️ Photo Gallery</h1>
          <p class="page-subtitle">Capturing moments of learning, play, and growth at KSM</p>
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

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const student = Auth.getStudent();
    if (!student) { window.location.href = 'login.php'; return; }
    document.getElementById('studentNameLabel').textContent = student.name;
    document.getElementById('studentAvatar').innerHTML = student.profilePic ? `<img src="${student.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : student.name.charAt(0).toUpperCase();
    buildSidebar('student');
    renderGallery();
  });

  async function renderGallery() {
    const res = await API.getGallery();
    const gallery = res.data || [];
    const grid = document.getElementById('galleryGrid');
    if (gallery.length === 0) {
      grid.innerHTML = `<div class="empty-state" style="column-span:all"><div class="empty-state-icon">🖼️</div><div class="empty-state-title">No photos yet</div><p class="empty-state-text">Gallery photos will appear here once added.</p></div>`;
      return;
    }
    grid.innerHTML = gallery.map((item, i) => `
      <div class="gallery-item" onclick="openLightbox(${i})">
        <img src="${item.url}" alt="${item.caption || 'Gallery photo'}" loading="lazy" onerror="this.src='https://via.placeholder.com/400x300/EFF6FF/1E3A8A?text=Photo'">
        <div class="gallery-item-caption">${item.caption || ''}</div>
      </div>
    `).join('');
  }

  async function openLightbox(index) {
    const res = await API.getGallery();
    const gallery = res.data || [];
    const item = gallery[index];
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


