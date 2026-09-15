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
  $student_id=intval($_GET['student_id']??0);
  if(!$student_id)ksm_err('No student ID.');
  $hw=[];$r=ksm_db()->query("SELECT h.*,us.name staff_name FROM homework h LEFT JOIN users_staff us ON h.staff_id=us.id WHERE h.class_name=(SELECT class FROM users_students WHERE id=$student_id) ORDER BY h.date_assigned DESC LIMIT 5");
  while($row=$r->fetch_assoc())$hw[]=$row;
  $att=[];$r2=ksm_db()->query("SELECT * FROM attendance WHERE student_id=$student_id ORDER BY date DESC LIMIT 30");
  while($row=$r2->fetch_assoc())$att[]=$row;
  $news=[];$r3=ksm_db()->query("SELECT * FROM news ORDER BY date DESC LIMIT 3");
  while($row=$r3->fetch_assoc())$news[]=$row;
  ksm_json(['homework'=>$hw,'attendance'=>$att,'news'=>$news]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Student Dashboard — KSM School Portal">
  <title>Student Dashboard — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Student Dashboard</span>
      </div>
      <div class="topbar-right">
        <span id="studentNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:var(--accent-warm);color:var(--primary-deep);" id="studentAvatar">S</div>
        <button class="btn btn-sm btn-outline" onclick="doLogout()">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">

      <!-- Student Info Banner -->
      <div style="background:linear-gradient(135deg,var(--accent-warm),var(--accent-hover));border-radius:var(--radius-lg);padding:2rem;margin-bottom:2rem;color:var(--primary-deep);display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
        <div style="width:70px;height:70px;background:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;">🎓</div>
        <div>
          <h1 id="welcomeMsg" style="color:var(--primary-deep);font-size:1.5rem;margin-bottom:0.3rem;">Welcome!</h1>
          <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:0.5rem;">
            <span style="background:rgba(255,255,255,0.6);padding:0.3rem 0.75rem;border-radius:var(--radius-full);font-size:0.82rem;font-weight:600;" id="studentClass"></span>
            <span style="background:rgba(255,255,255,0.6);padding:0.3rem 0.75rem;border-radius:var(--radius-full);font-size:0.82rem;font-weight:600;" id="studentRoll"></span>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <h2 style="font-size:1.15rem;margin-bottom:1rem;">📌 Quick Access</h2>
      <div class="grid-4 mb-3">
        <a href="homework.php" class="card" style="text-decoration:none;text-align:center;">
          <div style="font-size:2rem;margin-bottom:0.5rem;">📋</div>
          <p style="font-size:0.85rem;font-weight:600;">Homework (Diary)</p>
        </a>
        <a href="attendance.php" class="card" style="text-decoration:none;text-align:center;">
          <div style="font-size:2rem;margin-bottom:0.5rem;">✅</div>
          <p style="font-size:0.85rem;font-weight:600;">Attendance</p>
        </a>
        <a href="news.php" class="card" style="text-decoration:none;text-align:center;">
          <div style="font-size:2rem;margin-bottom:0.5rem;">📢</div>
          <p style="font-size:0.85rem;font-weight:600;">Announcements</p>
        </a>
        <a href="events.php" class="card" style="text-decoration:none;text-align:center;">
          <div style="font-size:2rem;margin-bottom:0.5rem;">📅</div>
          <p style="font-size:0.85rem;font-weight:600;">Events</p>
        </a>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;" class="student-cols">
        <!-- Latest News -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">📰 Latest Announcements</h2>
            <a href="news.php" style="font-size:0.82rem;color:var(--primary-light);">View All</a>
          </div>
          <div id="studentNews"></div>
        </div>

        <!-- Upcoming Events -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">📅 Upcoming Events</h2>
            <a href="events.php" style="font-size:0.82rem;color:var(--primary-light);">View All</a>
          </div>
          <div id="studentEvents"></div>
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
  buildSidebar('student');

  document.addEventListener('DOMContentLoaded', () => {
    const student = Auth.getStudent();
    if (!student) { window.location.href = 'login.php'; return; }

    // Set student info in topbar
    document.getElementById('studentNameTopbar').textContent = student.name;
    document.getElementById('studentAvatar').textContent = student.name.charAt(0).toUpperCase();

    // Welcome banner
    document.getElementById('welcomeMsg').textContent = 'Welcome back, ' + student.name + '! 👋';
    document.getElementById('studentClass').textContent = '🏫 ' + student.class;
    document.getElementById('studentRoll').textContent = '🎫 Roll No: ' + student.rollNo;



    // News
    const news = DB.get('news').slice(0, 3);
    const newsEl = document.getElementById('studentNews');
    if (news.length === 0) {
      newsEl.innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-medium);font-size:0.88rem;">No announcements yet.</div>';
    } else {
      newsEl.innerHTML = news.map(n => `
        <div style="padding:0.75rem 0;border-bottom:1px solid var(--border-color);">
          <p style="font-size:0.72rem;color:var(--accent-warm);font-weight:700;margin-bottom:0.2rem;">${formatDate(n.date)}</p>
          <p style="font-weight:600;font-size:0.88rem;">${n.title}</p>
          <p style="font-size:0.8rem;color:var(--text-medium);margin-top:0.15rem;">${n.body.substring(0,80)}...</p>
        </div>
      `).join('');
    }

    // Events
    const events = DB.get('events').slice(0, 3);
    const eventsEl = document.getElementById('studentEvents');
    if (events.length === 0) {
      eventsEl.innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-medium);font-size:0.88rem;">No events yet.</div>';
    } else {
      eventsEl.innerHTML = events.map(e => `
        <div style="display:flex;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid var(--border-color);align-items:center;">
          <div style="min-width:44px;height:50px;background:linear-gradient(135deg,var(--primary-deep),var(--primary-light));border-radius:var(--radius-sm);display:flex;flex-direction:column;align-items:center;justify-content:center;color:white;flex-shrink:0;">
            <span style="font-size:1.1rem;font-weight:700;line-height:1;">${getDay(e.date)}</span>
            <span style="font-size:0.55rem;text-transform:uppercase;letter-spacing:0.5px;">${getMonthName(e.date)}</span>
          </div>
          <div>
            <p style="font-weight:600;font-size:0.88rem;">${e.title}</p>
            <p style="font-size:0.78rem;color:var(--text-medium);">📍 ${e.location || 'TBA'}</p>
          </div>
        </div>
      `).join('');
    }
  });

  function doLogout() {
    Auth.logoutStudent();
    window.location.href = 'login.php';
  }
</script>
<style>
  @media(max-width:700px){ .student-cols{grid-template-columns:1fr!important;} }
</style>
</body>
</html>


