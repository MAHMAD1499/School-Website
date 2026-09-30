<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_staff_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error)die('DB Error');$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}

$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
if($isAjax){
  $method=$_SERVER['REQUEST_METHOD']??'GET';
  $teacher_id = (int)$_SESSION['ksm_staff_auth'];
  $db = ksm_db();

  if($method==='GET'){
    $submissions = [];
    $classFilter = isset($_GET['class']) ? ksm_db()->real_escape_string($_GET['class']) : '';
    
    $where = "sess.teacher_id=$teacher_id AND sess.type='exam'";
    if($classFilter) {
       $where .= " AND st.class='$classFilter'";
    }

    $res = $db->query("SELECT sub.id, sub.status, sub.score, sub.completed_at, sess.title as session_title, sess.type, st.name as student_name, st.class 
      FROM cbt_submissions sub 
      JOIN cbt_sessions sess ON sub.session_id = sess.id 
      JOIN users_students st ON sub.student_id = st.id 
      WHERE $where 
      ORDER BY sub.completed_at DESC");
    if($res) {
        while($r = $res->fetch_assoc()) $submissions[] = $r;
    }
    ksm_json(['submissions'=>$submissions]);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CBT Exam Reports — KSM Staff Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Staff CBT Exam Reports</span>
      </div>
      <div class="topbar-right">
        <span id="staffNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:#10B981;color:white;" id="staffAvatar"></div>
        <button class="btn btn-sm btn-outline" onclick="Auth.logoutStaff(); window.location='login.php';">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="d-flex" style="justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h1 class="page-title" style="margin-bottom:0;">Exam Reports & Term Analytics</h1>
        <div>
          <button class="btn btn-outline" onclick="window.print()">Export PDF</button>
        </div>
      </div>

      <div class="card mb-4" style="padding: 1rem; display: flex; gap: 1rem; align-items:center;">
        <select class="form-control" style="max-width:200px;" id="classFilter">
          <option value="">All Classes</option>
          <option value="Playgroup">Playgroup</option>
          <option value="Nursery">Nursery</option>
          <option value="Prep">Prep</option>
          <option value="Grade One">Grade One</option>
          <option value="Grade Two">Grade Two</option>
          <option value="Grade Three">Grade Three</option>
          <option value="Grade Four">Grade Four</option>
          <option value="Grade Five">Grade Five</option>
        </select>
        <button class="btn btn-primary" onclick="loadExamReports()">Filter</button>
      </div>

      <div class="card">
        <table class="table">
          <thead><tr><th>Student Name</th><th>Class</th><th>Exam Title</th><th>Status</th><th>Score</th><th>Action</th></tr></thead>
          <tbody id="submissionsList">
            <tr><td colspan="6" style="text-align:center;padding:1rem;">Loading...</td></tr>
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
  buildSidebar('staff');
  document.addEventListener('DOMContentLoaded', () => {
    let staff = Auth.getStaff();
    if(staff) {
        document.getElementById('staffNameTopbar').textContent = staff.name;
        document.getElementById('staffAvatar').innerHTML = staff.profilePic ? `<img src="${staff.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : staff.name.charAt(0).toUpperCase();
    }
    loadExamReports();
  });

  async function loadExamReports() {
    const classFilter = document.getElementById('classFilter').value;
    const res = await selfApi('GET', null, classFilter ? `class=${classFilter}` : '');
    if(res.success) {
      const tb = document.getElementById('submissionsList');
      if(res.data.submissions.length === 0) {
        tb.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:1rem;">No submissions found for the selected filters.</td></tr>';
        return;
      }
      tb.innerHTML = res.data.submissions.map(s => `
        <tr>
          <td>${s.student_name}</td>
          <td>${s.class}</td>
          <td>${s.session_title}</td>
          <td><span class="badge badge-${s.status==='graded'?'green':'blue'}">${s.status}</span></td>
          <td><strong style="color:var(--primary);">${s.score !== null ? s.score : '--'}</strong></td>
          <td>
            <a href="cbt_grade_submission.php?id=${s.id}" class="btn btn-sm btn-primary">${s.status==='graded'?'View / Edit Result':'Review & Grade'}</a>
          </td>
        </tr>
      `).join('');
    }
  }

  function exportPDF() {
    window.print();
  }
</script>
</body>
</html>
