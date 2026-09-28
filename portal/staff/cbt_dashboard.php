<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_staff_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error)die('DB Error');$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}

$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
if($isAjax){
  $method=$_SERVER['REQUEST_METHOD']??'GET';
  $body=json_decode(file_get_contents('php://input'),true)??[];
  $teacher_id = (int)$_SESSION['ksm_staff_auth'];
  $db = ksm_db();

  if($method==='GET'){
    $sessions = [];
    $res = $db->query("SELECT * FROM cbt_sessions WHERE teacher_id=$teacher_id ORDER BY id DESC");
    while($r = $res->fetch_assoc()) $sessions[] = $r;
    ksm_json(['sessions'=>$sessions]);
  }
  if($method==='POST'){
    $action = $body['action'] ?? '';
    if($action==='add_session'){
      $title = ksm_esc($body['title']);
      $type = ksm_esc($body['type']);
      $class = ksm_esc($body['class']);
      $subject = ksm_esc($body['subject']);
      $total_marks = (float)$body['total_marks'];
      $duration = (int)$body['duration'];
      $res = $db->query("INSERT INTO cbt_sessions (title, type, subject_id, class_id, teacher_id, start_time, end_time, duration_minutes, total_marks, status) VALUES ('$title', '$type', '$subject', '$class', $teacher_id, NOW(), NOW(), $duration, $total_marks, 'published')");
      if($res) {
          ksm_json(null, "Session created successfully");
      } else {
          ksm_err("DB Error: " . $db->error);
      }
    }
    if($action==='delete_session'){
      $id = (int)$body['id'];
      $db->query("DELETE FROM cbt_sessions WHERE id=$id AND teacher_id=$teacher_id");
      ksm_json(null, "Session deleted");
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CBT Dashboard — KSM Staff Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <style>
    .ksm-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999999; align-items:center; justify-content:center; }
    .ksm-modal.active { display:flex; }
    .ksm-modal-content { background:white; padding:2.5rem; border-radius:var(--radius-md); width:90%; max-width:650px; box-shadow:0 10px 25px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
  </style>
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Staff CBT Dashboard</span>
      </div>
      <div class="topbar-right">
        <span id="staffNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <button class="btn btn-sm btn-outline" onclick="Auth.logoutStaff(); window.location='login.php';">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="d-flex" style="justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h1 class="page-title" style="margin-bottom:0;">CBT Exams & Tests</h1>
        <button class="btn btn-primary" onclick="document.getElementById('addSessionModal').classList.add('active')">Create New Exam/Test</button>
      </div>

      <div class="card">
        <table class="table">
          <thead><tr><th>Title</th><th>Type</th><th>Class (ID)</th><th>Subject (ID)</th><th>Duration</th><th>Total Marks</th><th>Status</th><th>Action</th></tr></thead>
          <tbody id="sessionsList">
            <tr><td colspan="7" style="text-align:center;padding:1rem;">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="portal-footer">© 2026 KSM School Portal.</div>
  </div>
</div>

<!-- Modal -->
<div class="ksm-modal" id="addSessionModal">
  <div class="ksm-modal-content">
    <h2 style="margin-bottom:1.5rem; color:var(--primary-deep); font-weight:600;">Create CBT Exam/Test</h2>
    <form id="addSessionForm" onsubmit="addSession(event)">
      
      <div class="form-group mb-3">
        <label style="font-weight:600; margin-bottom:0.5rem; display:block;">Exam/Test Title</label>
        <input type="text" class="form-control" name="title" required placeholder="e.g. Mid-Term Mathematics Exam">
      </div>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;" class="mb-3">
        <div class="form-group">
          <label style="font-weight:600; margin-bottom:0.5rem; display:block;">Type</label>
          <select class="form-control" name="type" required>
            <option value="test">Class Test / Quiz</option>
            <option value="exam">Term Exam</option>
          </select>
        </div>
        <div class="form-group">
          <label style="font-weight:600; margin-bottom:0.5rem; display:block;">Class</label>
          <select class="form-control" name="class" required>
            <option value="" disabled selected>Select Class</option>
            <option value="Playgroup">Playgroup</option>
            <option value="Nursery">Nursery</option>
            <option value="Prep">Prep</option>
            <option value="Grade One">Grade One</option>
            <option value="Grade Two">Grade Two</option>
            <option value="Grade Three">Grade Three</option>
            <option value="Grade Four">Grade Four</option>
            <option value="Grade Five">Grade Five</option>
          </select>
        </div>
      </div>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;" class="mb-3">
        <div class="form-group">
          <label style="font-weight:600; margin-bottom:0.5rem; display:block;">Subject</label>
          <select class="form-control" name="subject" required>
            <option value="" disabled selected>Select Subject</option>
            <option value="English">English</option>
            <option value="Mathematics">Mathematics</option>
            <option value="Science">Science</option>
            <option value="Urdu">Urdu</option>
            <option value="Islamiat">Islamiat</option>
            <option value="Social Studies">Social Studies</option>
            <option value="Computer Science">Computer Science</option>
            <option value="Art">Art</option>
          </select>
        </div>
        <div class="form-group">
          <label style="font-weight:600; margin-bottom:0.5rem; display:block;">Duration (minutes)</label>
          <input type="number" class="form-control" name="duration" required min="5" value="45">
        </div>
      </div>

      <div class="form-group mb-4">
        <label style="font-weight:600; margin-bottom:0.5rem; display:block;">Total Marks</label>
        <input type="number" class="form-control" name="total_marks" required min="1" value="50">
      </div>

      <div style="display:flex;gap:1rem;justify-content:flex-end; border-top: 1px solid #eee; padding-top: 1.5rem;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('addSessionModal').classList.remove('active')" style="padding: 0.6rem 1.5rem;">Cancel</button>
        <button type="submit" class="btn btn-primary" style="padding: 0.6rem 2rem;">Create</button>
      </div>
    </form>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('staff');
  document.addEventListener('DOMContentLoaded', () => {
    let staff = Auth.getStaff();
    if(staff) document.getElementById('staffNameTopbar').textContent = staff.name;
    loadSessions();
  });

  async function loadSessions() {
    const res = await selfApi('GET');
    if(res.success) {
      const tbody = document.getElementById('sessionsList');
      if(res.data.sessions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:1rem;">No sessions found.</td></tr>';
        return;
      }
      tbody.innerHTML = res.data.sessions.map(s => `
        <tr>
          <td>${s.title}</td>
          <td style="text-transform:capitalize;">${s.type}</td>
          <td>${s.class_id}</td>
          <td>${s.subject_id}</td>
          <td>${s.duration_minutes} min</td>
          <td>${s.total_marks}</td>
          <td><span class="badge badge-${s.status==='published'?'green':'blue'}">${s.status}</span></td>
          <td>
            <a href="cbt_questions.php?session_id=${s.id}" class="btn btn-sm btn-primary">Manage Questions</a>
            <button class="btn btn-sm btn-danger" onclick="deleteSession(${s.id})">Delete</button>
          </td>
        </tr>
      `).join('');
    }
  }

  async function addSession(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    const data = Object.fromEntries(fd.entries());
    data.action = 'add_session';
    const res = await selfApi('POST', data);
    if(res.success) {
      document.getElementById('addSessionModal').classList.remove('active');
      e.target.reset();
      loadSessions();
    } else { alert(res.message); }
  }

  async function deleteSession(id) {
    if(!confirm("Delete this session?")) return;
    const res = await selfApi('POST', {action: 'delete_session', id});
    if(res.success) loadSessions();
  }
</script>
</body>
</html>
