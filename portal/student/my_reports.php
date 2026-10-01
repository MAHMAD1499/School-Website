<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_student_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}

$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);

if($isAjax){

    $student_id = intval($_SESSION['ksm_student_auth'] ?? 0);
    if(!$student_id){
        // fallback to session storage approach if cookie auth is used
        if(isset($_COOKIE['ksm_student_token'])){
            $token = $_COOKIE['ksm_student_token'];
            $chk = ksm_db()->query("SELECT id FROM users_students WHERE id = (SELECT user_id FROM auth_tokens WHERE token='$token' LIMIT 1)");
            if($chk && $chk->num_rows > 0){
                $student_id = $chk->fetch_assoc()['id'];
            }
        }
    }
    
    // If we can't find from session/cookie, maybe we pass it from frontend
    if(isset($_GET['student_id'])) {
        $student_id = intval($_GET['student_id']);
    }

    if($student_id) {
        $r=ksm_db()->query("SELECT * FROM student_reports WHERE student_id=$student_id ORDER BY created_at DESC");
        $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
        ksm_json($rows);
    } else {
        ksm_json([]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student's Monthly Report — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <style>
    .report-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08);
    }
    .report-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-deep);
        margin-bottom: 0.5rem;
    }
    .report-date {
        font-size: 0.85rem;
        color: var(--text-medium);
        margin-bottom: 1rem;
    }
    .report-text {
        color: var(--text-dark);
        line-height: 1.6;
        white-space: pre-wrap;
    }
  </style>
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Student's Monthly Report</span>
      </div>
      <div class="topbar-right">
        <span id="studentNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:var(--accent-warm);color:var(--primary-deep);" id="studentAvatar"></div>
        <button class="btn btn-sm btn-outline" onclick="Auth.logoutStudent(); window.location='login.php';">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">📝 Student's Monthly Report</h1>
          <p class="page-subtitle">View official reports and feedback from the school administration.</p>
        </div>
      </div>

      <div id="reportsContainer">
          <div style="text-align:center; padding:3rem; color:var(--text-medium);">Loading reports...</div>
      </div>
    </div>
    <div class="portal-footer">© 2026 KSM Student Portal.</div>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('student');
  let studentInfo = null;

  document.addEventListener('DOMContentLoaded', () => {
    studentInfo = Auth.getStudent();
    if(studentInfo) {
        document.getElementById('studentNameTopbar').textContent = studentInfo.name;
        document.getElementById('studentAvatar').innerHTML = studentInfo.profilePic 
            ? `<img src="${studentInfo.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` 
            : studentInfo.name.charAt(0).toUpperCase();
        loadReports();
    } else {
        window.location.href = 'login.php';
    }
  });

  async function loadReports() {
    try {
      const res = await apiCall(`my_reports.php?_api=1&student_id=${studentInfo.id}`, 'GET');
      const container = document.getElementById('reportsContainer');
      
      if (res.success) {
        if (!res.data || res.data.length === 0) {
            container.innerHTML = `
                <div class="card" style="text-align:center; padding:3rem;">
                    <div style="font-size:3rem; margin-bottom:1rem;">📄</div>
                    <h3 style="color:var(--text-dark); margin-bottom:0.5rem;">No Reports Yet</h3>
                    <p style="color:var(--text-medium);">You don't have any reports from the administration at this time.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = res.data.map(r => {
            const date = new Date(r.created_at).toLocaleDateString('en-US', {
                year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
            return `
            <div class="report-card">
                <div class="report-title">${r.title}</div>
                <div class="report-date">Received on ${date}</div>
                <a href="view_report.php?id=${r.id}" class="btn btn-sm btn-primary">View Report</a>
            </div>
            `;
        }).join('');
      } else {
        container.innerHTML = `<div class="card" style="padding:2rem; color:red;">Failed to load reports.</div>`;
      }
    } catch (e) {
      console.error(e);
      document.getElementById('reportsContainer').innerHTML = `<div class="card" style="padding:2rem; color:red;">Network error while loading reports.</div>`;
    }
  }
</script>
</body>
</html>
