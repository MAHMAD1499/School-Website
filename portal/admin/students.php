<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

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
  if($method==='GET'){$r=ksm_db()->query("SELECT * FROM users_students ORDER BY id ASC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
  if($method==='POST'){$name=ksm_esc($body['name']??'');$email=ksm_esc($body['email']??'');$password=ksm_esc($body['password']??'');$class=ksm_esc($body['class']??'');$rollNo=ksm_esc($body['rollNo']??'');$parentName=ksm_esc($body['parentName']??'');if(!$name||!$email||!$password)ksm_err('Name, Email, and Password required.');ksm_db()->query("INSERT INTO users_students(name,email,password,class,rollNo,parentName)VALUES('$name','$email','$password','$class','$rollNo','$parentName')");ksm_json(['id'=>ksm_db()->insert_id],'Student added.');}
  if($method==='PUT'){$id=intval($body['id']??0);$name=ksm_esc($body['name']??'');$email=ksm_esc($body['email']??'');$password=ksm_esc($body['password']??'');$class=ksm_esc($body['class']??'');$rollNo=ksm_esc($body['rollNo']??'');$parentName=ksm_esc($body['parentName']??'');if(!$id)ksm_err('Invalid ID.');ksm_db()->query("UPDATE users_students SET name='$name',email='$email',password='$password',class='$class',rollNo='$rollNo',parentName='$parentName' WHERE id=$id");ksm_json(null,'Updated.');}
  if($method==='DELETE'){$id=intval($_GET['id']??0);if(!$id)ksm_err('Invalid ID.');ksm_db()->query("DELETE FROM users_students WHERE id=$id");ksm_json(null,'Deleted.');}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Students — Admin Panel</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Manage Students</span>
      </div>
      <div class="topbar-right">
        <button class="btn btn-sm btn-danger" onclick="Auth.logoutAdmin();window.location.href='login.php'">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">👨‍🎓 Manage Students</h1>
          <p class="page-subtitle">Add, edit, or remove students and manage their login credentials</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('studentModal'); clearForm()">+ Add Student</button>
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

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="studentModal">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="studentModalTitle">Add Student</h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <input type="hidden" id="editId">
    
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input type="text" id="sName" class="form-control" placeholder="Student Name" required>
      </div>
      <div class="form-group">
        <label class="form-label">Roll No</label>
        <input type="text" id="sRollNo" class="form-control" placeholder="e.g. KA-001">
      </div>
    </div>
    
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Class</label>
        <input type="text" id="sClass" class="form-control" placeholder="e.g. Kindergarten A">
      </div>
      <div class="form-group">
        <label class="form-label">Parent Name</label>
        <input type="text" id="sParentName" class="form-control" placeholder="Parent Name">
      </div>
    </div>

    <hr style="margin:1rem 0;border:none;border-top:1px solid var(--border-color);">
    <h3 style="margin-bottom:1rem;font-size:1rem;color:var(--text-dark);">Portal Login Credentials</h3>

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Email Address *</label>
        <input type="email" id="sEmail" class="form-control" placeholder="student@ksm.edu" required>
      </div>
      <div class="form-group" id="sPwdGroup">
        <label class="form-label">Password *</label>
        <input type="text" id="sPassword" class="form-control" placeholder="student123" required>
      </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;">
      <button class="btn btn-outline" data-modal-close>Cancel</button>
      <button class="btn btn-primary" onclick="saveStudent()">💾 Save Student</button>
    </div>
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

<!-- Student Gallery Modal -->
<div class="modal-overlay" id="studentGalleryModal">
  <div class="modal" style="max-width:600px;">
    <div class="modal-header">
      <h2 class="modal-title">🖼️ Personal Gallery: <span id="galleryStudentName"></span></h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <input type="hidden" id="galleryStudentId">
    
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Upload New Photo</label>
        <input type="file" id="gFile" class="form-control" accept="image/*">
        <input type="hidden" id="gUrl">
      </div>
      <div class="form-group">
        <label class="form-label">Caption</label>
        <input type="text" id="gCaption" class="form-control" placeholder="Caption (optional)">
      </div>
    </div>
    <button class="btn btn-primary btn-sm" onclick="saveStudentPhoto()" style="margin-bottom:1rem;">Upload Photo</button>

    <hr style="border:none;border-top:1px solid var(--border-color);margin-bottom:1rem;">
    <h3 style="font-size:1rem;margin-bottom:0.75rem;">Existing Photos</h3>
    <div id="studentGalleryGrid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;max-height:300px;overflow-y:auto;padding-right:0.5rem;">
      <!-- Photos will load here -->
    </div>
  </div>
</div>

<script src="../assets/portal.js?v=3"></script>
<script src="../assets/api.js?v=3"></script>
<script src="../assets/sidebar.js?v=3"></script>
<script>
  buildSidebar('admin');

  function ksmEscapeJs(str) {
    if(!str) return '';
    return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
  }

  document.addEventListener('DOMContentLoaded', async () => {
    if (!sessionStorage.getItem('ksm_admin_auth')) { window.location.href = 'login.php'; return; }
    await renderTable();

    // File input listener for base64 conversion
    document.getElementById('gFile').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
          document.getElementById('gUrl').value = evt.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        document.getElementById('gUrl').value = '';
      }
    });
  });

  async function renderTable() {
    try {
      const res = await API.getStudents();
      const students = res.data || [];
      // Also push to local DB to sync for credentials panel fallback
      try { DB.set('students', students); } catch(e) { console.error('DB set failed:', e); }
      
      const tbody = document.getElementById('studentsTbody');
      if (students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-medium);padding:2rem;">No students added yet.</td></tr>';
        return;
      }
      tbody.innerHTML = students.map(s => `
      <tr>
        <td><strong>${s.name}</strong></td>
        <td>${s.email}</td>
        <td><span class="badge badge-blue">${s.class}</span></td>
        <td>${s.rollNo}</td>
        <td>
          <div style="display:flex;gap:0.5rem;">
            <button class="btn btn-sm btn-outline" onclick="editStudent('${s.id}')">✏️ Edit</button>
            <button class="btn btn-sm btn-outline" onclick="openStudentGalleryModal('${s.id}', '${ksmEscapeJs(s.name)}')">🖼️ Photos</button>
            <button class="btn btn-sm btn-accent" onclick="openResetModal('${s.id}', '${ksmEscapeJs(s.name)}', '${ksmEscapeJs(s.email)}')">🔑 Reset</button>
            <button class="btn btn-sm btn-danger" onclick="deleteStudent('${s.id}')">🗑️</button>
          </div>
        </td>
      </tr>
    `).join('');
    } catch(err) {
      console.error('Error rendering table:', err);
      const tbody = document.getElementById('studentsTbody');
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#EF4444;padding:2rem;">Failed to load students. Please refresh the page.</td></tr>';
    }
  }

  function clearForm() {
    document.getElementById('editId').value = '';
    document.getElementById('sName').value = '';
    document.getElementById('sEmail').value = '';
    document.getElementById('sEmail').readOnly = false;
    document.getElementById('sPassword').value = '';
    document.getElementById('sPwdGroup').style.display = 'block';
    document.getElementById('sClass').value = '';
    document.getElementById('sRollNo').value = '';
    document.getElementById('sParentName').value = '';
    document.getElementById('studentModalTitle').textContent = 'Add Student';
  }

  async function editStudent(id) {
    const res = await API.getStudents();
    const s = (res.data || []).find(st => String(st.id) === String(id));
    if (!s) return;
    document.getElementById('editId').value = s.id;
    document.getElementById('sName').value = s.name;
    document.getElementById('sEmail').value = s.email;
    document.getElementById('sEmail').readOnly = true;
    document.getElementById('sPassword').value = s.password || '';
    document.getElementById('sPwdGroup').style.display = 'none';
    document.getElementById('sClass').value = s.class || '';
    document.getElementById('sRollNo').value = s.rollNo || '';
    document.getElementById('sParentName').value = s.parentName || '';
    document.getElementById('studentModalTitle').textContent = 'Edit Student';
    openModal('studentModal');
  }

  async function saveStudent() {
    const editId = document.getElementById('editId').value;
    const name = document.getElementById('sName').value.trim();
    const email = document.getElementById('sEmail').value.trim();
    const password = document.getElementById('sPassword').value.trim();
    
    if (!name || !email) { showToast('Please fill in Name and Email.', 'error'); return; }
    if (!editId && !password) { showToast('Please fill in Password for new student.', 'error'); return; }

    const data = { 
        name, 
        email, 
        password,
        class: document.getElementById('sClass').value.trim(),
        rollNo: document.getElementById('sRollNo').value.trim(),
        parentName: document.getElementById('sParentName').value.trim()
    };
    
    let res;
    if (editId) {
      res = await API.updateStudent({ ...data, id: editId });
      if (res.success) {
        showToast('Student updated successfully!', 'success');
        DB.update('students', editId, data);
      }
    } else {
      res = await API.addStudent(data);
      if (res.success) {
        showToast('Student added successfully!', 'success');
        DB.push('students', {...data, id: res.data.id});
      }
    }
    if (!res.success) { showToast(res.message || 'Error saving student.', 'error'); return; }
    closeModal('studentModal');
    await renderTable();
  }

  async function deleteStudent(id) {
    confirmDelete('Delete this student? This cannot be undone.', async () => {
      const res = await API.deleteStudent(id);
      if (res.success) {
          showToast('Student deleted.', 'info');
          DB.delete('students', id);
      }
      await renderTable();
    });
  }

  function openResetModal(id, name, email) {
    document.getElementById('resetId').value = id;
    document.getElementById('resetName').textContent = name;
    document.getElementById('resetEmail').textContent = email;
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    openModal('resetModal');
  }

  async function doResetPassword() {
    const newPass = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;
    const id = document.getElementById('resetId').value;

    if (!newPass || newPass.length < 4) {
      showToast('Password must be at least 4 characters.', 'error');
      return;
    }
    if (newPass !== confirmPass) {
      showToast('Passwords do not match.', 'error');
      return;
    }

    const res = await API.updateStudent({ id, password: newPass });
    if (res && res.success) {
      DB.update('students', id, { password: newPass });
      showToast('Password reset successfully!', 'success');
      closeModal('resetModal');
    } else {
      showToast(res?.message || 'Failed to reset password', 'error');
    }
  }

  function toggleNewPwd() {
    const p = document.getElementById('newPassword');
    p.type = p.type === 'password' ? 'text' : 'password';
  }

  async function openStudentGalleryModal(id, name) {
    document.getElementById('galleryStudentId').value = id;
    document.getElementById('galleryStudentName').textContent = name;
    document.getElementById('gFile').value = '';
    document.getElementById('gUrl').value = '';
    document.getElementById('gCaption').value = '';
    openModal('studentGalleryModal');
    await renderStudentGallery(id);
  }

  async function renderStudentGallery(studentId) {
    const grid = document.getElementById('studentGalleryGrid');
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--text-medium);">Loading...</div>';
    
    const res = await API.getStudentPersonalGallery(studentId);
    const photos = res.data || [];
    
    if (photos.length === 0) {
      grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--text-medium);padding:1rem;">No personal photos uploaded for this student yet.</div>';
      return;
    }
    
    grid.innerHTML = photos.map(p => `
      <div class="card" style="padding:0;overflow:hidden;position:relative;">
        <img src="${p.url}" alt="${ksmEscapeJs(p.caption)}" style="width:100%;height:100px;object-fit:cover;" onerror="this.src='https://via.placeholder.com/300x200/EFF6FF/1E3A8A?text=Error'">
        <button class="btn btn-danger" style="position:absolute;top:0.25rem;right:0.25rem;padding:0.25rem 0.5rem;font-size:0.75rem;" onclick="deleteStudentPhoto('${p.id}')">✕</button>
        <div style="padding:0.5rem;font-size:0.75rem;color:var(--text-medium);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
          ${p.caption || 'No caption'}
        </div>
      </div>
    `).join('');
  }

  async function saveStudentPhoto() {
    const studentId = document.getElementById('galleryStudentId').value;
    const url = document.getElementById('gUrl').value;
    const caption = document.getElementById('gCaption').value.trim();

    if (!url) {
      showToast('Please select a photo.', 'error');
      return;
    }

    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Uploading...';

    const res = await API.addStudentPersonalPhoto({ student_id: studentId, url, caption });
    
    btn.disabled = false;
    btn.textContent = 'Upload Photo';

    if (res.success) {
      showToast('Photo uploaded to personal gallery!', 'success');
      document.getElementById('gFile').value = '';
      document.getElementById('gUrl').value = '';
      document.getElementById('gCaption').value = '';
      await renderStudentGallery(studentId);
    } else {
      showToast(res.message || 'Error uploading photo', 'error');
    }
  }

  async function deleteStudentPhoto(photoId) {
    if (!confirm('Delete this photo from the student\'s personal gallery?')) return;
    const res = await API.deleteStudentPersonalPhoto(photoId);
    if (res.success) {
      showToast('Photo deleted.', 'info');
      const studentId = document.getElementById('galleryStudentId').value;
      await renderStudentGallery(studentId);
    }
  }
</script>
<script>
window.addEventListener('error', function(e) {
  const t = document.getElementById('studentsTbody');
  if(t) t.innerHTML = '<tr><td colspan="5" style="color:red">JS Error: ' + e.message + '</td></tr>';
});
window.addEventListener('unhandledrejection', function(e) {
  const t = document.getElementById('studentsTbody');
  if(t) t.innerHTML = '<tr><td colspan="5" style="color:red">Promise Error: ' + e.reason + '</td></tr>';
});
</script>
</body>
</html>




