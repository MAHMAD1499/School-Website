<?php
require_once __DIR__ . '/../auth.php';
check_admin_auth();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

$_ksm = ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => 'ksm_database'];
function ksm_db()
{
  global $_ksm;
  static $c = null;
  if ($c)
    return $c;
  $c = new mysqli($_ksm['host'], $_ksm['user'], $_ksm['pass'], $_ksm['name']);
  if ($c->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => $c->connect_error]));
  }
  $c->set_charset('utf8mb4');
  return $c;
}
function ksm_json($d, $m = 'OK', $code = 200)
{
  header('Content-Type: application/json');
  http_response_code($code);
  echo json_encode(['success' => $code < 400, 'message' => $m, 'data' => $d]);
  exit;
}
function ksm_err($m, $code = 400)
{
  ksm_json(null, $m, $code);
}
function ksm_esc($v)
{
  return ksm_db()->real_escape_string(trim($v ?? ''));
}
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS')
  exit;
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false || isset($_GET['_api']);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$body = json_decode(file_get_contents('php://input'), true) ?? [];
if ($isAjax) {
  $db = ksm_db();
  if ($method === 'GET') {
    $r = $db->query("SELECT id, name, role, subject, emoji, bio, email, staffNumber, password, profilePic FROM users_staff ORDER BY id ASC");
    $rows = [];
    while ($row = $r->fetch_assoc())
      $rows[] = $row;
    ksm_json($rows);
  }
  if ($method === 'POST') {
    $name = ksm_esc($body['name'] ?? '');
    $role = ksm_esc($body['role'] ?? '');
    $subject = ksm_esc($body['subject'] ?? '');
    $emoji = ksm_esc($body['emoji'] ?? '👤');
    $bio = ksm_esc($body['bio'] ?? '');
    $email = ksm_esc($body['email'] ?? '');
    $staffNumber = ksm_esc($body['staffNumber'] ?? '');
    $password = ksm_esc($body['password'] ?? '');
    if (!$name || !$role || !$subject || !$staffNumber)
      ksm_err('Name, Role, Subject, and Staff Number required.');
    if (!$password)
      ksm_err('Password required for new teacher.');
    if(!preg_match('/^[A-Za-z\s]{2,50}$/', $name)) ksm_err('Invalid name format.');
    if($email && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email)>100)) ksm_err('Invalid email format.');
    if(strlen($subject)>50 || strlen($staffNumber)>50) ksm_err('Subject or Staff Number too long.');
    if(strlen($bio)>1000) ksm_err('Bio too long.');
    if(strlen($password)<6 || strlen($password)>50) ksm_err('Password must be between 6 and 50 characters.');

    // Check duplicate staffNumber in users_staff
    $chk = $db->query("SELECT id FROM users_staff WHERE staffNumber='$staffNumber' LIMIT 1");
    if ($chk && $chk->num_rows > 0) {
      ksm_err("Staff number '$staffNumber' is already assigned to another teacher. Please use a different staff number.");
    }

    // Check duplicate email if provided
    if ($email) {
      $chkEmail = $db->query("SELECT id FROM users_staff WHERE email='$email' LIMIT 1");
      if ($chkEmail && $chkEmail->num_rows > 0) {
        ksm_err("Email '$email' is already registered. Please use a different email.");
      }
    }

    // Insert directly into users_staff
    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users_staff (name, email, password, subject, bio, emoji, staffNumber, role) VALUES ('$name', '$email', '$hashed_pass', '$subject', '$bio', '$emoji', '$staffNumber', '$role')";
    if (!$db->query($sql)) {
      ksm_err('Failed to create teacher account: ' . $db->error);
    }
    $newId = $db->insert_id;
    ksm_json(['id' => $newId], 'Teacher added successfully.');
  }
  if ($method === 'PUT') {
    $id = intval($body['id'] ?? 0);
    if (!$id) ksm_err('Invalid ID.');

    $curr = $db->query("SELECT * FROM users_staff WHERE id=$id")->fetch_assoc();
    if (!$curr) ksm_err('Teacher not found.');

    $name = isset($body['name']) ? ksm_esc($body['name']) : ksm_esc($curr['name'] ?? '');
    $role = isset($body['role']) ? ksm_esc($body['role']) : ksm_esc($curr['role'] ?? '');
    $subject = isset($body['subject']) ? ksm_esc($body['subject']) : ksm_esc($curr['subject'] ?? '');
    $emoji = isset($body['emoji']) ? ksm_esc($body['emoji']) : ksm_esc($curr['emoji'] ?? '👤');
    $bio = isset($body['bio']) ? ksm_esc($body['bio']) : ksm_esc($curr['bio'] ?? '');
    $email = isset($body['email']) ? ksm_esc($body['email']) : ksm_esc($curr['email'] ?? '');
    $staffNumber = isset($body['staffNumber']) ? ksm_esc($body['staffNumber']) : ksm_esc($curr['staffNumber'] ?? '');
    $password = trim($body['password'] ?? '');

    if(!preg_match('/^[A-Za-z\s]{2,50}$/', $name)) ksm_err('Invalid name format.');
    if($email && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email)>100)) ksm_err('Invalid email format.');
    if(strlen($subject)>50 || strlen($staffNumber)>50) ksm_err('Subject or Staff Number too long.');
    if(strlen($bio)>1000) ksm_err('Bio too long.');
    
    $password_update_sql = "";
    if ($password !== '') {
        if(strlen($password)<6 || strlen($password)>50) ksm_err('Password must be between 6 and 50 characters.');
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $password_update_sql = ", password='$hashed'";
    }

    $oldStaffNumber = ksm_esc($curr['staffNumber'] ?? '');

    if ($staffNumber && $staffNumber !== $oldStaffNumber) {
      $chk = $db->query("SELECT id FROM users_staff WHERE staffNumber='$staffNumber' AND id != $id LIMIT 1");
      if ($chk && $chk->num_rows > 0) {
        ksm_err("Staff number '$staffNumber' is already in use by another teacher.");
      }
    }

    $db->query("UPDATE users_staff SET name='$name', role='$role', subject='$subject', emoji='$emoji', bio='$bio', email='$email', staffNumber='$staffNumber' $password_update_sql WHERE id=$id");
    ksm_json(null, 'Updated.');
  }
  if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id)
      ksm_err('Invalid ID.');

    $s = $db->query("SELECT * FROM users_staff WHERE id=$id")->fetch_assoc();
    $sn = ksm_esc($s['staffNumber'] ?? '');

    // Clean up homework
    $db->query("DELETE FROM homework WHERE staff_id=$id");

    // Delete from users_staff
    $db->query("DELETE FROM users_staff WHERE id=$id");
    if ($sn) $db->query("DELETE FROM users_staff WHERE staffNumber='$sn'");

    ksm_json(null, 'Deleted.');
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Teachers — Admin Panel</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>

<body>
  <div class="portal-wrapper">
    <div class="portal-main">
      <div class="portal-topbar">
        <div class="topbar-left">
          <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2">
              <line x1="3" y1="6" x2="21" y2="6" />
              <line x1="3" y1="12" x2="21" y2="12" />
              <line x1="3" y1="18" x2="21" y2="18" />
            </svg></button>
          <span class="topbar-title">Manage Teachers</span>
        </div>
        <div class="topbar-right">
          <button class="btn btn-sm btn-danger"
            onclick="Auth.logoutAdmin();window.location.href='login.php'">Logout</button>
        </div>
      </div>

      <div class="portal-content fade-up">
        <div class="page-header">
          <div class="header-text">
            <h1 class="page-title">👨‍🏫 Manage Teachers</h1>
            <p class="page-subtitle">Add, edit or remove teachers</p>
          </div>
          <button class="btn btn-primary" onclick="openModal('teacherModal'); clearForm()">+ Add Teacher</button>
        </div>

        <div class="card">
          <div class="table-container">
            <table id="teachersTable">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Role</th>
                  <th>Subject</th>
                  <th>Bio</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="teachersTbody"></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="portal-footer">© 2026 KSM Admin Panel.</div>
    </div>
  </div>

  <!-- Add/Edit Modal -->
  <div class="modal-overlay" id="teacherModal">
    <div class="modal">
      <div class="modal-header">
        <h2 class="modal-title" id="teacherModalTitle">Add Teacher</h2>
        <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg></button>
      </div>
      <input type="hidden" id="editId">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" id="tName" class="form-control" placeholder="Ms. Jane Doe" pattern="[A-Za-z\s]{2,50}" maxlength="50" title="Only letters and spaces allowed" required>
        </div>
        <div class="form-group">
          <label class="form-label">Role *</label>
          <select id="tRole" class="form-control" required>
            <option value="">Select role</option>
            <option>MD</option>
            <option>Principal</option>
            <option>Co ordinator</option>
            <option>Junior/senior teacher</option>
            <option>Science teacher</option>
            <option>Computer teacher</option>
            <option>English teacher</option>
            <option>Montessori teacher</option>
            <option>Montessori Directors/Early Year Directors</option>
            <option>Library Teachers</option>
            <option>P.E teacher's</option>
            <option>Language Teacher</option>
          </select>
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Email Address (Optional)</label>
          <input type="email" id="tEmail" class="form-control" placeholder="teacher@ksm.edu" maxlength="100">
        </div>
        <div class="form-group">
          <label class="form-label">Subject / Specialty *</label>
          <input type="text" id="tSubject" class="form-control" placeholder="e.g. Mathematics" maxlength="50">
        </div>
        <div class="form-group">
          <label class="form-label">Emoji / Avatar</label>
          <input type="text" id="tEmoji" class="form-control" placeholder="e.g. 👩‍🏫" maxlength="4">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Bio / Description</label>
        <textarea id="tBio" class="form-control" rows="3" placeholder="Short bio..." maxlength="1000"></textarea>
      </div>

      <hr style="margin:1rem 0;border:none;border-top:1px solid var(--border-color);">
      <h3 style="margin-bottom:1rem;font-size:1rem;color:var(--text-dark);">Teacher Portal Credentials</h3>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Staff Number</label>
          <input type="text" id="tStaffNumber" class="form-control" placeholder="e.g. ST-001" maxlength="50">
        </div>
        <div class="form-group" id="tPwdGroup">
          <label class="form-label">Password</label>
          <input type="text" id="tPassword" class="form-control" placeholder="teacher123" minlength="6" maxlength="50">
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;">
        <button class="btn btn-outline" data-modal-close>Cancel</button>
        <button class="btn btn-primary" onclick="saveTeacher()">💾 Save Teacher</button>
      </div>
    </div>
  </div>

  <!-- Reset Password Modal -->
  <div class="modal-overlay" id="resetModal">
    <div class="modal">
      <div class="modal-header">
        <h2 class="modal-title">🔒 Reset Password</h2>
        <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg></button>
      </div>
      <input type="hidden" id="resetId">
      <div class="info-banner" style="margin-bottom:1.25rem;">
        Resetting password for: <strong id="resetName">—</strong>
        <br><small id="resetEmail" style="color:var(--text-medium);"></small>
      </div>
      <div class="form-group">
        <label class="form-label">New Password *</label>
        <div style="position:relative;">
          <input type="password" id="newPassword" class="form-control" placeholder="Enter new password" minlength="6" maxlength="50"
            required>
          <button type="button" onclick="toggleNewPwd()"
            style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-medium);font-size:0.85rem;">Show</button>
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

    document.addEventListener('DOMContentLoaded', async () => {
      if (!sessionStorage.getItem('ksm_admin_auth')) { window.location.href = 'login.php'; return; }
      await renderTable();
    });

    async function renderTable() {
      const res = await API.getTeachers();
      const teachers = res.data || [];
      const tbody = document.getElementById('teachersTbody');
      if (teachers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-medium);padding:2rem;">No teachers added yet.</td></tr>';
        return;
      }
      tbody.innerHTML = teachers.map(t => `
      <tr>
        <td><strong>${t.emoji || '👤'} ${t.name}</strong></td>
        <td><span class="badge badge-blue">${t.role}</span></td>
        <td>${t.subject}</td>
        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${t.bio || '—'}</td>
        <td>
          <div style="display:flex;gap:0.5rem;">
            <button class="btn btn-sm btn-outline" onclick="editTeacher('${t.id}')">✏️ Edit</button>
            <button class="btn btn-sm btn-accent" onclick="openResetModal('${t.id}', '${t.name}', '${t.email}')">🔑 Reset</button>
            <button class="btn btn-sm btn-danger" onclick="deleteTeacher('${t.id}')">🗑️</button>
          </div>
        </td>
      </tr>
    `).join('');
    }

    function clearForm() {
      document.getElementById('editId').value = '';
      document.getElementById('tName').value = '';
      document.getElementById('tRole').value = '';
      document.getElementById('tSubject').value = '';
      document.getElementById('tEmoji').value = '';
      document.getElementById('tBio').value = '';
      document.getElementById('tEmail').value = '';
      document.getElementById('tEmail').readOnly = false;
      document.getElementById('tStaffNumber').value = '';
      document.getElementById('tStaffNumber').readOnly = false;
      document.getElementById('tPassword').value = '';
      document.getElementById('tPwdGroup').style.display = 'block';
      document.getElementById('teacherModalTitle').textContent = 'Add Teacher';
    }

    async function editTeacher(id) {
      const res = await API.getTeachers();
      const t = (res.data || []).find(t => String(t.id) === String(id));
      if (!t) return;
      document.getElementById('editId').value = t.id;
      document.getElementById('tName').value = t.name;
      document.getElementById('tRole').value = t.role;
      document.getElementById('tSubject').value = t.subject;
      document.getElementById('tEmoji').value = t.emoji || '';
      document.getElementById('tBio').value = t.bio || '';
      document.getElementById('tEmail').value = t.email || '';
      document.getElementById('tEmail').readOnly = true;
      document.getElementById('tStaffNumber').value = t.staffNumber || '';
      document.getElementById('tStaffNumber').readOnly = true;
      document.getElementById('tPassword').value = t.password || '';
      document.getElementById('tPwdGroup').style.display = 'none';
      document.getElementById('teacherModalTitle').textContent = 'Edit Teacher';
      openModal('teacherModal');
    }

    async function saveTeacher() {
      const editId = document.getElementById('editId').value;
      const name = document.getElementById('tName').value.trim();
      const role = document.getElementById('tRole').value;
      const subject = document.getElementById('tSubject').value.trim();
      const email = document.getElementById('tEmail').value.trim();
      const staffNumber = document.getElementById('tStaffNumber').value.trim();
      const password = document.getElementById('tPassword').value.trim();
      if (!name || !role || !subject || !staffNumber) { showToast('Please fill in Name, Role, Subject, and Staff Number.', 'error'); return; }
      if (!editId && !password) { showToast('Please fill in Password for new teacher.', 'error'); return; }

      const data = { name, role, subject, emoji: document.getElementById('tEmoji').value || '👤', bio: document.getElementById('tBio').value.trim(), email, staffNumber, password };

      let res;
      if (editId) {
        res = await API.updateTeacher({ ...data, id: editId });
        if (res.success) showToast('Teacher updated successfully!', 'success');
      } else {
        res = await API.addTeacher(data);
        if (res.success) showToast('Teacher added successfully!', 'success');
      }
      if (!res.success) { showToast(res.message || 'Error saving teacher.', 'error'); return; }
      closeModal('teacherModal');
      await renderTable();
    }

    async function deleteTeacher(id) {
      confirmDelete('Delete this teacher? This cannot be undone.', async () => {
        const res = await API.deleteTeacher(id);
        if (res.success) {
          DB.delete('teachers', id);
          DB.delete('staff', id);
          showToast('Teacher deleted.', 'info');
        } else {
          showToast(res.message || 'Failed to delete teacher.', 'error');
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

      const res = await API.updateTeacher({ id, password: newPass });
      if (res && res.success) {
        DB.update('teachers', id, { password: newPass });
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
  </script>
</body>

</html>
