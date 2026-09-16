<?php
$_ksm=['host'=>'localhost','user'=>'root','pass'=>'','name'=>'ksm_database'];
function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
header('Access-Control-Allow-Origin: *');header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax && $method==='POST'){
  $cn=ksm_esc($body['child_name']??'');$dob=ksm_esc($body['dob']??'');$pn=ksm_esc($body['parent_name']??'');$ph=ksm_esc($body['phone']??'');$em=ksm_esc($body['email']??'');$addr=ksm_esc($body['address']??'');$ps=ksm_esc($body['prior_school']??'');$ca=ksm_esc($body['program']??'');$msg=ksm_esc($body['notes']??'');
  if(!$cn||!$pn||!$ph)ksm_err('Child name, parent name and phone required.');
  ksm_db()->query("INSERT INTO admissions(child_name,dob,parent_name,phone,email,address,prior_school,class_applied,message,status)VALUES('$cn','$dob','$pn','$ph','$em','$addr','$ps','$ca','$msg','Pending')");
  $newId=ksm_db()->insert_id;
  ksm_json(['id'=>$newId,'refCode'=>'KSM-'.date('Ymd').'-'.$newId],'Application submitted.');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Apply for enrollment at Kindergarten Saadia's Montessori School — complete our step-by-step online inquiry form.">
    <title>Admissions & Inquiry | Kindergarten Saadia's Montessori School</title>

    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages.css">
</head>

<body>

    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader-content">
            <div class="logo preloader-logo">
                <img src="assets/images/logo.svg" alt="KSM Haripur Logo" />
                <div class="logo-text">
                    <span class="logo-title">Kindergarten Saadia's</span>
                    <span class="logo-subtitle">Montessori School</span>
                </div>
            </div>
            <div class="loading-bar-container">
                <div class="loading-bar"></div>
            </div>
        </div>
    </div>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-info">
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    +92 331 5620055
                </span>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                    kindergartenmontessori1@gmail.com
                </span>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header id="header">
        <div class="header-container">
            <a href="index.php" class="logo" aria-label="KSM Home">
                <img src="assets/images/logo.svg" alt="KSM Haripur Logo" />
                <div class="logo-text">
                    <span class="logo-title">Kindergarten Saadia's</span>
                    <span class="logo-subtitle">Montessori School</span>
                </div>
            </a>

            <nav class="nav-menu" id="navMenu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="index.php#programs" class="nav-link">Programs</a>
                <a href="admissions.php" class="nav-link active">Admissions</a>
                <a href="index.php#gallery" class="nav-link">Gallery & Events</a>
                <a href="about.php" class="nav-link">About Us</a>
                <a href="contact.php" class="nav-link">Contact</a>

                <div class="mobile-menu-buttons">
                    <a href="portal/index.php" class="btn btn-accent">Portal</a>
                    <a href="admissions.php" class="btn btn-primary">Apply Now</a>
                </div>
            </nav>

            <div class="header-buttons">
                <a href="portal/index.php" class="btn btn-accent" id="portalBtn">Portal</a>
                <a href="admissions.php" class="btn btn-primary">Apply Now</a>
            </div>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle Navigation">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <!-- Hero Header -->
    <section class="page-hero">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>›</span>
                <span style="color:white;">Admissions</span>
            </div>
            <h1>Admissions & Inquiry</h1>
            <p>Join our Montessori community. Review our process or fill out the online application below.</p>
        </div>
    </section>

    <!-- Admissions Main Section -->
    <style>
        #admissions-page {
            background-image: url('assets/images/bg-building.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 4rem 1rem;
            position: relative;
        }
        #admissions-page::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        .paper-form-card {
            position: relative;
            z-index: 1;
            background: #e8ebf0; /* Light grayish-blue background */
            max-width: 850px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .paper-header {
            background: #d84545; /* Red header */
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
        }
        .paper-logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .paper-logo-area img {
            width: 60px;
            height: 60px;
            background: #fff;
            border-radius: 50%;
            padding: 5px;
        }
        .paper-logo-text {
            display: flex;
            flex-direction: column;
        }
        .paper-logo-text .title {
            font-size: 1.6rem;
            font-weight: 600;
            letter-spacing: 1px;
            margin: 0;
        }
        .paper-logo-text .subtitle {
            font-size: 1.2rem;
            font-weight: 400;
            margin: 0;
        }
        .paper-badge {
            background: #fff;
            color: #d84545;
            padding: 8px 20px;
            font-weight: bold;
            font-size: 1.1rem;
            border-radius: 4px;
        }
        .paper-sub-header {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
        }
        .paper-sub-title {
            color: #d84545;
            font-size: 1.2rem;
            font-weight: 600;
            max-width: 70%;
            text-transform: uppercase;
        }
        .paper-address {
            color: #d84545;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        .passport-photo-box {
            width: 120px;
            height: 150px;
            border: 1px solid #999;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
            background: #fff;
        }
        .paper-instruction-bar {
            background: #d84545;
            color: #fff;
            text-align: center;
            padding: 8px;
            font-weight: 500;
            font-size: 1.1rem;
        }
        .paper-body {
            padding: 2rem;
        }
        .paper-instruction-text {
            font-size: 0.9rem;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .paper-form-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .paper-form-group label {
            width: 30%;
            font-size: 0.95rem;
            color: #444;
            font-weight: 500;
        }
        .paper-form-group input[type="text"],
        .paper-form-group input[type="date"],
        .paper-form-group input[type="tel"],
        .paper-form-group input[type="email"],
        .paper-form-group select {
            width: 70%;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fff;
            font-size: 0.95rem;
            outline: none;
        }
        .paper-form-group textarea {
            width: 70%;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fff;
            font-size: 0.95rem;
            outline: none;
            resize: vertical;
        }
        .paper-checkbox-group {
            margin-top: 30px;
        }
        .paper-check-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }
        .paper-check-item input[type="checkbox"] {
            margin-right: 10px;
            width: 16px;
            height: 16px;
        }
        .paper-check-item input[type="text"] {
            margin-left: 10px;
            padding: 4px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fff;
            flex-grow: 1;
            max-width: 300px;
        }
        .paper-declaration {
            margin-top: 40px;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #444;
        }
        .paper-signature-row {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
        }
        .paper-signature-box {
            width: 300px;
            text-align: center;
        }
        .paper-signature-box input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #d84545;
            background: transparent;
            text-align: center;
            font-family: 'Brush Script MT', cursive;
            font-size: 1.5rem;
            padding-bottom: 5px;
            outline: none;
        }
        .paper-signature-box .signature-label {
            color: #d84545;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        .paper-submit {
            margin-top: 40px;
            text-align: center;
        }
        .paper-submit button {
            background: #d84545;
            color: #fff;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
        }
        .paper-submit button:hover {
            background: #c03535;
        }
        @media (max-width: 768px) {
            .paper-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .paper-sub-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 20px;
            }
            .paper-sub-title {
                max-width: 100%;
            }
            .paper-form-group {
                flex-direction: column;
                align-items: flex-start;
            }
            .paper-form-group label {
                width: 100%;
                margin-bottom: 5px;
            }
            .paper-form-group input[type="text"],
            .paper-form-group input[type="date"],
            .paper-form-group input[type="tel"],
            .paper-form-group input[type="email"],
            .paper-form-group select,
            .paper-form-group textarea {
                width: 100%;
            }
        }
    </style>

    <section id="admissions-page">
        <div class="container">
            <div class="paper-form-card">
                
                <div class="paper-header">
                    <div class="paper-logo-area">
                        <img src="assets/images/logo.svg" alt="KSM Logo">
                        <div class="paper-logo-text">
                            <span class="title">Kindergarten</span>
                            <span class="subtitle">Saadia's Montessori</span>
                        </div>
                    </div>
                    <div class="paper-badge">
                        Admission Form
                    </div>
                </div>

                <div class="paper-sub-header">
                    <div>
                        <div class="paper-sub-title">FORM FOR THE ADMISSION IN KINDERGARTEN SAADIA'S MONTESSORI</div>
                        <div class="paper-address">Circular Road, 1st Floor of the Micro Finance Bank</div>
                    </div>
                    <div class="passport-photo-box">
                        Passport<br>Size<br>Photo
                    </div>
                </div>

                <div class="paper-instruction-bar">
                    Provide the correct information below.
                </div>

                <div class="paper-body">
                    <form id="admissionForm" novalidate>
                        <div class="paper-instruction-text">Parents or Guardian must fill out and sign the form</div>

                        <div class="paper-form-group">
                            <label for="studentName">Name of Student:</label>
                            <input type="text" id="studentName" name="child_name" required>
                        </div>
                        <div class="paper-form-group">
                            <label for="studentDOB">Date of Birth:</label>
                            <input type="date" id="studentDOB" name="dob" required>
                        </div>
                        <div class="paper-form-group">
                            <label for="parentName">Name of Parent/Guardian:</label>
                            <input type="text" id="parentName" name="parent_name" required>
                        </div>
                        <div class="paper-form-group">
                            <label for="parentContact">Contact No.:</label>
                            <input type="tel" id="parentContact" name="phone" required>
                        </div>
                        <div class="paper-form-group">
                            <label for="parentEmail">E-mail:</label>
                            <input type="email" id="parentEmail" name="email" required>
                        </div>

                        <div class="paper-form-group" style="margin-top: 20px;">
                            <label for="bloodGroup">Blood Group:</label>
                            <select id="bloodGroup" name="blood_group">
                                <option value="" disabled selected>Select</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        
                        <div class="paper-form-group">
                            <label for="medicalHistory">Medical Records/ADHD:</label>
                            <input type="text" id="medicalHistory" name="notes" placeholder="Any disease, inherited condition, medications...">
                        </div>
                        
                        <div class="paper-form-group">
                            <label for="programSelect">Program Applying For:</label>
                            <select id="programSelect" name="program" required>
                                <option value="" disabled selected>Select Program</option>
                                <option value="Early Toddler (1.5 - 3 Yrs)">Early Toddler (1.5 - 3 Yrs)</option>
                                <option value="Primary/Kindergarten (3 - 6 Yrs)">Primary/Kindergarten (3 - 6 Yrs)</option>
                                <option value="Junior Level (6 - 9 Yrs)">Junior Level (6 - 9 Yrs)</option>
                            </select>
                        </div>

                        <div class="paper-checkbox-group">
                            <label class="paper-check-item">
                                <input type="checkbox" id="checkIdCard" required>
                                Attach copy of Father/Mother/Guardian ID Card, Birth Certificate, 4 Passport Size Pictures.
                            </label>
                            <label class="paper-check-item">
                                <input type="checkbox" id="checkOccupation">
                                Father/Mother/Guardian Occupation.
                                <input type="text" id="parentOccupation" name="address" placeholder="Occupation">
                            </label>
                            <label class="paper-check-item">
                                <input type="checkbox" id="termsCheck" required>
                                I have read and understand the terms & conditions of this form.
                            </label>
                        </div>

                        <div class="paper-declaration">
                            I agree with the rules & regulations of institution & shall conform to them. I certify that the above information is correct please admit my Son/ Daughter in this institution.
                        </div>

                        <div class="paper-signature-row">
                            <div class="paper-signature-box">
                                <input type="text" id="digitalSignature" name="prior_school" placeholder="Type name" required>
                                <div class="signature-label">Signature of Parents or Guardian</div>
                            </div>
                        </div>

                        <div class="paper-submit">
                            <button type="submit">Submit Application Form</button>
                        </div>
                    </form>

                    <div id="formSuccess" class="form-success-msg" style="display: none; text-align:center; margin-top:30px; color: #28a745;">
                        <h3>Application Received!</h3>
                        <p>Thank you for submitting your application. Our admissions office will contact you shortly.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Full Page Footer -->
    <footer style="margin-top: 5rem;">
        <div class="container footer-grid">
            <div>
                <div class="footer-logo">
                    <img src="assets/images/logo.svg" alt="KSM Haripur Logo" />
                    <div class="logo-text">
                        <span class="logo-title">Kindergarten Saadia's</span>
                        <span class="logo-subtitle">Montessori School</span>
                    </div>
                </div>
                <p class="footer-desc">Free The Child's Potential and you will Transform him into the world.</p>
            </div>
            <div>
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php#programs">Montessori Programs</a></li>
                    <li><a href="admissions.php">Admissions</a></li>
                    <li><a href="index.php#gallery">Gallery & Events</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact Us</h4>
                <ul class="footer-contact">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" style="color: var(--accent-warm); flex-shrink: 0;">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span>Circular road HBL microfinance bank, Haripur, Pakistan</span>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" style="color: var(--accent-warm); flex-shrink: 0;">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        <span>+92 333 3660174</span>
                    </li>
                </ul>
            </div>
            <div>
                <h4>Newsletter</h4>
                <div class="footer-newsletter">
                    <p>Get latest events updates and parent tips.</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <input type="email" placeholder="Your Email" aria-label="Email Address" required>
                        <button type="submit">Join</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; 2026 Kindergarten Saadia's Montessori School. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/app.js"></script>
    <script>
        // Single-Step Form Submission Logic
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('admissionForm');
            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const btn = form.querySelector('button[type="submit"]');
                    btn.disabled = true;
                    btn.textContent = 'Submitting...';

                    const formData = new FormData(form);
                    const data = {
                        parent_name: formData.get('parent_name'),
                        email: formData.get('email'),
                        phone: formData.get('phone'),
                        child_name: formData.get('child_name'),
                        dob: formData.get('dob'),
                        address: formData.get('address'),
                        prior_school: formData.get('prior_school'),
                        program: formData.get('program'),
                        notes: formData.get('notes')
                    };

                    try {
                        const res = await fetch('admissions.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(data)
                        });
                        const result = await res.json();
                        
                        if (result.success) {
                            // Show success message
                            form.style.display = 'none';
                            document.getElementById('formSuccess').style.display = 'block';
                            if (result.data && result.data.refCode) {
                                document.getElementById('formSuccess').innerHTML += `<p style="margin-top:1rem;font-weight:bold;">Your Reference Code: ${result.data.refCode}</p>`;
                            }
                        } else {
                            alert(result.message || 'Failed to submit application.');
                            btn.disabled = false;
                            btn.textContent = 'Submit Application';
                        }
                    } catch (err) {
                        alert('Server error. Please try again.');
                        btn.disabled = false;
                        btn.textContent = 'Submit Application';
                    }
                });
            }
        });
    </script>
</body>

</html>
