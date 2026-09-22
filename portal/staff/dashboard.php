<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_staff_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
header('Access-Control-Allow-Origin: *');header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){
  $staff_id=intval($_GET['staff_id']??0);
  if(!$staff_id)ksm_err('No staff ID.');
  if($staff_id !== (int)$_SESSION['ksm_staff_auth']) ksm_err('Access denied.', 403);
  $db2=ksm_db();
  $staff=$db2->query("SELECT id, name, subject, class, emoji, profilePic, staffNumber FROM users_staff WHERE id=$staff_id LIMIT 1")->fetch_assoc();
  if(!$staff) ksm_err('Staff member not found or account was removed.', 404);
  $class=ksm_esc($staff['class']??'');
  $hw=[];$r=$db2->query("SELECT * FROM homework WHERE staff_id=$staff_id ORDER BY date_assigned DESC LIMIT 5");while($row=$r->fetch_assoc())$hw[]=$row;
  $att=[];$r2=$db2->query("SELECT a.*,us.name student_name,us.rollNo FROM attendance a JOIN users_students us ON a.student_id=us.id WHERE us.class='$class' ORDER BY a.date DESC LIMIT 20");while($row=$r2->fetch_assoc())$att[]=$row;
  $news=[];$r3=$db2->query("SELECT * FROM news ORDER BY date DESC LIMIT 3");while($row=$r3->fetch_assoc())$news[]=$row;
  
  $hwCount=$db2->query("SELECT COUNT(*) c FROM homework WHERE staff_id=$staff_id")->fetch_assoc()['c'];
  $attCount=$db2->query("SELECT COUNT(*) c FROM attendance a JOIN users_students us ON a.student_id=us.id WHERE us.class='$class'")->fetch_assoc()['c'];
  $newsCount=$db2->query("SELECT COUNT(*) c FROM news")->fetch_assoc()['c'];

  ksm_json(['staff'=>$staff,'homework'=>$hw,'attendance'=>$att,'news'=>$news,'class'=>$class,'stats'=>['hw'=>$hwCount,'att'=>$attCount,'news'=>$newsCount]]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Teacher Dashboard - KSM School Portal">
  <title>Teacher Dashboard - KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <style>
    .quick-action { display:flex;align-items:center;gap:0.75rem;padding:0.9rem 1rem;background:var(--primary-bg);border-radius:var(--radius-sm);text-decoration:none;color:var(--text-dark);transition:var(--transition);font-weight:500;font-size:0.9rem;border:1px solid rgba(59,130,246,0.15); }
    .quick-action:hover { background:var(--primary-deep);color:white;transform:translateX(4px); }
    .quick-action:hover .qa-icon { background:rgba(255,255,255,0.2); }
    .qa-icon { width:36px;height:36px;background:var(--primary-deep);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;transition:var(--transition); }
  </style>
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Teacher Dashboard</span>
      </div>
      <div class="topbar-right">
        <span id="staffNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:#10B981;color:white;" id="staffAvatar">S</div>
        <button class="btn btn-sm btn-danger" onclick="doLogout()">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <!-- Welcome Banner -->
      <div style="background:linear-gradient(135deg,#065F46,#10B981);border-radius:var(--radius-lg);padding:1.75rem 2rem;color:white;margin-bottom:2rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div>
          <h1 style="color:white;font-size:1.5rem;margin-bottom:0.3rem;" id="welcomeMsg">Welcome, Teacher 👋</h1>
          <p style="color:rgba(255,255,255,0.75);font-size:0.9rem;">Manage your classes, homework and attendance from here.</p>
        </div>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid" id="staffStats"></div>

      <!-- Quick Actions + Class Info -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:0.5rem;" class="staff-cols">
        <div class="card">
          <div class="card-header"><h2 class="card-title">⚡ Quick Actions</h2></div>
          <div style="display:flex;flex-direction:column;gap:0.5rem;">
            <a href="homework.php" class="quick-action"><div class="qa-icon">📋</div>Assign Homework</a>
            <a href="attendance.php" class="quick-action"><div class="qa-icon">✅</div>Mark Attendance</a>
            <a href="profile.php" class="quick-action"><div class="qa-icon">👤</div>Edit My Profile</a>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h2 class="card-title">📢 Announcements</h2></div>
          <div id="announcementsList"></div>
        </div>
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

  document.addEventListener('DOMContentLoaded', async () => {
    let staff = Auth.getStaff();
    if (!staff || !staff.id) { window.location.href = 'login.php'; return; }

    // Live verification from database
    let res;
    try {
      res = await API.getStaffDashboard(staff.id);
      if (!res || !res.success || !res.data) {
        Auth.logoutStaff();
        window.location.href = 'login.php';
        return;
      }
      if (res.data.staff) {
        staff = { ...staff, ...res.data.staff };
        sessionStorage.setItem('ksm_staff_auth', JSON.stringify(staff));
      }
    } catch(e) {
      Auth.logoutStaff();
      window.location.href = 'login.php';
      return;
    }

    // Set topbar info
    document.getElementById('staffNameTopbar').textContent = staff.name;
    document.getElementById('staffAvatar').innerHTML = staff.profilePic ? `<img src="${staff.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : staff.name.charAt(0).toUpperCase();
    document.getElementById('welcomeMsg').textContent = `Welcome, ${staff.name} 👋`;

    // Stats
    const stats = res.data.stats || { hw: 0, att: 0, news: 0 };
    const news = res.data.news || [];

    document.getElementById('staffStats').innerHTML = `
      <a href="homework.php" class="stat-card" style="text-decoration:none;color:inherit;"><div class="stat-icon green"><svg viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg></div><div class="stat-info"><div class="stat-number">${stats.hw}</div><div class="stat-label">Homework Assigned</div></div></a>
      <a href="attendance.php" class="stat-card" style="text-decoration:none;color:inherit;"><div class="stat-icon blue"><svg viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div><div class="stat-info"><div class="stat-number">${stats.att}</div><div class="stat-label">Attendance Records</div></div></a>
      <div class="stat-card" style="cursor:default;"><div class="stat-icon gold"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></div><div class="stat-info"><div class="stat-number">${stats.news}</div><div class="stat-label">Announcements</div></div></div>
      <a href="profile.php" class="stat-card" style="text-decoration:none;color:inherit;"><div class="stat-icon purple"><svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div><div class="stat-info" style="min-width:0;"><div style="font-size:1.15rem;font-weight:700;line-height:1.2;color:var(--text-dark);margin-bottom:0.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${staff.subject}">${staff.subject}</div><div class="stat-label">Subject</div></div></a>
    `;

    // Announcements
    const el = document.getElementById('announcementsList');
    if (news.length === 0) {
      el.innerHTML = '<div class="empty-state" style="padding:2rem;"><div class="empty-state-icon">📢</div><p>No announcements available.</p></div>';
    } else {
      el.innerHTML = news.slice(0, 4).map(n => `
        <div style="padding:0.75rem 0;border-bottom:1px solid var(--border-color);">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.25rem;">
            <p style="font-weight:600;font-size:0.9rem;color:var(--text-dark);">${n.title}</p>
            <span class="badge badge-blue" style="font-size:0.7rem;">${n.category}</span>
          </div>
          <p style="font-size:0.8rem;color:var(--text-medium);margin-bottom:0.35rem;">${n.body}</p>
        </div>
      `).join('');
    }
  });

  function doLogout() {
    Auth.logoutStaff();
    window.location.href = 'login.php';
  }
</script>
<style>
  @media(max-width:700px){ .staff-cols{grid-template-columns:1fr!important;} }
</style>
</body>
</html>


