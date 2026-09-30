<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_student_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error)die('DB Error');$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}

$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
if($isAjax){
  $method=$_SERVER['REQUEST_METHOD']??'GET';
  $student_id = (int)$_SESSION['ksm_student_auth'];
  $db = ksm_db();

  if($method==='GET'){
    $results = [];
    $res = $db->query("SELECT sub.id, sess.title, sess.subject_id, sub.score, sub.completed_at 
      FROM cbt_submissions sub 
      JOIN cbt_sessions sess ON sub.session_id = sess.id 
      WHERE sub.student_id=$student_id AND sub.status='graded' AND sess.type='test' 
      ORDER BY sub.completed_at DESC");
    while($r = $res->fetch_assoc()) $results[] = $r;

    ksm_json(['results'=>$results]);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CBT Test Reports — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">CBT Test Reports</span>
      </div>
      <div class="topbar-right">
        <span id="studentNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:var(--accent-warm);color:white;" id="studentAvatar"></div>
        <button class="btn btn-sm btn-outline" onclick="Auth.logoutStudent(); window.location='login.php';">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <h1 class="page-title">Test Reports</h1>
      <div class="card">
        <table class="table">
          <thead><tr><th>Test Name</th><th>Subject</th><th>Score</th><th>Date Completed</th><th>Action</th></tr></thead>
          <tbody id="resultsList">
            <tr><td colspan="5" style="text-align:center;padding:1rem;">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="portal-footer">© 2026 KSM School Portal.</div>
  </div>
</div>
<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('student');
  document.addEventListener('DOMContentLoaded', () => {
    let student = Auth.getStudent();
    if(student) {
        document.getElementById('studentNameTopbar').textContent = student.name;
        document.getElementById('studentAvatar').innerHTML = student.profilePic ? `<img src="${student.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : student.name.charAt(0).toUpperCase();
    }
    loadResults();
  });

  async function loadResults() {
    const res = await selfApi('GET');
    if(res.success) {
      const tb = document.getElementById('resultsList');
      if(res.data.results.length === 0) {
        tb.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:1rem;">No graded reports available.</td></tr>';
      } else {
        tb.innerHTML = res.data.results.map(r => `
          <tr>
            <td>${r.title}</td>
            <td>${r.subject_id}</td>
            <td><strong>${r.score}</strong></td>
            <td>${r.completed_at}</td>
            <td><a href="cbt_view_result.php?id=${r.id}" class="btn btn-sm btn-primary">View Full Result</a></td>
          </tr>
        `).join('');
      }
    }
  }
</script>
</body>
</html>
