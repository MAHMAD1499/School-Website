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
      grid-template-columns: 1fr 2fr;
      /* Make slip preview larger */
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
      box-shadow: 0 10px 25px rgba(30, 58, 138, 0.1);
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
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
      outline: none;
      background-color: #ffffff;
    }

    .slip-panel {
      background: #fff;
      padding: 1rem;
      border-radius: 2px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      overflow-x: auto;
    }

    @media print {
      body * {
        visibility: hidden;
      }

      .slip-panel,
      .slip-panel * {
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

      .portal-sidebar,
      .portal-topbar,
      .form-panel {
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
              <line x1="3" y1="6" x2="21" y2="6" />
              <line x1="3" y1="12" x2="21" y2="12" />
              <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
          </button>
          <span class="topbar-title">Fee Slip</span>
        </div>
        <div class="topbar-right">
          <button class="btn btn-sm btn-danger"
            onclick="sessionStorage.removeItem('ksm_admin_auth'); window.location.href='login.php'">Logout</button>
        </div>
      </div>

      <div class="portal-content fade-up">
        <div style="margin-bottom:1rem;">
          <h1 style="font-size:1.5rem;color:var(--text-dark);">Generate 2-Copy Fee Slip</h1>
        </div>

        <div class="fees-container">
          <!-- Form Panel for Tri-Slip -->
          <div class="form-panel">
            <h3 style="margin-bottom:1.5rem;color:var(--primary-color);">Data Entry (Fee Slip)</h3>

            <div class="form-group" style="padding: 1rem; background: #f8fafc; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #cbd5e1;">
              <label style="color:#0f172a; font-weight: 600; margin-bottom: 0.5rem;">⚡ Quick Fill (Select Student)</label>
              <select id="quickStudentSelect" class="form-control" onchange="fillStudentData()">
                <option value="">-- Manual Entry --</option>
              </select>
            </div>

            <div class="form-group">
              <label>Student's Name</label>
              <input type="text" id="fsInputName" class="form-control" placeholder="Enter student name"
                oninput="updateTriSlip()">
            </div>
            <div class="form-group">
              <label>Father's Name</label>
              <input type="text" id="fsInputFname" class="form-control" placeholder="Enter father's name"
                oninput="updateTriSlip()">
            </div>
            <div class="form-group">
              <label>Grade</label>
              <input type="text" id="fsInputGrade" class="form-control" placeholder="Enter grade"
                oninput="updateTriSlip()">
            </div>
            <div class="form-group">
              <label>Dated</label>
              <input type="date" id="fsInputDate" class="form-control"
                oninput="updateTriSlip()">
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
              <button class="btn btn-primary" style="width:100%" onclick="downloadTriSlipPDF()">Download Fee Slip
                PDF</button>
            </div>
            
            <hr style="margin: 2rem 0; border: none; border-top: 1px solid #cbd5e1;">

            <h3 style="margin-bottom:1.5rem;color:var(--primary-color);">Bulk Class Download</h3>
            <div class="form-group">
              <label>Select Class</label>
              <select id="bulkClassSelect" class="form-control" onchange="loadClassStudentsForBulk()">
                <option value="">-- Choose Class --</option>
                <option value="Playgroup">Playgroup</option>
                <option value="Nursery">Nursery</option>
                <option value="Prep">Prep</option>
                <option value="Grade One">Grade One</option>
                <option value="Grade Two">Grade Two</option>
                <option value="Grade Three">Grade Three</option>
                <option value="Grade Four">Grade Four</option>
                <option value="Grade Five">Grade Five</option>
              </select>
            </div>
            
            <div id="bulkStudentsList" style="margin-top: 1rem; max-height: 280px; overflow-y: auto; display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem;">
            </div>

            <div class="form-group" style="margin-top: 1.5rem;">
              <button class="btn btn-primary" style="width:100%; background: #10b981; border-color: #10b981;" onclick="downloadBulkSlips()" id="bulkBtn">Download Class Slips</button>
            </div>
          </div>

          <!-- Preview Panel -->
          <div class="slip-panel">
            <div class="tri-slip-wrapper"
              style="width: 720px; min-width: 720px; margin: 0 auto; background: #fff; padding: 12px; border: 4px double #000; display: flex; gap: 8px; box-sizing: border-box; font-family: 'Calibri', 'Arial', sans-serif; color: #000; z-index: 1;">

              <!-- PHP Loop for 2 Slips -->
              <?php for ($i = 0; $i < 2; $i++): ?>
                <div
                  style="flex: 1; border: 2px solid #000; padding: 12px 10px; position: relative; z-index: 1; background: transparent;">
                  <!-- Sub-Watermark -->
                  <div
                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 300px; height: 300px; background-image: url('../../assets/images/logo.svg'); background-repeat: no-repeat; background-position: center; background-size: contain; opacity: 0.15; z-index: -1; pointer-events: none;">
                  </div>

                  <!-- Header -->
                  <div
                    style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 12px;">
                    <img src="../../assets/images/logo.svg" style="width: 65px; height: 65px;" alt="Logo">
                    <div style="text-align: center; font-weight: 700; font-size: 0.95rem; line-height: 1.3;">
                      <div>Kindergarten Saadia's</div>
                      <div>Montessori School Haripur</div>
                      <div style="margin-top: 5px; font-weight: 800; text-decoration: underline; letter-spacing: 1px; color: #000;">
                        <?= $i === 0 ? 'STUDENT SLIP' : 'ADMIN SLIP' ?>
                      </div>
                    </div>
                  </div>

                  <!-- Details -->
                  <div style="font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; align-items: flex-end;">
                      <div style="width: 115px;">Student's Name:</div>
                      <div class="fs-out-name" style="flex: 1; border-bottom: 1px solid #000; font-weight: 600; padding-left: 5px; min-height: 16px;"></div>
                    </div>
                    <div style="display: flex; align-items: flex-end;">
                      <div style="width: 115px;">Father's Name:</div>
                      <div class="fs-out-fname" style="flex: 1; border-bottom: 1px solid #000; font-weight: 600; padding-left: 5px; min-height: 16px;"></div>
                    </div>
                    <div style="display: flex; align-items: flex-end;">
                      <div style="width: 115px;">Grade:</div>
                      <div class="fs-out-grade" style="flex: 1; border-bottom: 1px solid #000; font-weight: 600; padding-left: 5px; min-height: 16px;"></div>
                    </div>
                    <div style="display: flex; align-items: flex-end;">
                      <div style="width: 115px;">Dated:</div>
                      <div class="fs-out-date" style="flex: 1; border-bottom: 1px solid #000; font-weight: 600; padding-left: 5px; min-height: 16px;"></div>
                    </div>
                  </div>

                  <!-- Table -->
                  <table
                    style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 0.8rem; font-weight: 700; border: 2px solid #000; background: transparent !important;">
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
                    <div>Received: <span
                        style="display:inline-block; border-bottom: 1px solid #000; min-width: 150px;"></span></div>
                    <div>Balance: <span
                        style="display:inline-block; border-bottom: 1px solid #000; min-width: 155px;"></span></div>

                    <div style="margin-top: 15px;">(Online Payment) Saadia Tariq</div>
                    <div>Account No: 1721043185210012</div>
                    <div>(HBL Micro Finance Bank)</div>
                    <div style="margin-top: 5px;">Easypaisa number: 03135620045</div>

                    <div style="margin-top: 15px;">Principal's Signature: <span
                        style="display:inline-block; border-bottom: 1px solid #000; min-width: 90px;"></span></div>
                    <div>School Stamp: <span
                        style="display:inline-block; border-bottom: 1px solid #000; min-width: 120px;"></span></div>
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
  <script src="../assets/api.js?v=4"></script>
  <script src="../assets/portal.js"></script>
  <script src="../assets/sidebar.js"></script>
  <script>
    buildSidebar('admin');

    let allStudentsForFee = [];
    window.addEventListener('DOMContentLoaded', async () => {
        // Auto-fill today's date
        const dateInput = document.getElementById('fsInputDate');
        if (!dateInput.value) {
            dateInput.value = new Date().toISOString().split('T')[0];
            updateTriSlip();
        }

        try {
            const res = await API.getStudents();
            allStudentsForFee = res.data || [];
            const select = document.getElementById('quickStudentSelect');
            allStudentsForFee.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = `${s.name} (${s.class || 'No Class'}) - ${s.rollNo || 'N/A'}`;
                select.appendChild(opt);
            });
        } catch(e) { console.error(e); }
    });

    function fillStudentData() {
        const id = document.getElementById('quickStudentSelect').value;
        if (!id) {
          // Clear if needed, or leave manual inputs
          return;
        }
        const student = allStudentsForFee.find(s => String(s.id) === String(id));
        if (!student) return;

        document.getElementById('fsInputName').value = student.name || '';
        document.getElementById('fsInputFname').value = student.parentName || '';
        document.getElementById('fsInputGrade').value = student.class || '';

        if (student.tuition_fee) document.getElementById('fsInputTuit').value = student.tuition_fee;
        if (student.annual_dues) document.getElementById('fsInputStat').value = student.annual_dues;

        updateTriSlip();
    }

    let currentBulkStudents = [];

    async function loadClassStudentsForBulk() {
      const classSelect = document.getElementById('bulkClassSelect').value;
      const listContainer = document.getElementById('bulkStudentsList');
      if (!classSelect) {
        listContainer.style.display = 'none';
        currentBulkStudents = [];
        return;
      }
      
      try {
        const res = await API.getStudents();
        currentBulkStudents = (res.data || []).filter(s => s.class === classSelect);
        
        if (currentBulkStudents.length === 0) {
          listContainer.innerHTML = '<p style="color:var(--text-medium); margin:0;">No students found in this class.</p>';
          listContainer.style.display = 'block';
          return;
        }
        
        let html = `
          <input type="text" id="bulkStudentSearch" class="form-control" placeholder="Search student by name or roll no..." style="margin-bottom: 10px; font-size: 0.9rem; padding: 0.5rem;" oninput="filterBulkStudentsList()">
          <div id="bulkStudentsRows" style="display:flex; flex-direction:column; gap: 0.5rem;">
        `;
        currentBulkStudents.forEach((s, idx) => {
          html += `
            <div class="bulk-student-row" data-name="${(s.name || '').toLowerCase()}" data-roll="${(s.rollNo || '').toLowerCase()}" style="display:flex; justify-content:space-between; align-items:center; padding: 0.5rem; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px;">
              <div style="font-weight:600; font-size: 0.85rem; color:#1e293b; max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                ${s.name} <br><span style="font-size:0.75rem; color:#64748b; font-weight:normal;">(${s.rollNo || 'N/A'})</span>
              </div>
              <div style="display:flex; align-items:center; gap: 0.25rem;">
                <label style="font-size: 0.75rem; margin:0; color:#475569;">Arrears:</label>
                <input type="number" id="bulkArr_${idx}" class="form-control" style="width: 70px; padding: 0.25rem; font-size: 0.85rem; height: auto;" placeholder="0">
              </div>
            </div>
          `;
        });
        html += '</div>';
        
        listContainer.innerHTML = html;
        listContainer.style.display = 'block';
        
      } catch(e) {
        console.error('Error loading students for bulk:', e);
      }
    }

    function filterBulkStudentsList() {
      const query = document.getElementById('bulkStudentSearch').value.toLowerCase();
      document.querySelectorAll('.bulk-student-row').forEach(row => {
        const name = row.getAttribute('data-name');
        const roll = row.getAttribute('data-roll');
        if (name.includes(query) || roll.includes(query)) {
          row.style.display = 'flex';
        } else {
          row.style.display = 'none';
        }
      });
    }

    async function downloadBulkSlips() {
      const classSelect = document.getElementById('bulkClassSelect').value;
      if (!classSelect) return alert('Please select a class first.');
      if (currentBulkStudents.length === 0) return alert('No students to process.');

      const btn = document.getElementById('bulkBtn');
      btn.disabled = true;

      // Save global values to restore them later if needed
      const globalArrears = document.getElementById('fsInputArr').value;
      const globalTuit = document.getElementById('fsInputTuit').value;
      const globalStat = document.getElementById('fsInputStat').value;

      try {
        for (let i = 0; i < currentBulkStudents.length; i++) {
          const student = currentBulkStudents[i];
          btn.textContent = `Downloading ${i + 1} / ${currentBulkStudents.length}...`;
          
          document.getElementById('fsInputName').value = student.name || '';
          document.getElementById('fsInputFname').value = student.parentName || '';
          document.getElementById('fsInputGrade').value = student.class || '';
          
          const specificArr = document.getElementById(`bulkArr_${i}`)?.value;
          if (specificArr !== "" && specificArr !== undefined) {
             document.getElementById('fsInputArr').value = specificArr;
          } else {
             document.getElementById('fsInputArr').value = '0'; // default to 0 if not provided
          }

          if (student.tuition_fee && parseFloat(student.tuition_fee) > 0) {
              document.getElementById('fsInputTuit').value = student.tuition_fee;
          } else {
              document.getElementById('fsInputTuit').value = globalTuit;
          }

          if (student.annual_dues && parseFloat(student.annual_dues) > 0) {
              document.getElementById('fsInputStat').value = student.annual_dues;
          } else {
              document.getElementById('fsInputStat').value = globalStat;
          }
          
          updateTriSlip();
          
          window.scrollTo(0, 0);
          const element = document.querySelector('.tri-slip-wrapper');
          const safeName = (student.name || 'Student').replace(/[^a-zA-Z0-9 ]/g, "");
          const opt = {
            margin: 0.2,
            filename: `${safeName}-fee-slip.pdf`,
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
          };
          
          await html2pdf().set(opt).from(element).save();
          // Short delay to allow the browser to process the download event without hanging
          await new Promise(r => setTimeout(r, 800)); 
        }
        
        alert('Bulk download complete!');
      } catch (err) {
        console.error(err);
        alert('An error occurred during bulk download.');
      } finally {
        // Restore globals and button state
        document.getElementById('fsInputArr').value = globalArrears;
        document.getElementById('fsInputTuit').value = globalTuit;
        document.getElementById('fsInputStat').value = globalStat;
        updateTriSlip();
        btn.disabled = false;
        btn.textContent = 'Download Class Slips';
      }
    }

    function downloadTriSlipPDF() {
      window.scrollTo(0, 0);
      const element = document.querySelector('.tri-slip-wrapper');
      const studentName = document.getElementById('fsInputName').value.trim() || 'Student';
      const safeName = studentName.replace(/[^a-zA-Z0-9 ]/g, "");
      const opt = {
        margin: 0.2,
        filename: `${safeName}-fee-slip.pdf`,
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
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