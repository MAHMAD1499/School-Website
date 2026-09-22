<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth.php';
check_admin_auth();

$id = intval($_GET['id'] ?? 0);
if (!$id) die("Invalid ID.");

$conn = new mysqli($_ksm['host'], $_ksm['user'], $_ksm['pass'], $_ksm['name']);
if ($conn->connect_error) die("Database connection failed.");
$conn->set_charset('utf8mb4');

$stmt = $conn->prepare("SELECT * FROM admissions WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$record = $res->fetch_assoc();

if (!$record) die("Application not found.");

// Resolve correct column names
$child_name   = htmlspecialchars($record['child_name'] ?? '');
$dob          = htmlspecialchars($record['dob'] ?? '');
$blood_group  = htmlspecialchars($record['blood_group'] ?? '');
$program      = htmlspecialchars($record['class_applied'] ?? '');
$medical      = htmlspecialchars($record['message'] ?? '');
$parent_name  = htmlspecialchars($record['parent_name'] ?? '');
$occupation   = htmlspecialchars($record['address'] ?? '');
$phone        = htmlspecialchars($record['phone'] ?? '');
$email        = htmlspecialchars($record['email'] ?? '');
$signature    = htmlspecialchars($record['prior_school'] ?? '');
$status       = htmlspecialchars($record['status'] ?? 'Pending');
$submitted    = htmlspecialchars($record['submittedAt'] ?? '');
$passport_url = $record['passport_photo_url'] ?? '';
$id_card_url  = $record['id_card_url'] ?? '';
$birth_cert_url = $record['birth_cert_url'] ?? '';
$photos_url   = $record['photos_url'] ?? '';

// Build absolute URL base for uploaded files
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST'];
// admissions.php is at /School-Website-main/portal/admin/, uploads are at /School-Website-main/uploads/
$site_root = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$uploads_base = $base_url . $site_root . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Form — <?php echo $child_name; ?></title>
    <style>
        @page { size: A4 portrait; margin: 0.5cm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #e2e6ec;
            color: #5c6b8c;
            font-size: 13px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .paper-form-container {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            background: #e2e6ec;
        }
        /* Header */
        .pf-header {
            background: #df5349 !important;
            color: #fff;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pf-header-left { display: flex; align-items: center; gap: 1rem; }
        .pf-logo-text { display: flex; flex-direction: column; }
        .pf-logo-text h1 { margin: 0; font-size: 1.7rem; font-weight: normal; color: #fff; }
        .pf-logo-text h2 { margin: 0; font-size: 1.1rem; font-weight: normal; color: #fff; }
        .pf-header-right {
            background: #fff;
            color: #606e89;
            padding: 0.3rem 1rem;
            font-weight: bold;
            font-size: 1rem;
            border-radius: 2px;
        }
        /* Sub-header */
        .pf-sub-header {
            background: #fff !important;
            padding: 0.9rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .pf-sub-text h3 {
            color: #df5349;
            font-size: 1.1rem;
            margin: 0 0 0.6rem 0;
            font-weight: normal;
            text-transform: uppercase;
            max-width: 480px;
            line-height: 1.3;
        }
        .pf-sub-text p { color: #df5349; margin: 0; font-size: 0.9rem; }
        .pf-photo-box {
            width: 90px;
            height: 108px;
            border: 1px dashed #5c6b8c;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #df5349;
            font-size: 0.82rem;
            background: #fff !important;
            overflow: hidden;
            flex-shrink: 0;
        }
        .pf-photo-box img { width: 100%; height: 100%; object-fit: cover; }
        /* Red band */
        .pf-red-band {
            background: #df5349 !important;
            color: #fff;
            text-align: center;
            padding: 0.4rem;
            font-size: 1rem;
            font-weight: bold;
        }
        /* Body */
        .pf-body { padding: 0.9rem 1.5rem; }
        .pf-instruction { font-size: 0.92rem; margin-bottom: 0.7rem; font-weight: bold; }
        .pf-row {
            display: flex;
            margin-bottom: 0.45rem;
            align-items: center;
        }
        .pf-label { width: 210px; font-size: 0.88rem; font-weight: bold; color: #5c6b8c; flex-shrink: 0; }
        .pf-value {
            flex: 1;
            font-size: 0.88rem;
            color: #333;
            padding: 0.2rem 0.5rem;
            background: #fff !important;
            border: 1px solid #c5ccd8;
            min-height: 1.6rem;
        }
        /* Checkbox rows */
        .pf-checkbox-group { margin-top: 0.65rem; }
        .pf-checkbox-row {
            display: flex;
            align-items: center;
            margin-bottom: 0.4rem;
            font-size: 0.88rem;
            font-weight: bold;
            color: #5c6b8c;
            gap: 0.65rem;
        }
        .pf-tick {
            width: 16px; height: 16px;
            border: 1px solid #5c6b8c;
            background: #fff !important;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; color: #df5349; flex-shrink: 0;
        }
        /* Declaration */
        .pf-declaration {
            margin-top: 0.85rem;
            font-size: 0.88rem;
            line-height: 1.5;
            font-weight: bold;
            color: #5c6b8c;
        }
        /* Signature */
        .pf-signature-area {
            margin-top: 1.1rem;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            margin-bottom: 0.6rem;
        }
        .pf-signature-value {
            width: 260px;
            border-bottom: 1px solid #df5349;
            font-size: 1.1rem;
            color: #333;
            text-align: center;
            padding-bottom: 0.25rem;
            font-family: 'Brush Script MT', cursive, sans-serif;
        }
        .pf-signature-label {
            color: #df5349;
            font-size: 0.82rem;
            margin-right: 35px;
            margin-top: 0.25rem;
        }
        /* Footer row */
        .pf-submit-row {
            padding: 0.6rem 1.5rem;
            background: #fff !important;
            border-top: 1px solid #dcdcdc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
        }
        .pf-status-badge {
            display: inline-block;
            padding: 0.25rem 0.9rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
        }
        .status-Pending    { background: #fef3c7 !important; color: #92400e; }
        .status-Approved   { background: #d1fae5 !important; color: #065f46; }
        .status-Rejected   { background: #fee2e2 !important; color: #991b1b; }
        .status-other      { background: #ede9fe !important; color: #4c1d95; }
        .doc-link {
            display: inline-block;
            margin: 0.1rem 0.3rem 0.1rem 0;
            padding: 0.15rem 0.5rem;
            border: 1px solid #5c6b8c;
            border-radius: 3px;
            color: #5c6b8c;
            font-size: 0.82rem;
            text-decoration: none;
        }
        @media print {
            @page { size: A4 portrait; margin: 0.5cm; }
            body { background: #e2e6ec; }
            .no-print { display: none !important; }
            .paper-form-container { max-width: 100%; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="paper-form-container">

    <!-- Header -->
    <div class="pf-header">
        <div class="pf-header-left">
            <img src="<?php echo $uploads_base; ?>assets/images/logo-white-wreath.svg" alt="KSM Logo" style="width:80px;height:80px;">
            <div class="pf-logo-text">
                <h1>Kindergarten</h1>
                <h2>Saadia's Montessori</h2>
            </div>
        </div>
        <div class="pf-header-right">Admission Form</div>
    </div>

    <!-- Sub-header with passport photo -->
    <div class="pf-sub-header">
        <div class="pf-sub-text">
            <h3>FORM FOR THE ADMISSION IN KINDERGARTEN SAADIA'S MONTESSORI</h3>
            <p>Circular Road, 1st Floor of the Micro Finance Bank</p>
        </div>
        <div class="pf-photo-box">
            <?php if (!empty($passport_url)): ?>
                <img src="<?php echo $uploads_base . htmlspecialchars($passport_url); ?>" alt="Passport Photo">
            <?php else: ?>
                <span>Passport<br>Size<br>Photo</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Red band -->
    <div class="pf-red-band">Provide the correct information below.</div>

    <!-- Body -->
    <div class="pf-body">
        <div class="pf-instruction">Parents or Guardian must fill out and sign the form</div>

        <div class="pf-row">
            <div class="pf-label">Name of Student: *</div>
            <div class="pf-value"><?php echo $child_name ?: '<span class="empty">—</span>'; ?></div>
        </div>

        <div class="pf-row">
            <div class="pf-label">Date of Birth: *</div>
            <div class="pf-value"><?php echo $dob ?: '<span class="empty">—</span>'; ?></div>
        </div>

        <div class="pf-row">
            <div class="pf-label">Blood Group:</div>
            <div class="pf-value"><?php echo $blood_group ?: '<span class="empty">—</span>'; ?></div>
        </div>

        <div class="pf-row">
            <div class="pf-label">Program Applying For: *</div>
            <div class="pf-value"><?php echo $program ?: '<span class="empty">—</span>'; ?></div>
        </div>

        <div class="pf-row" style="align-items:flex-start;">
            <div class="pf-label" style="margin-top:6px;">Medical History:</div>
            <div class="pf-value" style="min-height:60px;"><?php echo $medical ? nl2br($medical) : '<span class="empty">None</span>'; ?></div>
        </div>

        <div class="pf-row">
            <div class="pf-label">Name of Parent/ Guardian: *</div>
            <div class="pf-value"><?php echo $parent_name ?: '<span class="empty">—</span>'; ?></div>
        </div>

        <div class="pf-row">
            <div class="pf-label">Contact No. *</div>
            <div class="pf-value"><?php echo $phone ?: '<span class="empty">—</span>'; ?></div>
        </div>

        <div class="pf-row">
            <div class="pf-label">E-mail:</div>
            <div class="pf-value"><?php echo $email ?: '<span class="empty">—</span>'; ?></div>
        </div>

        <!-- Checkbox items -->
        <div class="pf-checkbox-group">
            <div class="pf-checkbox-row">
                <div class="pf-tick"><?php echo !empty($id_card_url) ? '✓' : ''; ?></div>
                <span>Attach copy of Father/ Mother/ Guardian ID Card.</span>
                <?php if (!empty($id_card_url)): ?>
                    <a href="<?php echo $uploads_base . htmlspecialchars($id_card_url); ?>" target="_blank" class="doc-link no-print">View</a>
                <?php endif; ?>
            </div>

            <div class="pf-checkbox-row">
                <div class="pf-tick"><?php echo !empty($birth_cert_url) ? '✓' : ''; ?></div>
                <span>Attach copy of Birth Certificate of Child.</span>
                <?php if (!empty($birth_cert_url)): ?>
                    <a href="<?php echo $uploads_base . htmlspecialchars($birth_cert_url); ?>" target="_blank" class="doc-link no-print">View</a>
                <?php endif; ?>
            </div>

            <div class="pf-checkbox-row">
                <div class="pf-tick"><?php echo !empty($photos_url) ? '✓' : ''; ?></div>
                <span>Attach 4 Passport Size Pictures.</span>
                <?php if (!empty($photos_url)): ?>
                    <a href="<?php echo $uploads_base . htmlspecialchars($photos_url); ?>" target="_blank" class="doc-link no-print">View</a>
                <?php endif; ?>
            </div>

            <div class="pf-checkbox-row">
                <div class="pf-tick"></div>
                <span>Father/ Mother/ Guardian Occupation:</span>
                <span style="font-weight:normal; color:#333;"><?php echo $occupation ?: '—'; ?></span>
            </div>

            <div class="pf-checkbox-row">
                <div class="pf-tick">✓</div>
                <span>I have read and understand the terms &amp; conditions of this form.</span>
            </div>
        </div>

        <!-- Declaration -->
        <div class="pf-declaration">
            I agree with the rules &amp; regulations of institution &amp; shall confirm to them. I certify that<br>
            the above information is correct please admit my Son/ Daughter in this institution.
        </div>

        <!-- Signature -->
        <div class="pf-signature-area">
            <div class="pf-signature-value"><?php echo $signature ?: ''; ?></div>
            <div class="pf-signature-label">Signature of Parents or Guardian</div>
        </div>
    </div>

    <!-- Footer row -->
    <div class="pf-submit-row">
        <div style="font-size:0.85rem; color:#888;">
            Application ID: <strong>KSM-<?php echo date('Y', strtotime($submitted ?: 'now')); ?>-<?php echo $id; ?></strong>
            &nbsp;|&nbsp; Submitted: <strong><?php echo $submitted ?: '—'; ?></strong>
        </div>
        <div>
            Status: <span class="pf-status-badge status-<?php echo in_array($status, ['Pending','Approved','Rejected']) ? $status : 'other'; ?>"><?php echo $status; ?></span>
        </div>
    </div>

</div>
</body>
</html>
