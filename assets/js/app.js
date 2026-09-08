document.addEventListener('DOMContentLoaded', () => {

  /* ============================================================
     1. PRELOADER
     ============================================================ */
  const preloader = document.getElementById('preloader');
  if (preloader) {
    const hide = () => preloader.classList.add('hidden');
    if (document.readyState === 'complete') {
      setTimeout(hide, 900);
    } else {
      window.addEventListener('load', () => setTimeout(hide, 900));
    }
  }

  /* ============================================================
     2. SCROLL PROGRESS BAR
     ============================================================ */
  const progressBar = document.createElement('div');
  progressBar.id = 'scrollProgress';
  document.body.prepend(progressBar);

  /* ============================================================
     3. STICKY HEADER + FROSTED GLASS + BACK-TO-TOP + PROGRESS
     ============================================================ */
  const header = document.getElementById('header');
  const backToTopBtn = document.getElementById('backToTop');

  window.addEventListener('scroll', () => {
    const scrollY = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;

    // Scroll progress bar
    progressBar.style.width = `${Math.min((scrollY / docHeight) * 100, 100)}%`;

    // Frosted glass header
    if (header) {
      header.classList.toggle('scrolled', scrollY > 50);
    }

    // Back-to-top visibility
    if (backToTopBtn) {
      backToTopBtn.classList.toggle('visible', scrollY > 450);
    }

    // Active nav link on scroll
    let current = '';
    sections.forEach(section => {
      if (scrollY >= section.offsetTop - 200) {
        current = section.getAttribute('id') || '';
      }
    });
    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') && link.getAttribute('href').includes(current)) {
        link.classList.add('active');
      }
    });
  }, { passive: true });

  /* ============================================================
     4. BACK TO TOP
     ============================================================ */
  if (backToTopBtn) {
    backToTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* ============================================================
     5. MOBILE NAVIGATION TOGGLE
     ============================================================ */
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');

  if (navToggle && navMenu) {
    const spans = navToggle.querySelectorAll('span');

    const openMenu = () => {
      navMenu.classList.add('active');
      navToggle.classList.add('active');
      spans[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
      spans[1].style.opacity = '0';
      spans[2].style.transform = 'rotate(-45deg) translate(6px, -6px)';
    };

    const closeMenu = () => {
      navMenu.classList.remove('active');
      navToggle.classList.remove('active');
      spans[0].style.transform = 'none';
      spans[1].style.opacity = '1';
      spans[2].style.transform = 'none';
    };

    navToggle.addEventListener('click', () => {
      navMenu.classList.contains('active') ? closeMenu() : openMenu();
    });

    navMenu.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', closeMenu);
    });
  }

  /* ============================================================
     6. ACTIVE NAV SECTIONS (reference for scroll handler above)
     ============================================================ */
  const sections = document.querySelectorAll('section');
  const navLinks = document.querySelectorAll('.nav-link');

  /* ============================================================
     7. SCROLL REVEAL ENGINE
     Programmatically assign data-reveal to key elements
     ============================================================ */
  const revealConfigs = [
    { selector: '.slogan-banner',                  reveal: 'up',    delays: [] },
    { selector: '.about-image-wrapper',            reveal: 'left',  delays: [] },
    { selector: '.about-info',                     reveal: 'right', delays: [] },
    { selector: '.about-feat-item',                reveal: 'up',    delays: ['1','2','3','4'] },
    { selector: '.program-card',                   reveal: 'up',    delays: ['1','2','3'] },
    { selector: '.stat-item',                      reveal: 'scale', delays: ['1','2','3','4'] },
    { selector: '.step-item',                      reveal: 'left',  delays: ['1','2','3'] },
    { selector: '.inquiry-card',                   reveal: 'right', delays: [] },
    { selector: '.admissions-info .section-title', reveal: 'up',    delays: [] },
    { selector: '.gallery-item',                   reveal: 'scale', delays: ['1','2','3','4','5','6'] },
  ];

  revealConfigs.forEach(({ selector, reveal, delays }) => {
    document.querySelectorAll(selector).forEach((el, i) => {
      if (!el.hasAttribute('data-reveal')) {
        el.setAttribute('data-reveal', reveal);
        if (delays.length > 0 && delays[i]) {
          el.setAttribute('data-reveal-delay', delays[i]);
        }
      }
    });
  });

  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('revealed');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -55px 0px' });

  document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el));

  /* ============================================================
     8. HERO SLIDER with Ken Burns
     ============================================================ */
  const slides = document.querySelectorAll('.hero-slide');
  const dotsContainer = document.getElementById('sliderDots');
  const sliderPrev = document.getElementById('sliderPrev');
  const sliderNext = document.getElementById('sliderNext');
  let currentSlide = 0;
  let slideInterval;

  if (slides.length > 0 && dotsContainer) {
    // Build dots
    slides.forEach((_, idx) => {
      const dot = document.createElement('div');
      dot.classList.add('slider-dot');
      if (idx === 0) dot.classList.add('active');
      dot.addEventListener('click', () => goToSlide(idx));
      dotsContainer.appendChild(dot);
    });

    const dots = document.querySelectorAll('.slider-dot');

    function updateSlides() {
      slides.forEach((slide, idx) => {
        slide.classList.toggle('active', idx === currentSlide);
        dots[idx].classList.toggle('active', idx === currentSlide);
      });
    }

    function nextSlide() {
      currentSlide = (currentSlide + 1) % slides.length;
      updateSlides();
    }

    function prevSlide() {
      currentSlide = (currentSlide - 1 + slides.length) % slides.length;
      updateSlides();
    }

    function goToSlide(idx) {
      currentSlide = idx;
      updateSlides();
      resetInterval();
    }

    function resetInterval() {
      clearInterval(slideInterval);
      slideInterval = setInterval(nextSlide, 6000);
    }

    if (sliderNext) sliderNext.addEventListener('click', () => { nextSlide(); resetInterval(); });
    if (sliderPrev) sliderPrev.addEventListener('click', () => { prevSlide(); resetInterval(); });

    slideInterval = setInterval(nextSlide, 6000);
  }

  /* ============================================================
     9. STATS COUNTER with Ease-Out easing
     ============================================================ */
  const statsCounters = document.querySelectorAll('.stat-counter');

  if (statsCounters.length > 0) {
    const easeOutQuart = t => 1 - Math.pow(1 - t, 4);

    const startCounter = (el) => {
      const target = +el.getAttribute('data-target');
      const suffix = target > 50 ? '+' : '';
      const duration = 1800;
      let startTime = null;

      const step = (timestamp) => {
        if (!startTime) startTime = timestamp;
        const elapsed = Math.min((timestamp - startTime) / duration, 1);
        const eased = easeOutQuart(elapsed);
        el.textContent = Math.ceil(eased * target) + suffix;
        if (elapsed < 1) requestAnimationFrame(step);
        else el.textContent = target + suffix;
      };
      requestAnimationFrame(step);
    };

    const statsObserver = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          startCounter(entry.target);
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    statsCounters.forEach(c => statsObserver.observe(c));
  }

  /* ============================================================
     10. GALLERY FILTER with smooth animation
     ============================================================ */
  const filterButtons = document.querySelectorAll('.filter-btn');
  const galleryItems = document.querySelectorAll('.gallery-item');

  if (filterButtons.length > 0 && galleryItems.length > 0) {
    filterButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        filterButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const filter = btn.getAttribute('data-filter');
        let visibleIndex = 0;

        galleryItems.forEach(item => {
          const category = item.getAttribute('data-category');
          const matches = filter === 'all' || category === filter;

          if (matches) {
            item.classList.remove('filter-hidden');
            item.style.display = '';
            item.style.transitionDelay = `${visibleIndex * 0.07}s`;
            visibleIndex++;
          } else {
            item.style.transitionDelay = '0s';
            item.classList.add('filter-hidden');
            // Keep in DOM flow for layout stability
            setTimeout(() => {
              if (item.classList.contains('filter-hidden')) {
                item.style.display = 'none';
              }
            }, 400);
          }
        });
      });
    });
  }

  /* ============================================================
     11. ADMISSIONS FORM WIZARD (multi-step)
     ============================================================ */
  const inquiryForm = document.getElementById('inquiryForm');
  const formSteps = document.querySelectorAll('.form-step');
  const progressSteps = document.querySelectorAll('.progress-step');
  const progressLine = document.getElementById('progressLine');
  const nextBtn = document.getElementById('nextBtn');
  const prevBtn = document.getElementById('prevBtn');
  const formSuccess = document.getElementById('formSuccess');

  if (inquiryForm && formSteps.length > 0) {
    let currentStep = 1;
    const totalSteps = formSteps.length;

    const updateProgress = () => {
      progressSteps.forEach((step, idx) => {
        step.classList.remove('active', 'completed');
        if (idx + 1 < currentStep) step.classList.add('completed');
        if (idx + 1 === currentStep) step.classList.add('active');
      });
      if (progressLine) {
        const pct = ((currentStep - 1) / (totalSteps - 1)) * 100;
        progressLine.style.width = pct + '%';
      }
      if (prevBtn) {
        prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-flex';
      }
      if (nextBtn) {
        nextBtn.textContent = currentStep === totalSteps ? 'Submit' : 'Next →';
      }
    };

    const showStep = (step) => {
      formSteps.forEach(s => {
        s.classList.remove('active');
        s.style.display = 'none';
      });
      const target = document.querySelector(`.form-step[data-step="${step}"]`);
      if (target) {
        target.style.display = 'block';
        // Trigger reflow for animation restart
        void target.offsetWidth;
        target.classList.add('active');
      }
      updateProgress();
    };

    const validateStep = () => {
      const currentEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
      if (!currentEl) return true;
      let isValid = true;
      currentEl.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
          isValid = false;
          field.style.borderColor = '#EF4444';
          field.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.15)';
          field.animate([
            { transform: 'translateX(-6px)' },
            { transform: 'translateX(6px)' },
            { transform: 'translateX(-4px)' },
            { transform: 'translateX(4px)' },
            { transform: 'translateX(0)' }
          ], { duration: 350, easing: 'ease-out' });
          field.addEventListener('input', () => {
            field.style.borderColor = '';
            field.style.boxShadow = '';
          }, { once: true });
        }
      });
      return isValid;
    };

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        if (!validateStep()) return;

        if (currentStep < totalSteps) {
          currentStep++;
          showStep(currentStep);
        } else {
          // Submit - show success
          inquiryForm.style.display = 'none';
          const formHeader = document.querySelector('.form-header');
          const formProgress = document.querySelector('.form-progress');
          if (formHeader) formHeader.style.display = 'none';
          if (formProgress) formProgress.style.display = 'none';
          if (formSuccess) {
            formSuccess.style.display = 'block';
            formSuccess.style.animation = 'formStepIn 0.5s cubic-bezier(0.22,1,0.36,1) forwards';
          }
        }
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
          currentStep--;
          showStep(currentStep);
        }
      });
    }

    // Initialize wizard
    showStep(1);
  }

  /* ============================================================
     12. NEWSLETTER FORM with success feedback
     ============================================================ */
  const newsletterForm = document.getElementById('newsletterForm');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const emailInput = newsletterForm.querySelector('input[type="email"]');
      const btn = newsletterForm.querySelector('button[type="submit"]');
      if (!emailInput || !btn) return;

      const origText = btn.textContent;
      const origBg = btn.style.backgroundColor;

      btn.textContent = '✓ Subscribed!';
      btn.style.background = '#10B981';
      btn.style.color = 'white';
      btn.disabled = true;

      setTimeout(() => {
        btn.textContent = origText;
        btn.style.background = origBg;
        btn.style.color = '';
        btn.disabled = false;
        emailInput.value = '';
      }, 3000);
    });
  }

});
