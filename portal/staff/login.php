<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){
  if (!isset($_SESSION['staff_login_attempts'])) $_SESSION['staff_login_attempts'] = 0;
  if (!isset($_SESSION['staff_login_lockout'])) $_SESSION['staff_login_lockout'] = 0;
  if ($_SESSION['staff_login_lockout'] > time()) {
      $wait = ceil(($_SESSION['staff_login_lockout'] - time()) / 60);
      ksm_err("Too many failed attempts. Please try again in {$wait} minute(s).", 429);
  } elseif ($_SESSION['staff_login_lockout'] > 0 && $_SESSION['staff_login_lockout'] <= time()) {
      $_SESSION['staff_login_attempts'] = 0;
      $_SESSION['staff_login_lockout'] = 0;
  }

  $staffNumber = trim($body['staffNumber'] ?? '');
  $pass = trim($body['password'] ?? '');
  
  $db = ksm_db();
  $stmt = $db->prepare("SELECT * FROM users_staff WHERE staffNumber=? LIMIT 1");
  if ($stmt) {
      $stmt->bind_param("s", $staffNumber);
      $stmt->execute();
      $r = $stmt->get_result();
      if ($r && $r->num_rows > 0) {
          $user = $r->fetch_assoc();
          if ($pass === $user['password'] || password_verify($pass, $user['password'])) {
              if ($pass === $user['password']) {
                  $hashed = password_hash($pass, PASSWORD_DEFAULT);
                  $upd_stmt = $db->prepare("UPDATE users_staff SET password=? WHERE id=?");
                  $upd_stmt->bind_param("si", $hashed, $user['id']);
                  $upd_stmt->execute();
              }
              $_SESSION['staff_login_attempts'] = 0;
              $_SESSION['staff_login_lockout'] = 0;
              session_regenerate_id(true);
              $_SESSION['ksm_staff_auth'] = $user['id'];
              unset($user['password']);
              ksm_json($user, 'Login successful.');
          }
      }
  }

  $_SESSION['staff_login_attempts']++;
  if ($_SESSION['staff_login_attempts'] >= 5) {
      $_SESSION['staff_login_lockout'] = time() + 1800;
      ksm_err('Too many failed login attempts. Locked out for 30 minutes.', 429);
  }
  ksm_err('Invalid staff number or password.', 401);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Teacher Login — KSM School Portal">
  <title>Teacher Login — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="auth-page auth-staff">
  <div class="auth-card">
    <div class="auth-logo">
      <div style="width:60px;height:60px;background:linear-gradient(135deg,#047857,#10B981);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 0.75rem;">👨‍🏫</div>
      <div class="auth-logo-title">KSM Teacher Portal</div>
      <div class="auth-logo-sub">Kindergarten Saadia's Montessori School</div>
    </div>

    <h1 class="auth-title">Teacher Login</h1>
    <p class="auth-subtitle">Sign in to manage homework, attendance & your profile</p>



    <form id="loginForm" onsubmit="doLogin(event)">
      <div class="form-group">
        <label class="form-label" for="staffNumber">Staff Number</label>
        <input type="text" id="staffNumber" class="form-control" placeholder="e.g. ST-001" autocomplete="username" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position:relative;">
          <input type="password" id="password" class="form-control" placeholder="Enter password" autocomplete="current-password" required>
          <button type="button" onclick="togglePwd()" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-medium);font-size:0.85rem;">Show</button>
        </div>
      </div>

      <div id="loginError" class="hidden" style="background:#FEE2E2;color:#991B1B;padding:0.75rem;border-radius:var(--radius-sm);font-size:0.85rem;margin-bottom:1rem;text-align:center;">
        Invalid staff number or password. Please try again.
      </div>

      <button type="submit" class="btn btn-primary w-full" style="margin-top:0.5rem;background:#047857;">👨‍🏫 Login to Teacher Portal</button>
    </form>

    <div style="text-align:center;margin-top:1.5rem;display:flex;flex-direction:column;gap:0.5rem;">
      <a href="../index.php" style="font-size:0.85rem;color:var(--text-medium);">← Back to Portal</a>
    </div>
  </div>
</div>

<script src="../assets/portal.js?v=2"></script>
<script src="../assets/api.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', async () => {
    const raw = sessionStorage.getItem('ksm_staff_auth');
    if (raw) {
      try {
        const staff = JSON.parse(raw);
        if (staff && staff.id) {
          const res = await API.getStaffDashboard(staff.id);
          if (res && res.success) {
            window.location.href = 'dashboard.php';
            return;
          }
        }
      } catch(e) {}
      sessionStorage.removeItem('ksm_staff_auth');
    }
  });

  async function doLogin(e) {
    e.preventDefault();
    const staffNumber = document.getElementById('staffNumber').value.trim();
    const pass = document.getElementById('password').value;
    const btn = e.submitter;
    btn.disabled = true;
    btn.textContent = 'Signing in...';
    try {
      const res = await API.staffLogin(staffNumber, pass);
      if (res.success) {
        sessionStorage.setItem('ksm_staff_auth', JSON.stringify(res.data));
        showToast('Welcome back, ' + res.data.name + '!', 'success');
        setTimeout(() => window.location.href = 'dashboard.php', 700);
      } else {
        const errEl = document.getElementById('loginError');
        errEl.textContent = res.message || 'Invalid staff number or password. Please try again.';
        errEl.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = '👨‍🏫 Login to Teacher Portal';
      }
    } catch(e) {
      const errEl = document.getElementById('loginError');
      errEl.textContent = 'An error occurred. Please try again.';
      errEl.classList.remove('hidden');
      btn.disabled = false;
      btn.textContent = '👨‍🏫 Login to Teacher Portal';
    }
  }

  function togglePwd() {
    const p = document.getElementById('password');
    p.type = p.type === 'password' ? 'text' : 'password';
  }
</script>
</body>
</html>

