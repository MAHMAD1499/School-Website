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

    // Create submission with temporary status
    $db->query("INSERT INTO cbt_submissions (session_id, student_id, started_at, completed_at, status, score) VALUES ($session_id, $student_id, NOW(), NOW(), 'submitted', 0)");
    $sub_id = $db->insert_id;

    // Fetch valid question IDs for this session
    $valid_q_res = $db->query("SELECT id, correct_answer, marks FROM cbt_questions WHERE session_id=$session_id");
    $valid_q = [];
    while($vq = $valid_q_res->fetch_assoc()) $valid_q[(int)$vq['id']] = $vq;

    $total_score = 0;
    // Save responses
    foreach($answers as $q_id => $ans) {
      $q_id = (int)$q_id;
      if (!isset($valid_q[$q_id])) continue;
      
      $ans = ksm_esc(substr(strtoupper(trim($ans)), 0, 50));
      $is_correct = ($ans === strtoupper(trim($valid_q[$q_id]['correct_answer'] ?? '')));
      
      $marks_awarded = 0;
      if($is_correct) {
          $marks_awarded = (float)$valid_q[$q_id]['marks'];
          $total_score += $marks_awarded;
      }
      $is_correct_bit = $is_correct ? 1 : 0;
      
      $db->query("INSERT INTO cbt_responses (submission_id, question_id, student_answer, marks_awarded, is_correct) VALUES ($sub_id, $q_id, '$ans', $marks_awarded, $is_correct_bit)");
    }
    
    // Update submission score and status to graded
    $db->query("UPDATE cbt_submissions SET score=$total_score, status='graded' WHERE id=$sub_id");
    
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
    body { background-color: #f0f4f8; font-family: 'Inter', sans-serif; }
    .portal-main { margin-left: 0 !important; width: 100% !important; border-radius:0 !important; }
    .exam-header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 2rem; border-radius: var(--radius-md); margin-bottom: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display:flex; justify-content:space-between; align-items:center; }
    .exam-title-box h1 { margin: 0 0 0.5rem 0; font-size: 1.8rem; }
    .exam-title-box p { margin: 0; opacity: 0.9; font-size: 0.95rem; }
    .exam-layout { display: grid; grid-template-columns: 1fr 320px; gap: 2rem; align-items: start; }
    .exam-sidebar { position: sticky; top: 2rem; display: flex; flex-direction: column; gap: 1.5rem; }
    .timer-card { background: white; border-radius: var(--radius-md); box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 1.5rem; text-align: center; border-top: 4px solid #1e3c72; }
    .timer-text { font-size: 2.5rem; font-weight: 700; color: #1e3c72; font-variant-numeric: tabular-nums; display:block; margin-top: 0.5rem; }
    .timer-label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-medium); font-weight: 600; }
    
    .nav-card { background: white; border-radius: var(--radius-md); box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 1.5rem; }
    .nav-title { font-weight: 600; font-size: 1.1rem; margin-bottom: 1rem; color: #1e3c72; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
    .navigator-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem; }
    .nav-box { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border-radius: 6px; font-weight: 600; font-size: 0.95rem; color: #475569; transition: all 0.2s ease; cursor: pointer; border: 1px solid #cbd5e1; text-decoration: none; }
    .nav-box.answered { background: #10B981; color: white; border-color: #059669; }
    .nav-box:hover { transform: translateY(-2px); box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

    .question-card { background: white; padding: 2.5rem; border-radius: var(--radius-md); box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 1.5rem; border-left: 5px solid var(--primary); }
    .question-text { font-weight: 600; font-size: 1.15rem; margin-bottom: 1.5rem; color: #2c3e50; line-height: 1.5; }
    .options-grid { display: grid; gap: 0.75rem; }
    .option-label { display: flex; align-items: center; padding: 1rem 1.25rem; border: 1px solid #e2e8f0; border-radius: var(--radius-sm); cursor: pointer; transition: all 0.2s ease; background: #f8fafc; font-size: 1.05rem; color: #334155; }
    .option-label:hover { border-color: var(--primary); background: #f0f7ff; }
    .option-label input { margin-right: 1rem; transform: scale(1.2); }
    .option-label:has(input:checked) { border-color: var(--primary); background: #eff6ff; box-shadow: 0 0 0 1px var(--primary); font-weight: 500; color: #0f172a; }
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

    <div class="portal-content fade-up" style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
      
      <div id="loadingState" style="text-align:center;padding:5rem;font-size:1.5rem;color:var(--text-medium);">
        Loading Exam...
      </div>

      <div class="exam-layout" id="examLayout" style="display:none;">
        <div class="exam-main">
          <div class="exam-header">
            <div class="exam-title-box">
              <h1 id="examTitle">Loading Exam...</h1>
              <p id="examInfo"></p>
            </div>
          </div>

          <form id="examForm" onsubmit="submitExam(event)">
            <div id="questionsContainer"></div>
            <div style="text-align:right; margin-top: 2rem; display:none;" id="submitBtnContainer">
              <button type="submit" class="btn btn-primary" id="finalSubmitBtn" style="padding: 1rem 3rem; font-size:1.1rem; border-radius:30px; box-shadow:0 4px 15px rgba(59, 130, 246, 0.4);">Submit Exam</button>
            </div>
          </form>
        </div>

        <div class="exam-sidebar">
          <div class="timer-card">
            <span class="timer-label">Time Remaining</span>
            <span class="timer-text" id="countdownTimer">--:--</span>
          </div>
          <div class="nav-card">
            <div class="nav-title">Question Navigator</div>
            <div class="navigator-grid" id="navigatorGrid"></div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="../assets/api.js"></script>
<script>
  const sessionId = <?php echo $session_id; ?>;
  let timerInterval = null;
  let remainingSeconds = 0;

  document.addEventListener('DOMContentLoaded', () => {
    loadExam();
  });

  async function loadExam() {
    const res = await selfApi('GET', null, `id=${sessionId}`);
    document.getElementById('loadingState').style.display = 'none';
    
    if(res.success) {
      document.getElementById('examLayout').style.display = 'grid';
      document.getElementById('examTitle').textContent = res.data.session.title;
      document.getElementById('examInfo').textContent = `Total Marks: ${res.data.session.total_marks} | ${res.data.questions.length} Questions`;
      
      remainingSeconds = parseInt(res.data.session.duration_minutes) * 60;
      startTimer();
      
      const qc = document.getElementById('questionsContainer');
      if(res.data.questions.length === 0) {
        qc.innerHTML = '<div class="question-card" style="text-align:center;padding:3rem;">No questions found for this exam.</div>';
        return;
      }
      
      qc.innerHTML = res.data.questions.map((q, idx) => {
        let optsHTML = '';
        try {
          const opts = JSON.parse(q.options);
          for(const [k, v] of Object.entries(opts)) {
            if(v) optsHTML += `<label class="option-label"><input type="radio" name="q_${q.id}" value="${k}" required> ${k}) ${v}</label>`;
          }
        }catch(e){}
        return `
          <div class="question-card" id="q_${q.id}">
            <div class="question-text">
              <span style="background:var(--primary);color:white;padding:2px 8px;border-radius:4px;font-size:0.9rem;margin-right:10px;">Q${idx+1}</span> 
              ${q.question_text} 
              <span style="font-size:0.85rem; color:var(--text-medium); float:right; font-weight:normal; background:#f1f5f9; padding:2px 8px; border-radius:4px;">${q.marks} Marks</span>
            </div>
            <div class="options-grid">
              ${optsHTML}
            </div>
          </div>
        `;
      }).join('');
      
      const navGrid = document.getElementById('navigatorGrid');
      navGrid.innerHTML = res.data.questions.map((q, idx) => `
        <a href="#q_${q.id}" class="nav-box" id="nav_${q.id}" onclick="event.preventDefault(); document.getElementById('q_${q.id}').scrollIntoView({behavior:'smooth', block:'center'});">${idx+1}</a>
      `).join('');

      // Add event listeners to radio buttons to update nav-boxes
      const radios = document.querySelectorAll('input[type="radio"]');
      radios.forEach(r => {
        r.addEventListener('change', (e) => {
          const qId = e.target.name.replace('q_', '');
          document.getElementById('nav_' + qId).classList.add('answered');
        });
      });
      
      document.getElementById('submitBtnContainer').style.display = 'block';
    } else {
      document.getElementById('loadingState').style.display = 'block';
      document.getElementById('loadingState').innerHTML = `<div style="color:red;">${res.message}</div>`;
    }
  }

  function startTimer() {
    const el = document.getElementById('countdownTimer');
    
    function updateDisplay() {
      if(remainingSeconds <= 0) {
        el.textContent = "00:00";
        clearInterval(timerInterval);
        alert("Time is up! Your exam will now be automatically submitted.");
        document.getElementById('finalSubmitBtn').click();
        return;
      }
      let m = Math.floor(remainingSeconds / 60).toString().padStart(2, '0');
      let s = (remainingSeconds % 60).toString().padStart(2, '0');
      el.textContent = `${m}:${s}`;
      
      if(remainingSeconds <= 60) {
        el.style.color = '#ff4757';
      }
      remainingSeconds--;
    }
    
    updateDisplay();
    timerInterval = setInterval(updateDisplay, 1000);
  }

  let examSubmitted = false;

  // Anti-Cheat: Auto-submit if tab is changed or page is refreshed/closed
  document.addEventListener('visibilitychange', () => {
    if(document.visibilityState === 'hidden' && !examSubmitted) {
      forceAutoSubmit("Tab switch detected. Your exam was automatically submitted.");
    }
  });
  
  window.addEventListener('beforeunload', (e) => {
    if(!examSubmitted) {
      forceAutoSubmit();
    }
  });

  function forceAutoSubmit(msg) {
    if(examSubmitted) return;
    examSubmitted = true;
    
    const fd = new FormData(document.getElementById('examForm'));
    const answers = {};
    for(const [k, v] of fd.entries()) {
      if(k.startsWith('q_')) answers[k.replace('q_', '')] = v;
    }
    
    // Use keepalive fetch so it completes even while page is closing/refreshing
    fetch(window.location.pathname + `?id=${sessionId}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ answers }),
      keepalive: true
    });
    
    if(msg) {
      alert(msg);
      window.location.href = 'cbt_dashboard.php';
    }
  }

  async function submitExam(e) {
    e.preventDefault();
    if(examSubmitted) return;
    if(!confirm("Are you sure you want to submit your answers? You cannot change them later.")) return;
    
    examSubmitted = true;
    const fd = new FormData(e.target);
    const answers = {};
    for(const [k, v] of fd.entries()) {
      if(k.startsWith('q_')) {
        answers[k.replace('q_', '')] = v;
      }
    }
    
    const res = await selfApi('POST', {answers}, `id=${sessionId}`);
    if(res.success) {
      alert("Exam submitted successfully!");
      window.location.href = 'cbt_dashboard.php';
    } else {
      examSubmitted = false; // allow retry if server failed
      alert(res.message);
    }
  }
</script>
</body>
</html>
