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
  if($method==='GET'){$class=ksm_esc($_GET['class']??'');$where=$class?"WHERE us.class='$class'":'';$r=ksm_db()->query("SELECT a.*,us.name student_name,us.rollNo FROM attendance a JOIN users_students us ON a.student_id=us.id $where ORDER BY a.date DESC,us.name ASC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
  if($method==='POST'){$records=$body['records']??[];if(empty($records))ksm_err('No records.');$ins=0;foreach($records as $rec){$sid=intval($rec['student_id']??0);$date=ksm_esc($rec['date']??date('Y-m-d'));$status=ksm_esc($rec['status']??'Present');$notes=ksm_esc($rec['notes']??'');if(!$sid)continue;ksm_db()->query("INSERT INTO attendance(student_id,date,status,notes)VALUES($sid,'$date','$status','$notes')ON DUPLICATE KEY UPDATE status='$status',notes='$notes'");$ins++;}ksm_json(['inserted'=>$ins],'Attendance saved.');}
  if($method==='GET'&&isset($_GET['students'])){$class=ksm_esc($_GET['class']??'');$r=ksm_db()->query("SELECT id,name,rollNo FROM users_students WHERE class='$class' ORDER BY name ASC");$rows=[];while($row=$r->fetch_assoc())$rows[]=$row;ksm_json($rows);}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Mark Attendance - KSM Teacher Portal">
  <title>Mark Attendance - KSM Teacher Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Mark Attendance</span>
      </div>
      <div class="topbar-right">
        <span id="staffNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <div class="topbar-avatar" style="background:#10B981;color:white;" id="staffAvatar">S</div>
        <button class="btn btn-sm btn-danger" onclick="doLogout()">Logout</button>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">✅ Mark Attendance</h1>
          <p class="page-subtitle">Record daily attendance for your class</p>
        </div>
      </div>

      <!-- Controls -->
      <div class="card mb-3">
        <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
          <div class="form-group" style="margin-bottom:0;flex:1;min-width:180px;">
            <label class="form-label">Class</label>
            <select id="attClass" class="form-control" onchange="loadStudents()">
              <option value="">Select class</option>
              <option>Kindergarten A</option>
              <option>Early Childhood B</option>
              <option>Junior Level</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0;flex:1;min-width:180px;">
            <label class="form-label">Date</label>
            <input type="date" id="attDate" class="form-control" onchange="loadStudents()">
          </div>
          <button class="btn btn-primary" onclick="saveAttendance()" id="saveBtn" disabled>💾 Save Attendance</button>
        </div>
      </div>

      <!-- Existing attendance check -->
      <div id="existingNotice" class="hidden info-banner">
        ⚠️ Attendance already recorded for this date and class. You can update it below.
      </div>

      <!-- Student attendance list -->
      <div class="card" id="attendanceCard">
        <div class="card-header">
          <h2 class="card-title" id="attendanceTitle">Select a class and date to begin</h2>
          <div id="attendanceSummary" style="font-size:0.85rem;color:var(--text-medium);"></div>
        </div>
        <div id="studentsList"></div>
      </div>

      <!-- Past Records -->
      <div class="card mt-3">
        <div class="card-header">
          <h2 class="card-title">📊 Past Attendance Records</h2>
        </div>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Class</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody id="pastRecords"></tbody>
          </table>
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
  let currentStaff = null;
  let attendanceData = {};

  document.addEventListener('DOMContentLoaded', () => {
    currentStaff = Auth.getStaff();
    if (!currentStaff) { window.location.href = 'login.php'; return; }

    document.getElementById('staffNameTopbar').textContent = currentStaff.name;
    document.getElementById('staffAvatar').textContent = currentStaff.name.charAt(0).toUpperCase();

    // Set default values
    document.getElementById('attClass').value = currentStaff.class || '';
    document.getElementById('attDate').value = new Date().toISOString().split('T')[0];

    loadStudents();
    renderPastRecords();
  });

  function loadStudents() {
    const cls = document.getElementById('attClass').value;
    const date = document.getElementById('attDate').value;
    const el = document.getElementById('studentsList');
    const saveBtn = document.getElementById('saveBtn');
    const notice = document.getElementById('existingNotice');

    if (!cls || !date) {
      el.innerHTML = '<div class="empty-state" style="padding:2rem;"><div class="empty-state-icon">📋</div><p>Select a class and date above.</p></div>';
      saveBtn.disabled = true;
      notice.classList.add('hidden');
      return;
    }

    const students = DB.get('students').filter(s => s.class === cls);
    if (students.length === 0) {
      el.innerHTML = '<div class="empty-state" style="padding:2rem;"><div class="empty-state-icon">👤</div><p>No students found in this class.</p></div>';
      saveBtn.disabled = true;
      notice.classList.add('hidden');
      return;
    }

    // Check for existing attendance record
    const allAttendance = DB.get('attendance');
    const existing = allAttendance.find(a => a.date === date && a.class === cls && a.staffId === currentStaff.id);

    attendanceData = {};
    if (existing) {
      notice.classList.remove('hidden');
      existing.records.forEach(r => { attendanceData[r.studentId] = r.status; });
    } else {
      notice.classList.add('hidden');
      students.forEach(s => { attendanceData[s.id] = 'present'; });
    }

    document.getElementById('attendanceTitle').textContent = `${cls} — ${formatDate(date)}`;
    saveBtn.disabled = false;

    renderStudentList(students);
    updateSummary(students);
  }

  function renderStudentList(students) {
    const el = document.getElementById('studentsList');
    el.innerHTML = students.map(s => {
      const status = attendanceData[s.id] || 'present';
      return `
      <div class="attendance-student-row">
        <div class="attendance-student-info">
          <div class="attendance-student-avatar">${s.name.charAt(0)}</div>
          <div>
            <div class="attendance-student-name">${s.name}</div>
            <div class="attendance-student-roll">${s.rollNo}</div>
          </div>
        </div>
        <div class="attendance-toggle">
          <button class="toggle-btn ${status === 'present' ? 'present' : ''}" onclick="setAttendance('${s.id}','present',this)" data-student="${s.id}">✅ Present</button>
          <button class="toggle-btn ${status === 'absent' ? 'absent' : ''}" onclick="setAttendance('${s.id}','absent',this)" data-student="${s.id}">❌ Absent</button>
        </div>
      </div>`;
    }).join('');
  }

  function setAttendance(studentId, status, btn) {
    attendanceData[studentId] = status;

    // Update button styles
    const row = btn.closest('.attendance-student-row');
    const buttons = row.querySelectorAll('.toggle-btn');
    buttons.forEach(b => {
      b.classList.remove('present', 'absent');
    });

    if (status === 'present') {
      buttons[0].classList.add('present');
    } else {
      buttons[1].classList.add('absent');
    }

    const students = DB.get('students').filter(s => s.class === document.getElementById('attClass').value);
    updateSummary(students);
  }

  function updateSummary(students) {
    const present = students.filter(s => attendanceData[s.id] === 'present').length;
    const absent = students.length - present;
    document.getElementById('attendanceSummary').innerHTML = `
      <span style="color:#10B981;font-weight:600;">✅ ${present} Present</span> &nbsp;|&nbsp;
      <span style="color:#EF4444;font-weight:600;">❌ ${absent} Absent</span> &nbsp;|&nbsp;
      <span>Total: ${students.length}</span>
    `;
  }

  function saveAttendance() {
    const cls = document.getElementById('attClass').value;
    const date = document.getElementById('attDate').value;

    if (!cls || !date) {
      showToast('Please select class and date.', 'error');
      return;
    }

    const students = DB.get('students').filter(s => s.class === cls);
    const records = students.map(s => ({
      studentId: s.id,
      studentName: s.name,
      rollNo: s.rollNo,
      status: attendanceData[s.id] || 'present'
    }));

    const present = records.filter(r => r.status === 'present').length;
    const absent = records.filter(r => r.status === 'absent').length;

    const allAttendance = DB.get('attendance');
    const existingIdx = allAttendance.findIndex(a => a.date === date && a.class === cls && a.staffId === currentStaff.id);

    const entry = {
      id: existingIdx >= 0 ? allAttendance[existingIdx].id : Date.now().toString(),
      date, class: cls,
      staffId: currentStaff.id,
      staffName: currentStaff.name,
      records,
      presentCount: present,
      absentCount: absent,
      total: students.length,
      savedAt: new Date().toISOString()
    };

    if (existingIdx >= 0) {
      allAttendance[existingIdx] = entry;
      DB.set('attendance', allAttendance);
      showToast('Attendance updated!', 'success');
    } else {
      DB.push('attendance', entry);
      showToast('Attendance saved successfully!', 'success');
    }

    document.getElementById('existingNotice').classList.remove('hidden');
    renderPastRecords();
  }

  function renderPastRecords() {
    const allAttendance = DB.get('attendance').filter(a => a.staffId === currentStaff.id);
    const tbody = document.getElementById('pastRecords');

    if (allAttendance.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-medium);padding:2rem;">No attendance records yet.</td></tr>';
      return;
    }

    const sorted = [...allAttendance].sort((a, b) => new Date(b.date) - new Date(a.date));
    tbody.innerHTML = sorted.map(a => `
      <tr>
        <td><strong>${formatDate(a.date)}</strong></td>
        <td><span class="badge badge-blue">${a.class}</span></td>
        <td><span style="color:#10B981;font-weight:600;">${a.presentCount}</span></td>
        <td><span style="color:#EF4444;font-weight:600;">${a.absentCount}</span></td>
        <td>${a.total}</td>
      </tr>
    `).join('');
  }

  function doLogout() {
    Auth.logoutStaff();
    window.location.href = 'login.php';
  }
</script>
</body>
</html>


