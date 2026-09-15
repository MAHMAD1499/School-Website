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
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){$r=ksm_db()->query("SELECT * FROM teachers ORDER BY id ASC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Meet our dedicated teachers and staff at Kindergarten Saadia's Montessori School.">
  <title>Teachers & Staff — KSM Portal</title>
  <link rel="stylesheet" href="assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span class="topbar-title">Teachers & Staff</span>
      </div>
      <div class="topbar-right">
        <div class="topbar-avatar">K</div>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">👨‍🏫 Teachers & Staff</h1>
          <p class="page-subtitle">Meet the dedicated team shaping young minds at KSM</p>
        </div>
      </div>

      <!-- Search -->
      <div class="search-bar">
        <input type="text" id="searchInput" class="search-input" placeholder="Search teachers by name or subject...">
        <select id="filterRole" class="form-control" style="max-width:180px;border-radius:var(--radius-full);">
          <option value="">All Roles</option>
          <option value="Principal">Principal</option>
          <option value="Senior Teacher">Senior Teacher</option>
          <option value="Teacher">Teacher</option>
          <option value="Teaching Assistant">Teaching Assistant</option>
        </select>
      </div>

      <div id="teachersGrid" class="grid-3"></div>
    </div>

    <div class="portal-footer">© 2026 Kindergarten Saadia's Montessori School. All rights reserved.</div>
  </div>
</div>

<script src="assets/portal.js"></script>
<script src="assets/api.js"></script>
<script src="assets/sidebar.js"></script>
<script>
  buildSidebar('portal');

  let allTeachers = [];

  function renderTeachers() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const role = document.getElementById('filterRole').value;
    let teachers = allTeachers;

    if (search) teachers = teachers.filter(t => t.name.toLowerCase().includes(search) || t.subject.toLowerCase().includes(search));
    if (role) teachers = teachers.filter(t => t.role.includes(role));

    const grid = document.getElementById('teachersGrid');
    if (teachers.length === 0) {
      grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1"><div class="empty-state-icon">👤</div><div class="empty-state-title">No teachers found</div><p class="empty-state-text">Try adjusting your search.</p></div>`;
      return;
    }

    grid.innerHTML = teachers.map(t => `
      <div class="teacher-card fade-up">
        <div class="teacher-card-img">${t.emoji || '👤'}</div>
        <div class="teacher-card-body">
          <h3 class="teacher-card-name">${t.name}</h3>
          <p class="teacher-card-role">${t.role}</p>
          <span class="badge badge-blue teacher-card-subject">${t.subject}</span>
          <p style="font-size:0.82rem;color:var(--text-medium);margin-top:0.75rem;">${t.bio || ''}</p>
        </div>
      </div>
    `).join('');
  }

  document.addEventListener('DOMContentLoaded', async () => {
    const res = await API.getTeachers();
    allTeachers = res.data || [];
    renderTeachers();
    document.getElementById('searchInput').addEventListener('input', renderTeachers);
    document.getElementById('filterRole').addEventListener('change', renderTeachers);
  });
</script>
</body>
</html>

