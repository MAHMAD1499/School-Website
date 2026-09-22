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
  if($method==='GET'){$r=ksm_db()->query("SELECT * FROM contacts ORDER BY date DESC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
  if($method==='PUT'){$id=intval($body['id']??0);$status=ksm_esc($body['status']??'Read');if(!$id)ksm_err('Invalid ID.');ksm_db()->query("UPDATE contacts SET status='$status' WHERE id=$id");ksm_json(null,'Status updated.');}
  if($method==='DELETE'){$id=intval($_GET['id']??0);if(!$id)ksm_err('Invalid ID.');ksm_db()->query("DELETE FROM contacts WHERE id=$id");ksm_json(null,'Deleted.');}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Messages — Admin Panel</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Contact Messages</span>
      </div>
      <div class="topbar-right">
        <button class="btn btn-sm btn-danger" onclick="Auth.logoutAdmin();window.location.href='login.php'">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">✉️ Contact Messages</h1>
          <p class="page-subtitle">View and manage messages sent via the contact form</p>
        </div>
      </div>

      <div class="card">
        <div class="table-container">
          <table>
            <thead>
              <tr><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody id="contactsTbody"></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="portal-footer">© 2026 KSM Admin Panel.</div>
  </div>
</div>

<!-- View Message Modal -->
<div class="modal-overlay" id="msgModal">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title">Message Details</h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div id="msgContent"></div>
    <div style="display:flex;gap:0.75rem;margin-top:1.25rem;justify-content:flex-end;">
      <button class="btn btn-danger btn-sm" onclick="deleteMsg()">🗑️ Delete</button>
      <button class="btn btn-outline btn-sm" data-modal-close>Close</button>
    </div>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('admin');
  let allContacts = [];

  document.addEventListener('DOMContentLoaded', async () => {
    if (!Auth.isAdminLoggedIn()) { window.location.href = 'login.php'; return; }
    await fetchAndRender();
  });

  async function fetchAndRender() {
    try {
      const res = await API.getContacts();
      allContacts = res.data || [];
      renderTable();
    } catch (e) {
      showToast('Error fetching contacts.', 'error');
    }
  }

  function renderTable() {
    const contacts = allContacts;
    const tbody = document.getElementById('contactsTbody');
    if (contacts.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-medium);padding:2rem;">No messages yet.</td></tr>';
      return;
    }
    tbody.innerHTML = contacts.map(c => `
      <tr>
        <td><strong>${c.name}</strong></td>
        <td><a href="mailto:${c.email}" style="color:var(--primary-light);">${c.email}</a></td>
        <td>${c.subject}</td>
        <td><span class="badge ${c.status === 'Read' ? 'badge-green' : 'badge-gold'}">${c.status || 'Unread'}</span></td>
        <td style="font-size:0.82rem;color:var(--text-medium);">${formatDate(c.date || c.sentAt)}</td>
        <td>
          <div style="display:flex;gap:0.5rem;">
            <button class="btn btn-sm btn-outline" onclick="viewMsg('${c.id}')">👁️ View</button>
            <button class="btn btn-sm btn-danger" onclick="deleteMsgById('${c.id}')">🗑️</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  async function viewMsg(id) {
    const c = allContacts.find(c => String(c.id) === String(id));
    if (!c) return;
    currentMsgId = id;

    // Mark as read
    if (c.status !== 'Read') {
      await API.updateContactStatus(id, 'Read');
      await fetchAndRender();
    }

    document.getElementById('msgContent').innerHTML = `
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1rem;">
        <div style="background:var(--primary-bg);padding:0.75rem;border-radius:var(--radius-sm);"><p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.2rem;">From</p><p style="font-weight:600;">${c.name}</p></div>
        <div style="background:var(--primary-bg);padding:0.75rem;border-radius:var(--radius-sm);"><p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.2rem;">Email</p><p style="font-weight:600;">${c.email}</p></div>
        <div style="background:var(--primary-bg);padding:0.75rem;border-radius:var(--radius-sm);"><p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.2rem;">Phone</p><p style="font-weight:600;">${c.phone || '—'}</p></div>
        <div style="background:var(--primary-bg);padding:0.75rem;border-radius:var(--radius-sm);"><p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.2rem;">Subject</p><p style="font-weight:600;">${c.subject}</p></div>
      </div>
      <div style="background:var(--bg-main);border:1px solid var(--border-color);padding:1rem;border-radius:var(--radius-sm);">
        <p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.5rem;">Message</p>
        <p style="font-size:0.92rem;line-height:1.7;color:var(--text-dark);">${c.message}</p>
      </div>
      <p style="font-size:0.78rem;color:var(--text-light);margin-top:0.75rem;">Received: ${formatDate(c.date || c.sentAt)}</p>
    `;
    openModal('msgModal');
  }

  async function deleteMsg() {
    if (!currentMsgId) return;
    await API.deleteContact(currentMsgId);
    closeModal('msgModal');
    showToast('Message deleted.', 'info');
    await fetchAndRender();
  }

  function deleteMsgById(id) {
    confirmDelete('Delete this message?', async () => {
      await API.deleteContact(id);
      showToast('Message deleted.', 'info');
      await fetchAndRender();
    });
  }
</script>
</body>
</html>


