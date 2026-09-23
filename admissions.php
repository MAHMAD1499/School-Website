<?php
require_once __DIR__ . '/config/database.php';

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
header('Access-Control-Allow-Origin: *');header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='POST'){
  $isJson = strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false;
  $body = $isJson ? json_decode(file_get_contents('php://input'),true)??[] : $_POST;
  $cn=ksm_esc($body['child_name']??'');$dob=ksm_esc($body['dob']??'');$bg=ksm_esc($body['blood_group']??'');$pn=ksm_esc($body['parent_name']??'');$ph=ksm_esc($body['phone']??'');$em=ksm_esc($body['email']??'');$occ=ksm_esc($body['address']??'');$sig=ksm_esc($body['prior_school']??'');$ca=ksm_esc($body['program']??'');$med=ksm_esc($body['notes']??'');
  if(!$cn||!$pn||!$ph)ksm_err('Child name, parent name and phone required.');
  
  function up_file($k){
    if(!isset($_FILES[$k])||$_FILES[$k]['error']!==UPLOAD_ERR_OK)return '';
    $n=uniqid().'_'.basename($_FILES[$k]['name']);
    $d='uploads/admissions/';
    if(!is_dir($d))mkdir($d,0777,true);
    if(move_uploaded_file($_FILES[$k]['tmp_name'],$d.$n))return $d.$n;
    return '';
  }
  
  $id_url=ksm_esc(up_file('id_card_upload'));
  $bc_url=ksm_esc(up_file('birth_cert_upload'));
  $ph_url=ksm_esc(up_file('photos_upload'));
  $pass_url=ksm_esc(up_file('passport_photo'));

  ksm_db()->query("INSERT INTO admissions(child_name,dob,blood_group,parent_name,phone,email,address,prior_school,class_applied,message,status,id_card_url,birth_cert_url,photos_url,passport_photo_url)VALUES('$cn','$dob','$bg','$pn','$ph','$em','$occ','$sig','$ca','$med','Pending','$id_url','$bc_url','$ph_url','$pass_url')");
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
            <div class="top-bar-socials">
                <a href="https://www.facebook.com/Kindergarten786/" aria-label="Facebook">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                    </svg>
                </a>
                <a href="https://www.instagram.com/kindergartenmontessori2021/" aria-label="Instagram">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                    </svg>
                </a>
                <a href="https://www.youtube.com/@kindergartensaadiasmontess9970" aria-label="YouTube" target="_blank" rel="noopener noreferrer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z" />
                        <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" />
                    </svg>
                </a>
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
                    <a href="portal/admin/login.php" class="btn btn-outline" style="border-color:rgba(30,58,140,0.3);font-size:0.85rem;">Admin Portal</a>
                    <a href="admissions.php" class="btn btn-primary">Apply Now</a>
                </div>
            </nav>

            <div class="header-buttons">
                <a href="portal/admin/login.php" class="btn btn-outline" id="adminPortalBtn" style="padding:0.5rem 1rem;font-size:0.8rem;border-color:rgba(30,58,138,0.3);">Admin Portal</a>
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
    <section id="admissions-page" style="position: relative; padding: 4rem 1rem; overflow: hidden; width: 100%; max-width: 100%; margin: 0; box-sizing: border-box;">
        <div style="position: absolute; top: -30px; left: -30px; right: -30px; bottom: -30px; background: url('assets/images/class-image.jpg') center/cover fixed no-repeat; filter: blur(4px); z-index: -1;"></div>
        <style>
            .paper-form-container {
                max-width: 850px;
                margin: 0 auto;
                background: #e2e6ec;
                font-family: Arial, Helvetica, sans-serif;
                color: #5c6b8c;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .pf-header {
                background: #df5349;
                color: #fff;
                padding: 1.2rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .pf-header-left {
                display: flex;
                align-items: center;
                gap: 1.5rem;
            }
            .pf-logo {
                width: 70px;
                height: 70px;
                border: 2px solid #fff;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-weight: bold;
                font-style: italic;
                font-size: 1.8rem;
            }
            .pf-logo-text {
                display: flex;
                flex-direction: column;
            }
            .pf-logo-text h1 {
                margin: 0;
                font-size: 2.2rem;
                font-weight: normal;
                color: #fff;
            }
            .pf-logo-text h2 {
                margin: 0;
                font-size: 1.4rem;
                font-weight: normal;
                color: #fff;
            }
            .pf-header-right {
                background: #fff;
                color: #606e89;
                padding: 0.4rem 1.2rem;
                font-weight: bold;
                font-size: 1.2rem;
                border-radius: 2px;
            }
            .pf-sub-header {
                background: #fff;
                padding: 1.5rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }
            .pf-sub-text h3 {
                color: #df5349;
                font-size: 1.4rem;
                margin: 0 0 1.5rem 0;
                font-weight: normal;
                text-transform: uppercase;
                max-width: 500px;
                line-height: 1.3;
            }
            .pf-sub-text p {
                color: #df5349;
                margin: 0;
                font-size: 1rem;
            }
            .pf-photo-box {
                width: 100px;
                height: 120px;
                border: 1px solid #5c6b8c;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                color: #df5349;
                font-size: 0.9rem;
                background: #fff;
            }
            .pf-red-band {
                background: #df5349;
                color: #fff;
                text-align: center;
                padding: 0.5rem;
                font-size: 1.2rem;
                font-weight: bold;
            }
            .pf-body {
                padding: 2rem;
            }
            .pf-instruction {
                font-size: 1.1rem;
                margin-bottom: 2rem;
                font-weight: bold;
            }
            .pf-row {
                display: flex;
                margin-bottom: 1rem;
                align-items: center;
            }
            .pf-label {
                width: 250px;
                font-size: 1.1rem;
                font-weight: bold;
                color: #5c6b8c;
            }
            .pf-input {
                flex: 1;
                border: 1px solid #5c6b8c;
                padding: 0.5rem;
                background: #fff;
                font-size: 1rem;
                color: #333;
                border-radius: 0;
                box-shadow: none;
                outline: none;
            }
            select.pf-input {
                cursor: pointer;
            }
            textarea.pf-input {
                resize: vertical;
            }
            .pf-checkbox-group {
                margin-top: 2rem;
            }
            .pf-checkbox-row {
                display: flex;
                align-items: center;
                margin-bottom: 1rem;
                font-size: 1.1rem;
                font-weight: bold;
                color: #5c6b8c;
                gap: 1rem;
            }
            .pf-checkbox {
                width: 20px;
                height: 20px;
                border: 1px solid #5c6b8c;
                background: #fff;
                cursor: pointer;
                appearance: none;
                -webkit-appearance: none;
                outline: none;
                position: relative;
            }
            .pf-checkbox:checked::after {
                content: '✓';
                position: absolute;
                color: #df5349;
                font-size: 16px;
                left: 3px;
                top: -1px;
            }
            .pf-declaration {
                margin-top: 3rem;
                font-size: 1rem;
                line-height: 1.5;
                font-weight: bold;
                color: #5c6b8c;
            }
            .pf-signature-area {
                margin-top: 4rem;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                margin-bottom: 2rem;
            }
            .pf-signature-input {
                width: 300px;
                border: none;
                border-bottom: 1px solid #df5349;
                background: transparent;
                font-size: 1.2rem;
                color: #333;
                outline: none;
                text-align: center;
                margin-bottom: 0.5rem;
                font-family: 'Brush Script MT', cursive, sans-serif;
            }
            .pf-signature-label {
                color: #df5349;
                font-size: 1rem;
                margin-right: 40px;
            }
            .pf-submit-row {
                padding: 2rem;
                background: #fff;
                text-align: center;
                border-top: 1px solid #dcdcdc;
            }
            .pf-submit-btn {
                background: #df5349;
                color: #fff;
                border: none;
                padding: 1rem 3rem;
                font-size: 1.2rem;
                font-weight: bold;
                cursor: pointer;
                border-radius: 5px;
                transition: background 0.3s;
            }
            .pf-submit-btn:hover {
                background: #c9463e;
            }
        </style>

        <div class="paper-form-container">
            <form id="admissionForm" novalidate>
                <div class="pf-header">
                    <div class="pf-header-left">
                        <img src="assets/images/logo-white-wreath.svg" alt="KSM Logo" style="width: 80px; height: 80px; display: block;">
                        <div class="pf-logo-text">
                            <h1>Kindergarten</h1>
                            <h2>Saadia's Montessori</h2>
                        </div>
                    </div>
                    <div class="pf-header-right">
                        Admission Form
                    </div>
                </div>
                
                <div class="pf-sub-header">
                    <div class="pf-sub-text">
                        <h3>FORM FOR THE ADMISSION IN KINDERGARTEN SAADIA'S MONTESSORI</h3>
                        <p>Circular Road, 1st Floor of the Micro Finance Bank</p>
                    </div>
                    <div class="pf-photo-box" id="passportPhotoBox" onclick="document.getElementById('passportUpload').click()" style="position:relative; overflow:hidden; border-style:dashed;">
                        <span id="passportPhotoText">Passport<br>Size<br>Photo</span>
                        <input type="file" id="passportUpload" name="passport_photo" accept="image/*" style="display:none;" onchange="previewPassportPhoto(event)">
                    </div>
                </div>
                
                <script>
                function previewPassportPhoto(e) {
                    const file = e.target.files[0];
                    if(file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const box = document.getElementById('passportPhotoBox');
                            box.style.backgroundImage = 'url(' + e.target.result + ')';
                            box.style.backgroundSize = 'cover';
                            box.style.backgroundPosition = 'center';
                            document.getElementById('passportPhotoText').style.display = 'none';
                        }
                        reader.readAsDataURL(file);
                    }
                }
                </script>
                
                <div class="pf-red-band">
                    Provide the correct information below.
                </div>
                
                <div class="pf-body">
                    <div class="pf-instruction">
                        Parents or Guardian must fill out and sign the form
                    </div>
                    
                    <div class="pf-row">
                        <div class="pf-label">Name of Student: *</div>
                        <input type="text" name="child_name" class="pf-input" placeholder="e.g. Sarah Doe" required>
                    </div>
                    
                    <div class="pf-row">
                        <div class="pf-label">Date of Birth: *</div>
                        <input type="date" name="dob" class="pf-input" required>
                    </div>
                    
                    <div class="pf-row">
                        <div class="pf-label">Blood Group:</div>
                        <select name="blood_group" class="pf-input">
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
                    
                    <div class="pf-row">
                        <div class="pf-label">Program Applying For: *</div>
                        <select name="program" class="pf-input" required>
                            <option value="" disabled selected>Select Program</option>
                            <option value="Early Toddler (1.5 - 3 Yrs)">Early Toddler (1.5 - 3 Yrs)</option>
                            <option value="Primary/Kindergarten (3 - 6 Yrs)">Primary/Kindergarten (3 - 6 Yrs)</option>
                            <option value="Junior Level (6 - 9 Yrs)">Junior Level (6 - 9 Yrs)</option>
                        </select>
                    </div>
                    
                    <div class="pf-row">
                        <div class="pf-label" style="align-self:flex-start; margin-top:10px;">Medical History:</div>
                        <textarea name="notes" class="pf-input" rows="3" placeholder="Disease / Medical Records / Inherited Condition / Medications / Autistic / ADHD"></textarea>
                    </div>
                    
                    <div class="pf-row">
                        <div class="pf-label">Name of Parent/ Guardian: *</div>
                        <input type="text" name="parent_name" class="pf-input" placeholder="e.g. John Doe" required>
                    </div>
                    
                    <div class="pf-row">
                        <div class="pf-label">Contact No. *</div>
                        <input type="tel" name="phone" class="pf-input" placeholder="+92 300 0000000" required>
                    </div>
                    
                    <div class="pf-row">
                        <div class="pf-label">E-mail: *</div>
                        <input type="email" name="email" class="pf-input" placeholder="example@domain.com" required>
                    </div>
                    
                    <div class="pf-checkbox-group">
                        <div class="pf-checkbox-row" style="flex-wrap: wrap;">
                            <input type="checkbox" class="pf-checkbox" id="checkIdCard" required>
                            <label for="checkIdCard" style="cursor:pointer; flex: 1;">Attach copy of Father/ Mother/ Guardian ID Card.</label>
                            <input type="file" name="id_card_upload" accept="image/*,.pdf" style="font-size:0.9rem;" onchange="document.getElementById('checkIdCard').checked = true;">
                        </div>
                        
                        <div class="pf-checkbox-row" style="flex-wrap: wrap;">
                            <input type="checkbox" class="pf-checkbox" id="checkBirthCert" required>
                            <label for="checkBirthCert" style="cursor:pointer; flex: 1;">Attach copy of Birth Certificate of Child.</label>
                            <input type="file" name="birth_cert_upload" accept="image/*,.pdf" style="font-size:0.9rem;" onchange="document.getElementById('checkBirthCert').checked = true;">
                        </div>

                        <div class="pf-checkbox-row" style="flex-wrap: wrap;">
                            <input type="checkbox" class="pf-checkbox" id="checkPhotos" required>
                            <label for="checkPhotos" style="cursor:pointer; flex: 1;">Attach 4 Passport Size Pictures.</label>
                            <input type="file" name="photos_upload" accept="image/*" multiple style="font-size:0.9rem;" onchange="document.getElementById('checkPhotos').checked = true;">
                        </div>
                        
                        <div class="pf-checkbox-row">
                            <input type="checkbox" class="pf-checkbox">
                            <label style="white-space: nowrap;">Father/ Mother/ Guardian Occupation.</label>
                            <input type="text" name="address" class="pf-input" placeholder="Occupation">
                        </div>
                        
                        <div class="pf-checkbox-row">
                            <input type="checkbox" class="pf-checkbox" id="termsCheck" required>
                            <label for="termsCheck" style="cursor:pointer;">I have read and understand the terms & conditions of this form.</label>
                        </div>
                    </div>
                    
                    <div class="pf-declaration">
                        I agree with the rules & regulations of institution & shall confirm to them. I certify that<br>
                        the above information is correct please admit my Son/ Daughter in this institution.
                    </div>
                    
                    <div class="pf-signature-area">
                        <input type="text" name="prior_school" class="pf-signature-input" placeholder="Type your full name as signature" required>
                        <div class="pf-signature-label">Signature of Parents or Guardian</div>
                    </div>
                </div>
                
                <div class="pf-submit-row">
                    <button type="submit" class="pf-submit-btn">Submit Application</button>
                </div>
            </form>
            
            <div id="formSuccess" class="form-success-msg" style="display: none; padding: 3rem; text-align: center; background: #fff;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#df5349" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:64px;height:64px;margin-bottom:1rem;display:inline-block;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                <h3 style="color:#df5349;font-size:1.8rem;margin-bottom:1rem;">Application Received!</h3>
                <p style="color:#5c6b8c;font-size:1.1rem;">Thank you for submitting your application. Our admissions office will contact you shortly regarding the next steps.</p>
            </div>
        </div>
    </section>

    <!-- Full Page Footer -->
    <footer>
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
                <div class="footer-socials">
                    <a href="https://www.facebook.com/profile.php?id=61555316418649" aria-label="Facebook">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/kindergartenmontessori2021/" aria-label="Instagram">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                        </svg>
                    </a>
                    <a href="https://www.youtube.com/@kindergartensaadiasmontess9970" aria-label="YouTube">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z" />
                            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" />
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php#about">About & Philosophy</a></li>
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
                        <span>Circular Road, HBL Microfinance Bank Haripur, Pakistan</span>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" style="color: var(--accent-warm); flex-shrink: 0;">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        <span>+92 331 5620055</span>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" style="color: var(--accent-warm); flex-shrink: 0;">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                        <span>kindergartenmontessori1@gmail.com</span>
                    </li>
                </ul>
            </div>
            <div>
                <div class="map-container">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3307.705748870337!2d72.93316067499752!3d34.00009107317665!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38dfb37001d2f68f%3A0x15918636c55c1c6b!2sKindergarten%20Saadia%27s%20Montessori%20School!5e0!3m2!1sen!2s!4v1788940104876!5m2!1sen!2s"
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
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

                    try {
                        const res = await fetch('admissions.php', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
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
