<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_admin_auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fee Slip — KSM Admin Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <style>
    .fees-container {
      display: grid;
      grid-template-columns: 1fr 2fr; /* Make slip preview larger */
      gap: 2rem;
      margin-top: 1rem;
    }
    @media (max-width: 1200px) {
      .fees-container {
        grid-template-columns: 1fr;
      }
    }
    .form-panel {
      background: linear-gradient(145deg, #ffffff, #f3f6fa);
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(30,58,138,0.1);
      border: 1px solid #e1e8f0;
      height: fit-content;
    }
    .form-group {
      margin-bottom: 1.25rem;
    }
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #1e293b;
      font-size: 0.95rem;
    }
    .form-control {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1.5px solid #cbd5e1;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background-color: #f8fafc;
      box-sizing: border-box;
    }
    .form-control:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
      outline: none;
      background-color: #ffffff;
    }
    .slip-panel {
      background: #fff;
      padding: 1rem;
      border-radius: 2px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      overflow-x: auto;
    }
    
    @media print {
      body * { visibility: hidden; }
      .slip-panel, .slip-panel * { visibility: visible; }
      .slip-panel {
        position: absolute; left: 0; top: 0;
        width: 100%; margin: 0; padding: 0;
        box-shadow: none; display: block;
      }
      .portal-sidebar, .portal-topbar, .form-panel { display: none !important; }
    }
  </style>
</head>
<body>
  <div class="portal-wrapper">
    <div class="portal-main">
      <div class="portal-topbar">
        <div class="topbar-left">
          <button class="menu-toggle" id="menuToggle">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="3" y1="6" x2="21" y2="6"/>
              <line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
          </button>
          <span class="topbar-title">Fee Slip</span>
        </div>
        <div class="topbar-right">
          <button class="btn btn-sm btn-danger" onclick="sessionStorage.removeItem('ksm_admin_auth'); window.location.href='login.php'">Logout</button>
        </div>
      </div>

      <div class="portal-content fade-up">
        <div style="margin-bottom:1rem;">
          <h1 style="font-size:1.5rem;color:var(--text-dark);">Generate 3-Copy Fee Slip</h1>
        </div>

        <div class="fees-container">
          <!-- Form Panel for Tri-Slip -->
          <div class="form-panel">
            <h3 style="margin-bottom:1.5rem;color:var(--primary-color);">Data Entry (Fee Slip)</h3>
            
            <div class="form-group">
              <label>Student's Name</label>
              <input type="text" id="fsInputName" class="form-control" placeholder="Enter student name" oninput="updateTriSlip()">
            </div>
            <div class="form-group">
              <label>Father's Name</label>
              <input type="text" id="fsInputFname" class="form-control" placeholder="Enter father's name" oninput="updateTriSlip()">
            </div>
            <div class="form-group">
              <label>Grade</label>
              <input type="text" id="fsInputGrade" class="form-control" placeholder="Enter grade" oninput="updateTriSlip()">
            </div>
            <div class="form-group">
              <label>Dated</label>
              <input type="text" id="fsInputDate" class="form-control" placeholder="e.g. 25-09-2026" oninput="updateTriSlip()">
            </div>
            <div class="form-group">
              <label>Tuition Fee</label>
              <input type="number" id="fsInputTuit" class="form-control" value="" oninput="updateTriSlip()">
            </div>
            <div class="form-group">
              <label>Annual dues Stationery 2026</label>
              <input type="number" id="fsInputStat" class="form-control" value="" oninput="updateTriSlip()">
            </div>
            <div class="form-group">
              <label>Uniform</label>
              <input type="number" id="fsInputUni" class="form-control" value="" oninput="updateTriSlip()">
            </div>
            <div class="form-group">
              <label>Arrears</label>
              <input type="number" id="fsInputArr" class="form-control" value="" oninput="updateTriSlip()">
            </div>
            <div class="form-group" style="margin-top: 1.5rem;">
              <button class="btn btn-primary" style="width:100%" onclick="downloadTriSlipPDF()">Download Fee Slip PDF</button>
            </div>
          </div>

          <!-- Preview Panel -->
          <div class="slip-panel">
            <div class="tri-slip-wrapper" style="width: 1040px; min-width: 1040px; background: #fff; padding: 12px; border: 4px double #000; display: flex; gap: 8px; box-sizing: border-box; font-family: 'Calibri', 'Arial', sans-serif; color: #000; z-index: 1;">
              
              <!-- PHP Loop for 3 Slips -->
              <?php for ($i = 0; $i < 3; $i++): ?>
              <div style="flex: 1; border: 2px solid #000; padding: 12px 10px; position: relative; z-index: 1; background: transparent;">
                  <!-- Sub-Watermark -->
                  <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 350px; height: 350px; background-image: url('../../assets/images/logo.svg'); background-repeat: no-repeat; background-position: center; background-size: contain; opacity: 0.18; z-index: -1; pointer-events: none;"></div>
                  
                  <!-- Header -->
                  <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 12px;">
                    <img src="../../assets/images/logo.svg" style="width: 65px; height: 65px;" alt="Logo">
                    <div style="text-align: center; font-weight: 700; font-size: 0.95rem; line-height: 1.3;">
                      <div>Kindergarten Saadia's</div>
                      <div>Montessori School Haripur</div>
                    </div>
                  </div>

                  <!-- Details -->
                  <div style="font-size: 0.85rem; line-height: 2; font-weight: 700; margin-bottom: 10px;">
                    <div>Student's Name: <span class="fs-out-name" style="display:inline-block; border-bottom: 1px solid #000; min-width: 160px; font-weight: 600;"></span></div>
                    <div>Father's Name: <span class="fs-out-fname" style="display:inline-block; border-bottom: 1px solid #000; min-width: 165px; font-weight: 600;"></span></div>
                    <div>Grade: <span class="fs-out-grade" style="display:inline-block; border-bottom: 1px solid #000; min-width: 200px; font-weight: 600;"></span></div>
                    <div>Dated: <span class="fs-out-date" style="display:inline-block; border-bottom: 1px solid #000; min-width: 205px; font-weight: 600;"></span></div>
                  </div>

                  <!-- Table -->
                  <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 0.8rem; font-weight: 700; border: 2px solid #000; background: transparent !important;">
                    <thead>
                      <tr>
                        <th style="border: 1px solid #000; padding: 4px; text-align: left; width: 12%;">S.No</th>
                        <th style="border: 1px solid #000; padding: 4px; text-align: left; width: 58%;">Particulars</th>
                        <th style="border: 1px solid #000; padding: 4px; text-align: left; width: 30%;">Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td style="border: 1px solid #000; padding: 4px;">1-</td>
                        <td style="border: 1px solid #000; padding: 4px;">Tuition Fee</td>
                        <td style="border: 1px solid #000; padding: 4px;" class="fs-out-tuit"></td>
                      </tr>
                      <tr>
                        <td style="border: 1px solid #000; padding: 4px;">2-</td>
                        <td style="border: 1px solid #000; padding: 4px;">Annual dues<br>Stationery 2026</td>
                        <td style="border: 1px solid #000; padding: 4px;" class="fs-out-stat"></td>
                      </tr>
                      <tr>
                        <td style="border: 1px solid #000; padding: 4px;">3-</td>
                        <td style="border: 1px solid #000; padding: 4px;">Uniform</td>
                        <td style="border: 1px solid #000; padding: 4px;" class="fs-out-uni"></td>
                      </tr>
                      <tr>
                        <td style="border: 1px solid #000; padding: 4px;">4-</td>
                        <td style="border: 1px solid #000; padding: 4px;">Arrears</td>
                        <td style="border: 1px solid #000; padding: 4px;" class="fs-out-arr"></td>
                      </tr>
                      <tr>
                        <td style="border: 1px solid #000; padding: 4px;">5-</td>
                        <td style="border: 1px solid #000; padding: 4px;">Total</td>
                        <td style="border: 1px solid #000; padding: 4px;" class="fs-out-total"></td>
                      </tr>
                    </tbody>
                  </table>

                  <!-- Footer -->
                  <div style="font-size: 0.8rem; line-height: 2; font-weight: 700;">
                    <div>Received: <span style="display:inline-block; border-bottom: 1px solid #000; min-width: 150px;"></span></div>
                    <div>Balance: <span style="display:inline-block; border-bottom: 1px solid #000; min-width: 155px;"></span></div>
                    
                    <div style="margin-top: 15px;">(Online Payment) Saadia Tariq</div>
                    <div>Account No: 1721043185210012</div>
                    <div>(HBL Micro Finance Bank)</div>
                    <div style="margin-top: 5px;">Easypaisa number: 03135620045</div>

                    <div style="margin-top: 15px;">Principal's Signature: <span style="display:inline-block; border-bottom: 1px solid #000; min-width: 90px;"></span></div>
                    <div>School Stamp: <span style="display:inline-block; border-bottom: 1px solid #000; min-width: 120px;"></span></div>
                  </div>
              </div>
              <?php endfor; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script src="../assets/portal.js"></script>
  <script src="../assets/sidebar.js"></script>
  <script>
    buildSidebar('admin');
    
    function downloadTriSlipPDF() {
      window.scrollTo(0, 0);
      const element = document.querySelector('.tri-slip-wrapper');
      const studentName = document.getElementById('fsInputName').value.trim() || 'Student';
      const opt = {
        margin:       0.2,
        filename:     `Fee_Slip_${studentName}.pdf`,
        image:        { type: 'jpeg', quality: 1 },
        html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
      };
      html2pdf().set(opt).from(element).save();
    }

    function updateTriSlip() {
      const name = document.getElementById('fsInputName').value;
      const fname = document.getElementById('fsInputFname').value;
      const grade = document.getElementById('fsInputGrade').value;
      const date = document.getElementById('fsInputDate').value;
      
      const tuit = parseFloat(document.getElementById('fsInputTuit').value) || 0;
      const stat = parseFloat(document.getElementById('fsInputStat').value) || 0;
      const uni = parseFloat(document.getElementById('fsInputUni').value) || 0;
      const arr = parseFloat(document.getElementById('fsInputArr').value) || 0;
      const total = tuit + stat + uni + arr;

      document.querySelectorAll('.fs-out-name').forEach(el => el.textContent = name);
      document.querySelectorAll('.fs-out-fname').forEach(el => el.textContent = fname);
      document.querySelectorAll('.fs-out-grade').forEach(el => el.textContent = grade);
      document.querySelectorAll('.fs-out-date').forEach(el => el.textContent = date);
      
      document.querySelectorAll('.fs-out-tuit').forEach(el => el.textContent = tuit ? tuit : '');
      document.querySelectorAll('.fs-out-stat').forEach(el => el.textContent = stat ? stat : '');
      document.querySelectorAll('.fs-out-uni').forEach(el => el.textContent = uni ? uni : '');
      document.querySelectorAll('.fs-out-arr').forEach(el => el.textContent = arr ? arr : '');
      document.querySelectorAll('.fs-out-total').forEach(el => el.textContent = total ? total : '');
    }
  </script>
</body>
</html>
