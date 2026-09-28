<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_student_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error)die('DB Error');$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}

$session_id = (int)($_GET['id'] ?? 0);
if(!$session_id) { header("Location: cbt_dashboard.php"); exit; }

$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
if($isAjax){
  $method=$_SERVER['REQUEST_METHOD']??'GET';
  $student_id = (int)$_SESSION['ksm_student_auth'];
  $db = ksm_db();

  // Validate session
  $session = $db->query("SELECT * FROM cbt_sessions WHERE id=$session_id AND status='published'")->fetch_assoc();
  if(!$session) ksm_json(null, "Exam not available", 404);

  // Validate student belongs to exam class
  $stu = $db->query("SELECT class FROM users_students WHERE id=$student_id LIMIT 1")->fetch_assoc();
  $student_class = trim($stu['class'] ?? '');
  if (!empty($session['class_id']) && $session['class_id'] !== 'All' && strcasecmp($session['class_id'], $student_class) !== 0) {
      ksm_json(null, "Access denied: This exam is designated for " . htmlspecialchars($session['class_id']) . " students.", 403);
  }

  if($method==='GET'){
    $questions = [];
    $res = $db->query("SELECT id, question_text, options, marks FROM cbt_questions WHERE session_id=$session_id ORDER BY id ASC");
    while($r = $res->fetch_assoc()) $questions[] = $r;
    ksm_json(['session'=>$session, 'questions'=>$questions]);
  }
  
  if($method==='POST'){
    $body = json_decode(file_get_contents('php://input'), true);
    $answers = $body['answers'] ?? [];
    
    // Check if already submitted
    $chk = $db->query("SELECT id FROM cbt_submissions WHERE session_id=$session_id AND student_id=$student_id")->fetch_assoc();
    if($chk) ksm_json(null, "You have already submitted this exam.", 400);

    // Create submission
    $db->query("INSERT INTO cbt_submissions (session_id, student_id, started_at, completed_at, status) VALUES ($session_id, $student_id, NOW(), NOW(), 'submitted')");
    $sub_id = $db->insert_id;

    // Fetch valid question IDs for this session
    $valid_q_res = $db->query("SELECT id FROM cbt_questions WHERE session_id=$session_id");
    $valid_q_ids = [];
    while($vq = $valid_q_res->fetch_assoc()) $valid_q_ids[(int)$vq['id']] = true;

    // Save responses
    foreach($answers as $q_id => $ans) {
      $q_id = (int)$q_id;
      if (!isset($valid_q_ids[$q_id])) continue;
      $ans = ksm_esc(substr(strtoupper(trim($ans)), 0, 50));
      $db->query("INSERT INTO cbt_responses (submission_id, question_id, student_answer) VALUES ($sub_id, $q_id, '$ans')");
    }
    
    ksm_json(null, "Exam submitted successfully");
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Take Exam — KSM Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <style>
    .question-card { background: white; padding: 1.5rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); margin-bottom: 1rem; }
    .question-text { font-weight: 600; font-size: 1.1rem; margin-bottom: 1rem; }
    .option-label { display: block; margin-bottom: 0.5rem; cursor: pointer; }
    .option-label input { margin-right: 0.5rem; }
  </style>
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <span class="topbar-title">Take Exam</span>
      </div>
      <div class="topbar-right">
        <a href="cbt_dashboard.php" class="btn btn-sm btn-outline">Cancel</a>
      </div>
    </div>

    <div class="portal-content fade-up" style="max-width: 800px; margin: 0 auto;">
      <h1 class="page-title" id="examTitle">Loading Exam...</h1>
      <p id="examInfo" style="color:var(--text-medium); margin-bottom:2rem;"></p>

      <form id="examForm" onsubmit="submitExam(event)">
        <div id="questionsContainer">
           <div style="text-align:center;padding:2rem;">Loading questions...</div>
        </div>
        <div style="text-align:right; margin-top: 1rem; display:none;" id="submitBtnContainer">
          <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size:1.1rem;">Submit Exam</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="../assets/api.js"></script>
<script>
  const sessionId = <?php echo $session_id; ?>;
  document.addEventListener('DOMContentLoaded', () => {
    loadExam();
  });

  async function loadExam() {
    const res = await selfApi('GET');
    if(res.success) {
      document.getElementById('examTitle').textContent = res.data.session.title;
      document.getElementById('examInfo').textContent = `Duration: ${res.data.session.duration_minutes} min | Total Marks: ${res.data.session.total_marks}`;
      
      const qc = document.getElementById('questionsContainer');
      if(res.data.questions.length === 0) {
        qc.innerHTML = '<div class="question-card">No questions found for this exam.</div>';
        return;
      }
      
      qc.innerHTML = res.data.questions.map((q, idx) => {
        let optsHTML = '';
        try {
          const opts = JSON.parse(q.options);
          for(const [k, v] of Object.entries(opts)) {
            if(v) optsHTML += `<label class="option-label"><input type="radio" name="q_${q.id}" value="${k}" required> ${k}: ${v}</label>`;
          }
        }catch(e){}
        return `
          <div class="question-card">
            <div class="question-text">${idx+1}. ${q.question_text} <span style="font-size:0.8rem; color:var(--text-medium); float:right;">(${q.marks} marks)</span></div>
            ${optsHTML}
          </div>
        `;
      }).join('');
      
      document.getElementById('submitBtnContainer').style.display = 'block';
    } else {
      document.getElementById('examTitle').textContent = 'Error loading exam';
      document.getElementById('questionsContainer').innerHTML = `<div class="question-card">${res.message}</div>`;
    }
  }

  async function submitExam(e) {
    e.preventDefault();
    if(!confirm("Are you sure you want to submit your answers? You cannot change them later.")) return;
    
    const fd = new FormData(e.target);
    const answers = {};
    for(const [k, v] of fd.entries()) {
      if(k.startsWith('q_')) {
        answers[k.replace('q_', '')] = v;
      }
    }
    
    const res = await selfApi('POST', {answers});
    if(res.success) {
      alert("Exam submitted successfully!");
      window.location.href = 'cbt_dashboard.php';
    } else {
      alert(res.message);
    }
  }
</script>
</body>
</html>
