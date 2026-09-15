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
$body=json_decode(file_get_contents('php://input'),true)??[];if($isAjax){
  if($method==='POST'){
    $name=ksm_esc($body['name']??'');$email=ksm_esc($body['email']??'');$sub=ksm_esc($body['subject']??'');$msg=ksm_esc($body['message']??'');
    if(!$name||!$email||!$msg)ksm_err('Name, email and message required.');
    ksm_db()->query("INSERT INTO contacts(name,email,subject,message,status)VALUES('$name','$email','$sub','$msg','Unread')");
    ksm_json(['id'=>ksm_db()->insert_id],'Message sent.');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Contact Kindergarten Saadia's Montessori School — get in touch with our team for inquiries, admissions, and more.">
  <title>Contact Us — KSM Portal</title>
  <link rel="stylesheet" href="assets/portal.css">
</head>
<body>
<div class="portal-wrapper">
  <div class="portal-main">
    <div class="portal-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span class="topbar-title">Contact Us</span>
      </div>
      <div class="topbar-right"><div class="topbar-avatar">K</div></div>
    </div>

    <div class="portal-content fade-up">
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">📞 Contact Us</h1>
          <p class="page-subtitle">We'd love to hear from you — reach out anytime</p>
        </div>
      </div>

      <div class="contact-grid">
        <!-- Contact Info -->
        <div>
          <h2 style="font-size:1.1rem;margin-bottom:1.25rem;">Get In Touch</h2>

          <div class="contact-info-item">
            <div class="contact-icon">
              <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <div class="contact-info-title">School Address</div>
              <div class="contact-info-text">Street 4, Near District Courts,<br>Haripur, Khyber Pakhtunkhwa, Pakistan</div>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-icon">
              <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.62 3.38 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6.06 6.06l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
              <div class="contact-info-title">Phone Numbers</div>
              <div class="contact-info-text">+92 995 123456<br>+92 300 9876543</div>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-icon">
              <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div>
              <div class="contact-info-title">Email Addresses</div>
              <div class="contact-info-text">info@ksmmontessori.edu.pk<br>admissions@ksmmontessori.edu.pk</div>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-icon">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
              <div class="contact-info-title">Office Hours</div>
              <div class="contact-info-text">Monday – Friday: 8:00 AM – 3:00 PM<br>Saturday: 9:00 AM – 12:00 PM</div>
            </div>
          </div>

          <!-- Map Embed -->
          <div style="margin-top:1.5rem;border-radius:var(--radius-md);overflow:hidden;border:1px solid var(--border-color);">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d26522.77395059636!2d72.89497!3d33.99465!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38dfb9b60cd7ca05%3A0xfba7a35f0e6e7050!2sHaripur%2C%20Khyber%20Pakhtunkhwa%2C%20Pakistan!5e0!3m2!1sen!2s!4v1694000000000!5m2!1sen!2s"
              width="100%" height="220" style="border:0;display:block;" allowfullscreen="" loading="lazy"
              referrerpolicy="no-referrer-when-downgrade" title="KSM School Location"></iframe>
          </div>
        </div>

        <!-- Contact Form -->
        <div>
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">✉️ Send Us a Message</h2>
            </div>

            <div id="formSuccess" class="hidden" style="text-align:center;padding:2rem 1rem;">
              <div style="font-size:3rem;margin-bottom:1rem;">✅</div>
              <h3 style="color:var(--success);margin-bottom:0.5rem;">Message Sent!</h3>
              <p style="color:var(--text-medium);font-size:0.9rem;">Thank you for reaching out. We'll get back to you within 1–2 business days.</p>
              <button class="btn btn-primary" style="margin-top:1.25rem;" onclick="resetForm()">Send Another Message</button>
            </div>

            <form id="contactForm" onsubmit="submitContact(event)">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label" for="ctName">Your Name *</label>
                  <input type="text" id="ctName" class="form-control" placeholder="Full name" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="ctEmail">Email Address *</label>
                  <input type="email" id="ctEmail" class="form-control" placeholder="your@email.com" required>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" for="ctPhone">Phone Number</label>
                <input type="tel" id="ctPhone" class="form-control" placeholder="+92 300 0000000">
              </div>
              <div class="form-group">
                <label class="form-label" for="ctSubject">Subject *</label>
                <select id="ctSubject" class="form-control" required>
                  <option value="">Select a subject</option>
                  <option>Admission Inquiry</option>
                  <option>Fee Information</option>
                  <option>Program Information</option>
                  <option>Complaint / Feedback</option>
                  <option>General Inquiry</option>
                  <option>Other</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="ctMessage">Message *</label>
                <textarea id="ctMessage" class="form-control" rows="5" placeholder="Type your message here..." required></textarea>
              </div>
              <button type="submit" class="btn btn-primary w-full">📨 Send Message</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="portal-footer">© 2026 Kindergarten Saadia's Montessori School. All rights reserved.</div>
  </div>
</div>

<script src="assets/portal.js"></script>
<script src="assets/api.js"></script>
<script src="assets/sidebar.js"></script>
<script>
  buildSidebar('portal');

  async function submitContact(e) {
    e.preventDefault();
    const btn = e.submitter;
    btn.disabled = true;
    btn.textContent = 'Sending...';
    const msg = {
      name: document.getElementById('ctName').value,
      email: document.getElementById('ctEmail').value,
      subject: document.getElementById('ctSubject').value,
      message: document.getElementById('ctMessage').value,
    };
    try {
      const res = await API.submitContact(msg);
      if (res.success) {
        document.getElementById('contactForm').classList.add('hidden');
        document.getElementById('formSuccess').classList.remove('hidden');
        showToast('Message sent successfully!', 'success');
      } else {
        showToast(res.message || 'Error sending message.', 'error');
        btn.disabled = false;
        btn.textContent = '📨 Send Message';
      }
    } catch {
      showToast('Server error. Please try again later.', 'error');
      btn.disabled = false;
      btn.textContent = '📨 Send Message';
    }
  }

  function resetForm() {
    document.getElementById('contactForm').reset();
    document.getElementById('contactForm').classList.remove('hidden');
    document.getElementById('formSuccess').classList.add('hidden');
  }
</script>
</body>
</html>

