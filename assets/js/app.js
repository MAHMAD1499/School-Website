document.addEventListener('DOMContentLoaded', () => {

  // 0. Preloader Logic
  const preloader = document.getElementById('preloader');

  if (preloader) {
    // Hide preloader when the page has fully loaded
    window.addEventListener('load', () => {
      setTimeout(() => {
        preloader.style.opacity = '0';
        preloader.style.visibility = 'hidden';
        document.body.classList.remove('preloader-active');
      }, 500); // 500ms delay to ensure the animation is seen
    });

    // Show preloader when clicking on links that navigate away
    const allLinks = document.querySelectorAll('a');
    allLinks.forEach(link => {
      link.addEventListener('click', function (e) {
        const target = this.getAttribute('href');

        // Only trigger preloader for external or actual page transitions, ignore # anchors or empty links
        if (target && target !== '#' && !target.startsWith('#') && !target.startsWith('javascript')) {
          // Check if it's not opening in a new tab
          if (this.target !== '_blank') {
            e.preventDefault();
            document.body.classList.add('preloader-active');
            preloader.style.opacity = '1';
            preloader.style.visibility = 'visible';

            // Navigate after animation delay
            setTimeout(() => {
              window.location.href = target;
            }, 800); // Wait 800ms for preloader to show
          }
        }
      });
    });
  }

  // 1. Sticky Header
  const header = document.getElementById('header');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });

  // 2. Mobile Navigation Menu Toggle
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
      navMenu.classList.toggle('active');
      navToggle.classList.toggle('active');

      // Animate hamburger lines
      const spans = navToggle.querySelectorAll('span');
      if (navToggle.classList.contains('active')) {
        spans[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(6px, -6px)';
      } else {
        spans[0].style.transform = 'none';
        spans[1].style.opacity = '1';
        spans[2].style.transform = 'none';
      }
    });

    // Close menu when clicking navigation links
    navMenu.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        navToggle.classList.remove('active');
        const spans = navToggle.querySelectorAll('span');
        spans[0].style.transform = 'none';
        spans[1].style.opacity = '1';
        spans[2].style.transform = 'none';
      });
    });
  }

  // Active Link on Scroll
  const sections = document.querySelectorAll('section');
  const navLinks = document.querySelectorAll('.nav-link');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.clientHeight;
      if (window.scrollY >= (sectionTop - 150)) {
        current = section.getAttribute('id');
      }
    });

    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href').includes(current)) {
        link.classList.add('active');
      }
    });
  });

  // 3. Hero Slider / Slideshow
  const slides = document.querySelectorAll('.hero-slide');
  const dotsContainer = document.getElementById('sliderDots');
  const prevBtn = document.getElementById('sliderPrev');
  const nextBtn = document.getElementById('sliderNext');
  let currentSlide = 0;
  let slideInterval;

  if (slides.length > 0) {
    // Generate Dots
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
        if (idx === currentSlide) {
          slide.classList.add('active');
          dots[idx].classList.add('active');
        } else {
          slide.classList.remove('active');
          dots[idx].classList.remove('active');
        }
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

    function startInterval() {
      slideInterval = setInterval(nextSlide, 6000);
    }

    function resetInterval() {
      clearInterval(slideInterval);
      startInterval();
    }

    if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetInterval(); });
    if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetInterval(); });

    startInterval();
  }

  // 4. Stats Counter Animation using Intersection Observer
  const statsCounters = document.querySelectorAll('.stat-counter');

  if (statsCounters.length > 0) {
    const startCounterAnimation = (element) => {
      const target = +element.getAttribute('data-target');
      const duration = 2000; // Animation duration in ms
      const increment = target / (duration / 16); // ~60fps
      let count = 0;

      const updateCount = () => {
        count += increment;
        if (count < target) {
          element.innerText = Math.ceil(count) + (target > 50 ? '+' : '');
          requestAnimationFrame(updateCount);
        } else {
          element.innerText = target + (target > 50 ? '+' : '');
        }
      };
      updateCount();
    };

    const statsObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          startCounterAnimation(entry.target);
        } else {
          entry.target.innerText = '0'; // Reset when out of view to re-trigger
        }
      });
    }, { threshold: 0.5 });

    statsCounters.forEach(counter => statsObserver.observe(counter));
  }



  // 6. News, Events & Gallery Filtering
  const filterButtons = document.querySelectorAll('.filter-btn');
  const galleryItems = document.querySelectorAll('.gallery-item');

  if (filterButtons.length > 0 && galleryItems.length > 0) {
    filterButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        // Toggle Active state on buttons
        filterButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const filter = btn.getAttribute('data-filter');

        galleryItems.forEach(item => {
          const category = item.getAttribute('data-category');
          if (filter === 'all' || category === filter) {
            item.style.display = 'block';
            setTimeout(() => {
              item.style.opacity = '1';
              item.style.transform = 'scale(1)';
            }, 50);
          } else {
            item.style.opacity = '0';
            item.style.transform = 'scale(0.8)';
            setTimeout(() => {
              item.style.display = 'none';
            }, 300);
          }
        });
      });
    });
  }



  // 8. Newsletter Form Submit
  const newsletterForm = document.getElementById('newsletterForm');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const emailInput = newsletterForm.querySelector('input');
      alert(`Thank you for subscribing with ${emailInput.value}!`);
      emailInput.value = '';
    });
  }

  // 9. Scroll Animations
  const scrollElements = document.querySelectorAll('.section-title, .section-subtitle, .about-image-wrapper, .about-info, .program-card, .stat-item, .admissions-info, .inquiry-card, .gallery-item, .mission-card, .animate-on-scroll');

  scrollElements.forEach((el) => {
    if (!el.classList.contains('slide-in-left') && !el.classList.contains('slide-in-right')) {
      el.classList.add('animate-on-scroll');
    }
  });

  const slideElements = document.querySelectorAll('.slide-in-left, .slide-in-right');

  const scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('show-anim');
      } else {
        entry.target.classList.remove('show-anim'); // Remove class when scrolled out to trigger again
      }
    });
  }, { threshold: 0.12 });

  scrollElements.forEach(el => scrollObserver.observe(el));
  slideElements.forEach(el => scrollObserver.observe(el));
});

// Auto-hide header on scroll down, reveal on scroll up
let lastScrollY = window.scrollY;
const headerElement = document.getElementById('header');

window.addEventListener('scroll', () => {
  const currentScrollY = window.scrollY;

  // Don't hide header if mobile menu is actively open
  const navMenu = document.getElementById('navMenu');
  if (navMenu && navMenu.classList.contains('active')) return;

  if (currentScrollY > lastScrollY && currentScrollY > 100) {
    // Scrolling down & past top threshold -> Hide header
    headerElement.classList.add('nav-hidden');
  } else {
    // Scrolling up -> Show header
    headerElement.classList.remove('nav-hidden');
  }

  lastScrollY = currentScrollY;
});