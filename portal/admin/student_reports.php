<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_admin_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}

$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];

if($isAjax){
  if($method==='GET'){
    if(isset($_GET['action']) && $_GET['action'] === 'reports'){
        $student_id = intval($_GET['student_id']??0);
        $r=ksm_db()->query("SELECT * FROM student_reports WHERE student_id=$student_id ORDER BY created_at DESC");
        $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
        ksm_json($rows);
    } else {
        $r=ksm_db()->query("SELECT id,name,email,class,status,rollNo FROM users_students ORDER BY id ASC");
        $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
        ksm_json($rows);
    }
  }
  if($method==='POST'){
    $student_id = intval($body['student_id']??0);
    $title = ksm_esc($body['title']??'');
    $report_text = ksm_esc($body['report_text']??'');
    
    if(!$student_id || !$title || !$report_text) ksm_err('All fields are required.');
    
    ksm_db()->query("INSERT INTO student_reports (student_id, title, report_text) VALUES ($student_id, '$title', '$report_text')");
    ksm_json(['id'=>ksm_db()->insert_id],'Report added successfully.');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student's Monthly Report — Admin Panel</title>
  <link rel="stylesheet" href="../assets/portal.css">
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
        <button class="btn btn-sm btn-danger" onclick="Auth.logoutAdmin();window.location.href='login.php'">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">📄 Student's Monthly Report</h1>
          <p class="page-subtitle">Write and manage reports for students</p>
        </div>
      </div>

      <div class="card">
        <div class="table-container">
          <table id="studentsTable">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Class</th>
                <th>Roll No</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="studentsTbody"></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="portal-footer">© 2026 KSM Admin Panel.</div>
  </div>
</div>

<!-- Write Report Modal Removed - we use write_report.php now -->

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('admin');
  let studentsData = [];

  document.addEventListener('DOMContentLoaded', loadStudents);

  async function loadStudents() {
    try {
      const res = await apiCall('student_reports.php?_api=1', 'GET');
      if (res && res.success) {
        studentsData = res.data;
        renderTable();
      }
    } catch (e) {
      console.error(e);
    }
  }

  function renderTable() {
    const tbody = document.getElementById('studentsTbody');
    if (!studentsData.length) {
      tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:1rem;">No students found.</td></tr>';
      return;
    }
    
    tbody.innerHTML = studentsData.map(s => `
      <tr>
        <td><strong>${s.name}</strong></td>
        <td>${s.email}</td>
        <td><span class="badge badge-blue">${s.class||'N/A'}</span></td>
        <td>${s.rollNo||'N/A'}</td>
        <td>
          <div style="display:flex;gap:0.5rem;">
            <a href="write_report.php?student_id=${s.id}" class="btn btn-sm btn-outline">✍️ Write Montessori Report</a>
          </div>
        </td>
      </tr>
    `).join('');
  }
</script>
</body>
</html>
