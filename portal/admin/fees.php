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
  <title>Fees Structure — KSM Admin Portal</title>
  <link rel="stylesheet" href="../assets/portal.css">
  <style>
    .fees-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
      margin-top: 1rem;
    }
    @media (max-width: 900px) {
      .fees-container {
        grid-template-columns: 1fr;
      }
    }
    .form-panel {
      background: var(--surface-color, #fff);
      padding: 1.5rem;
      border-radius: var(--radius-md, 8px);
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      border: 1px solid var(--border-color, #eee);
    }
    .form-group {
      margin-bottom: 1rem;
    }
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--text-dark, #333);
    }
    .form-control {
      width: 100%;
      padding: 0.6rem;
      border: 1px solid var(--border-color, #ccc);
      border-radius: 4px;
      font-size: 1rem;
    }
    
    /* Exact Replication of the slip */
    .slip-panel {
      background: #fff;
      padding: 1rem;
      border-radius: 2px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      position: relative;
      display: flex;
      justify-content: center;
    }
    .slip-wrapper {
      position: relative;
      border: 2px solid #666;
      padding: 3rem 2rem 2rem 2rem;
      font-family: 'Calibri', 'Arial', sans-serif;
      color: #333;
      background: #fff;
      width: 100%;
      max-width: 700px;
      min-height: 700px;
      box-sizing: border-box;
    }
    .slip-wrapper::before {
      content: '';
      position: absolute;
      top: 6px; bottom: 6px; left: 6px; right: 6px;
      border: 1px solid #aaa;
      pointer-events: none;
    }
    .slip-header {
      position: relative;
      margin-bottom: 2.5rem;
      text-align: center;
    }
    .slip-logo {
      position: absolute;
      right: 100%;
      margin-right: 25px;
      top: 50%;
      transform: translateY(-50%);
      width: 85px;
      height: 85px;
    }
    .slip-title h2 {
      margin: 0;
      font-size: 1.35rem;
      font-weight: normal;
      color: #222;
    }
    .slip-title h3 {
      margin: 8px 0 25px 0;
      font-size: 1.2rem;
      font-weight: normal;
      color: #333;
    }
    .slip-subtitle {
      margin: 0 0 1.5rem 0;
      font-size: 1.4rem;
      font-weight: 600;
      color: #444;
      text-align: left;
    }
    .slip-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1.5rem;
    }
    .slip-table th, .slip-table td {
      border: 1px solid #777;
      padding: 1.2rem 1rem;
      font-size: 1.2rem;
      text-align: left;
    }
    .slip-table tbody tr:last-child td,
    .slip-table tr:last-child td {
      border-bottom: 1px solid #777 !important;
    }
    .slip-table td {
      color: #333;
      font-weight: 600;
    }
    .slip-table tr td:first-child {
      width: 65%;
      color: #444;
      font-weight: 600;
    }

    /* Print styles */
    @media print {
      body * {
        visibility: hidden;
      }
      .slip-panel, .slip-panel * {
        visibility: visible;
      }
      .slip-panel {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none;
        display: block;
      }
      .slip-wrapper {
        border: 2px solid #333;
        outline: 1px solid #777;
        outline-offset: -6px;
        padding: 3rem 2rem;
        width: 100%;
        max-width: none;
        height: 100%;
      }
      .portal-sidebar, .portal-topbar, .form-panel {
        display: none !important;
      }
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
          <span class="topbar-title">Fees Structure</span>
        </div>
        <div class="topbar-right">
          <button class="btn btn-sm btn-danger" onclick="sessionStorage.removeItem('ksm_admin_auth'); window.location.href='login.php'">Logout</button>
        </div>
      </div>

      <div class="portal-content fade-up">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
          <h1 style="font-size:1.5rem;color:var(--text-dark);">Generate Fee Structure</h1>
          <button class="btn btn-primary" onclick="downloadPDF()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:0.5rem;vertical-align:-3px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Download PDF
          </button>
        </div>

        <div class="fees-container">
          <!-- Form Panel -->
          <div class="form-panel">
            <h3 style="margin-bottom:1.5rem;color:var(--primary-color);">Data Entry</h3>
            
            <div class="form-group">
              <label>Student Name</label>
              <input type="text" id="inputName" class="form-control" placeholder="Enter student name" oninput="updateSlip()">
            </div>
            <div class="form-group">
              <label>Class</label>
              <input type="text" id="inputClass" class="form-control" placeholder="Enter class" oninput="updateSlip()">
            </div>
            <div class="form-group">
              <label>Registration Fee (Once)</label>
              <input type="number" id="inputReg" class="form-control" value="10000" oninput="updateSlip()">
            </div>
            <div class="form-group">
              <label>Stationery Fee (Once in a year)</label>
              <input type="number" id="inputStat" class="form-control" value="10000" oninput="updateSlip()">
            </div>
            <div class="form-group">
              <label>Tuition Fee (Monthly)</label>
              <input type="number" id="inputTuit" class="form-control" value="7000" oninput="updateSlip()">
            </div>
            <div class="form-group" style="margin-top: 1.5rem;">
              <button class="btn btn-primary" style="width:100%" onclick="downloadPDF()">Download Form</button>
            </div>
          </div>

          <!-- Slip Panel -->
          <div class="slip-panel">
            <div class="slip-wrapper">
              <div class="slip-header" style="text-align: center; margin-bottom: 2.5rem;">
                <div style="display: inline-block; text-align: center;">
                  <h2 style="position: relative; margin: 0; font-size: 1.2rem; font-weight: 600; color: #222; white-space: nowrap;">
                    <!-- Logo vertically centered precisely with the school name -->
                    <img src="../../assets/images/logo.svg" alt="KSM" class="slip-logo" onerror="this.src='../assets/images/logo.svg'">
                    Kindergarten Saadia's Montessori School
                  </h2>
                  <h3 style="margin: 8px 0 0 0; font-size: 1.1rem; font-weight: normal; color: #333;">Haripur</h3>
                </div>
              </div>
              <h4 class="slip-subtitle">Fee Structure</h4>
              <table class="slip-table">
                <tr>
                  <td>Name</td>
                  <td id="slipName"></td>
                </tr>
                <tr>
                  <td>Class</td>
                  <td id="slipClass"></td>
                </tr>
                <tr>
                  <td>Registration Once</td>
                  <td id="slipReg">10000</td>
                </tr>
                <tr>
                  <td>Stationery once in a year</td>
                  <td id="slipStat">10000</td>
                </tr>
                <tr>
                  <td>Tuition Fee/Monthly Fee</td>
                  <td id="slipTuit">7000</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id="slipTotal">27000</td>
                </tr>
              </table>
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

    function downloadPDF() {
      window.scrollTo(0, 0);
      const element = document.querySelector('.slip-wrapper');
      const studentName = document.getElementById('inputName').value.trim() || 'Student';
      const opt = {
        margin:       0.5,
        filename:     `Fee_Structure_${studentName}.pdf`,
        image:        { type: 'jpeg', quality: 1 },
        html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
      };
      html2pdf().set(opt).from(element).save();
    }

    function updateSlip() {
      const name = document.getElementById('inputName').value;
      const cls = document.getElementById('inputClass').value;
      const reg = parseFloat(document.getElementById('inputReg').value) || 0;
      const stat = parseFloat(document.getElementById('inputStat').value) || 0;
      const tuit = parseFloat(document.getElementById('inputTuit').value) || 0;

      document.getElementById('slipName').textContent = name;
      document.getElementById('slipClass').textContent = cls;
      document.getElementById('slipReg').textContent = reg ? reg : '';
      document.getElementById('slipStat').textContent = stat ? stat : '';
      document.getElementById('slipTuit').textContent = tuit ? tuit : '';
      
      const total = reg + stat + tuit;
      document.getElementById('slipTotal').textContent = total ? total : '';
    }
    
    // Initial call to set total correctly based on default values
    updateSlip();
  </script>
</body>
</html>
