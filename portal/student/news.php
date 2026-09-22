<?php
require_once __DIR__ . '/../../config/database.php';

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
header('Access-Control-Allow-Origin: *');header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){$r=ksm_db()->query("SELECT * FROM news ORDER BY date DESC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Announcements — KSM Student Portal">
  <title>Announcements — Student Portal</title>
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
        <span class="topbar-title">News & Announcements</span>
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
          <h1 class="page-title">📰 News & Announcements</h1>
          <p class="page-subtitle">Stay up to date with the latest school news and important announcements</p>
        </div>
      </div>

      <div class="search-bar">
        <input type="text" id="searchInput" class="search-input" placeholder="Search announcements...">
        <select id="filterCategory" class="form-control" style="max-width:160px;border-radius:var(--radius-full);">
          <option value="">All Categories</option>
          <option value="General">General</option>
          <option value="Achievement">Achievement</option>
          <option value="Event">Event</option>
          <option value="Academic">Academic</option>
        </select>
      </div>

      <div id="newsList" style="display:flex;flex-direction:column;gap:1.25rem;"></div>
    </div>
    <div class="portal-footer">© 2026 Kindergarten Saadia's Montessori School. All rights reserved.</div>
  </div>
</div>

<!-- News Detail Modal -->
<div class="modal-overlay" id="newsModal">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="newsModalTitle">News Title</h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="news-date" id="newsModalDate"></div>
    <span class="badge badge-blue" id="newsModalCategory" style="margin-bottom:1rem;display:inline-block;"></span>
    <p id="newsModalBody" style="font-size:0.92rem;color:var(--text-medium);line-height:1.8;"></p>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  let allNews = [];

  document.addEventListener('DOMContentLoaded', async () => {
    const student = Auth.getStudent();
    if (!student) { window.location.href = 'login.php'; return; }
    document.getElementById('studentNameLabel').textContent = student.name;
    document.getElementById('studentAvatar').innerHTML = student.profilePic ? `<img src="${student.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : student.name.charAt(0).toUpperCase();
    buildSidebar('student');
    
    try {
      const res = await API.getNews();
      allNews = res.data || [];
    } catch(e) {
      console.error(e);
    }

    renderNews();
    document.getElementById('searchInput').addEventListener('input', renderNews);
    document.getElementById('filterCategory').addEventListener('change', renderNews);
  });

  function renderNews() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('filterCategory').value;
    let news = allNews;
    if (search) news = news.filter(n => n.title.toLowerCase().includes(search) || n.body.toLowerCase().includes(search));
    if (category) news = news.filter(n => n.category === category);

    const list = document.getElementById('newsList');
    if (news.length === 0) {
      list.innerHTML = `<div class="empty-state"><div class="empty-state-icon">📰</div><div class="empty-state-title">No announcements found</div><p class="empty-state-text">Check back later for updates.</p></div>`;
      return;
    }

    list.innerHTML = news.map(n => `
      <div class="news-card">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;">
          <span class="news-date">${formatDate(n.date)}</span>
          <span class="badge badge-blue">${n.category || 'General'}</span>
        </div>
        <h3 class="news-title">${n.title}</h3>
        <p class="news-body">${n.body.substring(0, 180)}${n.body.length > 180 ? '...' : ''}</p>
        ${n.body.length > 180 ? `<button class="btn btn-sm btn-outline" style="margin-top:0.75rem;" onclick="openNews('${n.id}')">Read More</button>` : ''}
      </div>
    `).join('');
  }

  function openNews(id) {
    const news = allNews.find(n => String(n.id) === String(id));
    if (!news) return;
    document.getElementById('newsModalTitle').textContent = news.title;
    document.getElementById('newsModalDate').textContent = formatDate(news.date);
    document.getElementById('newsModalCategory').textContent = news.category || 'General';
    document.getElementById('newsModalBody').textContent = news.body;
    openModal('newsModal');
  }
</script>
</body>
</html>


