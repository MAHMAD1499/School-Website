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
  if($method==='GET'){$r=ksm_db()->query("SELECT * FROM events ORDER BY date ASC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
  if($method==='POST'){$t=ksm_esc($body['title']??'');$d=ksm_esc($body['date']??'');$ti=ksm_esc($body['time']??'');$loc=ksm_esc($body['location']??'');$desc=ksm_esc($body['description']??'');$cat=ksm_esc($body['category']??'General');if(!$t||!$d)ksm_err('Title and date required.');ksm_db()->query("INSERT INTO events(title,date,time,location,description,category)VALUES('$t','$d','$ti','$loc','$desc','$cat')");ksm_json(['id'=>ksm_db()->insert_id],'Event added.');}
  if($method==='PUT'){$id=intval($body['id']??0);$t=ksm_esc($body['title']??'');$d=ksm_esc($body['date']??'');$ti=ksm_esc($body['time']??'');$loc=ksm_esc($body['location']??'');$desc=ksm_esc($body['description']??'');$cat=ksm_esc($body['category']??'General');if(!$id)ksm_err('Invalid ID.');ksm_db()->query("UPDATE events SET title='$t',date='$d',time='$ti',location='$loc',description='$desc',category='$cat' WHERE id=$id");ksm_json(null,'Updated.');}
  if($method==='DELETE'){$id=intval($_GET['id']??0);if(!$id)ksm_err('Invalid ID.');ksm_db()->query("DELETE FROM events WHERE id=$id");ksm_json(null,'Deleted.');}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Events — Admin Panel</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Manage Events</span>
      </div>
      <div class="topbar-right">
        <button class="btn btn-sm btn-danger" onclick="Auth.logoutAdmin();window.location.href='login.php'">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">📅 Manage Events</h1>
          <p class="page-subtitle">Schedule, update, or remove school events</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('eventModal'); clearForm()">+ Add Event</button>
      </div>

      <div class="card">
        <div class="table-container">
          <table>
            <thead>
              <tr><th>Event</th><th>Date</th><th>Time</th><th>Location</th><th>Category</th><th>Actions</th></tr>
            </thead>
            <tbody id="eventsTbody"></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="portal-footer">© 2026 KSM Admin Panel.</div>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="eventModal">
  <div class="modal" style="max-width:620px;">
    <div class="modal-header">
      <h2 class="modal-title" id="eventModalTitle">Add Event</h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <input type="hidden" id="editId">
    <div class="form-group">
      <label class="form-label">Event Title *</label>
      <input type="text" id="eTitle" class="form-control" placeholder="e.g. Annual Sports Day" required>
    </div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Date *</label>
        <input type="date" id="eDate" class="form-control" required>
      </div>
      <div class="form-group">
        <label class="form-label">Time</label>
        <input type="time" id="eTime" class="form-control" required>
      </div>
    </div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Location</label>
        <input type="text" id="eLocation" class="form-control" placeholder="e.g. School Grounds">
      </div>
      <div class="form-group">
        <label class="form-label">Category</label>
        <select id="eCategory" class="form-control">
          <option>Sports</option>
          <option>Academic</option>
          <option>Meeting</option>
          <option>Cultural</option>
          <option>Other</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea id="eDescription" class="form-control" rows="3" placeholder="Describe the event..."></textarea>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;">
      <button class="btn btn-outline" data-modal-close>Cancel</button>
      <button class="btn btn-primary" onclick="saveEvent()">💾 Save Event</button>
    </div>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('admin');

  const catColors = { Sports:'badge-green', Academic:'badge-blue', Meeting:'badge-purple', Cultural:'badge-gold', Other:'badge-red' };

  document.addEventListener('DOMContentLoaded', () => {
    if (!Auth.isAdminLoggedIn()) { window.location.href = 'login.php'; return; }
    renderTable();
  });

  function renderTable() {
    const events = DB.get('events');
    const tbody = document.getElementById('eventsTbody');
    if (events.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-medium);padding:2rem;">No events yet.</td></tr>';
      return;
    }
    tbody.innerHTML = events.map(e => `
      <tr>
        <td><strong>${e.title}</strong></td>
        <td>${formatDate(e.date)}</td>
        <td>${e.time || '—'}</td>
        <td>${e.location || '—'}</td>
        <td><span class="badge ${catColors[e.category] || 'badge-blue'}">${e.category || 'Event'}</span></td>
        <td>
          <div style="display:flex;gap:0.5rem;">
            <button class="btn btn-sm btn-outline" onclick="editEvent('${e.id}')">✏️</button>
            <button class="btn btn-sm btn-danger" onclick="deleteEvent('${e.id}')">🗑️</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function clearForm() {
    document.getElementById('editId').value = '';
    document.getElementById('eTitle').value = '';
    document.getElementById('eDate').value = '';
    document.getElementById('eTime').value = '';
    document.getElementById('eLocation').value = '';
    document.getElementById('eCategory').value = 'Sports';
    document.getElementById('eDescription').value = '';
    document.getElementById('eventModalTitle').textContent = 'Add Event';
  }

  function editEvent(id) {
    const e = DB.get('events').find(e => e.id === id);
    if (!e) return;
    document.getElementById('editId').value = e.id;
    document.getElementById('eTitle').value = e.title;
    document.getElementById('eDate').value = e.date;
    document.getElementById('eTime').value = e.time || '';
    document.getElementById('eLocation').value = e.location || '';
    document.getElementById('eCategory').value = e.category || 'Sports';
    document.getElementById('eDescription').value = e.description || '';
    document.getElementById('eventModalTitle').textContent = 'Edit Event';
    openModal('eventModal');
  }

  function saveEvent() {
    const title = document.getElementById('eTitle').value.trim();
    const date = document.getElementById('eDate').value;
    const time = document.getElementById('eTime').value;
    if (!title || !date || !time) { showToast('Please fill in Title, Date, and Time.', 'error'); return; }
    const data = {
      title, date,
      time: time.trim(),
      location: document.getElementById('eLocation').value.trim(),
      category: document.getElementById('eCategory').value,
      description: document.getElementById('eDescription').value.trim()
    };
    const editId = document.getElementById('editId').value;
    if (editId) { DB.update('events', editId, data); showToast('Event updated!', 'success'); }
    else { DB.push('events', data); showToast('Event created!', 'success'); }
    closeModal('eventModal');
    renderTable();
  }

  function deleteEvent(id) {
    confirmDelete('Delete this event?', () => {
      DB.delete('events', id);
      showToast('Event deleted.', 'info');
      renderTable();
    });
  }
</script>
</body>
</html>


