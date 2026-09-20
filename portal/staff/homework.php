<?php
require_once __DIR__ . '/../auth.php';
check_staff_auth();
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
  if($method==='GET' && isset($_GET['submissions'])){
    $hwId = intval($_GET['hwId']??0);
    $sid = (int)$_SESSION['ksm_staff_auth'];
    // Staff can see all submissions for a given homework ID ONLY if they authored it
    $where = ["h.staff_id=$sid"];
    if($hwId) $where[] = "s.homework_id=$hwId";
    $w = "WHERE " . implode(" AND ", $where);
    $r=ksm_db()->query("SELECT s.*, us.name student_name FROM homework_submissions s JOIN users_students us ON s.student_id=us.id JOIN homework h ON s.homework_id = h.id $w ORDER BY s.submitted_at DESC");
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;
    ksm_json($rows);
  }
  if($method==='GET'){
    $sid = (int)$_SESSION['ksm_staff_auth'];
    $cls = ksm_esc($_GET['class']??'');
    $where = "WHERE h.staff_id=$sid";
    if($cls) $where .= " AND h.class_name='$cls'";
    $r=ksm_db()->query("SELECT h.*,us.name staff_name FROM homework h LEFT JOIN users_staff us ON h.staff_id=us.id $where ORDER BY h.date_assigned DESC");
    $rows=[];
    while($row=$r->fetch_assoc())$rows[]=$row;
    ksm_json($rows);
  }
  if($method==='POST'){$t=ksm_esc($body['title']??'');$cl=ksm_esc($body['class_name']??$body['class']??'');$sub=ksm_esc($body['subject']??'');$desc=ksm_esc($body['description']??'');$due=ksm_esc($body['due_date']??'');$sid=intval($body['staff_id']??0);if(!$t||!$cl||!$sub)ksm_err('Title, class and subject required.');ksm_db()->query("INSERT INTO homework(title,class_name,subject,description,due_date,staff_id)VALUES('$t','$cl','$sub','$desc','$due',".($sid?$sid:'NULL').")");ksm_json(['id'=>ksm_db()->insert_id],'Homework assigned.');}
  if($method==='PUT'){$id=intval($body['id']??0);$t=ksm_esc($body['title']??'');$cl=ksm_esc($body['class_name']??'');$sub=ksm_esc($body['subject']??'');$desc=ksm_esc($body['description']??'');$due=ksm_esc($body['due_date']??'');if(!$id)ksm_err('Invalid ID.');ksm_db()->query("UPDATE homework SET title='$t',class_name='$cl',subject='$sub',description='$desc',due_date='$due' WHERE id=$id");ksm_json(null,'Updated.');}
  if($method==='DELETE'){$id=intval($_GET['id']??0);if(!$id)ksm_err('Invalid ID.');ksm_db()->query("DELETE FROM homework WHERE id=$id");ksm_json(null,'Deleted.');}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Assign Homework - KSM Teacher Portal">
  <title>Assign Homework - KSM Teacher Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Assign Homework</span>
      </div>
      <div class="topbar-right">
        <span id="staffNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:#10B981;color:white;" id="staffAvatar">S</div>
        <button class="btn btn-sm btn-danger" onclick="doLogout()">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">📋 Assign Homework (Diary)</h1>
          <p class="page-subtitle">Create and manage homework assignments for your class</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('homeworkModal'); clearForm()">+ New Homework</button>
      </div>

      <!-- NEW TABS -->
      <div style="display:flex; gap:1.5rem; border-bottom:1px solid var(--border); margin-bottom:1.5rem; padding-bottom:0.5rem;">
        <div id="tabBtnManage" onclick="switchTab('manage')" style="cursor:pointer; font-weight:600; color:var(--primary); border-bottom:2px solid var(--primary); padding:0.5rem; user-select:none;">Manage Homework</div>
        <div id="tabBtnSubmissions" onclick="switchTab('submissions')" style="cursor:pointer; font-weight:600; color:var(--text-medium); padding:0.5rem; user-select:none;">Check Submissions</div>
      </div>

      <!-- MANAGE VIEW -->
      <div id="viewManage">
        <!-- Filter bar -->
        <div class="search-bar">
          <select id="filterClass" class="form-control" style="max-width:200px;" onchange="renderHomework()">
            <option value="">-- All Classes --</option>
            <option value="Playgroup">Playgroup</option>
            <option value="Nursery">Nursery</option>
            <option value="Prep">Prep</option>
            <option value="Grade One">Grade One</option>
            <option value="Grade Two">Grade Two</option>
            <option value="Grade Three">Grade Three</option>
          </select>
          <input type="text" id="searchHomework" class="search-input" placeholder="Search homework..." oninput="renderHomework()">
        </div>

        <!-- Homework list -->
        <div id="homeworkList" class="grid-2"></div>
      </div>

      <!-- SUBMISSIONS VIEW -->
      <div id="viewSubmissions" style="display:none;">
        <div class="form-group" style="max-width: 400px; margin-bottom: 1.5rem;">
          <label class="form-label">Select Homework Assignment</label>
          <select id="selectHomeworkSubmission" class="form-control" onchange="renderSubmissionsTab()">
            <option value="">-- Choose Homework --</option>
          </select>
        </div>
        <div id="submissionsTabList" style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div class="empty-state"><p>Please select a homework assignment above.</p></div>
        </div>
      </div>
    </div>
    <div class="portal-footer">© 2026 Kindergarten Saadia's Montessori School. All rights reserved.</div>
  </div>
</div>

<!-- Add/Edit Homework Modal -->
<div class="modal-overlay" id="homeworkModal">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="modalTitle">New Homework</h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <input type="hidden" id="editId">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Class *</label>
        <select id="hwClass" class="form-control" required>
          <option value="">Select class</option>
          <option>Playgroup</option>
          <option>Nursery</option>
          <option>Prep</option>
          <option>Grade One</option>
          <option>Grade Two</option>
          <option>Grade Three</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Subject *</label>
        <input type="text" id="hwSubject" class="form-control" placeholder="e.g. Mathematics">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Title *</label>
      <input type="text" id="hwTitle" class="form-control" placeholder="e.g. Chapter 5 Exercises">
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea id="hwDesc" class="form-control" rows="3" placeholder="Detailed instructions for students..."></textarea>
    </div>
    <div class="form-group">
      <label class="form-label">Due Date *</label>
      <input type="date" id="hwDue" class="form-control" required>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;">
      <button class="btn btn-outline" data-modal-close>Cancel</button>
      <button class="btn btn-primary" onclick="saveHomework()">💾 Save Homework</button>
    </div>
  </div>
</div>




<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('staff');
  let currentHomeworkList = [];

  document.addEventListener('DOMContentLoaded', () => {
    currentStaff = Auth.getStaff();
    if (!currentStaff) { window.location.href = 'login.php'; return; }

    document.getElementById('staffNameTopbar').textContent = currentStaff.name;
    document.getElementById('staffAvatar').innerHTML = currentStaff.profilePic ? `<img src="${currentStaff.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : currentStaff.name.charAt(0).toUpperCase();

    // Default the class filter to teacher's class
    document.getElementById('filterClass').value = currentStaff.class || 'all';
    // Default subject
    document.getElementById('hwSubject').value = currentStaff.subject || '';
    document.getElementById('hwClass').value = currentStaff.class || '';

    renderHomework();
  });

  async function renderHomework() {
    const filterClass = document.getElementById('filterClass').value;
    const search = document.getElementById('searchHomework').value.toLowerCase();

    const res = await API.getHomework(filterClass === 'all' ? '' : filterClass);
    if (!res.success) return;
    
    let filtered = res.data;
    currentHomeworkList = filtered; // Store for submissions tab

    if (search) filtered = filtered.filter(h => h.title.toLowerCase().includes(search) || h.subject.toLowerCase().includes(search));

    const el = document.getElementById('homeworkList');
    if (filtered.length === 0) {
      el.innerHTML = `<div class="empty-state" style="grid-column:1/-1;"><div class="empty-state-icon">📋</div><h3 class="empty-state-title">No Homework Yet</h3><p class="empty-state-text">Click "+ New Homework" to assign homework to your students.</p></div>`;
      return;
    }

    el.innerHTML = filtered.map(h => {
      const isPast = new Date(h.due_date) < new Date();
      const statusBadge = isPast ? '<span class="badge badge-red">Past Due</span>' : '<span class="badge badge-green">Active</span>';
      return `
      <div class="homework-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.5rem;">
          <h3 class="homework-title">${h.title}</h3>
          ${statusBadge}
        </div>
        <div class="homework-meta">
          <span>📚 ${h.subject}</span>
          <span>🏫 ${h.class_name}</span>
          <span>📅 Due: ${formatDate(h.due_date)}</span>
        </div>
        <p class="homework-desc">${h.description || 'No description provided.'}</p>
        <div style="display:flex;gap:0.5rem;margin-top:0.75rem;">
          <button class="btn btn-sm btn-outline" onclick="editHomework('${h.id}')">✏️ Edit</button>
          <button class="btn btn-sm btn-danger" onclick="deleteHomework('${h.id}')">🗑️ Delete</button>
        </div>
      </div>`;
    }).join('');
  }

  function clearForm() {
    document.getElementById('editId').value = '';
    document.getElementById('hwClass').value = currentStaff.class || '';
    document.getElementById('hwSubject').value = currentStaff.subject || '';
    document.getElementById('hwTitle').value = '';
    document.getElementById('hwDesc').value = '';
    document.getElementById('hwDue').value = '';
    document.getElementById('modalTitle').textContent = 'New Homework';
  }

  async function saveHomework() {
    const cls = document.getElementById('hwClass').value;
    const subject = document.getElementById('hwSubject').value.trim();
    const title = document.getElementById('hwTitle').value.trim();
    const description = document.getElementById('hwDesc').value.trim();
    const dueDate = document.getElementById('hwDue').value;

    if (!cls || !subject || !title || !dueDate) {
      showToast('Please fill all required fields.', 'error');
      return;
    }

    const data = { class_name: cls, subject, title, description, due_date: dueDate, staff_id: currentStaff.id };
    const editId = document.getElementById('editId').value;

    if (editId) {
      data.id = editId;
      const res = await API.updateHomework(data);
      if (res.success) showToast('Homework updated!', 'success');
      else showToast(res.message, 'error');
    } else {
      const res = await API.addHomework(data);
      if (res.success) showToast('Homework assigned successfully!', 'success');
      else showToast(res.message, 'error');
    }

    closeModal('homeworkModal');
    renderHomework();
  }

  function editHomework(id) {
    const h = currentHomeworkList.find(h => h.id == id);
    if (!h) return;
    document.getElementById('editId').value = h.id;
    document.getElementById('hwClass').value = h.class_name;
    document.getElementById('hwSubject').value = h.subject;
    document.getElementById('hwTitle').value = h.title;
    document.getElementById('hwDesc').value = h.description || '';
    document.getElementById('hwDue').value = h.due_date;
    document.getElementById('modalTitle').textContent = 'Edit Homework';
    openModal('homeworkModal');
  }

  function deleteHomework(id) {
    confirmDelete('Delete this homework assignment?', async () => {
      const res = await API.deleteHomework(id);
      if (res.success) {
        showToast('Homework deleted.', 'info');
        renderHomework();
      } else {
        showToast(res.message, 'error');
      }
    });
  }

  function switchTab(tab) {
    if (tab === 'manage') {
      document.getElementById('tabBtnManage').style.color = 'var(--primary)';
      document.getElementById('tabBtnManage').style.borderBottom = '2px solid var(--primary)';
      document.getElementById('tabBtnSubmissions').style.color = 'var(--text-medium)';
      document.getElementById('tabBtnSubmissions').style.borderBottom = 'none';
      document.getElementById('viewManage').style.display = 'block';
      document.getElementById('viewSubmissions').style.display = 'none';
      renderHomework();
    } else {
      document.getElementById('tabBtnSubmissions').style.color = 'var(--primary)';
      document.getElementById('tabBtnSubmissions').style.borderBottom = '2px solid var(--primary)';
      document.getElementById('tabBtnManage').style.color = 'var(--text-medium)';
      document.getElementById('tabBtnManage').style.borderBottom = 'none';
      document.getElementById('viewManage').style.display = 'none';
      document.getElementById('viewSubmissions').style.display = 'block';
      
      const sel = document.getElementById('selectHomeworkSubmission');
      sel.innerHTML = '<option value="">-- Choose Homework --</option>' + currentHomeworkList.map(h => `<option value="${h.id}">${h.title} (${h.class_name})</option>`).join('');
      renderSubmissionsTab();
    }
  }

  async function renderSubmissionsTab() {
    const hwId = document.getElementById('selectHomeworkSubmission').value;
    const listEl = document.getElementById('submissionsTabList');
    if (!hwId) {
      listEl.innerHTML = '<div class="empty-state"><p>Please select a homework assignment above.</p></div>';
      return;
    }
    const hw = currentHomeworkList.find(h => h.id == hwId);
    if (!hw) return;

    listEl.innerHTML = '<div style="padding:2rem;text-align:center;">Loading students...</div>';

    // Fetch students using API.getAttendance trick or API.getStudents if available
    const resStudents = await API.getAttendance({ students: 1, class: hw.class_name });
    if (!resStudents.success || !resStudents.data || resStudents.data.length === 0) {
      listEl.innerHTML = '<div class="empty-state"><div class="empty-state-icon">🚸</div><h3 class="empty-state-title">No students found</h3><p class="empty-state-text">No students are currently assigned to this class.</p></div>';
      return;
    }
    const allStudents = resStudents.data;
    
    // Fetch submissions from API
    const subRes = await API.getHomeworkSubmissions(hwId);
    const submissions = subRes.success && subRes.data ? subRes.data : [];

    listEl.innerHTML = allStudents.map(student => {
      const sub = submissions.find(s => s.student_id == student.id);
      if (sub) {
        return `
          <div style="background:var(--primary-bg); padding:0.75rem; border-radius:var(--radius-sm); border-left:4px solid var(--success);">
            <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;">
              <strong>${student.name} <span style="color:var(--text-medium);font-size:0.8rem;">(${student.rollNo})</span></strong>
              <span class="badge badge-green">Submitted</span>
            </div>
            <p style="font-size:0.85rem; color:var(--text-dark); margin:0.5rem 0; background:white; padding:0.5rem; border-radius:4px;">${sub.answer}</p>
            <div style="font-size:0.75rem; color:var(--text-light);">Submitted: ${formatDate(sub.submitted_at)}</div>
          </div>`;
      } else {
        return `
          <div style="background:var(--bg-light); padding:0.75rem; border-radius:var(--radius-sm); border-left:4px solid var(--danger); opacity:0.8;">
            <div style="display:flex; justify-content:space-between;">
              <strong>${student.name} <span style="color:var(--text-medium);font-size:0.8rem;">(${student.rollNo})</span></strong>
              <span class="badge badge-red">Not Submitted</span>
            </div>
          </div>`;
      }
    }).join('');
  }

  function doLogout() {

    Auth.logoutStaff();
    window.location.href = 'login.php';
  }
</script>
</body>
</html>


