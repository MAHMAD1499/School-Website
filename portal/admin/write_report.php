<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_admin_auth();
function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);$c->set_charset('utf8mb4');return $c;}

$student_id = intval($_GET['student_id']??0);
if(!$student_id) die('Student ID required.');
$r = ksm_db()->query("SELECT name FROM users_students WHERE id=$student_id");
if(!$r || $r->num_rows===0) die('Student not found.');
$student_name = $r->fetch_assoc()['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="pageTitle">Montessori Monthly Progress Report</title>
    <link rel="stylesheet" href="../assets/portal.css">
    <link rel="stylesheet" href="../assets/report_style.css">
    <style>

      .action-toolbar { background: white; padding: 10px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; gap: 10px; justify-content: center; }
      .btn { padding: 10px 20px; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; color: white; background: var(--primary, #3b82f6); }
      .btn-success { background: #10B981; }
      .btn-outline { background: transparent; border: 1px solid #ccc; color: #333; }
    </style>
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <span class="topbar-title">Montessori Report</span>
      </div>
      <div class="topbar-right">
        <button class="btn btn-sm btn-danger" onclick="window.location.href='login.php'">Logout</button>
      </div>
    </div>
    <div class="portal-content fade-up" style="background: #f0f2f5;">

    <!-- Print Button Toolbar -->
    <div class="action-toolbar">
        <button class="btn btn-outline" onclick="window.location.href='student_reports.php'">Back</button> <button class="btn" onclick="window.print()">Print / Save as PDF</button> <button class="btn btn-success" onclick="submitToPortal()">Submit to Student Portal</button>
    </div>

    <!-- PAGE 1 -->
    <div class="page-container">
        <!-- School Header & Logo -->
        <div class="school-header">
            <img src="../assets/report_logo.svg" alt="School Logo" class="school-logo">
            <span>Kindergarten Saadia's Montessori</span>
        </div>

        <!-- Header -->
        <div class="report-header">
            <div class="report-title-row">
                <h1>Monthly Progress Report</h1>
            </div>
            
            <!-- Attendance Helper Box (Hidden on Print) -->
            <div class="attendance-calc-box">
                <span class="calc-title">Attendance Auto-Calculator:</span>
                <div class="calc-inputs">
                    <label>Total Month Days: <input type="number" id="totalDays" value="20" oninput="calculateAttendance()"></label>
                    <label>Days Present: <input type="number" id="presentDays" value="19" oninput="calculateAttendance()"></label>
                    <label>Days Late (Tardy): <input type="number" id="tardyDays" value="1" oninput="calculateAttendance()"></label>
                </div>
            </div>

            <div class="meta-grid">
                <div class="meta-field">
                    <label>Student's Name:</label>
                    <input type="text" id="studentNameInput" placeholder="Enter student name" oninput="updateDocumentTitle()" value="<?php echo htmlspecialchars($student_name, ENT_QUOTES); ?>">
                </div>
                <div class="meta-field">
                    <label>D.O.B.:</label>
                    <input type="text" placeholder="DD/MM/YYYY">
                </div>
                <div class="meta-field">
                    <label>Month / Year:</label>
                    <input type="text" placeholder="e.g., October 2026">
                </div>
            </div>
            <div class="meta-grid" style="margin-top: 6px;">
                <div class="meta-field">
                    <label>Teacher's Name:</label>
                    <input type="text" placeholder="Enter teacher name">
                </div>
                <div class="meta-field">
                    <label>Absent:</label>
                    <span class="attendance-display"><input type="text" id="absentOutput" value="1" readonly> days</span>
                </div>
                <div class="meta-field">
                    <label>Tardy:</label>
                    <span class="attendance-display"><input type="text" id="tardyOutput" value="1" readonly> days</span>
                </div>
            </div>
        </div>

        <!-- Evaluation Key -->
        <div class="eval-key">
            <span><strong>F</strong> = Frequently (Consistent)</span>
            <span><strong>O</strong> = Occasionally (Developing)</span>
            <span><strong>R</strong> = Rarely (Needs Support)</span>
        </div>

        <!-- Part 1 -->
        <h2>Part 1: Social Skills & Emotional Growth (Grace & Courtesy)</h2>
        <div class="section-desc">Evaluates emotional expression, peer relations, and integration into community routines.</div>
        <table>
            <thead>
                <tr>
                    <th class="col-item">Behavior / Skill</th>
                    <th class="col-rating">Monthly Rating</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Expresses needs and feelings clearly using words</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Adapts easily to changes and classroom transitions</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Participates warmly in group routines (morning greetings, circle time)</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Helps keep the classroom clean and cares for the environment</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Shares, takes turns, and plays cooperatively with peers</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Handles frustrations calmly and works through conflicts peacefully</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Asks a teacher or friend for help when needed</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Shows good manners and polite habits (please, thank you, greeting)</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- PAGE 2 (Part 2 & Part 3 + Comments) -->
    <div class="page-container page-break">
        <!-- School Header & Logo -->
        <div class="school-header">
            <img src="../assets/report_logo.svg" alt="School Logo" class="school-logo">
            <span>Kindergarten Saadia's Montessori</span>
        </div>

        <!-- Part 2 -->
        <h2>Part 2: Focus, Independence & Work Habits</h2>
        <div class="section-desc">Tracks concentration, choice of activities, and independent work styles in the classroom.</div>
        <table>
            <thead>
                <tr>
                    <th class="col-item">Behavior / Skill</th>
                    <th class="col-rating">Monthly Rating</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Chooses and begins activities independently</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Stays focused and deeply engaged on tasks</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Selects activities appropriate to learning level</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Listens carefully and follows multi-step instructions</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Sticks with tasks until completion and put away properly</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td>Respects other children's workspaces and materials</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
            </tbody>
        </table>

        <!-- Part 3 -->
        <h2>Part 3: Core Montessori Curriculum Progress</h2>
        <table>
            <thead>
                <tr>
                    <th class="col-item">Curriculum Area</th>
                    <th class="col-rating">Monthly Rating</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Practical Life:</strong> Coordination, concentration, and independence through everyday tasks</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td><strong>Sensorial:</strong> Refining senses via sorting shapes, textures, and dimensions</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td><strong>Language & Literacy:</strong> Letter sounds, vocabulary, storytelling, and writing readiness</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
                <tr>
                    <td><strong>Mathematics:</strong> Counting, number values, patterns, and quantities with hands-on materials</td>
                    <td class="col-rating"><select class="rating-select"><option value="">-</option><option value="F">F</option><option value="O">O</option><option value="R">R</option></select></td>
                </tr>
            </tbody>
        </table>

        <!-- Comments -->
        <div class="comments-box" style="margin-top: 15px;">
            <label>Teacher's Monthly Comments & Observations:</label>
            <textarea style="height: 100px;" placeholder="Write specific observations regarding the student's progress, strengths, and growth for this month..."></textarea>
        </div>
    </div>

    <!-- JavaScript for Auto-Calculating Attendance & Dynamic PDF Naming -->
    <script>
        function calculateAttendance() {
            let total = parseInt(document.getElementById('totalDays').value) || 0;
            let present = parseInt(document.getElementById('presentDays').value) || 0;
            let tardy = parseInt(document.getElementById('tardyDays').value) || 0;

            let absent = total - present;
            if (absent < 0) absent = 0;

            document.getElementById('absentOutput').value = absent;
            document.getElementById('tardyOutput').value = tardy;
        }

        function updateDocumentTitle() {
            let name = document.getElementById('studentNameInput').value.trim();
            if (name !== "") {
                document.title = name + "_Monthly_Progress_Report";
            } else {
                document.title = "Montessori Monthly Progress Report";
            }
        }

        async function submitToPortal() {
            // Gather all input, select, textarea values
            const elements = document.querySelectorAll('.page-container input, .page-container select, .page-container textarea');
            const values = [];
            elements.forEach(el => values.push(el.value));
            
            const title = document.getElementById('pageTitle').innerText;
            const student_id = <?php echo $student_id; ?>;
            
            // Send to the API
            const payload = {
                action: 'submit_report_json',
                student_id: student_id,
                title: "Monthly Progress Report",
                report_text: JSON.stringify(values)
            };
            
            const res = await fetch('student_reports.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            
            const result = await res.json();
            if (result.success) {
                alert('Report sent to student portal successfully!');
                window.location.href = 'student_reports.php';
            } else {
                alert('Error: ' + result.message);
            }
        }

        calculateAttendance();
    </script>

    </div>
  </div>
</div>
<script src="../assets/portal.js"></script>
<script src="../assets/api.js"></script>
<script src="../assets/sidebar.js"></script>
<script>
  if(typeof buildSidebar === 'function') buildSidebar('admin');
</script>
</body>
</html>


