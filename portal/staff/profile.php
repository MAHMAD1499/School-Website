<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_staff_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){
  if($method==='GET'||$method==='PUT'||$method==='POST'){
    $id = $method==='GET' ? intval($_GET['id']??($_SESSION['ksm_staff_auth']??0)) : intval($body['id']??($_SESSION['ksm_staff_auth']??0));
    if(!$id)ksm_err('Invalid ID.');
    if($id !== (int)$_SESSION['ksm_staff_auth']) ksm_err('Access denied.', 403);
    if($method==='GET'){
      $r=ksm_db()->query("SELECT id,name,email,phone,subject,class,bio,emoji,profilePic FROM users_staff WHERE id=$id LIMIT 1");if(!$r||$r->num_rows===0)ksm_err('Not found.',404);ksm_json($r->fetch_assoc());
    }else{
      $raw_name=trim($body['name']??'');
      $raw_phone=trim($body['phone']??'');
      $raw_sub=trim($body['subject']??'');
      $raw_bio=trim($body['bio']??'');
      $pic=trim($body['profilePic']??'');

      if(!$raw_name)ksm_err('Name required.');
      if(!preg_match('/^[A-Za-z\s.\'-]{2,50}$/', $raw_name)) ksm_err('Invalid name format.');
      if($raw_phone && !preg_match('/^[\+0-9\s\-]{10,20}$/', $raw_phone)) ksm_err('Invalid phone number format.');
      if(strlen($raw_sub)>100) ksm_err('Subject too long.');
      if(strlen($raw_bio)>1000) ksm_err('Bio too long.');

      if($pic && preg_match('/^data:image\/(jpeg|png|gif|webp);base64,/i', $pic, $matches)) {
          $data = explode(',', $pic)[1];
          $data = base64_decode(str_replace(' ', '+', $data));
          $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
          $filename = 'staff_' . $id . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
          $uploadDir = __DIR__ . '/../../assets/uploads/profile_pics/';
          if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
          file_put_contents($uploadDir . $filename, $data);
          $pic = '../../assets/uploads/profile_pics/' . $filename;
      } else if($pic && (!preg_match('/^(https?:\/\/|\.\.\/|\/|assets\/)/i', $pic) || preg_match('/javascript:/i', $pic))) {
          $pic = '';
      }

      $name=ksm_esc(htmlspecialchars($raw_name, ENT_QUOTES, 'UTF-8'));
      $phone=ksm_esc(htmlspecialchars($raw_phone, ENT_QUOTES, 'UTF-8'));
      $sub=ksm_esc(htmlspecialchars($raw_sub, ENT_QUOTES, 'UTF-8'));
      $bio=ksm_esc(htmlspecialchars($raw_bio, ENT_QUOTES, 'UTF-8'));
      $safe_pic=ksm_esc($pic);

      ksm_db()->query("UPDATE users_staff SET name='$name',phone='$phone',subject='$sub',bio='$bio',profilePic='$safe_pic' WHERE id=$id");
      $r=ksm_db()->query("SELECT id,name,email,phone,subject,class,bio,emoji,profilePic FROM users_staff WHERE id=$id LIMIT 1");ksm_json($r->fetch_assoc(),'Profile updated.');
    }
  }
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
  <link rel="stylesheet" href="../assets/cropper.min.css">
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


    </div>
    <div class="portal-footer">© 2026 Kindergarten Saadia's Montessori School. All rights reserved.</div>
  </div>
</div>

<!-- Edit Profile Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0, 15, 40, 0.6); z-index:9000; flex-direction:column; align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(4px);">
  <div style="background:white; border-radius:12px; padding:2rem; width:100%; max-width:600px; box-shadow:0 10px 30px rgba(0,0,0,0.3); max-height:90vh; overflow-y:auto;">
    <h2 style="margin-top:0; margin-bottom:1.5rem; font-size:1.5rem; color:var(--primary-deep); font-weight:700;">✏️ Edit Your Details</h2>
    <form id="editForm" onsubmit="saveProfile(event)">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" id="editName" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" id="editEmail" class="form-control" disabled style="opacity:0.6;cursor:not-allowed;">
          <small style="color:var(--text-light);font-size:0.75rem;">Email cannot be changed.</small>
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
      <div class="form-group" style="margin-bottom:1rem;">
        <label class="form-label">Profile Picture</label>
        <div style="display:flex; gap:1rem; align-items:center;">
          <input type="hidden" id="editProfilePic">
          <button type="button" class="btn btn-outline" style="width:100%;" onclick="document.getElementById('profilePicFile').click()">📸 Upload New Picture</button>
          <input type="file" id="profilePicFile" accept="image/*" style="display:none;" onchange="handleFileSelect(event)">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Bio / About</label>
        <textarea id="editBio" class="form-control" rows="3" placeholder="Tell us about yourself..."></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1.5rem;">
        <button type="button" class="btn btn-outline" onclick="toggleEdit()">Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Cropper Modal -->
<div id="cropperModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9999; flex-direction:column; align-items:center; justify-content:center; padding:1rem; backdrop-filter:blur(3px);">
  <div style="background:white; border-radius:12px; padding:1.5rem; width:100%; max-width:580px; text-align:center; box-shadow:0 15px 35px rgba(0,0,0,0.35);">
    <h3 style="margin-top:0; margin-bottom:1rem; font-weight:700; font-size:1.2rem; color:var(--primary-deep);">Crop Profile Picture</h3>
    <div id="cropperWrapper" style="width:100%; height:360px; max-height:55vh; background:#0f172a; margin-bottom:1.25rem; overflow:hidden; border-radius:8px; display:flex; align-items:center; justify-content:center;">
      <img id="cropperImage" style="max-width:100%; max-height:100%; display:block;" alt="To Crop">
    </div>
    <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:0.5rem; align-items:center;">
      <div style="display:flex; gap:0.5rem;">
        <button type="button" class="btn btn-sm btn-outline" onclick="if(cropper) cropper.zoom(0.1)" title="Zoom In">🔍 Zoom In</button>
        <button type="button" class="btn btn-sm btn-outline" onclick="if(cropper) cropper.zoom(-0.1)" title="Zoom Out">🔍 Zoom Out</button>
        <button type="button" class="btn btn-sm btn-outline" onclick="if(cropper) cropper.rotate(90)" title="Rotate">🔄 Rotate</button>
      </div>
      <div style="display:flex; gap:0.5rem;">
        <button type="button" class="btn btn-sm btn-outline" onclick="closeCropperModal()">Cancel</button>
        <button type="button" class="btn btn-sm btn-primary" id="btnCropSave" onclick="saveCroppedImage()">Crop & Save</button>
      </div>
    </div>
  </div>
</div>

<script src="../assets/cropper.min.js"></script>
<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('staff');
  let currentStaff = null;
  let isEditing = false;

  document.addEventListener('DOMContentLoaded', async () => {
    try {
      let rawAuth = sessionStorage.getItem('ksm_staff_auth');
      currentStaff = JSON.parse(rawAuth);
      if (typeof currentStaff !== 'object') currentStaff = { id: currentStaff };
    } catch(e) {
      currentStaff = null;
    }
    
    if (!currentStaff || !currentStaff.id) { window.location.href = 'login.php'; return; }
    
    // Refresh from DB
    const res = await API.getStaffMember(currentStaff.id);
    if (res && res.success && res.data) {
      currentStaff = res.data;
      sessionStorage.setItem('ksm_staff_auth', JSON.stringify(currentStaff));
    }
    
    renderProfile();
  });

  function renderProfile() {
    if (!currentStaff) return;
    const name = currentStaff.name || 'Unknown Staff';
    
    document.getElementById('staffNameTopbar').textContent = name;
    document.getElementById('bigName').textContent = name;

    const avatarHtml = currentStaff.profilePic 
      ? `<img src="${currentStaff.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` 
      : name.charAt(0).toUpperCase();

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
        <p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.2rem;">${escapeHtml(label)}</p>
        <p style="font-weight:600;font-size:0.9rem;">${escapeHtml(val)}</p>
      </div>
    `).join('');

    // Fill edit form
    document.getElementById('editName').value = currentStaff.name || '';
    document.getElementById('editEmail').value = currentStaff.email || '';
    document.getElementById('editPhone').value = currentStaff.phone || '';
    document.getElementById('editSubject').value = currentStaff.subject || '';
    document.getElementById('editBio').value = currentStaff.bio || '';
    document.getElementById('editProfilePic').value = currentStaff.profilePic || '';
  }

  function toggleEdit() {
    isEditing = !isEditing;
    document.getElementById('editModal').style.display = isEditing ? 'flex' : 'none';
  }

  async function saveProfile(e) {
    if (e && e.preventDefault) e.preventDefault();
    const name = document.getElementById('editName').value.trim();
    if (!name) { showToast('Name is required.', 'error'); return; }

    const updates = {
      id: (currentStaff && currentStaff.id) ? currentStaff.id : null,
      name,
      phone: document.getElementById('editPhone').value.trim(),
      subject: document.getElementById('editSubject').value.trim(),
      bio: document.getElementById('editBio').value.trim(),
      profilePic: document.getElementById('editProfilePic').value.trim(),
    };

    const res = await API.updateStaff(updates);
    if (res && res.success && res.data) {
      currentStaff = { ...currentStaff, ...res.data };
      sessionStorage.setItem('ksm_staff_auth', JSON.stringify(currentStaff));
      showToast('Profile updated successfully!', 'success');
    } else {
      showToast((res && res.message) ? res.message : 'Error updating profile.', 'error');
    }
    isEditing = false;
    document.getElementById('editModal').style.display = 'none';
    renderProfile();
  }

  function doLogout() {
    sessionStorage.removeItem('ksm_staff_auth');
    window.location.href = 'login.php';
  }

  // Cropper Logic
  let cropper = null;
  let currentImageType = 'image/jpeg';
  
  function handleFileSelect(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      showToast('Please select a valid image file.', 'error');
      return;
    }
    currentImageType = (file.type === 'image/png') ? 'image/png' : ((file.type === 'image/webp') ? 'image/webp' : 'image/jpeg');

    const reader = new FileReader();
    reader.onload = function(e) {
      if (cropper) {
        try { cropper.destroy(); } catch(err) {}
        cropper = null;
      }

      const modal = document.getElementById('cropperModal');
      modal.style.display = 'flex';

      const wrapper = document.getElementById('cropperWrapper');
      wrapper.innerHTML = '';
      const img = document.createElement('img');
      img.id = 'cropperImage';
      img.style.maxWidth = '100%';
      img.style.maxHeight = '100%';
      img.style.display = 'block';
      wrapper.appendChild(img);

      const initCropper = () => {
        if (typeof Cropper === 'undefined') {
          console.error('Cropper library not loaded');
          showToast('Image cropper failed to load. Please refresh the page.', 'error');
          return;
        }
        if (cropper) {
          try { cropper.destroy(); } catch(err) {}
          cropper = null;
        }
        try {
          cropper = new Cropper(img, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.85,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            background: false,
            responsive: true,
            checkOrientation: false
          });
        } catch (err) {
          console.error('Failed to initialize Cropper:', err);
          showToast('Error initializing image cropper.', 'error');
        }
      };

      img.onload = () => {
        setTimeout(initCropper, 60);
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
    event.target.value = '';
  }

  function closeCropperModal() {
    document.getElementById('cropperModal').style.display = 'none';
    if (cropper) {
      try { cropper.destroy(); } catch(err) {}
      cropper = null;
    }
    const img = document.getElementById('cropperImage');
    if (img) img.src = '';
  }

  async function saveCroppedImage() {
    if (!cropper) {
      showToast('Cropper is not ready yet. Please wait or re-select the image.', 'error');
      return;
    }
    const btn = document.getElementById('btnCropSave');
    const originalText = btn.textContent;
    btn.textContent = 'Saving...';
    btn.disabled = true;
    
    try {
      const canvas = cropper.getCroppedCanvas({
        width: 300,
        height: 300,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high'
      });
      
      if (!canvas) {
        showToast('Error cropping image.', 'error');
        btn.textContent = originalText;
        btn.disabled = false;
        return;
      }

      const base64Image = canvas.toDataURL(currentImageType, 0.9);
      
      const editPicInput = document.getElementById('editProfilePic');
      if (editPicInput) editPicInput.value = base64Image;

      closeCropperModal();
      showToast('Saving profile picture...', 'info');

      const name = document.getElementById('editName').value.trim() || currentStaff.name;
      const updates = {
        id: (currentStaff && currentStaff.id) ? currentStaff.id : null,
        name,
        phone: document.getElementById('editPhone').value.trim(),
        subject: document.getElementById('editSubject').value.trim(),
        bio: document.getElementById('editBio').value.trim(),
        profilePic: base64Image,
      };

      const res = await API.updateStaff(updates);
      if (res && res.success && res.data) {
        currentStaff = { ...currentStaff, ...res.data };
        sessionStorage.setItem('ksm_staff_auth', JSON.stringify(currentStaff));
        renderProfile();
        showToast('Profile picture updated successfully!', 'success');
      } else {
        showToast((res && res.message) ? res.message : 'Error updating profile.', 'error');
      }
    } catch (err) {
      console.error('Error in saveCroppedImage:', err);
      showToast('An unexpected error occurred while saving.', 'error');
    } finally {
      btn.textContent = originalText;
      btn.disabled = false;
    }
  }
</script>
</body>
</html>

