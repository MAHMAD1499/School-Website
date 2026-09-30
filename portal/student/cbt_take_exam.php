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
    if($chk) ksm_json(['submission_id'=>$chk['id']], "You have already submitted this exam.", 400);

    // Create submission with temporary status
    $db->query("INSERT INTO cbt_submissions (session_id, student_id, started_at, completed_at, status, score) VALUES ($session_id, $student_id, NOW(), NOW(), 'submitted', 0)");
    $sub_id = $db->insert_id;

    // Fetch valid question IDs for this session
    $valid_q_res = $db->query("SELECT id, correct_answer, marks FROM cbt_questions WHERE session_id=$session_id");
    $valid_q = [];
    while($vq = $valid_q_res->fetch_assoc()) $valid_q[(int)$vq['id']] = $vq;

    $total_score = 0;
    // Save responses for all valid questions
    foreach($valid_q as $q_id => $q_data) {
      $ans = isset($answers[$q_id]) ? ksm_esc(substr(strtoupper(trim($answers[$q_id])), 0, 50)) : '';
      $is_correct = (!empty($ans) && $ans === strtoupper(trim($q_data['correct_answer'] ?? '')));
      
      $marks_awarded = 0;
      if($is_correct) {
          $marks_awarded = (float)$q_data['marks'];
          $total_score += $marks_awarded;
      }
      $is_correct_bit = $is_correct ? 1 : 0;
      
      $db->query("INSERT INTO cbt_responses (submission_id, question_id, student_answer, marks_awarded, is_correct) VALUES ($sub_id, $q_id, '$ans', $marks_awarded, $is_correct_bit)");
    }
    
    // Update submission score and status to graded
    $db->query("UPDATE cbt_submissions SET score=$total_score, status='graded' WHERE id=$sub_id");
    
    ksm_json(['submission_id'=>$sub_id], "Exam submitted successfully");
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Live Assessment Session — KSM Student Portal</title>
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
      user-select: none;
    }
    .portal-main {
      margin-left: 0 !important;
      width: 100% !important;
      border-radius: 0 !important;
      min-height: 100vh;
      background: var(--exam-bg);
    }

    /* Topbar */
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
      gap: 0.75rem;
    }
    .brand-icon {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      background: linear-gradient(135deg, var(--primary-deep, #1E3A8A), var(--primary-light, #3B82F6));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
    }
    .brand-text h2 {
      font-family: var(--font-heading, 'Quicksand', sans-serif);
      font-size: 1.05rem;
      font-weight: 700;
      color: var(--primary-deep, #1E3A8A);
      margin: 0;
      line-height: 1.2;
    }
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.75rem;
      font-weight: 600;
      color: #059669;
      background: #ECFDF5;
      padding: 0.2rem 0.6rem;
      border-radius: 9999px;
      border: 1px solid #A7F3D0;
    }
    .pulse-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #10B981;
      box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
      animation: pulse 1.6s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
      70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
      100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* Exam Layout */
    .exam-wrapper {
      max-width: 1350px;
      margin: 0 auto;
      padding: 1.5rem 1.25rem 4rem;
    }
    .exam-layout {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 1.75rem;
      align-items: start;
    }
    @media (max-width: 992px) {
      .exam-layout {
        grid-template-columns: 1fr;
      }
      .exam-sidebar {
        order: -1;
      }
    }

    /* Hero Exam Header */
    .exam-header {
      background: linear-gradient(135deg, #1E3A8A 0%, #172554 100%);
      color: white;
      padding: 2rem 2.25rem;
      border-radius: var(--card-radius);
      margin-bottom: 1.5rem;
      box-shadow: 0 10px 25px -5px rgba(30, 58, 138, 0.25);
      position: relative;
      overflow: hidden;
    }
    .exam-header::after {
      content: '';
      position: absolute;
      right: -30px;
      bottom: -40px;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(255,255,255,0) 70%);
      border-radius: 50%;
      pointer-events: none;
    }
    .exam-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.6rem;
      margin-bottom: 0.85rem;
    }
    .exam-pill {
      background: rgba(255, 255, 255, 0.14);
      backdrop-filter: blur(8px);
      padding: 0.35rem 0.85rem;
      border-radius: 9999px;
      font-size: 0.8rem;
      font-weight: 600;
      color: #FEF3C7;
      border: 1px solid rgba(254, 243, 199, 0.25);
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }
    .exam-title {
      font-family: var(--font-heading, 'Quicksand', sans-serif);
      font-size: 1.85rem;
      font-weight: 800;
      color: #ffffff;
      margin: 0 0 0.5rem 0;
      line-height: 1.25;
    }
    .exam-info-bar {
      color: #E2E8F0;
      font-size: 0.92rem;
      display: flex;
      flex-wrap: wrap;
      gap: 1.25rem;
    }
    .info-bar-item {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }

    /* Warning Banner */
    .anti-cheat-pill {
      background: #FFFBEB;
      border: 1px solid #FDE68A;
      color: #92400E;
      border-radius: 10px;
      padding: 0.75rem 1.25rem;
      margin-bottom: 1.5rem;
      font-size: 0.88rem;
      display: flex;
      align-items: center;
      gap: 0.65rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    /* Question Cards */
    .question-card {
      background: #ffffff;
      padding: 2rem 2.25rem;
      border-radius: var(--card-radius);
      box-shadow: 0 2px 10px rgba(30, 58, 138, 0.04);
      margin-bottom: 1.5rem;
      border: 1px solid var(--border-color, #E5E7EB);
      border-left: 5px solid var(--primary-deep, #1E3A8A);
      transition: all 0.2s ease;
      scroll-margin-top: 5rem;
    }
    .question-card:hover {
      box-shadow: 0 6px 16px rgba(30, 58, 138, 0.06);
    }
    .question-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.25rem;
    }
    .q-number-pill {
      background: var(--primary-bg, #EFF6FF);
      color: var(--primary-deep, #1E3A8A);
      font-weight: 700;
      padding: 0.35rem 0.85rem;
      border-radius: 8px;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }
    .q-marks-pill {
      background: #F1F5F9;
      color: var(--text-medium, #4B5563);
      padding: 0.3rem 0.75rem;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .question-text {
      font-size: 1.15rem;
      font-weight: 600;
      color: #1E293B;
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }

    /* Option Selection Cards */
    .options-grid {
      display: grid;
      gap: 0.85rem;
    }
    .option-card {
      display: flex;
      align-items: center;
      padding: 1rem 1.25rem;
      border: 1.5px solid #E2E8F0;
      border-radius: 12px;
      cursor: pointer;
      background: #F8FAFC;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }
    .option-card:hover {
      border-color: var(--primary-light, #3B82F6);
      background: #EFF6FF;
      transform: translateX(3px);
    }
    .option-card input[type="radio"] {
      position: absolute;
      opacity: 0;
      width: 0;
      height: 0;
    }
    .option-letter-badge {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: #E2E8F0;
      color: #475569;
      font-weight: 700;
      font-size: 0.88rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 1rem;
      flex-shrink: 0;
      transition: all 0.2s ease;
    }
    .option-text {
      font-size: 1.02rem;
      color: #334155;
      font-weight: 500;
      flex: 1;
    }
    
    /* Checked State */
    .option-card:has(input[type="radio"]:checked) {
      border-color: var(--primary-deep, #1E3A8A);
      background: #EFF6FF;
      box-shadow: 0 2px 8px rgba(30, 58, 138, 0.08);
    }
    .option-card:has(input[type="radio"]:checked) .option-letter-badge {
      background: var(--primary-deep, #1E3A8A);
      color: #ffffff;
      box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
    }
    .option-card:has(input[type="radio"]:checked) .option-text {
      color: var(--primary-deep, #1E3A8A);
      font-weight: 600;
    }

    /* Sidebar Components */
    .exam-sidebar {
      position: sticky;
      top: 5rem;
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    /* Timer Card */
    .timer-card {
      background: #ffffff;
      border-radius: var(--card-radius);
      box-shadow: 0 4px 15px rgba(30, 58, 138, 0.05);
      border: 1px solid var(--border-color, #E5E7EB);
      padding: 1.5rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .timer-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-deep, #1E3A8A), var(--primary-light, #3B82F6));
    }
    .timer-label {
      font-size: 0.78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: var(--text-medium, #6B7280);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
    }
    .timer-display {
      font-family: var(--font-heading, 'Quicksand', sans-serif);
      font-size: 2.75rem;
      font-weight: 800;
      color: var(--primary-deep, #1E3A8A);
      font-variant-numeric: tabular-nums;
      margin: 0.4rem 0 0.5rem;
      line-height: 1;
      letter-spacing: -1px;
    }
    .timer-display.warning {
      color: #D97706 !important;
    }
    .timer-display.danger {
      color: #DC2626 !important;
      animation: urgentPulse 1s infinite alternate;
    }
    @keyframes urgentPulse {
      0% { transform: scale(1); }
      100% { transform: scale(1.05); }
    }
    .timer-progress {
      width: 100%;
      height: 6px;
      background: #F1F5F9;
      border-radius: 9999px;
      overflow: hidden;
      margin-top: 0.75rem;
    }
    .timer-progress-fill {
      height: 100%;
      width: 100%;
      background: var(--primary-light, #3B82F6);
      transition: width 1s linear;
    }

    /* Navigator Card */
    .nav-card {
      background: #ffffff;
      border-radius: var(--card-radius);
      box-shadow: 0 4px 15px rgba(30, 58, 138, 0.05);
      border: 1px solid var(--border-color, #E5E7EB);
      padding: 1.5rem;
    }
    .nav-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid #F1F5F9;
    }
    .nav-title {
      font-family: var(--font-heading, 'Quicksand', sans-serif);
      font-weight: 700;
      font-size: 1.05rem;
      color: var(--primary-deep, #1E3A8A);
      margin: 0;
    }
    .nav-progress-text {
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-medium, #6B7280);
    }
    .nav-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 0.5rem;
      margin-bottom: 1.25rem;
    }
    .nav-box {
      aspect-ratio: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #F8FAFC;
      border: 1.5px solid #E2E8F0;
      border-radius: 8px;
      font-weight: 700;
      font-size: 0.92rem;
      color: #475569;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
    }
    .nav-box:hover {
      border-color: var(--primary-light, #3B82F6);
      background: #EFF6FF;
      transform: translateY(-2px);
    }
    .nav-box.answered {
      background: #10B981;
      color: #ffffff;
      border-color: #059669;
      box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
    }
    .nav-box.active {
      border-color: var(--primary-deep, #1E3A8A);
      box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.25);
    }
    .nav-legend {
      display: flex;
      justify-content: space-around;
      font-size: 0.78rem;
      color: var(--text-medium, #6B7280);
      padding-top: 0.5rem;
      border-top: 1px dashed #E2E8F0;
    }
    .legend-item {
      display: flex;
      align-items: center;
      gap: 0.35rem;
    }
    .legend-dot {
      width: 10px;
      height: 10px;
      border-radius: 3px;
    }
    .legend-dot.green { background: #10B981; }
    .legend-dot.gray { background: #E2E8F0; }

    /* Submit Section */
    .submit-cta-card {
      background: #ffffff;
      border-radius: var(--card-radius);
      box-shadow: 0 4px 15px rgba(30, 58, 138, 0.05);
      border: 1px solid var(--border-color, #E5E7EB);
      padding: 1.5rem;
      text-align: center;
    }
    .btn-submit-exam {
      background: linear-gradient(135deg, #10B981 0%, #059669 100%);
      color: white;
      font-weight: 700;
      font-size: 1.05rem;
      padding: 0.95rem 1.75rem;
      border-radius: 12px;
      border: none;
      width: 100%;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    .btn-submit-exam:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
      filter: brightness(1.05);
    }

    /* Modal dialog */
    .modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      padding: 1.5rem;
    }
    .modal-dialog {
      background: white;
      border-radius: var(--card-radius);
      max-width: 480px;
      width: 100%;
      padding: 2rem;
      box-shadow: 0 20px 30px rgba(0,0,0,0.2);
      text-align: center;
      animation: modalPop 0.25s ease-out;
    }
    @keyframes modalPop {
      0% { transform: scale(0.9); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }
  </style>
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    
    <!-- Topbar -->
    <div class="portal-topbar">
      <div class="topbar-brand">
        <div class="brand-icon">🎓</div>
        <div class="brand-text">
          <h2>KSM Active Examination</h2>
        </div>
      </div>
      <div style="display:flex; align-items:center; gap:1rem;">
        <span class="status-badge">
          <span class="pulse-dot"></span>
          Live Session Active
        </span>
        <button onclick="confirmExit()" class="btn btn-sm btn-outline">
          Cancel & Exit
        </button>
      </div>
    </div>

    <!-- Main Exam Container -->
    <div class="exam-wrapper fade-up">
      
      <!-- Loading State -->
      <div id="loadingState" style="text-align:center; padding:6rem 2rem; background:white; border-radius:var(--card-radius); border:1px solid #E5E7EB;">
        <div style="font-size:2.5rem; margin-bottom:1rem;">⏳</div>
        <h2 style="font-family:var(--font-heading); color:var(--primary-deep); margin-bottom:0.5rem;">Loading Examination Session...</h2>
        <p style="color:var(--text-medium);">Preparing questions and initializing security protocols.</p>
      </div>

      <!-- Exam Layout -->
      <div class="exam-layout" id="examLayout" style="display:none;">
        
        <!-- Left: Exam Main Content -->
        <div class="exam-main">
          
          <!-- Exam Header -->
          <div class="exam-header">
            <div class="exam-tags">
              <span class="exam-pill" id="examSubjectPill">📚 Subject: Loading...</span>
              <span class="exam-pill" id="examClassPill">👥 Class: Loading...</span>
            </div>
            <h1 class="exam-title" id="examTitle">Loading Exam...</h1>
            <div class="exam-info-bar">
              <span class="info-bar-item" id="infoDuration">⏱️ Duration: -- mins</span>
              <span class="info-bar-item" id="infoMarks">🎯 Total Marks: --</span>
              <span class="info-bar-item" id="infoQCount">📝 Questions: --</span>
            </div>
          </div>

          <!-- Anti-Cheat Notification -->
          <div class="anti-cheat-pill">
            <span style="font-size:1.2rem;">🛡️</span>
            <span><strong>Exam Environment Notice:</strong> Please remain on this tab. Switching tabs, refreshing, or leaving the window will immediately auto-submit your test.</span>
          </div>

          <!-- Questions Form -->
          <form id="examForm" onsubmit="event.preventDefault(); promptSubmitConfirmation();">
            <div id="questionsContainer"></div>
          </form>

        </div>

        <!-- Right: Sticky Controls Sidebar -->
        <div class="exam-sidebar">
          
          <!-- Timer Card -->
          <div class="timer-card">
            <div class="timer-label">
              <span>⏱️ Time Remaining</span>
            </div>
            <div class="timer-display" id="countdownTimer">--:--</div>
            <div class="timer-progress">
              <div class="timer-progress-fill" id="timerProgressFill"></div>
            </div>
          </div>

          <!-- Question Navigator -->
          <div class="nav-card">
            <div class="nav-card-header">
              <h3 class="nav-title">Questions Map</h3>
              <span class="nav-progress-text" id="answeredProgress">0 / 0</span>
            </div>
            <div class="nav-grid" id="navigatorGrid"></div>
            <div class="nav-legend">
              <span class="legend-item"><span class="legend-dot green"></span> Answered</span>
              <span class="legend-item"><span class="legend-dot gray"></span> Unanswered</span>
            </div>
          </div>

          <!-- Quick Submit Card -->
          <div class="submit-cta-card">
            <button type="button" onclick="promptSubmitConfirmation()" class="btn-submit-exam" id="finalSubmitBtn">
              <span>🚀 Finish & Submit Exam</span>
            </button>
            <p style="font-size:0.8rem; color:var(--text-medium); margin-top:0.75rem; line-height:1.4;">
              Ensure you review all questions before submitting. You cannot re-take once submitted.
            </p>
          </div>

        </div>

      </div>

    </div>
  </div>
</div>

<!-- Submit Confirmation Modal -->
<div class="modal-overlay" id="submitModal">
  <div class="modal-dialog">
    <div style="font-size:2.5rem; margin-bottom:0.75rem;">📋</div>
    <h3 style="font-family:var(--font-heading); font-size:1.4rem; color:var(--primary-deep); margin-bottom:0.5rem;">
      Ready to Submit?
    </h3>
    <p id="modalSummaryText" style="color:var(--text-medium); font-size:0.95rem; margin-bottom:1.5rem; line-height:1.5;">
      You have answered 0 of 0 questions.
    </p>
    <div style="display:flex; justify-content:center; gap:0.75rem;">
      <button onclick="closeSubmitModal()" class="btn btn-outline">Review Answers</button>
      <button onclick="performFinalSubmit()" class="btn btn-primary" style="background:#10B981; border-color:#059669;">Confirm Submission</button>
    </div>
  </div>
</div>

<script src="../assets/api.js"></script>
<script>
  const sessionId = <?php echo $session_id; ?>;
  let timerInterval = null;
  let totalDurationSeconds = 0;
  let remainingSeconds = 0;
  let totalQuestionsCount = 0;
  let examSubmitted = false;

  document.addEventListener('DOMContentLoaded', () => {
    loadExam();
  });

  async function loadExam() {
    try {
      const res = await selfApi('GET', null, `id=${sessionId}`);
      document.getElementById('loadingState').style.display = 'none';
      
      if(res.success && res.data) {
        document.getElementById('examLayout').style.display = 'grid';
        const session = res.data.session;
        const questions = res.data.questions || [];
        totalQuestionsCount = questions.length;

        // Populate Header Data
        document.getElementById('examTitle').textContent = session.title || 'CBT Assessment';
        document.getElementById('examSubjectPill').textContent = `📚 ${session.subject_id || 'General Subject'}`;
        document.getElementById('examClassPill').textContent = `👥 ${session.class_id || 'All Classes'}`;
        document.getElementById('infoDuration').textContent = `⏱️ ${session.duration_minutes} Mins`;
        document.getElementById('infoMarks').textContent = `🎯 ${parseFloat(session.total_marks || 0).toFixed(2)} Marks`;
        document.getElementById('infoQCount').textContent = `📝 ${questions.length} Questions`;

        // Setup Timer
        totalDurationSeconds = parseInt(session.duration_minutes || 30) * 60;
        remainingSeconds = totalDurationSeconds;
        startTimer();

        // Render Questions
        const qc = document.getElementById('questionsContainer');
        if(questions.length === 0) {
          qc.innerHTML = `
            <div style="background:white; border-radius:var(--card-radius); padding:4rem 2rem; text-align:center; border:1px solid #E5E7EB;">
              <div style="font-size:2.5rem; margin-bottom:1rem;">⚠️</div>
              <h3 style="color:var(--primary-deep); font-family:var(--font-heading);">No Questions Published</h3>
              <p style="color:var(--text-medium);">No questions have been configured for this exam session yet. Please contact your instructor.</p>
              <a href="cbt_dashboard.php" class="btn btn-primary" style="margin-top:1rem;">Return to Dashboard</a>
            </div>
          `;
          document.getElementById('finalSubmitBtn').disabled = true;
          return;
        }

        qc.innerHTML = questions.map((q, idx) => {
          let optsHTML = '';
          try {
            const opts = typeof q.options === 'string' ? JSON.parse(q.options) : q.options;
            if(opts && typeof opts === 'object') {
              for(const [k, v] of Object.entries(opts)) {
                if(!v) continue;
                const letter = k.trim().toUpperCase();
                optsHTML += `
                  <label class="option-card" for="opt_${q.id}_${letter}">
                    <input type="radio" id="opt_${q.id}_${letter}" name="q_${q.id}" value="${letter}" onchange="onAnswerSelected(${q.id})">
                    <span class="option-letter-badge">${letter}</span>
                    <span class="option-text">${v}</span>
                  </label>
                `;
              }
            }
          } catch(e) {
            optsHTML = `<div style="color:red; font-size:0.9rem;">Unable to parse question choices.</div>`;
          }

          return `
            <div class="question-card" id="q_${q.id}">
              <div class="question-header">
                <span class="q-number-pill">Question ${idx + 1}</span>
                <span class="q-marks-pill">[ ${parseFloat(q.marks || 1).toFixed(2)} Marks ]</span>
              </div>
              <div class="question-text">
                ${q.question_text}
              </div>
              <div class="options-grid">
                ${optsHTML}
              </div>
            </div>
          `;
        }).join('');

        // Render Navigator Grid
        const navGrid = document.getElementById('navigatorGrid');
        navGrid.innerHTML = questions.map((q, idx) => `
          <a href="#q_${q.id}" class="nav-box" id="nav_${q.id}" onclick="event.preventDefault(); scrollToQuestion(${q.id});">${idx + 1}</a>
        `).join('');

        updateAnsweredCounter();

      } else {
        document.getElementById('loadingState').style.display = 'block';
        document.getElementById('loadingState').innerHTML = `
          <div style="font-size:2.5rem; margin-bottom:1rem; color:#EF4444;">⚠️</div>
          <h2 style="color:#B91C1C; margin-bottom:0.5rem;">Access Denied</h2>
          <p style="color:var(--text-medium); margin-bottom:1.5rem;">${res.message || 'Unable to open exam session.'}</p>
          <a href="cbt_dashboard.php" class="btn btn-primary">Return to CBT Dashboard</a>
        `;
      }
    } catch(e) {
      document.getElementById('loadingState').style.display = 'block';
      document.getElementById('loadingState').innerHTML = `
        <div style="font-size:2.5rem; margin-bottom:1rem; color:#EF4444;">⚠️</div>
        <h2 style="color:#B91C1C; margin-bottom:0.5rem;">Connection Failed</h2>
        <p style="color:var(--text-medium); margin-bottom:1.5rem;">Please check your connection and reload.</p>
        <button onclick="location.reload()" class="btn btn-primary">Reload Page</button>
      `;
    }
  }

  function scrollToQuestion(qId) {
    const el = document.getElementById(`q_${qId}`);
    if(el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      // highlight active box
      document.querySelectorAll('.nav-box').forEach(b => b.classList.remove('active'));
      const navEl = document.getElementById(`nav_${qId}`);
      if(navEl) navEl.classList.add('active');
    }
  }

  function onAnswerSelected(qId) {
    const navEl = document.getElementById(`nav_${qId}`);
    if(navEl) {
      navEl.classList.add('answered');
    }
    updateAnsweredCounter();
  }

  function updateAnsweredCounter() {
    const answeredCount = document.querySelectorAll('.nav-box.answered').length;
    document.getElementById('answeredProgress').textContent = `${answeredCount} / ${totalQuestionsCount} Answered`;
  }

  function startTimer() {
    const timerDisplay = document.getElementById('countdownTimer');
    const timerProgress = document.getElementById('timerProgressFill');

    function update() {
      if(remainingSeconds <= 0) {
        timerDisplay.textContent = "00:00";
        clearInterval(timerInterval);
        alert("Time is up! Your exam session has concluded and your responses are being submitted automatically.");
        performFinalSubmit(true);
        return;
      }

      const m = Math.floor(remainingSeconds / 60).toString().padStart(2, '0');
      const s = (remainingSeconds % 60).toString().padStart(2, '0');
      timerDisplay.textContent = `${m}:${s}`;

      // Progress bar percentage
      if(totalDurationSeconds > 0) {
        const pct = Math.max(0, (remainingSeconds / totalDurationSeconds) * 100);
        timerProgress.style.width = `${pct}%`;
      }

      // Warning color triggers
      if(remainingSeconds <= 60) {
        timerDisplay.className = 'timer-display danger';
        timerProgress.style.background = '#DC2626';
      } else if(remainingSeconds <= 300) {
        timerDisplay.className = 'timer-display warning';
        timerProgress.style.background = '#D97706';
      }

      remainingSeconds--;
    }

    update();
    timerInterval = setInterval(update, 1000);
  }

  // Anti-Cheat: Visibility Switch Auto-Submit
  document.addEventListener('visibilitychange', () => {
    if(document.visibilityState === 'hidden' && !examSubmitted) {
      forceAutoSubmit("Tab switch or minimize detected! In accordance with exam policy, your test was automatically submitted.");
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

  function promptSubmitConfirmation() {
    if(examSubmitted) return;
    const answeredCount = document.querySelectorAll('.nav-box.answered').length;
    const remaining = totalQuestionsCount - answeredCount;
    
    let summary = `You have answered <strong>${answeredCount}</strong> of <strong>${totalQuestionsCount}</strong> questions.`;
    if(remaining > 0) {
      summary += `<br><span style="color:#B45309; font-weight:600; display:inline-block; margin-top:0.5rem;">⚠️ You still have ${remaining} unanswered question(s)!</span>`;
    } else {
      summary += `<br><span style="color:#059669; font-weight:600; display:inline-block; margin-top:0.5rem;">✓ All questions have been completed!</span>`;
    }
    document.getElementById('modalSummaryText').innerHTML = summary;
    document.getElementById('submitModal').style.display = 'flex';
  }

  function closeSubmitModal() {
    document.getElementById('submitModal').style.display = 'none';
  }

  async function performFinalSubmit(isAuto = false) {
    if(examSubmitted) return;
    examSubmitted = true;
    closeSubmitModal();

    const btn = document.getElementById('finalSubmitBtn');
    btn.disabled = true;
    btn.textContent = 'Submitting Assessment...';

    const fd = new FormData(document.getElementById('examForm'));
    const answers = {};
    for(const [k, v] of fd.entries()) {
      if(k.startsWith('q_')) answers[k.replace('q_', '')] = v;
    }

    try {
      const res = await selfApi('POST', { answers }, `id=${sessionId}`);
      if(res.success) {
        if(!isAuto) alert("Assessment successfully submitted!");
        if(res.data && res.data.submission_id) {
          window.location.href = `cbt_view_result.php?id=${res.data.submission_id}`;
        } else {
          window.location.href = 'cbt_dashboard.php';
        }
      } else {
        examSubmitted = false;
        btn.disabled = false;
        btn.textContent = '🚀 Finish & Submit Exam';
        alert(res.message || "Failed to submit exam. Please try again.");
      }
    } catch(err) {
      examSubmitted = false;
      btn.disabled = false;
      btn.textContent = '🚀 Finish & Submit Exam';
      alert("Network error submitting exam. Please check your connection.");
    }
  }

  function confirmExit() {
    if(confirm("Are you sure you want to cancel and exit? Any answers you have entered will be discarded.")) {
      examSubmitted = true;
      window.location.href = 'cbt_dashboard.php';
    }
  }
</script>
</body>
</html>
