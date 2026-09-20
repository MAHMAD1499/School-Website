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
  if(!preg_match('/^[A-Za-z\s]{2,50}$/', $cn)) ksm_err('Invalid child name format.');
  if(!preg_match('/^[A-Za-z\s]{2,50}$/', $pn)) ksm_err('Invalid parent name format.');
  if(!preg_match('/^(\+92|0)[0-9]{10}$/', $ph)) ksm_err('Invalid phone number format.');
  if($em && (!filter_var($em, FILTER_VALIDATE_EMAIL) || strlen($em)>100)) ksm_err('Invalid email format.');
  if($dob && (strtotime($dob) < strtotime('2010-01-01') || strtotime($dob) > strtotime('2024-01-01'))) ksm_err('Invalid date of birth.');
  $msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
  $addr = htmlspecialchars($addr, ENT_QUOTES, 'UTF-8');
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
    <section class="section container" id="admissions-page" style="margin-bottom: 4rem;">
        <div class="admissions-full-wrapper">
            <div class="inquiry-card professional-form-card">
                <div class="form-header text-center">
                    <h2>Application for Admission</h2>
                    <p class="text-muted" style="font-size: 0.95rem;">Please provide the correct information below to enroll your child at Kindergarten Saadia's Montessori.</p>
                </div>

                <form id="admissionForm" class="professional-grid-form" novalidate>
                    <div class="form-section-title">Student Information</div>
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="studentName">Name of Student *</label>
                            <input type="text" id="studentName" name="child_name" class="form-control" placeholder="e.g. Sarah Doe" required>
                        </div>
                        <div class="form-group half-width">
                            <label for="studentDOB">Date of Birth *</label>
                            <input type="date" id="studentDOB" name="dob" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="bloodGroup">Blood Group</label>
                            <select id="bloodGroup" name="blood_group" class="form-control">
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
                        <div class="form-group half-width">
                            <label for="programSelect">Program Applying For *</label>
                            <select id="programSelect" name="program" class="form-control" required>
                                <option value="" disabled selected>Select Program</option>
                                <option value="Playgroup">Playgroup</option>
                                <option value="Nursery">Nursery</option>
                                <option value="Prep">Prep</option>
                                <option value="Grade One">Grade One</option>
                                <option value="Grade Two">Grade Two</option>
                                <option value="Grade Three">Grade Three</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section-title">Medical History</div>
                    <div class="form-group full-width">
                        <label for="medicalHistory">Disease / Medical Records / Inherited Condition / Medications / Autistic / ADHD</label>
                        <textarea id="medicalHistory" name="notes" class="form-control" rows="3" placeholder="Please provide any relevant medical details or type 'None'..."></textarea>
                    </div>

                    <div class="form-section-title">Parent / Guardian Information</div>
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="parentName">Name of Parent / Guardian *</label>
                            <input type="text" id="parentName" name="parent_name" class="form-control" placeholder="e.g. John Doe" required>
                        </div>
                        <div class="form-group half-width">
                            <label for="parentOccupation">Father / Mother / Guardian Occupation</label>
                            <input type="text" id="parentOccupation" name="address" class="form-control" placeholder="Occupation">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="parentContact">Contact No. *</label>
                            <input type="tel" id="parentContact" name="phone" class="form-control" placeholder="+92 300 0000000" required>
                        </div>
                        <div class="form-group half-width">
                            <label for="parentEmail">E-mail *</label>
                            <input type="email" id="parentEmail" name="email" class="form-control" placeholder="example@domain.com" required>
                        </div>
                    </div>

                    <div class="form-section-title">Required Documents Checklist</div>
                    <p class="text-muted" style="font-size:0.85rem; margin-bottom: 1rem;">Please confirm that you will attach the following copies along with this form:</p>
                    <div class="checkbox-group row-checkboxes">
                        <label class="checkbox-label">
                            <input type="checkbox" id="checkIdCard" required>
                            <span class="checkmark"></span>
                            ID Card of Father / Mother
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="checkBirthCert" required>
                            <span class="checkmark"></span>
                            Birth Certificate of Child
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="checkPhotos" required>
                            <span class="checkmark"></span>
                            4 Passport Size Pictures
                        </label>
                    </div>

                    <div class="form-section-title">Declaration</div>
                    <div class="checkbox-group full-width" style="margin-bottom:1.5rem;">
                        <label class="checkbox-label terms-label">
                            <input type="checkbox" id="termsCheck" required>
                            <span class="checkmark"></span>
                            I agree with the rules & regulations of the institution & shall conform to them. I certify that the above information is correct and please admit my son/daughter in this institution. I have read and understand the terms & conditions of this form.
                        </label>
                    </div>

                    <div class="form-row signature-row">
                        <div class="form-group full-width">
                            <label for="digitalSignature">Digital Signature of Parents or Guardian *</label>
                            <input type="text" id="digitalSignature" name="prior_school" class="form-control signature-input" placeholder="Type your full name as signature" required>
                        </div>
                    </div>

                    <div class="form-submit-row">
                        <button type="submit" class="btn btn-primary submit-btn-large">Submit Application</button>
                    </div>
                </form>

                <div id="formSuccess" class="form-success-msg" style="display: none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <h3>Application Received!</h3>
                    <p>Thank you for submitting your application. Our admissions office will contact you shortly regarding the next steps.</p>
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

</body>

</html>
