<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_student_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){
  if($method==='GET'||$method==='PUT'){
    $id = $method==='GET' ? intval($_GET['id']??0) : intval($body['id']??0);
    if(!$id)ksm_err('Invalid ID.');
    if($id !== (int)$_SESSION['ksm_student_auth']) ksm_err('Access denied.', 403);
    if($method==='GET'){
      $r=ksm_db()->query("SELECT id,name,email,phone,address,class,rollNo,parentName,profilePic FROM users_students WHERE id=$id LIMIT 1");if(!$r||$r->num_rows===0)ksm_err('Not found.',404);ksm_json($r->fetch_assoc());
    }else{
      $raw_phone=trim($body['phone']??'');
      $raw_addr=trim($body['address']??'');
      $pic=trim($body['profilePic']??'');

      if($raw_phone && !preg_match('/^[\+0-9\s\-]{10,20}$/', $raw_phone)) ksm_err('Invalid phone number format.');
      if(strlen($raw_addr)>255) ksm_err('Address too long.');

      if($pic && preg_match('/^data:image\/(jpeg|png|gif|webp);base64,/', $pic, $matches)) {
          $data = explode(',', $pic)[1];
          $data = base64_decode($data);
          $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
          $filename = 'student_' . $id . '_' . time() . '.' . $ext;
          $uploadDir = __DIR__ . '/../../uploads/profiles/';
          if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
          file_put_contents($uploadDir . $filename, $data);
          $pic = '../../uploads/profiles/' . $filename;
      } else if($pic && (!preg_match('/^(https?:\/\/|\.\.\/|\/|assets\/)/i', $pic) || preg_match('/javascript:/i', $pic))) {
          $pic = '';
      }

      $phone=ksm_esc(htmlspecialchars($raw_phone, ENT_QUOTES, 'UTF-8'));
      $addr=ksm_esc(htmlspecialchars($raw_addr, ENT_QUOTES, 'UTF-8'));
      $safe_pic=ksm_esc($pic);

      ksm_db()->query("UPDATE users_students SET phone='$phone',address='$addr',profilePic='$safe_pic' WHERE id=$id");
      $r=ksm_db()->query("SELECT id,name,email,phone,address,class,rollNo,parentName,profilePic FROM users_students WHERE id=$id LIMIT 1");ksm_json($r->fetch_assoc(),'Profile updated.');
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Student Profile — KSM School Portal">
  <title>My Profile — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
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
        <span id="studentNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:var(--accent-warm);color:var(--primary-deep);" id="studentAvatar">S</div>
        <button class="btn btn-sm btn-outline" onclick="doLogout()">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <!-- My Profile Card -->
      <div class="card mb-3">
        <div class="card-header">
          <h2 class="card-title">👤 My Profile</h2>
          <button class="btn btn-sm btn-primary" onclick="toggleEdit()" id="editToggle">✏️ Edit Profile</button>
        </div>
        <div style="display: flex; gap: 2rem; flex-wrap: wrap; align-items: flex-start; padding-top: 1rem;">
          <div style="flex-shrink: 0; width: 100%; max-width: 200px; text-align: center;">
            <div id="bigProfileAvatar" style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-deep), var(--primary-light)); color: white; display: flex; align-items: center; justify-content: center; font-size: 3.5rem; font-weight: bold; margin: 0 auto 1rem; box-shadow: var(--shadow-md);">
              S
            </div>
            <h3 id="bigProfileName" style="margin-bottom: 0.2rem; font-size: 1.2rem;">Name</h3>
            <p style="font-size: 0.85rem; color: var(--text-medium); font-weight: 600;">Student</p>
          </div>
          
          <div id="studentProfile" style="flex: 1; min-width: 250px; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;"></div>
        </div>
      </div>

    </div>
    <div class="portal-footer">© 2026 Kindergarten Saadia's Montessori School. All rights reserved.</div>
  </div>
</div>

<!-- Edit Profile Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0, 15, 40, 0.6); z-index:9000; flex-direction:column; align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(4px);">
  <div style="background:white; border-radius:12px; padding:2rem; width:100%; max-width:500px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
    <h2 style="margin-top:0; margin-bottom:1.5rem; font-size:1.5rem; color:var(--primary-deep); font-weight:700;">✏️ Edit Your Details</h2>
    <form id="editForm" onsubmit="saveProfile(event)">
      <div class="form-grid">
        <div class="form-group" style="margin-bottom:1rem;">
          <label class="form-label">Full Name</label>
          <input type="text" id="editName" class="form-control" disabled style="opacity:0.6;cursor:not-allowed;">
        </div>
        <div class="form-group" style="margin-bottom:1rem;">
          <label class="form-label">Email</label>
          <input type="email" id="editEmail" class="form-control" disabled style="opacity:0.6;cursor:not-allowed;">
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group" style="margin-bottom:1rem;">
          <label class="form-label">Father's Name</label>
          <input type="text" id="editFatherName" class="form-control" disabled style="opacity:0.6;cursor:not-allowed;">
        </div>
        <div class="form-group" style="margin-bottom:1rem;">
          <label class="form-label">Class</label>
          <input type="text" id="editClass" class="form-control" disabled style="opacity:0.6;cursor:not-allowed;">
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group" style="margin-bottom:1rem;">
          <label class="form-label">Phone Number</label>
          <input type="tel" id="editPhone" class="form-control" placeholder="+92 300 0000000">
        </div>
        <div class="form-group" style="margin-bottom:1rem;">
          <label class="form-label">Address</label>
          <input type="text" id="editAddress" class="form-control" placeholder="123 Street">
        </div>
      </div>
      <div class="form-group" style="margin-bottom:1.5rem;">
        <label class="form-label">Profile Picture</label>
        <div style="display:flex; gap:1rem; align-items:center;">
          <input type="hidden" id="editProfilePic">
          <button type="button" class="btn btn-outline" style="width:100%;" onclick="document.getElementById('profilePicFile').click()">📸 Upload New Picture</button>
          <input type="file" id="profilePicFile" accept="image/*" style="display:none;" onchange="handleFileSelect(event)">
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:2rem;">
        <button type="button" class="btn btn-outline" onclick="toggleEdit()">Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Cropper Modal -->
<div id="cropperModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; flex-direction:column; align-items:center; justify-content:center; padding:1rem;">
  <div style="background:white; border-radius:8px; padding:1.5rem; width:100%; max-width:600px; text-align:center;">
    <h3 style="margin-bottom:1rem; font-weight:600; font-size:1.2rem;">Crop Profile Picture</h3>
    <div style="width:100%; height:400px; background:#f0f0f0; margin-bottom:1rem; overflow:hidden;">
      <img id="cropperImage" style="max-width:100%; display:block;">
    </div>
    <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
      <div style="display:flex; gap:0.5rem;">
        <button type="button" class="btn btn-sm btn-outline" onclick="if(cropper) cropper.zoom(0.1)">🔍 Zoom In</button>
        <button type="button" class="btn btn-sm btn-outline" onclick="if(cropper) cropper.zoom(-0.1)">🔍 Zoom Out</button>
      </div>
      <div style="display:flex; gap:0.5rem;">
        <button type="button" class="btn btn-sm btn-outline" onclick="closeCropperModal()">Cancel</button>
        <button type="button" class="btn btn-sm btn-primary" id="btnCropSave" onclick="saveCroppedImage()">Crop & Upload</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('student');
  let currentStudent = null;
  let isEditing = false;

  document.addEventListener('DOMContentLoaded', async () => {
    try {
      let rawAuth = sessionStorage.getItem('ksm_student_auth');
      currentStudent = JSON.parse(rawAuth);
      if (typeof currentStudent !== 'object') currentStudent = { id: currentStudent };
    } catch(e) {
      currentStudent = null;
    }
    
    if (!currentStudent || !currentStudent.id) { window.location.href = 'login.php'; return; }
    
    // Refresh from DB
    const res = await API.getStudent(currentStudent.id);
    if (res && res.success && res.data) {
      currentStudent = res.data;
      sessionStorage.setItem('ksm_student_auth', JSON.stringify(currentStudent));
    }
    
    renderProfile();
  });

  function renderProfile() {
    if (!currentStudent) return;
    const name = currentStudent.name || 'Unknown Student';
    
    document.getElementById('studentNameTopbar').textContent = name;
    document.getElementById('bigProfileName').textContent = name;

    const avatarHtml = currentStudent.profilePic 
      ? `<img src="${currentStudent.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` 
      : name.charAt(0).toUpperCase();

    document.getElementById('studentAvatar').innerHTML = avatarHtml;
    document.getElementById('bigProfileAvatar').innerHTML = avatarHtml;

    // Profile
    const profileFields = [
      ['Full Name', currentStudent.name],
      ['Email', currentStudent.email],
      ['Phone Number', currentStudent.phone || 'N/A'],
      ['Address', currentStudent.address || 'N/A'],
      ['Father\'s Name', currentStudent.parentName],
      ['Class', currentStudent.class],
    ];
    document.getElementById('studentProfile').innerHTML = profileFields.map(([label, val]) => `
      <div style="background:var(--primary-bg);padding:0.75rem;border-radius:var(--radius-sm);">
        <p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.2rem;">${escapeHtml(label)}</p>
        <p style="font-weight:600;font-size:0.9rem;">${escapeHtml(val)}</p>
      </div>
    `).join('');

    // Fill edit form
    document.getElementById('editName').value = currentStudent.name || '';
    document.getElementById('editEmail').value = currentStudent.email || '';
    document.getElementById('editFatherName').value = currentStudent.parentName || '';
    document.getElementById('editClass').value = currentStudent.class || '';
    document.getElementById('editPhone').value = currentStudent.phone || '';
    document.getElementById('editAddress').value = currentStudent.address || '';
    document.getElementById('editProfilePic').value = currentStudent.profilePic || '';
  }

  function toggleEdit() {
    isEditing = !isEditing;
    document.getElementById('editModal').style.display = isEditing ? 'flex' : 'none';
  }

  async function saveProfile(e) {
    e.preventDefault();
    const updates = {
      id: currentStudent.id,
      phone: document.getElementById('editPhone').value.trim(),
      address: document.getElementById('editAddress').value.trim(),
      profilePic: document.getElementById('editProfilePic').value.trim(),
    };
    const res = await selfApi('PUT', updates);
    if (res.success) {
      currentStudent = { ...currentStudent, ...res.data };
      sessionStorage.setItem('ksm_student_auth', JSON.stringify(currentStudent));
      showToast('Profile updated successfully!', 'success');
    } else {
      showToast(res.message || 'Error updating profile.', 'error');
    }
    isEditing = false;
    document.getElementById('editModal').style.display = 'none';
    renderProfile();
  }

  function doLogout() {
    sessionStorage.removeItem('ksm_student_auth');
    window.location.href = 'login.php';
  }

  // Cropper Logic
  let cropper = null;
  let currentImageType = 'image/jpeg';
  
  function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      showToast('Please select a valid image file.', 'error');
      return;
    }
    currentImageType = file.type === 'image/png' ? 'image/png' : (file.type === 'image/webp' ? 'image/webp' : 'image/jpeg');
    const reader = new FileReader();
    reader.onload = (e) => {
      document.getElementById('cropperModal').style.display = 'flex';
      const img = document.getElementById('cropperImage');
      
      // Reset image state
      img.style.display = 'block';
      img.style.maxWidth = '100%';
      img.style.height = 'auto';
      
      img.onload = () => {
        // Wait for next frame to ensure modal layout is fully rendered before Cropper initializes
        setTimeout(() => {
          if (cropper) { cropper.destroy(); }
          cropper = new Cropper(img, {
            aspectRatio: 1,
            viewMode: 1,
            background: false
          });
        }, 100);
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
    event.target.value = ''; // Reset input
  }

  function closeCropperModal() {
    document.getElementById('cropperModal').style.display = 'none';
    if (cropper) { cropper.destroy(); cropper = null; }
  }

  async function saveCroppedImage() {
    if (!cropper) return;
    const btn = document.getElementById('btnCropSave');
    btn.textContent = 'Uploading...';
    btn.disabled = true;
    
    // Get cropped canvas
    const canvas = cropper.getCroppedCanvas({ width: 300, height: 300 });
    const base64Image = canvas.toDataURL(currentImageType, 0.8);
    
    document.getElementById('editProfilePic').value = base64Image;
    closeCropperModal();
    
    btn.textContent = 'Crop & Upload';
    btn.disabled = false;
    
    // Auto-save
    saveProfile({ preventDefault: () => {} });
  }
</script>
</body>
</html>

