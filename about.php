<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Learn about Kindergarten Saadia's Montessori School — our history, mission, values, and the passionate educators who guide our children.">
  <title>About Us | Kindergarten Saadia's Montessori School</title>
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
        <a href="https://www.youtube.com/@kindergartensaadiasmontess9970" aria-label="YouTube" target="_blank"
          rel="noopener noreferrer">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path
              d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z" />
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
        <a href="about.php" class="nav-link active">About Us</a>
        <a href="contact.php" class="nav-link">Contact</a>

        <!-- Mobile Only Action Buttons -->
        <div class="mobile-menu-buttons">
          <a href="portal/index.php" class="btn btn-accent">Portal</a>
          <a href="index.php#admissions" class="btn btn-primary">Apply Now</a>
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
        <span style="color:white;">About Us</span>
      </div>
      <h1>About Our School</h1>
      <p>Rooted in authentic Montessori principles, we have been shaping independent, curious, and compassionate
        learners since 2009.</p>
    </div>
  </section>

  <!-- Main Page Grid -->
  <div class="about-page-grid">

    <!-- Sidebar -->
    <aside class="about-sidebar">
      <nav class="sidebar-nav">
        <div class="sidebar-nav-title">On This Page</div>
        <a href="#our-story" class="sidebar-nav-link active">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
          </svg>
          Our Story
        </a>
        <a href="#our-values" class="sidebar-nav-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path
              d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
          </svg>
          Our Values
        </a>
        <a href="#our-history" class="sidebar-nav-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
          </svg>
          History
        </a>
        <a href="#our-team" class="sidebar-nav-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
          Our Team
        </a>
        <a href="contact.php" class="sidebar-nav-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path
              d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
          </svg>
          Contact Us
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="about-content-main">

      <div class="content-section" id="our-story">
        <h2>Our Story</h2>
        <p>Kindergarten Saadia's Montessori School was founded with a heartfelt vision by <strong>Saadia Tariq</strong> — a dedicated Pakistani mother and educator who understood the challenges families in Haripur face when searching for quality early childhood education. Growing up in a society where rote learning often takes the place of genuine understanding, she set out to build something different: a place where every child is seen, heard, and nurtured as an individual.</p>
        <p style="margin-top:1rem;">In Pakistan, parents work hard to give their children the best possible start in life. At KSM Haripur, we honour that effort by providing a Montessori environment rooted in respect, discipline, and love of learning — values that resonate deeply within our culture. From Playgroup through Grade Three, our school bridges the Montessori philosophy with the hopes and aspirations of Pakistani families, preparing children not just academically but as confident, responsible human beings.</p>
        <p style="margin-top:1rem;">Under the leadership of Saadia Tariq as Founder and Managing Director, our school continues to grow — guided by the belief that when you invest in a child's earliest years, you invest in the future of a whole family and community.</p>
        <div
          style="margin-top:2rem; padding: 2rem; background: var(--primary-bg); border-radius: var(--radius-md); border-left: 4px solid var(--primary-deep);">
          <p
            style="font-size:1.1rem; font-style:italic; color:var(--primary-deep); font-family:var(--font-heading); font-weight:600; margin:0;">
            "Free The Child's Potential and you will Transform him into the world."</p>
          <p style="margin-top:0.5rem; font-size:0.9rem; color:var(--text-medium);">— Saadia Tariq, Founder &amp; Managing Director, Kindergarten Saadia's Montessori School</p>
        </div>
      </div>

      <div class="content-section" id="our-values">
        <h2>Our Core Values</h2>
        <p>Every decision we make is guided by four foundational values that define who we are.</p>
        <div class="values-grid">
          <div class="value-card">
            <div class="value-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
              </svg></div>
            <h3>Safety & Trust</h3>
            <p>We create warm, structured spaces where children feel secure enough to take risks, ask questions, and
              express themselves freely.</p>
          </div>
          <div class="value-card">
            <div class="value-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg></div>
            <h3>Curiosity & Discovery</h3>
            <p>We ignite and protect children's natural curiosity, providing materials and experiences that deepen
              wonder rather than replace it.</p>
          </div>
          <div class="value-card">
            <div class="value-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg></div>
            <h3>Community & Respect</h3>
            <p>Multi-age classrooms and collaborative projects teach children mutual respect, leadership, and empathy
              for one another.</p>
          </div>
          <div class="value-card">
            <div class="value-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
              </svg></div>
            <h3>Excellence & Growth</h3>
            <p>We hold high standards for every student, guide, and parent interaction — continuously learning and
              improving together.</p>
          </div>
        </div>
      </div>

      <div class="content-section" id="our-history">
        <h2>Our History</h2>
        <p>A journey built on passion, community trust, and a commitment to quality education in Haripur.</p>
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-year">2024</div>
            <h3>School Founded</h3>
            <p>Kindergarten Saadia's Montessori School opens its doors in Haripur, KPK, welcoming its first batch of students with a vision to bring quality Montessori education to Pakistani families.</p>
          </div>
          <div class="timeline-item">
            <div class="timeline-year">2024</div>
            <h3>PSRA Registered</h3>
            <p>The school is officially certified and registered under the <strong>Punjab/KPK Private Schools Regulatory Authority (PSRA)</strong>, meeting all government standards for private educational institutions in Pakistan.</p>
          </div>
          <div class="timeline-item">
            <div class="timeline-year">2024</div>
            <h3>Montessori Curriculum Adopted</h3>
            <p>A structured Montessori curriculum is introduced across Playgroup and Nursery levels, blending internationally recognised child development principles with the cultural context of Pakistani families.</p>
          </div>
          <div class="timeline-item">
            <div class="timeline-year">2025</div>
            <h3>Prep & Primary Classes Launched</h3>
            <p>Due to strong parent demand and community trust, Prep, Grade One, and Grade Two classes are added — giving families a full early education pathway under one roof.</p>
          </div>
          <div class="timeline-item">
            <div class="timeline-year">2025</div>
            <h3>Annual Events & Community Programmes</h3>
            <p>The school launches its annual sports day, art exhibitions, and cultural events — celebrating Pakistani traditions while encouraging creativity, teamwork, and confidence in every child.</p>
          </div>
          <div class="timeline-item">
            <div class="timeline-year">2026</div>
            <h3>Growing Strong</h3>
            <p>KSM Haripur now serves students from Playgroup through Grade Three, with a dedicated team of trained educators and the continued trust of hundreds of families across Haripur.</p>
          </div>
        </div>
      </div>

      <div class="content-section" id="our-team">
        <h2>Our Leadership Team</h2>
        <p>Behind every happy child is a passionate educator who goes above and beyond every single day.</p>
        <div class="team-grid">
          <div class="team-card">
            <div class="team-avatar" style="background: linear-gradient(135deg, #EFF6FF, #DBEAFE);">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
            </div>
            <div class="team-info">
              <h3>Saadia Tariq</h3>
              <p class="team-role">Founder &amp; Managing Director</p>
              <p class="team-bio">Founder of KSM Haripur, driven by a passion for quality early education and a deep commitment to the families of this community.</p>
            </div>
          </div>
          <div class="team-card">
            <div class="team-avatar" style="background: linear-gradient(135deg, #FEF3C7, #FDE68A);">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
            </div>
            <div class="team-info">
              <h3>Ghazala Shakeel Qureshi</h3>
              <p class="team-role">Principal &amp; Academic Head</p>
              <p class="team-bio">Experienced educator overseeing academics and day-to-day school operations, ensuring every classroom meets the highest teaching standards.</p>
            </div>
          </div>
          <div class="team-card">
            <div class="team-avatar" style="background: linear-gradient(135deg, #D1FAE5, #A7F3D0);">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
            </div>
            <div class="team-info">
              <h3>Sidra Sadique</h3>
              <p class="team-role">Coordinator</p>
              <p class="team-bio">Keeps communication flowing between parents, teachers, and management — making every family's experience smooth and welcoming.</p>
            </div>
          </div>
        </div>
      </div>

    </main>
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
</body>

</html>
