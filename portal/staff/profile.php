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
  if($method==='GET'){$id=intval($_GET['id']??0);if(!$id)ksm_err('Invalid ID.');$r=ksm_db()->query("SELECT id,name,email,phone,subject,class,bio,emoji,profilePic FROM users_staff WHERE id=$id LIMIT 1");if(!$r||$r->num_rows===0)ksm_err('Not found.',404);ksm_json($r->fetch_assoc());}
  if($method==='PUT'){$id=intval($body['id']??0);$name=ksm_esc($body['name']??'');$phone=ksm_esc($body['phone']??'');$sub=ksm_esc($body['subject']??'');$bio=ksm_esc($body['bio']??'');$pic=ksm_esc($body['profilePic']??'');if(!$id)ksm_err('Invalid ID.');if(!$name)ksm_err('Name required.');ksm_db()->query("UPDATE users_staff SET name='$name',phone='$phone',subject='$sub',bio='$bio',profilePic='$pic' WHERE id=$id");$r=ksm_db()->query("SELECT id,name,email,phone,subject,class,bio,emoji,profilePic FROM users_staff WHERE id=$id LIMIT 1");ksm_json($r->fetch_assoc(),'Profile updated.');}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Teacher Profile - KSM School Portal">
  <title>My Profile - KSM Teacher Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">My Profile</span>
      </div>
      <div class="topbar-right">
        <span id="staffNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:#10B981;color:white;" id="staffAvatar">S</div>
        <button class="btn btn-sm btn-outline" onclick="doLogout()">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <!-- Profile Display Card -->
      <div class="card mb-3">
        <div class="card-header">
          <h2 class="card-title">👤 My Profile</h2>
          <button class="btn btn-sm btn-primary" onclick="toggleEdit()" id="editToggle">✏️ Edit Profile</button>
        </div>
        <div style="display:flex;gap:2rem;flex-wrap:wrap;align-items:flex-start;padding-top:1rem;">
          <div style="flex-shrink:0;width:100%;max-width:200px;text-align:center;">
            <div id="bigAvatar" style="width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg,#047857,#10B981);color:white;display:flex;align-items:center;justify-content:center;font-size:3.5rem;font-weight:bold;margin:0 auto 1rem;box-shadow:var(--shadow-md);">S</div>
            <h3 id="bigName" style="margin-bottom:0.2rem;font-size:1.2rem;">Name</h3>
            <p style="font-size:0.85rem;color:var(--text-medium);font-weight:600;">Teacher</p>
          </div>
          <div id="profileDisplay" style="flex:1;min-width:250px;display:grid;grid-template-columns:1fr 1fr;gap:1rem;"></div>
        </div>
      </div>

      <!-- Edit Form Card (hidden by default) -->
      <div class="card mb-3 hidden" id="editCard">
        <div class="card-header">
          <h2 class="card-title">✏️ Edit Your Details</h2>
        </div>
        <form id="editForm" onsubmit="saveProfile(event)">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input type="text" id="editName" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" id="editEmail" class="form-control" disabled style="opacity:0.6;cursor:not-allowed;">
              <small style="color:var(--text-light);font-size:0.75rem;">Email cannot be changed. Contact admin for assistance.</small>
            </div>
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Phone Number</label>
              <input type="tel" id="editPhone" class="form-control" placeholder="+92 300 0000000">
            </div>
            <div class="form-group">
              <label class="form-label">Subject</label>
              <input type="text" id="editSubject" class="form-control" placeholder="e.g. Mathematics">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Profile Picture URL</label>
            <input type="url" id="editProfilePic" class="form-control" placeholder="https://example.com/photo.jpg">
          </div>
          <div class="form-group">
            <label class="form-label">Bio / About</label>
            <textarea id="editBio" class="form-control" rows="3" placeholder="Tell us about yourself..."></textarea>
          </div>
          <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;">
            <button type="button" class="btn btn-outline" onclick="toggleEdit()">Cancel</button>
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
          </div>
        </form>
      </div>
    </div>
    <div class="portal-footer">© 2026 Kindergarten Saadia's Montessori School. All rights reserved.</div>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('staff');
  let currentStaff = null;
  let isEditing = false;

  document.addEventListener('DOMContentLoaded', async () => {
    currentStaff = JSON.parse(sessionStorage.getItem('ksm_staff_auth'));
    if (!currentStaff) { window.location.href = 'login.php'; return; }
    // Refresh from DB
    const res = await API.getStaffMember(currentStaff.id);
    if (res.success && res.data) currentStaff = res.data;
    renderProfile();
  });

  function renderProfile() {
    document.getElementById('staffNameTopbar').textContent = currentStaff.name;
    document.getElementById('bigName').textContent = currentStaff.name;

    const avatarHtml = currentStaff.profilePic 
      ? `<img src="${currentStaff.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` 
      : currentStaff.name.charAt(0).toUpperCase();

    document.getElementById('staffAvatar').innerHTML = avatarHtml;
    document.getElementById('bigAvatar').innerHTML = avatarHtml;

    const fields = [
      ['Full Name', currentStaff.name],
      ['Email', currentStaff.email],
      ['Phone', currentStaff.phone || 'N/A'],
      ['Subject', currentStaff.subject || 'N/A'],
      ['Assigned Class', currentStaff.class || 'N/A'],
      ['Bio', currentStaff.bio || 'N/A'],
    ];

    document.getElementById('profileDisplay').innerHTML = fields.map(([label, val]) => `
      <div style="background:var(--primary-bg);padding:0.75rem;border-radius:var(--radius-sm);">
        <p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.2rem;">${label}</p>
        <p style="font-weight:600;font-size:0.9rem;">${val}</p>
      </div>
    `).join('');

    // Fill edit form
    document.getElementById('editName').value = currentStaff.name;
    document.getElementById('editEmail').value = currentStaff.email;
    document.getElementById('editPhone').value = currentStaff.phone || '';
    document.getElementById('editSubject').value = currentStaff.subject || '';
    document.getElementById('editBio').value = currentStaff.bio || '';
    document.getElementById('editProfilePic').value = currentStaff.profilePic || '';
  }

  function toggleEdit() {
    isEditing = !isEditing;
    document.getElementById('editCard').classList.toggle('hidden', !isEditing);
    document.getElementById('editToggle').textContent = isEditing ? '✕ Cancel Edit' : '✏️ Edit Profile';
  }

  async function saveProfile(e) {
    e.preventDefault();
    const name = document.getElementById('editName').value.trim();
    if (!name) { showToast('Name is required.', 'error'); return; }

    const updates = {
      id: currentStaff.id,
      name,
      phone: document.getElementById('editPhone').value.trim(),
      subject: document.getElementById('editSubject').value.trim(),
      bio: document.getElementById('editBio').value.trim(),
      profilePic: document.getElementById('editProfilePic').value.trim(),
    };

    const res = await API.updateStaff(updates);
    if (res.success) {
      currentStaff = { ...currentStaff, ...res.data };
      sessionStorage.setItem('ksm_staff_auth', JSON.stringify(currentStaff));
      showToast('Profile updated successfully!', 'success');
    } else {
      showToast(res.message || 'Error updating profile.', 'error');
    }
    isEditing = false;
    document.getElementById('editCard').classList.add('hidden');
    document.getElementById('editToggle').textContent = '✏️ Edit Profile';
    renderProfile();
  }

  function doLogout() {
    sessionStorage.removeItem('ksm_staff_auth');
    window.location.href = 'login.php';
  }
</script>
</body>
</html>

