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
  if($method==='GET'){$r=ksm_db()->query("SELECT * FROM gallery ORDER BY id ASC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
  if($method==='POST'){$url=ksm_esc($body['url']??'');$cap=ksm_esc($body['caption']??'');if(!$url)ksm_err('URL required.');ksm_db()->query("INSERT INTO gallery(url,caption)VALUES('$url','$cap')");ksm_json(['id'=>ksm_db()->insert_id],'Image added.');}
  if($method==='PUT'){$id=intval($body['id']??0);$url=ksm_esc($body['url']??'');$cap=ksm_esc($body['caption']??'');if(!$id)ksm_err('Invalid ID.');ksm_db()->query("UPDATE gallery SET url='$url',caption='$cap' WHERE id=$id");ksm_json(null,'Updated.');}
  if($method==='DELETE'){$id=intval($_GET['id']??0);if(!$id)ksm_err('Invalid ID.');ksm_db()->query("DELETE FROM gallery WHERE id=$id");ksm_json(null,'Deleted.');}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Gallery — Admin Panel</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Manage Gallery</span>
      </div>
      <div class="topbar-right">
        <button class="btn btn-sm btn-danger" onclick="Auth.logoutAdmin();window.location.href='login.php'">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">🖼️ Manage Gallery</h1>
          <p class="page-subtitle">Add or remove photos from the school gallery</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('galleryModal'); clearForm()">+ Add Photo</button>
      </div>

      <div id="adminGalleryGrid" class="grid-4"></div>
    </div>
    <div class="portal-footer">© 2026 KSM Admin Panel.</div>
  </div>
</div>

<!-- Add Photo Modal -->
<div class="modal-overlay" id="galleryModal">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="galleryModalTitle">Add Photo</h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <input type="hidden" id="editId">
    <input type="hidden" id="gUrl">
    <div class="form-group">
      <label class="form-label">Upload Image *</label>
      <input type="file" id="gFile" class="form-control" accept="image/*" required>
    </div>
    <div class="form-group">
      <label class="form-label">Caption</label>
      <input type="text" id="gCaption" class="form-control" placeholder="Describe this photo...">
    </div>

    <!-- Preview -->
    <div id="imgPreview" style="display:none;margin-top:1rem;border-radius:var(--radius-sm);overflow:hidden;">
      <img id="previewImg" src="" alt="Preview" style="width:100%;max-height:200px;object-fit:cover;">
    </div>

    <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;">
      <button class="btn btn-outline" data-modal-close>Cancel</button>
      <button class="btn btn-primary" onclick="savePhoto()">💾 Save Photo</button>
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
    renderGrid();

    // Preview image on file change
    document.getElementById('gFile').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
          document.getElementById('gUrl').value = evt.target.result;
          document.getElementById('previewImg').src = evt.target.result;
          document.getElementById('imgPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else {
        document.getElementById('imgPreview').style.display = 'none';
        document.getElementById('gUrl').value = '';
      }
    });
  });

  async function renderGrid() {
    const res = await API.getGallery();
    const gallery = res.data || [];
    const grid = document.getElementById('adminGalleryGrid');
    if (gallery.length === 0) {
      grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1"><div class="empty-state-icon">🖼️</div><div class="empty-state-title">No photos yet</div><p class="empty-state-text">Add photos using the button above.</p></div>`;
      return;
    }
    grid.innerHTML = gallery.map(item => `
      <div class="card" style="padding:0;overflow:hidden;">
        <img src="${item.url}" alt="${item.caption || ''}" style="width:100%;height:140px;object-fit:cover;" onerror="this.src='https://via.placeholder.com/300x200/EFF6FF/1E3A8A?text=Error'">
        <div style="padding:0.75rem;">
          <p style="font-size:0.82rem;color:var(--text-medium);margin-bottom:0.5rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${item.caption || 'No caption'}</p>
          <div style="display:flex;gap:0.5rem;">
            <button class="btn btn-sm btn-outline" style="flex:1;" onclick="editPhoto('${item.id}')">✏️</button>
            <button class="btn btn-sm btn-danger" style="flex:1;" onclick="deletePhoto('${item.id}')">🗑️</button>
          </div>
        </div>
      </div>
    `).join('');
  }

  function clearForm() {
    document.getElementById('editId').value = '';
    document.getElementById('gUrl').value = '';
    document.getElementById('gFile').value = '';
    document.getElementById('gCaption').value = '';
    document.getElementById('imgPreview').style.display = 'none';
    document.getElementById('galleryModalTitle').textContent = 'Add Photo';
  }

  async function editPhoto(id) {
    const res = await API.getGallery();
    const gallery = res.data || [];
    const item = gallery.find(g => g.id == id);
    if (!item) return;
    document.getElementById('editId').value = id;
    document.getElementById('gUrl').value = item.url;
    document.getElementById('gFile').value = '';
    document.getElementById('gCaption').value = item.caption || '';
    document.getElementById('previewImg').src = item.url;
    document.getElementById('imgPreview').style.display = 'block';
    document.getElementById('galleryModalTitle').textContent = 'Edit Photo';
    openModal('galleryModal');
  }

  async function savePhoto() {
    const url = document.getElementById('gUrl').value.trim();
    if (!url) { showToast('Please select an image.', 'error'); return; }
    const data = { url, caption: document.getElementById('gCaption').value.trim() };
    const editId = document.getElementById('editId').value;
    if (editId) {
      await API.updateGalleryImage({ id: editId, ...data });
      showToast('Photo updated!', 'success');
    } else {
      await API.addGalleryImage(data);
      showToast('Photo added to gallery!', 'success');
    }
    closeModal('galleryModal');
    renderGrid();
  }

  function deletePhoto(id) {
    confirmDelete('Delete this photo from the gallery?', async () => {
      await API.deleteGalleryImage(id);
      showToast('Photo deleted.', 'info');
      renderGrid();
    });
  }
</script>
</body>
</html>


