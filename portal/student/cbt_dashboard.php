<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_student_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error)die('DB Error');$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}

$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
if($isAjax){
  $method=$_SERVER['REQUEST_METHOD']??'GET';
  $student_id = (int)$_SESSION['ksm_student_auth'];
  $db = ksm_db();

  if($method==='GET'){
    $student = $db->query("SELECT class FROM users_students WHERE id=$student_id")->fetch_assoc();
    $class = ksm_esc($student['class']);
    
    // Fetch available tests for this class that student hasn't completed
    $available_tests = [];
    $res = $db->query("SELECT s.* FROM cbt_sessions s 
      WHERE s.class_id='$class' AND s.status='published' 
      AND s.id NOT IN (SELECT session_id FROM cbt_submissions WHERE student_id=$student_id AND status IN ('submitted', 'graded'))
      ORDER BY s.id DESC");
    while($r = $res->fetch_assoc()) $available_tests[] = $r;
    
    // Fetch recent results
    $recent_results = [];
    $res2 = $db->query("SELECT s.title, sub.score, sub.completed_at FROM cbt_submissions sub JOIN cbt_sessions s ON sub.session_id = s.id WHERE sub.student_id=$student_id AND sub.status='graded' ORDER BY sub.completed_at DESC LIMIT 5");
    while($r2 = $res2->fetch_assoc()) $recent_results[] = $r2;

    ksm_json(['available_tests'=>$available_tests, 'recent_results'=>$recent_results]);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CBT Dashboard — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Student CBT Dashboard</span>
      </div>
      <div class="topbar-right">
        <span id="studentNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:var(--accent-warm);color:white;" id="studentAvatar"></div>
        <button class="btn btn-sm btn-outline" onclick="Auth.logoutStudent(); window.location='login.php';">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <h1 class="page-title">CBT Dashboard</h1>
      
      <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top:1.5rem;">
        <div class="card">
          <div class="card-header"><h2 class="card-title">Available Exams/Tests</h2></div>
          <table class="table">
            <thead><tr><th>Title</th><th>Subject</th><th>Duration</th><th>Action</th></tr></thead>
            <tbody id="availableTestsList">
              <tr><td colspan="4" style="text-align:center;padding:1rem;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
        <div class="card">
          <div class="card-header"><h2 class="card-title">Recent Graded Results</h2></div>
          <div id="recentResultsList" style="padding:1rem;">
             Loading...
          </div>
        </div>
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
    loadDashboard();
  });

  async function loadDashboard() {
    const res = await selfApi('GET');
    if(res.success) {
      const tb = document.getElementById('availableTestsList');
      if(res.data.available_tests.length === 0) {
        tb.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:1rem;">No available tests at the moment.</td></tr>';
      } else {
        tb.innerHTML = res.data.available_tests.map(t => `
          <tr>
            <td>${t.title}</td>
            <td>${t.subject_id}</td>
            <td>${t.duration_minutes} min</td>
            <td><a href="cbt_take_exam.php?id=${t.id}" class="btn btn-sm btn-primary">Take Exam</a></td>
          </tr>
        `).join('');
      }

      const rb = document.getElementById('recentResultsList');
      if(res.data.recent_results.length === 0) {
        rb.innerHTML = '<div style="text-align:center;color:var(--text-medium);">No recent results.</div>';
      } else {
        rb.innerHTML = res.data.recent_results.map(r => `
          <div style="padding:0.5rem 0; border-bottom:1px solid #eee;">
            <strong>${r.title}</strong><br>
            <span style="color:var(--text-medium);font-size:0.8rem;">Score: ${r.score}</span>
          </div>
        `).join('');
      }
    }
  }
</script>
</body>
</html>
