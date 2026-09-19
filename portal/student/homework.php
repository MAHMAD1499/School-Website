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
  if($method==='GET'){$class=ksm_esc($_GET['class']??'');$where=$class?"WHERE class_name='$class'":'';$r=ksm_db()->query("SELECT h.*,us.name staff_name FROM homework h LEFT JOIN users_staff us ON h.staff_id=us.id $where ORDER BY h.date_assigned DESC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
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
    document.getElementById('studentAvatar').textContent = currentStudent.name.charAt(0).toUpperCase();

    renderHomework();
  });

  function renderHomework() {
    const allHW = DB.get('homework');
    const myHW = allHW.filter(h => h.class === currentStudent.class).sort((a,b) => new Date(b.dueDate) - new Date(a.dueDate));
    const submissions = DB.get('homework_submissions');
    
    const el = document.getElementById('homeworkList');
    if (myHW.length === 0) {
      el.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><div class="empty-state-icon">📋</div><p>No homework assigned yet.</p></div>';
      return;
    }

    el.innerHTML = myHW.map(h => {
      const sub = submissions.find(s => s.hwId === h.id && s.studentId === currentStudent.id);
      const isPast = new Date(h.dueDate) < new Date();
        return `
      <div class="homework-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.5rem;">
          <h3 class="homework-title">${h.title}</h3>
          ${sub ? '<span class="badge badge-green">Submitted</span>' : isPast ? '<span class="badge badge-red">Past Due</span>' : '<span class="badge badge-blue">Pending</span>'}
        </div>
        <div class="homework-meta">
          <span>📚 ${h.subject}</span>
          <span>👨‍🏫 ${h.staffName}</span>
          <span>📅 Due: ${formatDate(h.dueDate)}</span>
        </div>
        <p class="homework-desc">${h.description || 'No description provided.'}</p>
        ${renderAssignment({ ...h, student_submitted: !!sub })}
      </div>`;
    }).join('');
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

  function submitAnswer() {
    const answer = document.getElementById('hwAnswer').value.trim();
    if (!answer) { showToast('Please enter your answer', 'error'); return; }
    const hwId = document.getElementById('hwId').value;
    const data = {
      hwId,
      studentId: currentStudent.id,
      studentName: currentStudent.name,
      answer,
      submittedAt: new Date().toISOString()
    };
    DB.push('homework_submissions', data);
    showToast('Homework answer submitted!', 'success');
    closeModal('submitModal');
    renderHomework();
  }

  function doLogout() { Auth.logoutStudent(); window.location.href = 'login.php'; }
</script>
</body>
</html>


