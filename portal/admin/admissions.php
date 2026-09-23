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
  if($method==='GET'){$r=ksm_db()->query("SELECT * FROM admissions ORDER BY id DESC");$rows=[];if($r)while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
  if($method==='PUT'){$id=intval($body['id']??0);$status=ksm_esc($body['status']??'Pending');if(!$id)ksm_err('Invalid ID.');ksm_db()->query("UPDATE admissions SET status='$status' WHERE id=$id");ksm_json(null,'Status updated.');}
  if($method==='DELETE'){$id=intval($_GET['id']??0);if(!$id)ksm_err('Invalid ID.');ksm_db()->query("DELETE FROM admissions WHERE id=$id");ksm_json(null,'Deleted.');}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admissions — Admin Panel</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Admissions Management</span>
      </div>
      <div class="topbar-right">
        <button class="btn btn-sm btn-danger" onclick="Auth.logoutAdmin();window.location.href='login.php'">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">📝 Admissions Applications</h1>
          <p class="page-subtitle">Review and manage all submitted admission applications</p>
        </div>
        <div style="display:flex;gap:0.75rem;">
          <select id="filterStatus" class="form-control" style="width:auto;border-radius:var(--radius-full);">
            <option value="">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
            <option value="Under Review">Under Review</option>
          </select>
        </div>
      </div>

      <div class="card">
        <div class="table-container">
          <table>
            <thead>
              <tr><th>Student Name</th><th>Program</th><th>Parent</th><th>Phone</th><th>Submitted</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody id="admissionsTbody"></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="portal-footer">© 2026 KSM Admin Panel.</div>
  </div>
</div>

<!-- View Details Modal -->
<div class="modal-overlay" id="viewModal">
  <div class="modal" style="max-width:620px;">
    <div class="modal-header">
      <h2 class="modal-title">Application Details</h2>
      <button class="modal-close" data-modal-close><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div id="viewContent" style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;"></div>
    <hr class="divider">
    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
      <button class="btn btn-accent" onclick="updateStatus('Approved')">✅ Approve</button>
      <button class="btn btn-outline" onclick="updateStatus('Under Review')">🔍 Under Review</button>
      <button class="btn btn-danger" onclick="updateStatus('Rejected')">❌ Reject</button>
      <button class="btn btn-primary" onclick="printApplication()" style="margin-left:auto;">🖨️ Download / Print</button>
      <button class="btn btn-outline" data-modal-close>Close</button>
    </div>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('admin');
  let currentViewId = null;

  const statusColors = { Pending:'badge-gold', Approved:'badge-green', Rejected:'badge-red', 'Under Review':'badge-purple' };

  let allAdmissions = [];

  document.addEventListener('DOMContentLoaded', async () => {
    if (!Auth.isAdminLoggedIn()) { window.location.href = 'login.php'; return; }
    await fetchAndRender();
    document.getElementById('filterStatus').addEventListener('change', renderTable);
  });

  async function fetchAndRender() {
    try {
      const res = await API.getAdmissions();
      allAdmissions = res.data || [];
      renderTable();
    } catch (e) {
      showToast('Error fetching admissions.', 'error');
    }
  }

  function renderTable() {
    const statusFilter = document.getElementById('filterStatus').value;
    let apps = allAdmissions;
    if (statusFilter) apps = apps.filter(a => a.status === statusFilter);

    const tbody = document.getElementById('admissionsTbody');
    if (apps.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-medium);padding:2rem;">No applications found.</td></tr>';
      return;
    }
    tbody.innerHTML = apps.map(a => `
      <tr>
        <td><strong>${a.child_name || a.studentName}</strong> ${a.id_card_url || a.birth_cert_url || a.photos_url || a.passport_photo_url ? '📎' : ''}</td>
        <td>${a.program}</td>
        <td>${a.parent_name || a.parentName}</td>
        <td>${a.phone}</td>
        <td style="font-size:0.82rem;color:var(--text-medium);">${formatDate(a.submitted_at || a.submittedAt)}</td>
        <td><span class="badge ${statusColors[a.status] || 'badge-blue'}">${a.status}</span></td>
        <td>
          <button class="btn btn-sm btn-outline" onclick="viewApplication('${a.id}')">👁️ View</button>
        </td>
      </tr>
    `).join('');
  }

  function viewApplication(id) {
    const a = allAdmissions.find(a => String(a.id) === String(id));
    if (!a) return;
    currentViewId = id;

    const fields = [
      ['Student Name', a.child_name || a.studentName], ['Date of Birth', a.dob],
      ['Blood Group', a.blood_group || '—'], ['Medical History', a.message || a.medical_history || a.medical || 'None'],
      ['Parent Name', a.parent_name || a.parentName], ['Occupation', a.address || a.occupation || '—'],
      ['Phone', a.phone], ['Email', a.email],
      ['Program', a.class_applied || a.program], ['Signature', a.prior_school || a.digital_signature || '—'],
      ['Status', a.status],
    ];

    let html = fields.map(([label, val]) => `
      <div style="background:var(--primary-bg);padding:0.75rem;border-radius:var(--radius-sm);">
        <p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.2rem;text-transform:uppercase;letter-spacing:0.5px;">${label}</p>
        <p style="font-weight:600;font-size:0.9rem;">${val || '—'}</p>
      </div>
    `).join('');

    // Documents Section
    let docsHtml = '';
    if(a.passport_photo_url) docsHtml += `<a href="../../${a.passport_photo_url}" target="_blank" download class="btn btn-sm btn-outline">Passport Photo</a> `;
    if(a.id_card_url) docsHtml += `<a href="../../${a.id_card_url}" target="_blank" download class="btn btn-sm btn-outline">ID Card</a> `;
    if(a.birth_cert_url) docsHtml += `<a href="../../${a.birth_cert_url}" target="_blank" download class="btn btn-sm btn-outline">Birth Cert</a> `;
    if(a.photos_url) docsHtml += `<a href="../../${a.photos_url}" target="_blank" download class="btn btn-sm btn-outline">Photos</a> `;
    
    if(docsHtml !== '') {
        html += `<div style="grid-column:1/-1;background:var(--accent-light);padding:0.75rem;border-radius:var(--radius-sm);"><p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.4rem;">Uploaded Documents</p><div>${docsHtml}</div></div>`;
    }

    if (a.message || a.additional_info) {
        html += `<div style="grid-column:1/-1;background:var(--accent-light);padding:0.75rem;border-radius:var(--radius-sm);"><p style="font-size:0.72rem;color:var(--text-medium);margin-bottom:0.2rem;">Additional Notes</p><p style="font-size:0.88rem;">${a.message || a.additional_info}</p></div>`;
    }
    
    document.getElementById('viewContent').innerHTML = html;

    openModal('viewModal');
  }

  async function updateStatus(newStatus) {
    if (!currentViewId) return;
    const res = await API.updateAdmissionStatus(currentViewId, newStatus);
    if(res && res.success){
      showToast(`Application marked as ${newStatus}.`, 'success');
      closeModal('viewModal');
      await fetchAndRender();
    } else {
      showToast('Error updating status.', 'error');
    }
  }

  function printApplication() {
    if (!currentViewId) return;
    window.open('print_admission.php?id=' + currentViewId, '_blank');
  }
</script>
</body>
</html>


