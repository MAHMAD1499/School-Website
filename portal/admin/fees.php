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
      background: linear-gradient(145deg, #ffffff, #f3f6fa);
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(30,58,138,0.1);
      border: 1px solid #e1e8f0;
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
    }
    .form-control:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
      outline: none;
      background-color: #ffffff;
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
      z-index: 1;
      overflow: hidden;
    }
    .slip-wrapper > * {
      position: relative;
      z-index: 1;
    }
    .slip-wrapper::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 750px;
      height: 750px;
      background-image: url('../../assets/images/logo.svg');
      background-repeat: no-repeat;
      background-position: center;
      background-size: contain;
      opacity: 0.22;
      z-index: 0;
      pointer-events: none;
    }
    .slip-wrapper::before {
      content: '';
      position: absolute;
      top: 6px; bottom: 6px; left: 6px; right: 6px;
      border: 1px solid #aaa;
      pointer-events: none;
    }
    .slip-header {
      margin-bottom: 2.5rem;
    }
    /* slip-logo removed as we use inline styles with flexbox now */
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
      font-size: 1.6rem;
      font-weight: 700;
      color: #222;
      text-align: left;
    }
    .slip-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1.5rem;
      background: transparent !important;
    }
    .slip-table th, .slip-table td {
      border: 1px solid #555;
      padding: 1.4rem 1rem;
      font-size: 1.35rem;
      text-align: left;
    }
    .slip-table tbody tr:last-child td,
    .slip-table tr:last-child td {
      border-bottom: 1px solid #555 !important;
    }
    .slip-table td {
      color: #111;
      font-weight: 700;
    }
    .slip-table tr td:first-child {
      width: 65%;
      color: #222;
      font-weight: 800;
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
        <div style="margin-bottom:1rem;">
          <h1 style="font-size:1.5rem;color:var(--text-dark);">Generate Fee Structure</h1>
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
            <div class="form-group">
              <label>Form Fee</label>
              <input type="number" id="inputForm" class="form-control" value="1000" oninput="updateSlip()">
            </div>
            <div class="form-group">
              <label>Uniform Fee</label>
              <input type="number" id="inputUniform" class="form-control" value="5000" oninput="updateSlip()">
            </div>
            <div class="form-group" style="margin-top: 1.5rem;">
              <button class="btn btn-primary" style="width:100%" onclick="downloadPDF()">Download Form</button>
            </div>
          </div>

          <!-- Slip Panel -->
          <div class="slip-panel">
            <div class="slip-wrapper">
              <div class="slip-header" style="display: flex; justify-content: center; align-items: center;">
                <img src="../../assets/images/logo.svg" alt="KSM" style="width: 110px; height: 110px; margin-right: 20px;">
                <div style="text-align: center;">
                  <h2 style="margin: 0; font-size: 1.6rem; font-weight: 800; color: #111; white-space: nowrap;">
                    Kindergarten Saadia's Montessori School
                  </h2>
                  <h3 style="margin: 8px 0 0 0; font-size: 1.3rem; font-weight: 600; color: #333;">Haripur</h3>
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
                  <td>Form Fee</td>
                  <td id="slipForm">1000</td>
                </tr>
                <tr>
                  <td>Uniform Fee</td>
                  <td id="slipUniform">5000</td>
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
      const formFee = parseFloat(document.getElementById('inputForm').value) || 0;
      const uniformFee = parseFloat(document.getElementById('inputUniform').value) || 0;

      document.getElementById('slipName').textContent = name;
      document.getElementById('slipClass').textContent = cls;
      document.getElementById('slipReg').textContent = reg ? reg : '';
      document.getElementById('slipStat').textContent = stat ? stat : '';
      document.getElementById('slipTuit').textContent = tuit ? tuit : '';
      document.getElementById('slipForm').textContent = formFee ? formFee : '';
      document.getElementById('slipUniform').textContent = uniformFee ? uniformFee : '';
      
      const total = reg + stat + tuit + formFee + uniformFee;
      document.getElementById('slipTotal').textContent = total ? total : '';
    }
    
    // Initial call to set total correctly based on default values
    updateSlip();
  </script>
</body>
</html>
