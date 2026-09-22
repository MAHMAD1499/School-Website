<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';

function ksm_db() { global $ksm_db_config; static $c=null; if($c)return $c; $c=new mysqli($ksm_db_config['host'],$ksm_db_config['user'],$ksm_db_config['pass'],$ksm_db_config['name']); if($c->connect_error){http_response_code(500);echo json_encode(['error'=>$c->connect_error]);exit;} $c->set_charset('utf8mb4'); return $c; }
function ksm_json($data,$msg='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$msg,'data'=>$data]);exit;}
function ksm_escape($v){return ksm_db()->real_escape_string(trim($v??''));}
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if(isset($_SERVER['REQUEST_METHOD'])&&$_SERVER['REQUEST_METHOD']==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||(isset($_SERVER['CONTENT_TYPE'])&&strpos($_SERVER['CONTENT_TYPE'],'application/json')!==false)||isset($_GET['_api']);

if($isAjax){
    $method=$_SERVER['REQUEST_METHOD'];
    $body=json_decode(file_get_contents('php://input'),true)??[];
    $action=$body['action']??$_GET['action']??'';
    if($action==='admin_login'){
        $user=trim($body['username']??''); $pass=trim($body['password']??'');
        if($user==='admin'&&$pass==='admin123') {
            $_SESSION['ksm_admin_auth'] = true;
            ksm_json(['role'=>'admin'],'Login successful.');
        } else ksm_json(null,'Invalid credentials.',401);
    }
    ksm_json(null,'Unknown action.',400);
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Admin Login — KSM School Portal">
  <title>Admin Login — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="auth-logo-img" style="width:60px;height:60px;background:linear-gradient(135deg,var(--primary-deep),var(--primary-light));border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 0.75rem;">🔐</div>
      <div class="auth-logo-title">KSM Admin Panel</div>
      <div class="auth-logo-sub">Kindergarten Saadia's Montessori School</div>
    </div>

    <h1 class="auth-title">Administrator Login</h1>
    <p class="auth-subtitle">Sign in to manage school portal content</p>



    <form id="loginForm" onsubmit="doLogin(event)">
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input type="text" id="username" class="form-control" placeholder="Enter username" autocomplete="username" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position:relative;">
          <input type="password" id="password" class="form-control" placeholder="Enter password" autocomplete="current-password" required>
          <button type="button" onclick="togglePwd()" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-medium);font-size:0.85rem;">Show</button>
        </div>
      </div>

      <div id="loginError" class="hidden" style="background:#FEE2E2;color:#991B1B;padding:0.75rem;border-radius:var(--radius-sm);font-size:0.85rem;margin-bottom:1rem;text-align:center;">
        Invalid username or password. Please try again.
      </div>

      <button type="submit" class="btn btn-primary w-full" style="margin-top:0.5rem;">🔓 Login to Admin Panel</button>
    </form>

    <div style="text-align:center;margin-top:1.5rem;">
      <a href="../../index.php" style="font-size:0.85rem;color:var(--text-medium);">← Back to KSM website</a>
    </div>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (sessionStorage.getItem('ksm_admin_auth')) {
      window.location.href = 'dashboard.php';
    }
  });

  async function doLogin(e) {
    e.preventDefault();
    const user = document.getElementById('username').value.trim();
    const pass = document.getElementById('password').value;
    const btn = e.submitter;
    btn.disabled = true;
    btn.textContent = 'Signing in...';
    try {
      const res = await API.adminLogin(user, pass);
      if (res.success) {
        sessionStorage.setItem('ksm_admin_auth', '1');
        showToast('Login successful! Redirecting...', 'success');
        setTimeout(() => window.location.href = 'dashboard.php', 800);
      } else {
        document.getElementById('loginError').classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = '🔓 Login to Admin Panel';
      }
    } catch {
      document.getElementById('loginError').classList.remove('hidden');
      btn.disabled = false;
      btn.textContent = '🔓 Login to Admin Panel';
    }
  }

  function togglePwd() {
    const p = document.getElementById('password');
    p.type = p.type === 'password' ? 'text' : 'password';
  }
</script>
</body>
</html>

