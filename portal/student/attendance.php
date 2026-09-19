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
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){
  if($method==='GET'){$sid=intval($_GET['student_id']??0);if(!$sid)ksm_err('No student ID.');$r=ksm_db()->query("SELECT * FROM attendance WHERE student_id=$sid ORDER BY date DESC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Attendance — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">My Attendance</span>
      </div>
      <div class="topbar-right">
        <span id="studentNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:var(--accent-warm);color:var(--primary-deep);" id="studentAvatar">S</div>
        <button class="btn btn-sm btn-outline" onclick="doLogout()">Logout</button>
      </div>
    </div>
    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">✅ My Attendance</h1>
          <p class="page-subtitle">View your attendance records</p>
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h2 class="card-title">Attendance History</h2></div>
        <div id="attendanceList"></div>
      </div>
    </div>
  </div>
</div>
<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('student');
  document.addEventListener('DOMContentLoaded', async () => {
    const student = Auth.getStudent();
    if (!student) { window.location.href = 'login.php'; return; }
    document.getElementById('studentNameTopbar').textContent = student.name;
    document.getElementById('studentAvatar').innerHTML = student.profilePic ? `<img src="${student.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : student.name.charAt(0).toUpperCase();

    const el = document.getElementById('attendanceList');
    el.innerHTML = '<div style="padding:2rem;text-align:center;">Loading...</div>';

    try {
      const res = await apiCall(ksm_self_url() + '?student_id=' + student.id, 'GET');
      if (!res.success || !res.data || res.data.length === 0) {
        el.innerHTML = '<div class="empty-state"><div class="empty-state-icon">📅</div><p>No attendance records found.</p></div>';
        return;
      }
      
      const myAtt = res.data;
      el.innerHTML = myAtt.map(a => `
        <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem;border-bottom:1px solid var(--border-color);">
          <div>
            <p style="font-weight:600;font-size:0.95rem;">${formatDate(a.date)}</p>
            ${a.notes ? `<p style="font-size:0.8rem;color:var(--text-medium);">${a.notes}</p>` : ''}
          </div>
          <div>
            ${a.status.toLowerCase() === 'present' ? '<span class="badge badge-green">Present</span>' : '<span class="badge badge-red">Absent</span>'}
          </div>
        </div>
      `).join('');
    } catch (e) {
      el.innerHTML = '<div class="empty-state"><p>Error loading attendance.</p></div>';
    }
  });
  function doLogout() { Auth.logoutStudent(); window.location.href = 'login.php'; }
</script>
</body>
</html>


