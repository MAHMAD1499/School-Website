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
  $email=ksm_esc($body['email']??'');$pass=ksm_db()->real_escape_string(trim($body['password']??''));
  $r=ksm_db()->query("SELECT id,name,email,phone,subject,class,bio,emoji,profilePic FROM users_staff WHERE email='$email' AND password='$pass' LIMIT 1");
  if($r&&$r->num_rows>0) ksm_json($r->fetch_assoc(),'Login successful.');
  else ksm_err('Invalid email or password.',401);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Staff Login — KSM School Portal">
  <title>Staff Login — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="auth-page auth-staff">
  <div class="auth-card">
    <div class="auth-logo">
      <div style="width:60px;height:60px;background:linear-gradient(135deg,#047857,#10B981);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 0.75rem;">👨‍🏫</div>
      <div class="auth-logo-title">KSM Staff Portal</div>
      <div class="auth-logo-sub">Kindergarten Saadia's Montessori School</div>
    </div>

    <h1 class="auth-title">Staff Login</h1>
    <p class="auth-subtitle">Sign in to manage homework, attendance & your profile</p>

    <div class="demo-creds" style="background:#D1FAE5;border-color:rgba(16,185,129,0.3);color:#065F46;">
      <strong>Demo Credentials:</strong><br>
      Email: <strong>ayesha@staff.ksm</strong> &nbsp;|&nbsp; Password: <strong>staff123</strong>
    </div>

    <form id="loginForm" onsubmit="doLogin(event)">
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" class="form-control" placeholder="your@staff.ksm" autocomplete="email" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position:relative;">
          <input type="password" id="password" class="form-control" placeholder="Enter password" autocomplete="current-password" required>
          <button type="button" onclick="togglePwd()" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-medium);font-size:0.85rem;">Show</button>
        </div>
      </div>

      <div id="loginError" class="hidden" style="background:#FEE2E2;color:#991B1B;padding:0.75rem;border-radius:var(--radius-sm);font-size:0.85rem;margin-bottom:1rem;text-align:center;">
        Invalid email or password. Please try again.
      </div>

      <button type="submit" class="btn btn-primary w-full" style="margin-top:0.5rem;background:#047857;">👨‍🏫 Login to Staff Portal</button>
    </form>

    <div style="text-align:center;margin-top:1.5rem;display:flex;flex-direction:column;gap:0.5rem;">
      <a href="../index.php" style="font-size:0.85rem;color:var(--text-medium);">← Back to Portal</a>
      <a href="../student/login.php" style="font-size:0.82rem;color:var(--text-light);">Student Login →</a>
    </div>
  </div>
</div>

<script src="../assets/portal.js?v=2"></script>
<script src="../assets/api.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (sessionStorage.getItem('ksm_staff_auth')) {
      window.location.href = 'dashboard.php';
    }
  });

  async function doLogin(e) {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const pass = document.getElementById('password').value;
    const btn = e.submitter;
    btn.disabled = true;
    btn.textContent = 'Signing in...';
    try {
      const res = await API.staffLogin(email, pass);
      if (res.success) {
        sessionStorage.setItem('ksm_staff_auth', JSON.stringify(res.data));
        showToast('Welcome back, ' + res.data.name + '!', 'success');
        setTimeout(() => window.location.href = 'dashboard.php', 700);
      } else {
        document.getElementById('loginError').classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = '👨‍🏫 Login to Staff Portal';
      }
    } catch {
      document.getElementById('loginError').classList.remove('hidden');
      btn.disabled = false;
      btn.textContent = '👨‍🏫 Login to Staff Portal';
    }
  }

  function togglePwd() {
    const p = document.getElementById('password');
    p.type = p.type === 'password' ? 'text' : 'password';
  }
</script>
</body>
</html>

