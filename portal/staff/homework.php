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
  if($method==='GET'){$sid=intval($_GET['staff_id']??0);$where=$sid?"WHERE h.staff_id=$sid":'';$r=ksm_db()->query("SELECT h.*,us.name staff_name FROM homework h LEFT JOIN users_staff us ON h.staff_id=us.id $where ORDER BY h.date_assigned DESC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
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

      <!-- Filter bar -->
      <div class="search-bar">
        <select id="filterClass" class="form-control" style="max-width:200px;" onchange="renderHomework()">
          <option value="all">All Classes</option>
          <option value="Kindergarten A">Kindergarten A</option>
          <option value="Early Childhood B">Early Childhood B</option>
          <option value="Junior Level">Junior Level</option>
        </select>
        <input type="text" id="searchHomework" class="search-input" placeholder="Search homework..." oninput="renderHomework()">
      </div>

      <!-- Homework list -->
      <div id="homeworkList" class="grid-2"></div>
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
          <option>Kindergarten A</option>
          <option>Early Childhood B</option>
          <option>Junior Level</option>
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
  let currentStaff = null;

  document.addEventListener('DOMContentLoaded', () => {
    currentStaff = Auth.getStaff();
    if (!currentStaff) { window.location.href = 'login.php'; return; }

    document.getElementById('staffNameTopbar').textContent = currentStaff.name;
    document.getElementById('staffAvatar').textContent = currentStaff.name.charAt(0).toUpperCase();

    // Default the class filter to teacher's class
    document.getElementById('filterClass').value = currentStaff.class || 'all';
    // Default subject
    document.getElementById('hwSubject').value = currentStaff.subject || '';
    document.getElementById('hwClass').value = currentStaff.class || '';

    renderHomework();
  });

  function renderHomework() {
    const allHW = DB.get('homework');
    const filterClass = document.getElementById('filterClass').value;
    const search = document.getElementById('searchHomework').value.toLowerCase();

    let filtered = allHW.filter(h => h.staffId === currentStaff.id);
    if (filterClass !== 'all') filtered = filtered.filter(h => h.class === filterClass);
    if (search) filtered = filtered.filter(h => h.title.toLowerCase().includes(search) || h.subject.toLowerCase().includes(search));

    filtered.sort((a, b) => new Date(b.dueDate) - new Date(a.dueDate));

    const el = document.getElementById('homeworkList');
    if (filtered.length === 0) {
      el.innerHTML = `<div class="empty-state" style="grid-column:1/-1;"><div class="empty-state-icon">📋</div><h3 class="empty-state-title">No Homework Yet</h3><p class="empty-state-text">Click "+ New Homework" to assign homework to your students.</p></div>`;
      return;
    }

    el.innerHTML = filtered.map(h => {
      const isPast = new Date(h.dueDate) < new Date();
      const statusBadge = isPast ? '<span class="badge badge-red">Past Due</span>' : '<span class="badge badge-green">Active</span>';
      return `
      <div class="homework-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.5rem;">
          <h3 class="homework-title">${h.title}</h3>
          ${statusBadge}
        </div>
        <div class="homework-meta">
          <span>📚 ${h.subject}</span>
          <span>🏫 ${h.class}</span>
          <span>📅 Due: ${formatDate(h.dueDate)}</span>
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

  function saveHomework() {
    const cls = document.getElementById('hwClass').value;
    const subject = document.getElementById('hwSubject').value.trim();
    const title = document.getElementById('hwTitle').value.trim();
    const description = document.getElementById('hwDesc').value.trim();
    const dueDate = document.getElementById('hwDue').value;

    if (!cls || !subject || !title || !dueDate) {
      showToast('Please fill all required fields.', 'error');
      return;
    }

    const data = { class: cls, subject, title, description, dueDate, staffId: currentStaff.id, staffName: currentStaff.name, createdAt: new Date().toISOString() };
    const editId = document.getElementById('editId').value;

    if (editId) {
      DB.update('homework', editId, data);
      showToast('Homework updated!', 'success');
    } else {
      DB.push('homework', data);
      showToast('Homework assigned successfully!', 'success');
    }

    closeModal('homeworkModal');
    renderHomework();
  }

  function editHomework(id) {
    const h = DB.get('homework').find(h => h.id === id);
    if (!h) return;
    document.getElementById('editId').value = h.id;
    document.getElementById('hwClass').value = h.class;
    document.getElementById('hwSubject').value = h.subject;
    document.getElementById('hwTitle').value = h.title;
    document.getElementById('hwDesc').value = h.description || '';
    document.getElementById('hwDue').value = h.dueDate;
    document.getElementById('modalTitle').textContent = 'Edit Homework';
    openModal('homeworkModal');
  }

  function deleteHomework(id) {
    confirmDelete('Delete this homework assignment?', () => {
      DB.delete('homework', id);
      showToast('Homework deleted.', 'info');
      renderHomework();
    });
  }

  function doLogout() {
    Auth.logoutStaff();
    window.location.href = 'login.php';
  }
</script>
</body>
</html>


