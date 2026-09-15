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
  if($method==='GET'){
    $type=$_GET['type']??'students';
    if($type==='students'){$r=ksm_db()->query("SELECT id,name,email,class,rollNo,parentName FROM users_students ORDER BY name ASC");}
    else{$r=ksm_db()->query("SELECT id,name,email,subject,class FROM users_staff ORDER BY name ASC");}
    $rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);
  }
  if($method==='POST'){
    $type=ksm_esc($body['type']??'student');
    if($type==='student'){$n=ksm_esc($body['name']??'');$e=ksm_esc($body['email']??'');$p=ksm_esc($body['password']??'student123');$cl=ksm_esc($body['class']??'');$roll=ksm_esc($body['rollNo']??'');$par=ksm_esc($body['parentName']??'');if(!$n||!$e)ksm_err('Name and email required.');ksm_db()->query("INSERT INTO users_students(name,email,password,class,rollNo,parentName)VALUES('$n','$e','$p','$cl','$roll','$par')");ksm_json(['id'=>ksm_db()->insert_id],'Student added.');}
    else{$n=ksm_esc($body['name']??'');$e=ksm_esc($body['email']??'');$p=ksm_esc($body['password']??'staff123');$sub=ksm_esc($body['subject']??'');$cl=ksm_esc($body['class']??'');if(!$n||!$e)ksm_err('Name and email required.');ksm_db()->query("INSERT INTO users_staff(name,email,password,subject,class)VALUES('$n','$e','$p','$sub','$cl')");ksm_json(['id'=>ksm_db()->insert_id],'Staff added.');}
  }
  if($method==='DELETE'){$id=intval($_GET['id']??0);$type=$_GET['type']??'student';if(!$id)ksm_err('Invalid ID.');$tbl=$type==='student'?'users_students':'users_staff';ksm_db()->query("DELETE FROM $tbl WHERE id=$id");ksm_json(null,'Deleted.');}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Manage Credentials — KSM Admin Portal">
  <title>Manage Credentials — KSM Admin Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Manage Credentials</span>
      </div>
      <div class="topbar-right">
        <span style="font-size:0.82rem;color:var(--text-medium);">Logged in as <strong>Admin</strong></span>
        <div class="topbar-avatar" style="background:var(--accent-warm);color:var(--primary-deep);">A</div>
        <button class="btn btn-sm btn-danger" onclick="doLogout()">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">🔑 Manage Credentials</h1>
          <p class="page-subtitle">View and reset login credentials for students and staff members</p>
        </div>
      </div>

      <div class="info-banner">
        🛡️ This page allows you to view and reset passwords for students and staff who have forgotten their credentials.
      </div>

      <!-- Tabs -->
      <div class="cred-tabs">
        <button class="cred-tab active" onclick="switchTab('students')">👨‍🎓 Student Credentials</button>
        <button class="cred-tab" onclick="switchTab('staff')">👨‍🏫 Staff Credentials</button>
      </div>

      <!-- Student Credentials Panel -->
      <div class="cred-panel active" id="panel-students">
        <div class="search-bar">
          <input type="text" class="search-input" placeholder="Search students by name or email..." oninput="renderStudents(this.value)">
        </div>
        <div class="card">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Class</th>
                  <th>Roll No</th>
                  <th>Password</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="studentsTbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Staff Credentials Panel -->
      <div class="cred-panel" id="panel-staff">
        <div class="search-bar">
          <input type="text" class="search-input" placeholder="Search staff by name or email..." oninput="renderStaff(this.value)">
        </div>
        <div class="card">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Subject</th>
                  <th>Class</th>
                  <th>Password</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="staffTbody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="portal-footer">© 2026 KSM Admin Panel. All rights reserved.</div>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal-overlay" id="resetModal">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title">🔒 Reset Password</h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <input type="hidden" id="resetId">
    <input type="hidden" id="resetType">
    <div class="info-banner" style="margin-bottom:1.25rem;">
      Resetting password for: <strong id="resetName">—</strong>
      <br><small id="resetEmail" style="color:var(--text-medium);"></small>
    </div>
    <div class="form-group">
      <label class="form-label">New Password *</label>
      <div style="position:relative;">
        <input type="password" id="newPassword" class="form-control" placeholder="Enter new password" minlength="4" required>
        <button type="button" onclick="toggleNewPwd()" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-medium);font-size:0.85rem;">Show</button>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Confirm Password *</label>
      <input type="password" id="confirmPassword" class="form-control" placeholder="Confirm new password" required>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;">
      <button class="btn btn-outline" data-modal-close>Cancel</button>
      <button class="btn btn-primary" onclick="doResetPassword()">🔑 Reset Password</button>
    </div>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('admin');

  document.addEventListener('DOMContentLoaded', () => {
    if (!Auth.isAdminLoggedIn()) { window.location.href = 'login.php'; return; }
    renderStudents();
    renderStaff();
  });

  function switchTab(tab) {
    document.querySelectorAll('.cred-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.cred-panel').forEach(p => p.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
  }

  function renderStudents(search = '') {
    let students = DB.get('students');
    if (search) {
      const s = search.toLowerCase();
      students = students.filter(st => st.name.toLowerCase().includes(s) || st.email.toLowerCase().includes(s));
    }

    const tbody = document.getElementById('studentsTbody');
    if (students.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-medium);padding:2rem;">No students found.</td></tr>';
      return;
    }

    tbody.innerHTML = students.map(s => `
      <tr>
        <td><strong>${s.name}</strong></td>
        <td style="font-size:0.85rem;">${s.email}</td>
        <td><span class="badge badge-blue">${s.class}</span></td>
        <td>${s.rollNo}</td>
        <td>
          <div class="pwd-field">
            <span class="pwd-text" id="pwd-student-${s.id}">••••••••</span>
            <button class="pwd-reveal" onclick="togglePwdReveal('student','${s.id}','${s.password}')">👁️</button>
          </div>
        </td>
        <td>
          <button class="btn btn-sm btn-accent" onclick="openResetModal('student','${s.id}','${s.name}','${s.email}')">🔑 Reset</button>
        </td>
      </tr>
    `).join('');
  }

  function renderStaff(search = '') {
    let staff = DB.get('staff');
    if (search) {
      const s = search.toLowerCase();
      staff = staff.filter(st => st.name.toLowerCase().includes(s) || st.email.toLowerCase().includes(s));
    }

    const tbody = document.getElementById('staffTbody');
    if (staff.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-medium);padding:2rem;">No staff members found.</td></tr>';
      return;
    }

    tbody.innerHTML = staff.map(s => `
      <tr>
        <td><strong>${s.emoji || '👤'} ${s.name}</strong></td>
        <td style="font-size:0.85rem;">${s.email}</td>
        <td>${s.subject}</td>
        <td><span class="badge badge-green">${s.class}</span></td>
        <td>
          <div class="pwd-field">
            <span class="pwd-text" id="pwd-staff-${s.id}">••••••••</span>
            <button class="pwd-reveal" onclick="togglePwdReveal('staff','${s.id}','${s.password}')">👁️</button>
          </div>
        </td>
        <td>
          <button class="btn btn-sm btn-accent" onclick="openResetModal('staff','${s.id}','${s.name}','${s.email}')">🔑 Reset</button>
        </td>
      </tr>
    `).join('');
  }

  // Track reveal states
  const revealed = {};
  function togglePwdReveal(type, id, password) {
    const key = type + '-' + id;
    const el = document.getElementById('pwd-' + key);
    if (revealed[key]) {
      el.textContent = '••••••••';
      revealed[key] = false;
    } else {
      el.textContent = password;
      revealed[key] = true;
    }
  }

  function openResetModal(type, id, name, email) {
    document.getElementById('resetId').value = id;
    document.getElementById('resetType').value = type;
    document.getElementById('resetName').textContent = name;
    document.getElementById('resetEmail').textContent = email;
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    openModal('resetModal');
  }

  function doResetPassword() {
    const newPass = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;
    const id = document.getElementById('resetId').value;
    const type = document.getElementById('resetType').value;

    if (!newPass || newPass.length < 4) {
      showToast('Password must be at least 4 characters.', 'error');
      return;
    }
    if (newPass !== confirmPass) {
      showToast('Passwords do not match.', 'error');
      return;
    }

    const key = type === 'student' ? 'students' : 'staff';
    DB.update(key, id, { password: newPass });

    showToast('Password reset successfully!', 'success');
    closeModal('resetModal');

    // Re-render to reflect changes
    if (type === 'student') renderStudents();
    else renderStaff();

    // Reset revealed state
    const revealKey = type + '-' + id;
    revealed[revealKey] = false;
  }

  function toggleNewPwd() {
    const p = document.getElementById('newPassword');
    p.type = p.type === 'password' ? 'text' : 'password';
  }

  function doLogout() {
    Auth.logoutAdmin();
    window.location.href = 'login.php';
  }
</script>
</body>
</html>


