<?php
require_once __DIR__ . '/config/database.php';

function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
header('Access-Control-Allow-Origin: *');header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;
$isAjax=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||strpos($_SERVER['CONTENT_TYPE']??'','application/json')!==false||isset($_GET['_api']);
$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax && $method==='POST'){
  $name=ksm_esc($body['name']??'');$email=ksm_esc($body['email']??'');$sub=ksm_esc($body['subject']??'');$msg=ksm_esc($body['message']??'');
  if(!$name||!$email||!$msg)ksm_err('Name, email and message required.');
  if(!preg_match('/^[A-Za-z\s]{2,50}$/', $name)) ksm_err('Invalid name format.');
  if(!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email)>100) ksm_err('Invalid email format.');
  if(strlen($msg)<10 || strlen($msg)>1000) ksm_err('Message must be between 10 and 1000 characters.');
  $msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
  ksm_db()->query("INSERT INTO contacts(name,email,subject,message,status)VALUES('$name','$email','$sub','$msg','Unread')");
  ksm_json(['id'=>ksm_db()->insert_id],'Message sent.');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Contact Kindergarten Saadia's Montessori School — reach out for admissions inquiries, campus visits, or general questions.">
  <title>Contact Us | Kindergarten Saadia's Montessori School</title>
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
          +92 348 9898618
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

  <!-- Header -->
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
        <a href="admissions.php" class="nav-link">Admissions</a>
        <a href="index.php#gallery" class="nav-link">Gallery & Events</a>
        <a href="about.php" class="nav-link">About Us</a>
        <a href="contact.php" class="nav-link active">Contact</a>

        <!-- Mobile Only Action Buttons -->
        <div class="mobile-menu-buttons">
          <a href="portal/index.php" class="btn btn-accent">Portal</a>
          <a href="portal/admin/login.php" class="btn btn-outline"
            style="border-color:rgba(30,58,138,0.3);font-size:0.85rem;">Admin Portal</a>
          <a href="admissions.php" class="btn btn-primary">Apply Now</a>
        </div>

      </nav>
      <div class="header-buttons">
        <a href="portal/admin/login.php" class="btn btn-outline"
          style="padding:0.5rem 1rem;font-size:0.8rem;border-color:rgba(30,58,138,0.3);">Admin Portal</a>
        <a href="portal/index.php" class="btn btn-accent" id="portalBtn">Portal</a>
        <a href="admissions.php" class="btn btn-primary">Apply Now</a>
      </div>
      <button class="nav-toggle" id="navToggle" aria-label="Toggle Navigation">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- Page Hero -->
  <section class="page-hero">
    <div class="container">
      <div class="breadcrumb">
        <a href="index.php">Home</a>
        <span>›</span>
        <span style="color:white;">Contact Us</span>
      </div>
      <h1>Get in Touch</h1>
      <p>We'd love to hear from you. Reach out for admissions questions, campus tours, or just to say hello!</p>
    </div>
  </section>

  <!-- Page Content -->
  <div class="contact-page-wrapper">

    <!-- Contact Info Cards -->
    <div class="contact-cards-row">
      <div class="contact-card">
        <div class="contact-card-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
            <circle cx="12" cy="10" r="3" />
          </svg>
        </div>
        <h3>Our Address</h3>
        <p>Circular road HBL microfinance bank<br>Haripur, KPK, Pakistan</p>
        <a href="https://www.google.com/maps/search/?api=1&query=Kindergarten+Saadia%27s+Montessori+School+Circular+Road+Haripur"
          target="_blank">Get Directions →</a>
      </div>

      <div class="contact-card">
        <div class="contact-card-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path
              d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
          </svg>
        </div>
        <h3>Phone Number</h3>
        <p>Main Office: +92 348 9898618<br>Admissions: +92 333 3660174</p>
        <a href="tel:+923489898618">Call Us →</a>
      </div>

      <div class="contact-card">
        <div class="contact-card-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
            <polyline points="22,6 12,13 2,6" />
          </svg>
        </div>
        <h3>Email Us</h3>
        <p>General: tsaadia784@gmail.com<br>School: kindergartenmontessori1@gmail.com</p>
        <a href="mailto:kindergartenmontessori1@gmail.com">Send Email →</a>
      </div>
    </div>

    <!-- Main Two-Column Section -->
    <div class="contact-main-grid">

      <!-- Contact Form -->
      <div class="contact-form-card">
        <h2>Send Us a Message</h2>
        <p>Fill out the form below and we'll get back to you within one business day.</p>
        <form id="contactForm" novalidate>
          <div class="contact-form-row">
            <div class="form-group">
              <label for="contactName">Full Name *</label>
              <input type="text" id="contactName" name="name" class="form-control" placeholder="Your full name" pattern="[A-Za-z\s]{2,50}" maxlength="50" title="Only letters and spaces allowed" required>
            </div>
            <div class="form-group">
              <label for="contactPhone">Phone Number</label>
              <input type="tel" id="contactPhone" name="phone" class="form-control" placeholder="+92 300 0000000" pattern="^(\+92|0)[0-9]{10}$" maxlength="13" title="Enter a valid 11-digit phone number">
            </div>
          </div>
          <div class="form-group">
            <label for="contactEmail">Email Address *</label>
            <input type="email" id="contactEmail" name="email" class="form-control" placeholder="your@email.com" maxlength="100" required>
          </div>
          <div class="form-group">
            <label for="contactSubject">Subject *</label>
            <select id="contactSubject" name="subject" class="form-control" required>
              <option value="" disabled selected>Select a topic</option>
              <option value="admissions">Admissions Inquiry</option>
              <option value="tour">Schedule a Campus Tour</option>
              <option value="programs">Program Information</option>
              <option value="fees">Tuition & Fees</option>
              <option value="general">General Question</option>
            </select>
          </div>
          <div class="form-group">
            <label for="contactMessage">Message *</label>
            <textarea id="contactMessage" name="message" class="form-control" rows="5" placeholder="How can we help you?"
              minlength="10" maxlength="1000" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%; justify-content: center;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="22" y1="2" x2="11" y2="13" />
              <polygon points="22 2 15 22 11 13 2 9 22 2" />
            </svg>
            Send Message
          </button>
        </form>
        <div class="contact-form-success" id="contactSuccess">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <polyline points="22 4 12 14.01 9 11.01" />
          </svg>
          <h3>Message Sent!</h3>
          <p>Thank you for reaching out. A member of our team will respond to you within 24 hours.</p>
        </div>
      </div>

      <!-- Side Info -->
      <div class="contact-side">

        <!-- Office Hours -->
        <div class="office-hours-card">
          <div class="office-hours-card-header">
            🕐 Office Hours
          </div>
          <div class="office-hours-table">
            <div class="hours-row">
              <span class="hours-day">Monday</span>
              <span class="hours-time">8:00 AM – 2:00 PM</span>
            </div>
            <div class="hours-row">
              <span class="hours-day">Tuesday</span>
              <span class="hours-time">8:00 AM – 2:00 PM</span>
            </div>
            <div class="hours-row">
              <span class="hours-day">Wednesday</span>
              <span class="hours-time">8:00 AM – 2:00 PM</span>
            </div>
            <div class="hours-row">
              <span class="hours-day">Thursday</span>
              <span class="hours-time">8:00 AM – 2:00 PM</span>
            </div>
            <div class="hours-row">
              <span class="hours-day">Friday</span>
              <span class="hours-time">8:00 AM – 2:00 PM</span>
            </div>
            <div class="hours-row">
              <span class="hours-day">Saturday</span>
              <span class="hours-time">8:00 AM – 2:00 PM</span>
            </div>
            <div class="hours-row">
              <span class="hours-day">Sunday</span>
              <span class="hours-badge-closed">Closed</span>
            </div>
          </div>
          <div style="padding: 0.75rem 1rem; font-size: 0.8rem; color: #6b7280; border-top: 1px solid var(--border-color); background: #f9fafb; text-align: center;">
            * Note: These timings are for administration and management only and are not student class timings.
          </div>
        </div>

        <!-- Map -->
        <div class="contact-map-card">
          <div class="map-header">📍 Find Us on the Map</div>
          <div class="map-embed">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3307.705748870337!2d72.93316067499752!3d34.00009107317665!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38dfb37001d2f68f%3A0x15918636c55c1c6b!2sKindergarten%20Saadia%27s%20Montessori%20School!5e0!3m2!1sen!2s!4v1788949841660!5m2!1sen!2s"
              width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
          <p class="map-address">📍 Circular road HBL microfinance bank, Haripur, Pakistan — <a
              href="https://www.google.com/maps/search/?api=1&query=Kindergarten+Saadia%27s+Montreal+School+Circular+Road+Haripur"
              target="_blank" style="color:var(--primary-deep); font-weight:600;">Open in Maps</a></p>
        </div>
      </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section">
      <h2>Frequently Asked Questions</h2>
      <p>Quick answers tailored for parents regarding our classes, curriculum, and admissions.</p>
      <div class="faq-grid">
        <div class="faq-item">
          <h4>What are the school timings for each class level?</h4>
          <p>
            Playgroup: 9:00 AM – 12:30 PM<br>
            Nursery: 8:30 AM – 12:30 PM<br>
            Prep – Grade 5: 7:45 AM – 1:30 PM
          </p>
        </div>
        <div class="faq-item">
          <h4>What is the age requirement for Playgroup and Nursery?</h4>
          <p>For Playgroup, children should be 2.5 to 3.5 years old, focusing on sensory exploration and motor skills. Nursery is designed for 3.5 to 4.5 years, introducing early phonics and numbers.</p>
        </div>
        <div class="faq-item">
          <h4>Is Grade One to Five suitable for transitioning to big schools?</h4>
          <p>Yes! Our primary and junior classes build strong academic foundations, independent study habits, and bilingual fluency, making it seamless for children to transition to top mainstream schools.</p>
        </div>
        <div class="faq-item">
          <h4>Do you offer sibling concessions on tuition fees?</h4>
          <p>Yes, we understand family budgeting. We offer a special sibling discount policy (10% for the second child and 15% for additional siblings) to ease the fee structure for parents.</p>
        </div>
        <div class="faq-item">
          <h4>How do you ensure student safety and secure pick-up?</h4>
          <p>Our campus is securely located on Circular Road with strict gate monitoring. Children are only handed over to verified parents or authorized guardians carrying school pickup cards.</p>
        </div>
        <div class="faq-item">
          <h4>When should we apply for admission?</h4>
          <p>Our primary admission intake opens from January through March for the upcoming academic session starting in September, but inquiry forms remain open year-round based on seat availability.</p>
        </div>
      </div>
    </div>

  </div>

  <!-- Footer -->
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
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              style="color: var(--accent-warm); flex-shrink: 0;">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <span>Circular Road, HBL Microfinance Bank Haripur, Pakistan</span>
          </li>
          <li>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              style="color: var(--accent-warm); flex-shrink: 0;">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
            </svg>
            <span>+92 331 5620055</span>
          </li>
          <li>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              style="color: var(--accent-warm); flex-shrink: 0;">
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

  <script src="assets/js/app.js"></script>
  <script>
    const contactForm = document.getElementById('contactForm');
    const contactSuccess = document.getElementById('contactSuccess');
    if (contactForm) {
      contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = contactForm.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Sending...';

        const formData = new FormData(contactForm);
        const data = {
          name: formData.get('name'),
          email: formData.get('email'),
          subject: formData.get('subject') || 'General Inquiry',
          message: formData.get('message')
        };

        try {
          const res = await fetch('contact.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
          });
          const result = await res.json();
          if (result.success) {
            contactForm.style.display = 'none';
            contactSuccess.style.display = 'block';
          } else {
            alert(result.message || 'Failed to send message.');
            btn.disabled = false;
            btn.innerHTML = 'Send Message';
          }
        } catch (err) {
          alert('Server error. Please try again.');
          btn.disabled = false;
          btn.innerHTML = 'Send Message';
        }
      });
    }
  </script>
</body>

</html>