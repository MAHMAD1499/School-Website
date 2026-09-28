<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_student_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error)die('DB Error');$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}

$sub_id = (int)($_GET['id'] ?? 0);
if(!$sub_id) { header("Location: cbt_test_reports.php"); exit; }

$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
if($isAjax){
  $method=$_SERVER['REQUEST_METHOD']??'GET';
  $student_id = (int)$_SESSION['ksm_student_auth'];
  $db = ksm_db();

  // Validate student owns the submission
  $sub = $db->query("SELECT sub.*, sess.title as exam_title, sess.total_marks as max_total
      FROM cbt_submissions sub 
      JOIN cbt_sessions sess ON sub.session_id = sess.id 
      WHERE sub.id=$sub_id AND sub.student_id=$student_id")->fetch_assoc();
  
  if(!$sub) ksm_json(null, "Not found or access denied", 404);
  if($sub['status'] !== 'graded') ksm_json(null, "Exam is not graded yet", 400);

  if($method==='GET'){
    $responses = [];
    $res = $db->query("SELECT r.student_answer, r.marks_awarded, q.question_text, q.options, q.correct_answer, q.marks as max_marks 
      FROM cbt_responses r 
      JOIN cbt_questions q ON r.question_id = q.id 
      WHERE r.submission_id=$sub_id ORDER BY q.id ASC");
    while($r = $res->fetch_assoc()) $responses[] = $r;
    
    ksm_json(['submission'=>$sub, 'responses'=>$responses]);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Result — KSM Student Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <style>
    .question-card { background: white; padding: 1.5rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); margin-bottom: 1rem; }
  </style>
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <span class="topbar-title">Exam Result</span>
      </div>
      <div class="topbar-right">
        <a href="cbt_test_reports.php" class="btn btn-sm btn-outline">Back</a>
      </div>
    </div>

    <div class="portal-content fade-up" style="max-width: 900px; margin: 0 auto;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem; background:white; padding:2rem; border-radius:var(--radius-md); box-shadow:0 4px 6px rgba(0,0,0,0.05);">
        <div>
          <h1 class="page-title" id="examTitle" style="margin-bottom:0;">Loading...</h1>
          <p style="color:var(--text-medium); margin-top:0.5rem;">Completed on: <span id="compDate"></span></p>
        </div>
        <div style="text-align:right;">
          <div style="font-size:0.9rem; color:var(--text-medium); text-transform:uppercase; letter-spacing:1px;">Final Score</div>
          <div style="font-size:2.5rem; font-weight:900; color:var(--primary-deep);" id="finalScore">--</div>
        </div>
      </div>

      <div id="responsesContainer">
         <div style="text-align:center;padding:2rem;">Loading responses...</div>
      </div>
    </div>
  </div>
</div>

<script src="../assets/api.js"></script>
<script>
  const subId = <?php echo $sub_id; ?>;
  document.addEventListener('DOMContentLoaded', () => {
    loadResult();
  });

  async function loadResult() {
    const res = await selfApi('GET');
    if(res.success) {
      document.getElementById('examTitle').textContent = res.data.submission.exam_title;
      document.getElementById('compDate').textContent = res.data.submission.completed_at;
      document.getElementById('finalScore').textContent = `${res.data.submission.score} / ${res.data.submission.max_total}`;
      
      const rc = document.getElementById('responsesContainer');
      if(res.data.responses.length === 0) {
        rc.innerHTML = '<div class="question-card">No responses found.</div>';
        return;
      }
      
      rc.innerHTML = res.data.responses.map((r, idx) => {
        let optsHTML = '';
        try {
          const opts = JSON.parse(r.options);
          for(const [k, v] of Object.entries(opts)) {
            if(v) {
              let isSelected = (r.student_answer === k);
              let isCorrectOpt = (r.correct_answer === k);
              let color = isSelected ? (isCorrectOpt ? 'color:green;font-weight:bold;' : 'color:red;font-weight:bold;') : (isCorrectOpt ? 'color:green;font-weight:bold;' : '');
              optsHTML += `<div style="${color}">${k}: ${v} ${isSelected?' (Your Answer)':''} ${isCorrectOpt?' (Correct Answer)':''}</div>`;
            }
          }
        }catch(e){}
        
        return `
          <div class="question-card">
            <div style="font-weight:600;margin-bottom:1rem; display:flex; justify-content:space-between;">
              <span>${idx+1}. ${r.question_text}</span>
              <span style="color:var(--text-medium); font-size:0.9rem;">Marks: ${r.marks_awarded} / ${r.max_marks}</span>
            </div>
            <div style="background:#f8fafc; padding:1rem; border-radius:4px;">
              ${optsHTML}
            </div>
          </div>
        `;
      }).join('');
    } else {
      document.getElementById('examTitle').textContent = 'Error loading result';
      document.getElementById('responsesContainer').innerHTML = `<div class="question-card">${res.message}</div>`;
    }
  }
</script>
</body>
</html>
