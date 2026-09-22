<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_admin_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
header('Access-Control-Allow-Origin: *');header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){
  if($method==='GET'){$r=ksm_db()->query("SELECT * FROM news ORDER BY date DESC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
  if($method==='POST'||$method==='PUT'){
    $t=ksm_esc($body['title']??'');$b=ksm_esc($body['body']??'');$d=ksm_esc($body['date']??date('Y-m-d'));$cat=ksm_esc($body['category']??'General');
    if(!$t||!$b)ksm_err('Title and body required.');
    if(strlen($t)>100) ksm_err('Title too long.');
    if(strlen($b)>2000) ksm_err('Body too long.');
    $b = htmlspecialchars($b, ENT_QUOTES, 'UTF-8');
    if($method==='POST'){
      ksm_db()->query("INSERT INTO news(title,body,date,category)VALUES('$t','$b','$d','$cat')");ksm_json(['id'=>ksm_db()->insert_id],'News added.');
    }else{
      $id=intval($body['id']??0);if(!$id)ksm_err('Invalid ID.');
      ksm_db()->query("UPDATE news SET title='$t',body='$b',date='$d',category='$cat' WHERE id=$id");ksm_json(null,'Updated.');
    }
  }
  if($method==='DELETE'){$id=intval($_GET['id']??0);if(!$id)ksm_err('Invalid ID.');ksm_db()->query("DELETE FROM news WHERE id=$id");ksm_json(null,'Deleted.');}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage News — Admin Panel</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Manage News</span>
      </div>
      <div class="topbar-right">
        <button class="btn btn-sm btn-danger" onclick="Auth.logoutAdmin();window.location.href='login.php'">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">📰 Manage News & Announcements</h1>
          <p class="page-subtitle">Create, edit, or delete news posts and announcements</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('newsModal'); clearForm()">+ Add News</button>
      </div>

      <div class="card">
        <div class="table-container">
          <table>
            <thead>
              <tr><th>Title</th><th>Category</th><th>Date</th><th>Preview</th><th>Actions</th></tr>
            </thead>
            <tbody id="newsTbody"></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="portal-footer">© 2026 KSM Admin Panel.</div>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="newsModal">
  <div class="modal" style="max-width:620px;">
    <div class="modal-header">
      <h2 class="modal-title" id="newsModalTitle">Add News</h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <input type="hidden" id="editId">
    <div class="form-group">
      <label class="form-label">Title *</label>
      <input type="text" id="nTitle" class="form-control" placeholder="News headline..." maxlength="100" required>
    </div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Category</label>
        <select id="nCategory" class="form-control">
          <option>General</option>
          <option>Achievement</option>
          <option>Event</option>
          <option>Academic</option>
          <option>Urgent</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Date *</label>
        <input type="date" id="nDate" class="form-control" required>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Body / Content *</label>
      <textarea id="nBody" class="form-control" rows="5" placeholder="Write the full announcement here..." maxlength="2000" required></textarea>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;">
      <button class="btn btn-outline" data-modal-close>Cancel</button>
      <button class="btn btn-primary" onclick="saveNews()">💾 Save Post</button>
    </div>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('admin');

  let allNews = [];

  document.addEventListener('DOMContentLoaded', async () => {
    if (!Auth.isAdminLoggedIn()) { window.location.href = 'login.php'; return; }
    // Set default date to today
    document.getElementById('nDate').value = new Date().toISOString().split('T')[0];
    await fetchAndRender();
  });

  async function fetchAndRender() {
    try {
      const res = await API.getNews();
      allNews = res.data || [];
      renderTable();
    } catch (e) {
      showToast('Error fetching news.', 'error');
    }
  }

  function renderTable() {
    const news = allNews;
    const tbody = document.getElementById('newsTbody');
    if (news.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-medium);padding:2rem;">No news posts yet.</td></tr>';
      return;
    }
    tbody.innerHTML = news.map(n => `
      <tr>
        <td><strong>${n.title}</strong></td>
        <td><span class="badge badge-blue">${n.category || 'General'}</span></td>
        <td>${formatDate(n.date)}</td>
        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:0.85rem;color:var(--text-medium);">${n.body.substring(0,60)}...</td>
        <td>
          <div style="display:flex;gap:0.5rem;">
            <button class="btn btn-sm btn-outline" onclick="editNews('${n.id}')">✏️ Edit</button>
            <button class="btn btn-sm btn-danger" onclick="deleteNews('${n.id}')">🗑️</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function clearForm() {
    document.getElementById('editId').value = '';
    document.getElementById('nTitle').value = '';
    document.getElementById('nCategory').value = 'General';
    document.getElementById('nDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('nBody').value = '';
    document.getElementById('newsModalTitle').textContent = 'Add News';
  }

  function editNews(id) {
    const n = allNews.find(n => String(n.id) === String(id));
    if (!n) return;
    document.getElementById('editId').value = n.id;
    document.getElementById('nTitle').value = n.title;
    document.getElementById('nCategory').value = n.category || 'General';
    document.getElementById('nDate').value = n.date;
    document.getElementById('nBody').value = n.body;
    document.getElementById('newsModalTitle').textContent = 'Edit News';
    openModal('newsModal');
  }

  async function saveNews() {
    const title = document.getElementById('nTitle').value.trim();
    const body = document.getElementById('nBody').value.trim();
    const date = document.getElementById('nDate').value;
    if (!title || !body || !date) { showToast('Please fill in all required fields.', 'error'); return; }
    const data = { title, body, date, category: document.getElementById('nCategory').value };
    const editId = document.getElementById('editId').value;
    
    let res;
    if (editId) { 
      res = await API.updateNews({id: editId, ...data}); 
    } else { 
      res = await API.addNews(data); 
    }
    
    if (res && res.success) {
      showToast(editId ? 'News updated!' : 'News post published!', 'success');
      closeModal('newsModal');
      await fetchAndRender();
    } else {
      showToast('Error saving news.', 'error');
    }
  }

  function deleteNews(id) {
    confirmDelete('Delete this news post? This cannot be undone.', async () => {
      const res = await API.deleteNews(id);
      if (res && res.success) {
        showToast('News post deleted.', 'info');
        await fetchAndRender();
      } else {
        showToast('Error deleting news.', 'error');
      }
    });
  }
</script>
</body>
</html>


