<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_staff_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error)die('DB Error');$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}

$session_id = (int)($_GET['session_id'] ?? 0);
if(!$session_id) { header("Location: cbt_dashboard.php"); exit; }

$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
if($isAjax){
  $method=$_SERVER['REQUEST_METHOD']??'GET';
  $body=json_decode(file_get_contents('php://input'),true)??[];
  $teacher_id = (int)$_SESSION['ksm_staff_auth'];
  $db = ksm_db();

  // Validate teacher owns session
  $chk = $db->query("SELECT * FROM cbt_sessions WHERE id=$session_id AND teacher_id=$teacher_id")->fetch_assoc();
  if(!$chk) ksm_json(null, "Access Denied", 403);

  if($method==='GET'){
    $questions = [];
    $res = $db->query("SELECT * FROM cbt_questions WHERE session_id=$session_id ORDER BY id ASC");
    while($r = $res->fetch_assoc()) $questions[] = $r;
    ksm_json(['questions'=>$questions]);
  }
  if($method==='POST'){
    $action = $body['action'] ?? '';
    if($action==='add_question'){
      $text = trim($body['question_text'] ?? '');
      $ans = strtoupper(trim($body['correct_answer'] ?? ''));
      $marks = (float)($body['marks'] ?? 1);
      
      if(!$text) ksm_err('Question text is required.');
      if(strlen($text) > 2000) ksm_err('Question text is too long.');
      if(!in_array($ans, ['A', 'B', 'C', 'D'], true)) ksm_err('Correct answer must be A, B, C, or D.');
      if($marks <= 0 || $marks > 100) ksm_err('Marks must be between 1 and 100.');
      
      $opt_a = trim($body['opt_a'] ?? '');
      $opt_b = trim($body['opt_b'] ?? '');
      $opt_c = trim($body['opt_c'] ?? '');
      $opt_d = trim($body['opt_d'] ?? '');
      if(!$opt_a || !$opt_b) ksm_err('Options A and B are required.');

      $opt = [
        'A' => htmlspecialchars($opt_a, ENT_QUOTES, 'UTF-8'),
        'B' => htmlspecialchars($opt_b, ENT_QUOTES, 'UTF-8'),
        'C' => htmlspecialchars($opt_c, ENT_QUOTES, 'UTF-8'),
        'D' => htmlspecialchars($opt_d, ENT_QUOTES, 'UTF-8')
      ];
      $options = ksm_esc(json_encode($opt));
      $text_safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
      
      $db->query("INSERT INTO cbt_questions (session_id, question_text, question_type, options, correct_answer, marks) VALUES ($session_id, '$text_safe', 'multiple_choice', '$options', '$ans', $marks)");
      ksm_json(null, "Question added");
    }
    if($action==='delete_question'){
      $id = (int)$body['id'];
      $db->query("DELETE FROM cbt_questions WHERE id=$id AND session_id=$session_id");
      ksm_json(null, "Question deleted");
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage CBT Questions — KSM Staff Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <style>
    .ksm-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999999; align-items:center; justify-content:center; }
    .ksm-modal.active { display:flex; }
    .ksm-modal-content { background:white; padding:2rem; border-radius:var(--radius-md); width:100%; max-width:600px; box-shadow:0 10px 25px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
  </style>
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Manage Questions</span>
      </div>
      <div class="topbar-right">
        <span id="staffNameTopbar" style="font-size:0.82rem;color:var(--text-medium);"></span>
        <a href="cbt_dashboard.php" class="btn btn-sm btn-outline">Back to Dashboard</a>
      </div>
    </div>

    <div class="portal-content fade-up">
      <div class="d-flex" style="justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h1 class="page-title" style="margin-bottom:0;">Questions for Session #<?php echo $session_id; ?></h1>
        <button class="btn btn-primary" onclick="document.getElementById('addQuestionModal').classList.add('active')">Add Question</button>
      </div>

      <div class="card">
        <table class="table">
          <thead><tr><th>Question</th><th>Options</th><th>Correct Answer</th><th>Marks</th><th>Action</th></tr></thead>
          <tbody id="questionsList">
            <tr><td colspan="5" style="text-align:center;padding:1rem;">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="ksm-modal" id="addQuestionModal">
  <div class="ksm-modal-content">
    <h2 style="margin-bottom:1rem;">Add Multiple Choice Question</h2>
    <form id="addQuestionForm" onsubmit="addQuestion(event)">
      <div class="form-group mb-3">
        <label>Question Text</label>
        <textarea class="form-control" name="question_text" required rows="3"></textarea>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;" class="mb-3">
        <div><label>Option A</label><input type="text" class="form-control" name="opt_a" required></div>
        <div><label>Option B</label><input type="text" class="form-control" name="opt_b" required></div>
        <div><label>Option C</label><input type="text" class="form-control" name="opt_c"></div>
        <div><label>Option D</label><input type="text" class="form-control" name="opt_d"></div>
      </div>
      <div class="form-group mb-3">
        <label>Correct Answer (A, B, C, or D)</label>
        <input type="text" class="form-control" name="correct_answer" required pattern="[A-Da-d]" style="text-transform:uppercase;">
      </div>
      <div class="form-group mb-3">
        <label>Marks</label>
        <input type="number" class="form-control" name="marks" value="1" required step="0.5">
      </div>
      <div style="display:flex;gap:1rem;justify-content:flex-end;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('addQuestionModal').classList.remove('active')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Question</button>
      </div>
    </form>
  </div>
</div>

<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  buildSidebar('staff');
  const sessionId = <?php echo $session_id; ?>;
  document.addEventListener('DOMContentLoaded', () => {
    loadQuestions();
  });

  async function loadQuestions() {
    const res = await selfApi('GET');
    if(res.success) {
      const tbody = document.getElementById('questionsList');
      if(res.data.questions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:1rem;">No questions added yet.</td></tr>';
        return;
      }
      tbody.innerHTML = res.data.questions.map(q => {
        let opts = '';
        try {
          const o = JSON.parse(q.options);
          opts = `A: ${escapeHtml(o.A)}<br>B: ${escapeHtml(o.B)}<br>C: ${escapeHtml(o.C)}<br>D: ${escapeHtml(o.D)}`;
        }catch(e){}
        return `
        <tr>
          <td style="max-width:300px;">${escapeHtml(q.question_text)}</td>
          <td style="font-size:0.8rem;">${opts}</td>
          <td><strong>${escapeHtml(q.correct_answer)}</strong></td>
          <td>${escapeHtml(q.marks)}</td>
          <td>
            <button class="btn btn-sm btn-danger" onclick="deleteQuestion(${q.id})">Delete</button>
          </td>
        </tr>
      `}).join('');
    }
  }

  async function addQuestion(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    const data = Object.fromEntries(fd.entries());
    data.action = 'add_question';
    data.correct_answer = data.correct_answer.toUpperCase();
    const res = await selfApi('POST', data);
    if(res.success) {
      document.getElementById('addQuestionModal').classList.remove('active');
      e.target.reset();
      loadQuestions();
    } else { alert(res.message); }
  }

  async function deleteQuestion(id) {
    if(!confirm("Delete this question?")) return;
    const res = await selfApi('POST', {action: 'delete_question', id});
    if(res.success) loadQuestions();
  }
</script>
</body>
</html>
