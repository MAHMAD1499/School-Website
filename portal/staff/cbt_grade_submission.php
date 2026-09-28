<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_staff_auth();

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error)die('DB Error');$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}

$sub_id = (int)($_GET['id'] ?? 0);
if(!$sub_id) { header("Location: cbt_test_reports.php"); exit; }

$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
if($isAjax){
  $method=$_SERVER['REQUEST_METHOD']??'GET';
  $teacher_id = (int)$_SESSION['ksm_staff_auth'];
  $db = ksm_db();

  // Validate teacher owns the session related to this submission
  $sub = $db->query("SELECT sub.*, st.name as student_name, sess.title as exam_title 
      FROM cbt_submissions sub 
      JOIN cbt_sessions sess ON sub.session_id = sess.id 
      JOIN users_students st ON sub.student_id = st.id
      WHERE sub.id=$sub_id AND sess.teacher_id=$teacher_id")->fetch_assoc();
  
  if(!$sub) ksm_json(null, "Not found or access denied", 404);

  if($method==='GET'){
    $responses = [];
    $res = $db->query("SELECT r.id as response_id, r.student_answer, q.id as question_id, q.question_text, q.options, q.correct_answer, q.marks 
      FROM cbt_responses r 
      JOIN cbt_questions q ON r.question_id = q.id 
      WHERE r.submission_id=$sub_id ORDER BY q.id ASC");
    while($r = $res->fetch_assoc()) $responses[] = $r;
    
    ksm_json(['submission'=>$sub, 'responses'=>$responses]);
  }
  
  if($method==='POST'){
    $body=json_decode(file_get_contents('php://input'),true)??[];
    $marks_data = $body['marks'] ?? [];
    
    // Fetch max marks for responses in this submission to enforce limits
    $resp_q = $db->query("SELECT r.id, q.marks FROM cbt_responses r JOIN cbt_questions q ON r.question_id = q.id WHERE r.submission_id=$sub_id");
    $max_marks_map = [];
    while($rq = $resp_q->fetch_assoc()) {
        $max_marks_map[(int)$rq['id']] = (float)$rq['marks'];
    }

    $total_score = 0;
    foreach($marks_data as $r_id => $awarded) {
      $r_id = (int)$r_id;
      if (!isset($max_marks_map[$r_id])) continue;
      $max_allowed = $max_marks_map[$r_id];
      $awarded = max(0, min((float)$awarded, $max_allowed));
      $total_score += $awarded;
      $db->query("UPDATE cbt_responses SET marks_awarded=$awarded, is_correct=".($awarded>0?'1':'0')." WHERE id=$r_id AND submission_id=$sub_id");
    }
    
    $db->query("UPDATE cbt_submissions SET score=$total_score, status='graded' WHERE id=$sub_id");
    ksm_json(null, "Grades saved successfully");
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grade Submission — KSM Staff Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <style>
    .question-card { background: white; padding: 1.5rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); margin-bottom: 1rem; }
    .correct { color: #16a34a; font-weight: bold; }
    .incorrect { color: #dc2626; font-weight: bold; }
    .mark-input { width: 80px; text-align: right; }
  </style>
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <span class="topbar-title">Grade Submission</span>
      </div>
      <div class="topbar-right">
        <a href="cbt_test_reports.php" class="btn btn-sm btn-outline">Back</a>
      </div>
    </div>

    <div class="portal-content fade-up" style="max-width: 900px; margin: 0 auto;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <div>
          <h1 class="page-title" id="pageTitle" style="margin-bottom:0;">Loading...</h1>
          <p id="examTitle" style="color:var(--text-medium);"></p>
        </div>
      </div>

      <form id="gradeForm" onsubmit="saveGrades(event)">
        <div id="responsesContainer">
           <div style="text-align:center;padding:2rem;">Loading responses...</div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; margin-top:1.5rem;">
           <div>
              <strong style="font-size:1.2rem;">Total Calculated Score: </strong>
              <span id="totalScoreDisplay" style="font-size:1.5rem; font-weight:bold; color:var(--primary-deep);">0</span>
           </div>
           <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">Save & Finalize Grades</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="../assets/api.js"></script>
<script>
  const subId = <?php echo $sub_id; ?>;
  document.addEventListener('DOMContentLoaded', () => {
    loadSubmission();
  });

  async function loadSubmission() {
    const res = await selfApi('GET');
    if(res.success) {
      document.getElementById('pageTitle').textContent = `Reviewing: ${res.data.submission.student_name}`;
      document.getElementById('examTitle').textContent = res.data.submission.exam_title;
      
      const rc = document.getElementById('responsesContainer');
      if(res.data.responses.length === 0) {
        rc.innerHTML = '<div class="question-card">No responses found.</div>';
        return;
      }
      
      let total = 0;
      rc.innerHTML = res.data.responses.map((r, idx) => {
        let optsHTML = '';
        try {
          const opts = JSON.parse(r.options);
          for(const [k, v] of Object.entries(opts)) {
            if(v) {
              let isSelected = (r.student_answer === k);
              let isCorrectOpt = (r.correct_answer === k);
              let color = isSelected ? (isCorrectOpt ? 'color:green;font-weight:bold;' : 'color:red;font-weight:bold;') : (isCorrectOpt ? 'color:green;font-weight:bold;' : '');
              optsHTML += `<div style="${color}">${k}: ${v} ${isSelected?' (Student Answer)':''} ${isCorrectOpt?' (Correct)':''}</div>`;
            }
          }
        }catch(e){}
        
        let autoMark = (r.student_answer === r.correct_answer) ? r.marks : 0;
        total += parseFloat(autoMark);
        
        return `
          <div class="question-card">
            <div style="font-weight:600;margin-bottom:1rem;">${idx+1}. ${r.question_text}</div>
            <div style="background:#f8fafc; padding:1rem; border-radius:4px; margin-bottom:1rem;">
              ${optsHTML}
            </div>
            <div style="display:flex; justify-content:flex-end; align-items:center; gap:1rem;">
               <span>Marks out of ${r.marks}:</span>
               <input type="number" class="form-control mark-input" name="mark_${r.response_id}" value="${autoMark}" step="0.5" max="${r.marks}" min="0" onchange="calcTotal()">
            </div>
          </div>
        `;
      }).join('');
      
      document.getElementById('totalScoreDisplay').textContent = total;
    } else {
      document.getElementById('pageTitle').textContent = 'Error loading submission';
    }
  }

  function calcTotal() {
    let inputs = document.querySelectorAll('.mark-input');
    let total = 0;
    inputs.forEach(inp => total += parseFloat(inp.value || 0));
    document.getElementById('totalScoreDisplay').textContent = total;
  }

  async function saveGrades(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    const marks = {};
    for(const [k, v] of fd.entries()) {
      if(k.startsWith('mark_')) {
        marks[k.replace('mark_', '')] = v;
      }
    }
    
    const res = await selfApi('POST', {marks});
    if(res.success) {
      alert("Grades saved successfully!");
      window.location.href = 'cbt_test_reports.php';
    } else {
      alert(res.message);
    }
  }
</script>
</body>
</html>
