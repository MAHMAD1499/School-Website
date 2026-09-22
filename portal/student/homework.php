<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_student_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
header('Access-Control-Allow-Origin: *');header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){
  if($method==='GET' && isset($_GET['submissions'])){
    $hwId = intval($_GET['hwId']??0);
    $studentId = (int)$_SESSION['ksm_student_auth']; // Force to logged-in student
    $where = [];
    if($hwId) $where[] = "homework_id=$hwId";
    $where[] = "student_id=$studentId";
    $w = count($where) ? "WHERE " . implode(" AND ", $where) : "";
    $r=ksm_db()->query("SELECT s.*, us.name student_name FROM homework_submissions s JOIN users_students us ON s.student_id=us.id $w ORDER BY s.submitted_at DESC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    ksm_json($rows);
  }
  if($method==='GET'){
    $class=ksm_esc($_GET['class']??'');$where=$class?"WHERE class_name='$class'":'';
    $r=ksm_db()->query("SELECT h.*,us.name staff_name FROM homework h LEFT JOIN users_staff us ON h.staff_id=us.id $where ORDER BY h.date_assigned DESC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);
  }
  if($method==='POST'){
    $hwId = intval($body['hwId']??0);
    $studentId = intval($body['studentId']??0);
    $answer = ksm_esc($body['answer']??'');
    if(!$hwId || !$studentId) ksm_err('Invalid data.');
    if($studentId !== (int)$_SESSION['ksm_student_auth']) ksm_err('Access denied.', 403);
    ksm_db()->query("INSERT INTO homework_submissions(homework_id, student_id, answer) VALUES($hwId, $studentId, '$answer') ON DUPLICATE KEY UPDATE answer='$answer', submitted_at=CURRENT_TIMESTAMP");
    ksm_json(null, 'Submitted.');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homework Diary — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Homework Diary</span>
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
          <h1 class="page-title">📋 Homework Diary</h1>
          <p class="page-subtitle">View and submit your homework assignments</p>
        </div>
      </div>
      <div id="homeworkList" class="grid-2"></div>
    </div>
  </div>
</div>

<!-- Submit Homework Modal -->
<div class="modal-overlay" id="submitModal">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title">Submit Homework Answer</h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <input type="hidden" id="hwId">
    <div class="form-group">
      <label class="form-label">Your Answer *</label>
      <textarea id="hwAnswer" class="form-control" rows="5" placeholder="Type your answer here..." required></textarea>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;">
      <button class="btn btn-outline" data-modal-close>Cancel</button>
      <button class="btn btn-primary" onclick="submitAnswer()">📤 Submit</button>
    </div>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('student');
  let currentStudent = null;
  document.addEventListener('DOMContentLoaded', () => {
    currentStudent = Auth.getStudent();
    if (!currentStudent) { window.location.href = 'login.php'; return; }
    document.getElementById('studentNameTopbar').textContent = currentStudent.name;
    document.getElementById('studentAvatar').innerHTML = currentStudent.profilePic ? `<img src="${currentStudent.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : currentStudent.name.charAt(0).toUpperCase();

    renderHomework();
  });

  async function renderHomework() {
    const el = document.getElementById('homeworkList');
    el.innerHTML = '<div style="grid-column:1/-1;padding:2rem;text-align:center;">Loading homework...</div>';

    try {
      const res = await API.getHomework(currentStudent.class);
      if (!res.success || !res.data || res.data.length === 0) {
        el.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><div class="empty-state-icon">📋</div><p>No homework assigned yet.</p></div>';
        return;
      }
      
      const myHW = res.data;
      
      const subRes = await API.getHomeworkSubmissions(null, currentStudent.id);
      const submissions = subRes.success && subRes.data ? subRes.data : [];
      
      el.innerHTML = myHW.map(h => {
        const sub = submissions.find(s => s.homework_id == h.id && s.student_id == currentStudent.id);
        const isPast = new Date(h.due_date) < new Date();
          return `
        <div class="homework-card">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.5rem;">
            <h3 class="homework-title">${h.title}</h3>
            ${sub ? '<span class="badge badge-green">Submitted</span>' : isPast ? '<span class="badge badge-red">Past Due</span>' : '<span class="badge badge-blue">Pending</span>'}
          </div>
          <div class="homework-meta">
            <span>📚 ${h.subject}</span>
            <span>👨‍🏫 ${h.staff_name || 'Teacher'}</span>
            <span>📅 Due: ${formatDate(h.due_date)}</span>
          </div>
          <p class="homework-desc">${h.description || 'No description provided.'}</p>
          ${renderAssignment({ ...h, student_submitted: !!sub })}
        </div>`;
      }).join('');
    } catch (e) {
      el.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><p>Error loading homework.</p></div>';
    }
  }

  function renderAssignment(h) {
    if (h.student_submitted) {
      return `
        <div style="margin-top:0.5rem;display:flex;align-items:center;gap:0.5rem;">
          <span style="color:var(--primary);font-size:0.85rem;">✅ Submitted</span>
        </div>
      `;
    }
    return `
      <div style="margin-top:0.5rem;">
        <button class="btn btn-sm btn-primary" onclick="openSubmitModal('${h.id}')">Answer</button>
      </div>
    `;
  }

  function openSubmitModal(id) {
    document.getElementById('hwId').value = id;
    document.getElementById('hwAnswer').value = '';
    openModal('submitModal');
  }

  async function submitAnswer() {
    const answer = document.getElementById('hwAnswer').value.trim();
    if (!answer) { showToast('Please enter your answer', 'error'); return; }
    const hwId = document.getElementById('hwId').value;
    const data = {
      hwId,
      studentId: currentStudent.id,
      answer
    };
    
    const res = await API.submitHomeworkAnswer(data);
    if(res.success){
      showToast('Homework answer submitted!', 'success');
      closeModal('submitModal');
      renderHomework();
    } else {
      showToast(res.message, 'error');
    }
  }

  function doLogout() { Auth.logoutStudent(); window.location.href = 'login.php'; }
</script>
</body>
</html>


