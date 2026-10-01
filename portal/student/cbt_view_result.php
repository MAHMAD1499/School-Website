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
  $sub = $db->query("SELECT sub.*, sess.title as exam_title, sess.total_marks as max_total, sess.subject_id, sess.class_id, sess.type as session_type, st.name as student_name, st.rollNo as student_roll
      FROM cbt_submissions sub 
      JOIN cbt_sessions sess ON sub.session_id = sess.id 
      JOIN users_students st ON sub.student_id = st.id
      WHERE sub.id=$sub_id AND sub.student_id=$student_id")->fetch_assoc();
  
  if(!$sub) ksm_json(null, "Not found or access denied", 404);
  if($sub['status'] !== 'graded') ksm_json(null, "Exam is not graded yet", 400);

  if($method==='GET'){
    $responses = [];
    // Join cbt_questions with responses so even unanswered questions in this session are shown
    $res = $db->query("SELECT 
        q.id as question_id,
        q.question_text, 
        q.options, 
        q.correct_answer, 
        q.marks as max_marks,
        r.student_answer, 
        COALESCE(r.marks_awarded, 0) as marks_awarded,
        COALESCE(r.is_correct, 0) as is_correct
      FROM cbt_questions q
      LEFT JOIN cbt_responses r ON q.id = r.question_id AND r.submission_id=$sub_id
      WHERE q.session_id={$sub['session_id']} 
      ORDER BY q.id ASC");
    if($res && $res->num_rows > 0) {
      while($r = $res->fetch_assoc()) $responses[] = $r;
    } else {
      // Fallback in case questions were queried directly from responses
      $res2 = $db->query("SELECT r.student_answer, r.marks_awarded, r.is_correct, q.question_text, q.options, q.correct_answer, q.marks as max_marks 
        FROM cbt_responses r 
        JOIN cbt_questions q ON r.question_id = q.id 
        WHERE r.submission_id=$sub_id ORDER BY q.id ASC");
      if($res2) {
        while($r = $res2->fetch_assoc()) $responses[] = $r;
      }
    }
    
    ksm_json(['submission'=>$sub, 'responses'=>$responses]);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CBT Exam Result — KSM Student Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <style>
    :root {
      --exam-primary: var(--primary-deep, #1E3A8A);
      --exam-accent: var(--accent-warm, #F59E0B);
      --exam-bg: var(--bg-main, #F8FAFF);
      --card-radius: 16px;
    }
    body {
      background-color: var(--exam-bg);
      font-family: var(--font-body, 'Inter', sans-serif);
      color: var(--text-dark, #1F2937);
    }
    .portal-main {
      margin-left: 0 !important;
      width: 100% !important;
      min-height: 100vh;
      background: var(--exam-bg);
    }
    .result-container {
      max-width: 1050px;
      margin: 0 auto;
      padding: 1.5rem 1.25rem 3rem;
    }
    
    /* Topbar Enhancements */
    .portal-topbar {
      background: #ffffff;
      border-bottom: 1px solid var(--border-color, #E5E7EB);
      box-shadow: 0 1px 3px rgba(0,0,0,0.03);
      padding: 0.85rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 50;
    }
    .topbar-brand {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      font-family: var(--font-heading, 'Quicksand', sans-serif);
      font-weight: 700;
      font-size: 1.15rem;
      color: var(--primary-deep, #1E3A8A);
    }
    .topbar-brand-icon {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      background: linear-gradient(135deg, var(--primary-deep, #1E3A8A), var(--primary-light, #3B82F6));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
    }

    /* Hero Banner */
    .hero-banner {
      background: linear-gradient(135deg, #1E3A8A 0%, #172554 100%);
      color: white;
      border-radius: var(--card-radius);
      padding: 2.25rem;
      margin-bottom: 1.75rem;
      box-shadow: 0 10px 25px -5px rgba(30, 58, 138, 0.25);
      position: relative;
      overflow: hidden;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      gap: 1.5rem;
    }
    .hero-banner::after {
      content: '';
      position: absolute;
      right: -30px;
      bottom: -40px;
      width: 220px;
      height: 220px;
      background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(255,255,255,0) 70%);
      border-radius: 50%;
      pointer-events: none;
    }
    .hero-meta-tag {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(8px);
      padding: 0.35rem 0.85rem;
      border-radius: 9999px;
      font-size: 0.8rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      color: #FEF3C7;
      margin-bottom: 0.75rem;
      border: 1px solid rgba(254, 243, 199, 0.25);
    }
    .hero-title {
      font-family: var(--font-heading, 'Quicksand', sans-serif);
      font-size: 2rem;
      font-weight: 800;
      color: #ffffff;
      line-height: 1.2;
      margin-bottom: 0.65rem;
    }
    .hero-subtext {
      color: #E2E8F0;
      font-size: 0.95rem;
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      align-items: center;
    }
    .hero-subtext-item {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }

    /* Score Spotlight Card */
    .score-spotlight {
      background: rgba(255, 255, 255, 0.98);
      border-radius: 14px;
      padding: 1.5rem 2rem;
      text-align: center;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
      border: 2px solid rgba(245, 158, 11, 0.3);
      min-width: 220px;
      color: var(--text-dark, #1F2937);
      flex-shrink: 0;
    }
    .score-label {
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: var(--text-medium, #4B5563);
      margin-bottom: 0.25rem;
    }
    .score-display {
      font-family: var(--font-heading, 'Quicksand', sans-serif);
      font-size: 2.4rem;
      font-weight: 800;
      color: var(--primary-deep, #1E3A8A);
      line-height: 1.1;
      margin-bottom: 0.5rem;
    }
    .score-display small {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-light, #9CA3AF);
    }
    .grade-badge {
      display: inline-block;
      padding: 0.35rem 0.85rem;
      border-radius: 9999px;
      font-size: 0.82rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .grade-badge.excellent { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
    .grade-badge.pass { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
    .grade-badge.needs-work { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }
    .grade-badge.fail { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }

    /* Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }
    .stat-card {
      background: #ffffff;
      border: 1px solid var(--border-color, #E5E7EB);
      border-radius: 12px;
      padding: 1.25rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.02);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(30, 58, 138, 0.06);
    }
    .stat-icon {
      width: 46px;
      height: 46px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      flex-shrink: 0;
    }
    .stat-icon.blue { background: #EFF6FF; color: #2563EB; }
    .stat-icon.green { background: #ECFDF5; color: #059669; }
    .stat-icon.amber { background: #FEF3C7; color: #D97706; }
    .stat-icon.rose { background: #FEF2F2; color: #DC2626; }
    .stat-meta .stat-val {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--text-dark, #1F2937);
      line-height: 1.2;
    }
    .stat-meta .stat-title {
      font-size: 0.8rem;
      color: var(--text-medium, #6B7280);
      font-weight: 500;
      margin-top: 2px;
    }

    /* Action Toolbar */
    .result-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--border-color, #E5E7EB);
    }
    .section-heading {
      font-family: var(--font-heading, 'Quicksand', sans-serif);
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--primary-deep, #1E3A8A);
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin: 0;
    }
    .action-btns {
      display: flex;
      gap: 0.75rem;
    }

    /* Question Cards */
    .question-card {
      background: #ffffff;
      border: 1px solid var(--border-color, #E5E7EB);
      border-radius: var(--card-radius);
      padding: 1.75rem;
      margin-bottom: 1.25rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.03);
      position: relative;
      border-left: 5px solid #CBD5E1;
      transition: all 0.2s ease;
    }
    .question-card.correct {
      border-left-color: #10B981;
    }
    .question-card.incorrect {
      border-left-color: #EF4444;
    }
    .question-card.unanswered {
      border-left-color: #F59E0B;
    }
    .question-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      flex-wrap: wrap;
      gap: 0.5rem;
    }
    .question-number-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-weight: 700;
      font-size: 0.9rem;
      color: var(--primary-deep, #1E3A8A);
      background: var(--primary-bg, #EFF6FF);
      padding: 0.3rem 0.75rem;
      border-radius: 6px;
    }
    .question-status-pill {
      font-size: 0.8rem;
      font-weight: 700;
      padding: 0.25rem 0.65rem;
      border-radius: 9999px;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }
    .question-status-pill.correct { background: #ECFDF5; color: #047857; }
    .question-status-pill.incorrect { background: #FEF2F2; color: #B91C1C; }
    .question-status-pill.unanswered { background: #FFFBEB; color: #B45309; }

    .question-marks {
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-medium, #4B5563);
      background: #F1F5F9;
      padding: 0.25rem 0.6rem;
      border-radius: 4px;
    }
    .question-text {
      font-size: 1.1rem;
      font-weight: 600;
      color: #1E293B;
      line-height: 1.5;
      margin-bottom: 1.25rem;
    }

    /* Option Rows */
    .options-list {
      display: grid;
      gap: 0.65rem;
    }
    .option-item {
      display: flex;
      align-items: center;
      padding: 0.85rem 1.15rem;
      border-radius: 10px;
      border: 1.5px solid #E2E8F0;
      background: #F8FAFC;
      font-size: 0.98rem;
      color: #334155;
      transition: all 0.2s ease;
    }
    .option-letter {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: #E2E8F0;
      color: #475569;
      font-weight: 700;
      font-size: 0.82rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 0.85rem;
      flex-shrink: 0;
    }
    
    /* Option State Styles */
    .option-item.selected-correct {
      background: #ECFDF5;
      border-color: #10B981;
      color: #065F46;
      font-weight: 600;
    }
    .option-item.selected-correct .option-letter {
      background: #10B981;
      color: white;
    }
    .option-item.selected-incorrect {
      background: #FEF2F2;
      border-color: #EF4444;
      color: #991B1B;
      font-weight: 600;
    }
    .option-item.selected-incorrect .option-letter {
      background: #EF4444;
      color: white;
    }
    .option-item.correct-answer-target {
      background: #F0FDF4;
      border-color: #34D399;
      border-style: dashed;
      color: #065F46;
      font-weight: 600;
    }
    .option-item.correct-answer-target .option-letter {
      background: #34D399;
      color: white;
    }
    .option-tag {
      margin-left: auto;
      font-size: 0.78rem;
      font-weight: 700;
      padding: 0.2rem 0.6rem;
      border-radius: 9999px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      flex-shrink: 0;
    }
    .option-tag.correct { background: #D1FAE5; color: #065F46; }
    .option-tag.incorrect { background: #FEE2E2; color: #991B1B; }

    /* Empty state */
    .empty-result-card {
      background: white;
      border-radius: var(--card-radius);
      padding: 3.5rem 2rem;
      text-align: center;
      border: 1px solid var(--border-color, #E5E7EB);
      box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .empty-result-icon {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: #EFF6FF;
      color: var(--primary-deep, #1E3A8A);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.2rem;
      margin: 0 auto 1.25rem;
    }

    /* Print styles */
    @media print {
      .portal-topbar, .result-actions .action-btns, .btn {
        display: none !important;
      }
      .portal-main {
        background: white !important;
      }
      .hero-banner {
        background: #1E3A8A !important;
        color: white !important;
        box-shadow: none !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      .question-card {
        box-shadow: none !important;
        break-inside: avoid;
      }
    }
  </style>
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    
    <!-- Topbar Navigation -->
    <div class="portal-topbar">
      <div class="topbar-brand">
        <div class="topbar-brand-icon">🎓</div>
        <span>KSM CBT Assessment</span>
      </div>
      <div style="display:flex; align-items:center; gap:0.75rem;">
        <button onclick="window.print()" class="btn btn-sm btn-outline" style="display:inline-flex; align-items:center; gap:0.4rem;">
          🖨️ Print Result
        </button>
        <a href="cbt_test_reports.php" class="btn btn-sm btn-primary" style="display:inline-flex; align-items:center; gap:0.4rem; background:var(--primary-deep, #1E3A8A);">
          ← Back to Reports
        </a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="result-container fade-up">
      
      <!-- Hero Banner -->
      <div class="hero-banner">
        <div style="max-width: 620px;">
          <div class="hero-meta-tag">
            <span>✨ Graded Assessment Report</span>
          </div>
          <h1 class="hero-title" id="examTitle">Loading Exam Result...</h1>
          <div class="hero-subtext">
            <span class="hero-subtext-item" id="subjectTag">
              📚 Subject: <strong id="subjectName">--</strong>
            </span>
            <span class="hero-subtext-item" id="classTag">
              👥 Class: <strong id="className">--</strong>
            </span>
            <span class="hero-subtext-item">
              📅 Completed: <strong id="compDate">--</strong>
            </span>
          </div>
        </div>

        <!-- Score Spotlight Badge -->
        <div class="score-spotlight">
          <div class="score-label">Final Assessment Score</div>
          <div class="score-display">
            <span id="finalScoreVal">--</span><small id="maxTotalVal"> / --</small>
          </div>
          <div>
            <span class="grade-badge" id="performanceBadge">Evaluating...</span>
          </div>
        </div>
      </div>

      <!-- Quick Metrics Grid -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">🏆</div>
          <div class="stat-meta">
            <div class="stat-val" id="statScore">--</div>
            <div class="stat-title">Total Marks Obtained</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">🎯</div>
          <div class="stat-meta">
            <div class="stat-val" id="statPercentage">--%</div>
            <div class="stat-title">Overall Percentage</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon amber">📝</div>
          <div class="stat-meta">
            <div class="stat-val" id="statTotalQ">--</div>
            <div class="stat-title">Total Questions</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon rose">⏱️</div>
          <div class="stat-meta">
            <div class="stat-val" id="statCorrectCount">--</div>
            <div class="stat-title">Correct Answers</div>
          </div>
        </div>
      </div>

      <!-- Section Actions / Header -->
      <div class="result-actions">
        <h2 class="section-heading">
          <span>📋</span> Detailed Response Review
        </h2>
        <div class="action-btns">
          <a href="cbt_dashboard.php" class="btn btn-sm btn-outline">CBT Dashboard</a>
          <a href="cbt_test_reports.php" class="btn btn-sm btn-outline">All Test Reports</a>
        </div>
      </div>

      <!-- Questions List Container -->
      <div id="responsesContainer">
        <div style="text-align:center; padding:3rem; color:var(--text-medium, #4B5563); background:white; border-radius:var(--card-radius); border:1px solid #E5E7EB;">
          <div style="font-size:1.8rem; margin-bottom:0.5rem;">⏳</div>
          Loading responses and performance analysis...
        </div>
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
    try {
      const res = await selfApi('GET', null, `id=${subId}`);
      if(res.success && res.data) {
        const sub = res.data.submission;
        const responses = res.data.responses || [];

        // Fill Hero Banner
        document.getElementById('examTitle').textContent = sub.exam_title || 'CBT Assessment';
        document.getElementById('subjectName').textContent = sub.subject_id || 'General';
        document.getElementById('className').textContent = sub.class_id || 'All';
        document.getElementById('compDate').textContent = sub.completed_at || 'Just now';

        // Score Calculation
        const score = parseFloat(sub.score || 0);
        const maxScore = parseFloat(sub.max_total || 0);
        document.getElementById('finalScoreVal').textContent = score.toFixed(2);
        document.getElementById('maxTotalVal').textContent = ` / ${maxScore.toFixed(2)}`;
        document.getElementById('statScore').textContent = `${score.toFixed(2)} / ${maxScore.toFixed(2)}`;

        // Percentage & Grade Badge
        let pct = 0;
        if(maxScore > 0) {
          pct = Math.round((score / maxScore) * 100);
        }
        document.getElementById('statPercentage').textContent = `${pct}%`;

        const badge = document.getElementById('performanceBadge');
        if(pct >= 80) {
          badge.className = 'grade-badge excellent';
          badge.textContent = `🌟 Outstanding (${pct}%)`;
        } else if(pct >= 60) {
          badge.className = 'grade-badge pass';
          badge.textContent = `👍 Passed (${pct}%)`;
        } else if(pct >= 40) {
          badge.className = 'grade-badge needs-work';
          badge.textContent = `⚠️ Needs Practice (${pct}%)`;
        } else {
          badge.className = 'grade-badge fail';
          badge.textContent = `❌ Below Average (${pct}%)`;
        }

        // Stats summary
        let correctCount = 0;
        responses.forEach(r => {
          if(r.is_correct == 1 || (r.student_answer && r.correct_answer && r.student_answer.trim().toUpperCase() === r.correct_answer.trim().toUpperCase())) {
            correctCount++;
          }
        });
        document.getElementById('statTotalQ').textContent = `${responses.length} Questions`;
        document.getElementById('statCorrectCount').textContent = `${correctCount} Correct`;

        // Render Questions
        const rc = document.getElementById('responsesContainer');
        if(responses.length === 0) {
          rc.innerHTML = `
            <div class="empty-result-card">
              <div class="empty-result-icon">📝</div>
              <h3 style="font-family:var(--font-heading); font-size:1.35rem; color:var(--primary-deep); margin-bottom:0.5rem;">
                No Question Responses Recorded
              </h3>
              <p style="color:var(--text-medium); max-width:500px; margin:0 auto 1.5rem; font-size:0.95rem; line-height:1.6;">
                No individual answer breakdown is available for this exam session. Your final recorded score has been saved as <strong>${score.toFixed(2)} / ${maxScore.toFixed(2)}</strong>.
              </p>
              <div style="display:flex; justify-content:center; gap:0.75rem;">
                <a href="cbt_dashboard.php" class="btn btn-primary" style="background:var(--primary-deep);">Return to CBT Dashboard</a>
                <a href="cbt_test_reports.php" class="btn btn-outline">View Test Reports</a>
              </div>
            </div>
          `;
          return;
        }

        rc.innerHTML = responses.map((r, idx) => {
          const studentAns = (r.student_answer || '').trim().toUpperCase();
          const correctAns = (r.correct_answer || '').trim().toUpperCase();
          const isAnswered = studentAns !== '';
          const isCorrect = isAnswered && (r.is_correct == 1 || studentAns === correctAns);
          
          let cardStatusClass = 'unanswered';
          let statusPill = `<span class="question-status-pill unanswered">⚠️ Not Answered</span>`;
          if(isAnswered) {
            if(isCorrect) {
              cardStatusClass = 'correct';
              statusPill = `<span class="question-status-pill correct">✓ Correct</span>`;
            } else {
              cardStatusClass = 'incorrect';
              statusPill = `<span class="question-status-pill incorrect">✗ Incorrect</span>`;
            }
          }

          let optsHTML = '';
          try {
            const opts = typeof r.options === 'string' ? JSON.parse(r.options) : r.options;
            if(opts && typeof opts === 'object') {
              for(const [k, v] of Object.entries(opts)) {
                if(!v) continue;
                const letter = k.trim().toUpperCase();
                const isUserChoice = (studentAns === letter);
                const isCorrectChoice = (correctAns === letter);

                let itemClass = '';
                let tagHTML = '';

                if(isUserChoice && isCorrectChoice) {
                  itemClass = 'selected-correct';
                  tagHTML = `<span class="option-tag correct">✓ Your Choice (Correct)</span>`;
                } else if(isUserChoice && !isCorrectChoice) {
                  itemClass = 'selected-incorrect';
                  tagHTML = `<span class="option-tag incorrect">✗ Your Choice</span>`;
                } else if(isCorrectChoice) {
                  itemClass = 'correct-answer-target';
                  tagHTML = `<span class="option-tag correct">✓ Correct Answer</span>`;
                }

                optsHTML += `
                  <div class="option-item ${itemClass}">
                    <div class="option-letter">${letter}</div>
                    <div style="flex:1;">${v}</div>
                    ${tagHTML}
                  </div>
                `;
              }
            }
          } catch(e) {
            optsHTML = `<div style="color:var(--text-medium); font-size:0.9rem;">Options data format unavailable.</div>`;
          }

          return `
            <div class="question-card ${cardStatusClass}">
              <div class="question-header">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                  <span class="question-number-badge">Question ${idx + 1}</span>
                  ${statusPill}
                </div>
                <div class="question-marks">
                  Marks: <strong>${parseFloat(r.marks_awarded || 0).toFixed(2)}</strong> / ${parseFloat(r.max_marks || 1).toFixed(2)}
                </div>
              </div>

              <div class="question-text">
                ${r.question_text || 'Question text unavailable'}
              </div>

              <div class="options-list">
                ${optsHTML}
              </div>
            </div>
          `;
        }).join('');

      } else {
        document.getElementById('examTitle').textContent = 'Unable to Load Result';
        document.getElementById('responsesContainer').innerHTML = `
          <div class="empty-result-card">
            <div class="empty-result-icon" style="color:#EF4444; background:#FEF2F2;">⚠️</div>
            <h3 style="color:#B91C1C; margin-bottom:0.5rem;">Error Loading Assessment</h3>
            <p style="color:var(--text-medium); margin-bottom:1rem;">${res.message || 'The exam result could not be retrieved.'}</p>
            <a href="cbt_test_reports.php" class="btn btn-primary" style="background:var(--primary-deep);">Back to Reports</a>
          </div>
        `;
      }
    } catch(err) {
      document.getElementById('examTitle').textContent = 'Connection Error';
      document.getElementById('responsesContainer').innerHTML = `
        <div class="empty-result-card">
          <div class="empty-result-icon" style="color:#EF4444; background:#FEF2F2;">⚠️</div>
          <h3 style="color:#B91C1C; margin-bottom:0.5rem;">Unable to Connect</h3>
          <p style="color:var(--text-medium); margin-bottom:1rem;">An error occurred while loading your exam results. Please refresh or try again.</p>
          <button onclick="loadResult()" class="btn btn-primary" style="background:var(--primary-deep);">Retry</button>
        </div>
      `;
    }
  }
</script>
</body>
</html>
