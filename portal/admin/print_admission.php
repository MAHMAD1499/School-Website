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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Form - <?php echo htmlspecialchars($record['child_name']); ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 2rem; }
        .header { text-align: center; border-bottom: 2px solid #1a365d; padding-bottom: 1rem; margin-bottom: 2rem; }
        .header img { height: 60px; margin-bottom: 0.5rem; }
        .header h1 { margin: 0; color: #1a365d; font-size: 1.8rem; }
        .header p { margin: 0; font-size: 0.9rem; color: #666; }
        .section-title { background: #f3f4f6; padding: 0.5rem 1rem; font-weight: bold; font-size: 1.1rem; border-left: 4px solid #3b82f6; margin-bottom: 1rem; margin-top: 2rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .grid-1 { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        .field { margin-bottom: 1rem; }
        .label { font-size: 0.8rem; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.2rem; }
        .value { font-size: 1rem; font-weight: 500; border-bottom: 1px dashed #ccc; padding-bottom: 0.2rem; }
        .signature-box { border: 1px solid #ccc; padding: 2rem 1rem; text-align: center; margin-top: 3rem; width: 300px; float: right; font-family: cursive; font-size: 1.2rem; color: #1a365d; }
        .clearfix::after { content: ""; clear: both; display: table; }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h1>Kindergarten Saadia's Montessori School</h1>
        <p>Application for Admission (ID: KSM-<?php echo date('Y', strtotime($record['submittedAt'] ?? 'now')) . '-' . $id; ?>)</p>
    </div>

    <div class="section-title">Student Information</div>
    <div class="grid-2">
        <div class="field"><div class="label">Name of Student</div><div class="value"><?php echo htmlspecialchars($record['child_name'] ?? ''); ?></div></div>
        <div class="field"><div class="label">Date of Birth</div><div class="value"><?php echo htmlspecialchars($record['dob'] ?? ''); ?></div></div>
        <div class="field"><div class="label">Blood Group</div><div class="value"><?php echo htmlspecialchars($record['blood_group'] ?? '—'); ?></div></div>
        <div class="field"><div class="label">Program Applied For</div><div class="value"><?php echo htmlspecialchars($record['class_applied'] ?? '—'); ?></div></div>
    </div>

    <div class="section-title">Medical History</div>
    <div class="grid-1">
        <div class="field"><div class="label">Disease / Records / Conditions / Medications</div><div class="value"><?php echo nl2br(htmlspecialchars($record['medical_history'] ?? '—')); ?></div></div>
    </div>

    <div class="section-title">Parent / Guardian Information</div>
    <div class="grid-2">
        <div class="field"><div class="label">Name of Parent / Guardian</div><div class="value"><?php echo htmlspecialchars($record['parent_name'] ?? ''); ?></div></div>
        <div class="field"><div class="label">Occupation</div><div class="value"><?php echo htmlspecialchars($record['occupation'] ?? '—'); ?></div></div>
        <div class="field"><div class="label">Contact No.</div><div class="value"><?php echo htmlspecialchars($record['phone'] ?? ''); ?></div></div>
        <div class="field"><div class="label">E-mail</div><div class="value"><?php echo htmlspecialchars($record['email'] ?? ''); ?></div></div>
    </div>

    <div class="section-title">Uploaded Documents Status</div>
    <div class="grid-2">
        <div class="field"><div class="label">ID Card</div><div class="value"><?php echo !empty($record['id_card_url']) ? 'Attached' : 'Not Attached'; ?></div></div>
        <div class="field"><div class="label">Birth Certificate</div><div class="value"><?php echo !empty($record['birth_cert_url']) ? 'Attached' : 'Not Attached'; ?></div></div>
        <div class="field"><div class="label">Passport Pictures</div><div class="value"><?php echo !empty($record['photos_url']) ? 'Attached' : 'Not Attached'; ?></div></div>
    </div>

    <div class="clearfix">
        <div class="signature-box">
            <?php echo htmlspecialchars($record['digital_signature'] ?? '—'); ?><br>
            <span style="font-family: sans-serif; font-size: 0.8rem; color: #666; border-top: 1px solid #333; display: inline-block; padding-top: 0.5rem; margin-top: 1rem;">Digital Signature of Parent/Guardian</span>
        </div>
    </div>

    <div style="margin-top: 2rem; text-align: center; font-size: 0.8rem; color: #888;">
        Submitted on: <?php echo htmlspecialchars($record['submittedAt'] ?? 'Unknown'); ?>
    </div>

</body>
</html>
