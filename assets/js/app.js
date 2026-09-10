document.addEventListener('DOMContentLoaded', () => {
  // Toggle Extra Gallery Items
  const toggleGalleryBtn = document.getElementById('toggleGalleryBtn');
  const hiddenGalleryItems = document.querySelectorAll('.gallery-item.gallery-hidden');

  if (toggleGalleryBtn) {
    let isExpanded = false;

    toggleGalleryBtn.addEventListener('click', () => {
      isExpanded = !isExpanded;

      hiddenGalleryItems.forEach(item => {
        if (isExpanded) {
          item.classList.remove('gallery-hidden');
          item.style.opacity = '0';
          item.style.transform = 'scale(0.95)';
          setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'scale(1)';
          }, 50);
        } else {
          item.classList.add('gallery-hidden');
        }
      });

      // Update button text and icon orientation
      const btnText = toggleGalleryBtn.querySelector('span');
      if (isExpanded) {
        btnText.textContent = 'Show Less';
        toggleGalleryBtn.classList.add('expanded');
      } else {
        btnText.textContent = 'View Full Gallery';
        toggleGalleryBtn.classList.remove('expanded');
      }
    });
  }
  // 0. Preloader Logic
  const preloader = document.getElementById('preloader');

  if (preloader) {
    window.addEventListener('load', () => {
      setTimeout(() => {
        preloader.style.opacity = '0';
        preloader.style.visibility = 'hidden';
        document.body.classList.remove('preloader-active');
      }, 500);
    });

    const allLinks = document.querySelectorAll('a');
    allLinks.forEach(link => {
      link.addEventListener('click', function (e) {
        const target = this.getAttribute('href');
        if (target && target !== '#' && !target.startsWith('#') && !target.startsWith('javascript')) {
          if (this.target !== '_blank') {
            e.preventDefault();
            document.body.classList.add('preloader-active');
            preloader.style.opacity = '1';
            preloader.style.visibility = 'visible';

            setTimeout(() => {
              window.location.href = target;
            }, 800);
          }
        }
      });
    });
  }

  // 1. Inverted Scroll Hide/Show Header Logic
  const header = document.getElementById('header');
  let lastScrollY = window.scrollY;

  if (header) {
    window.addEventListener('scroll', () => {
      const currentScrollY = window.scrollY;

      // Reset to default at top of page
      if (currentScrollY <= 50) {
        header.classList.remove('header-hidden');
        header.classList.remove('scrolled');
        lastScrollY = currentScrollY;
        return;
      }

      // Add drop shadow once scrolled past top bar
      header.classList.add('scrolled');

      // Check scroll direction (5px threshold prevents jittering)
      if (Math.abs(currentScrollY - lastScrollY) > 5) {
        if (currentScrollY > lastScrollY) {
          // Scrolling DOWN -> Re-appear (Show Header)
          header.classList.remove('header-hidden');
        } else {
          // Scrolling UP -> Hide Header
          header.classList.add('header-hidden');
        }
        lastScrollY = currentScrollY;
      }
    });
  }

  // 2. Mobile Navigation Menu Toggle
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
      navMenu.classList.toggle('active');
      navToggle.classList.toggle('active');

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

  // 3. Fixed Active Link Highlighting on Scroll
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      if (window.scrollY >= (sectionTop - 150)) {
        current = section.getAttribute('id');
      }
    });

    navLinks.forEach(link => {
      link.classList.remove('active');
      const href = link.getAttribute('href');
      if (current && href && href.includes(`#${current}`)) {
        link.classList.add('active');
      }
    });
  });

  // 4. Hero Slider / Slideshow
  const slides = document.querySelectorAll('.hero-slide');
  const dotsContainer = document.getElementById('sliderDots');
  const prevBtn = document.getElementById('sliderPrev');
  const nextBtn = document.getElementById('sliderNext');
  let currentSlide = 0;
  let slideInterval;

  if (slides.length > 0 && dotsContainer) {
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
          if (dots[idx]) dots[idx].classList.add('active');
        } else {
          slide.classList.remove('active');
          if (dots[idx]) dots[idx].classList.remove('active');
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

  // 5. Stats Counter Animation
  const statsCounters = document.querySelectorAll('.stat-counter');

  if (statsCounters.length > 0) {
    const startCounterAnimation = (element) => {
      const target = +element.getAttribute('data-target');
      const duration = 2000;
      const increment = target / (duration / 16);
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
          entry.target.innerText = '0';
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

  // 7. Newsletter Form Submit
  const newsletterForm = document.getElementById('newsletterForm');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const emailInput = newsletterForm.querySelector('input');
      alert(`Thank you for subscribing with ${emailInput.value}!`);
      emailInput.value = '';
    });
  }

  // 8. Scroll Animations
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
        entry.target.classList.remove('show-anim');
      }
    });
  }, { threshold: 0.12 });

  scrollElements.forEach(el => scrollObserver.observe(el));
  slideElements.forEach(el => scrollObserver.observe(el));
});

// 9. Lightbox Popup Logic for Event Photo Sets
let currentEventMedia = [];
let currentMediaIndex = 0;

function openEventLightbox(element) {
  const dataEl = element.querySelector('.event-photos-data');
  if (!dataEl) return;

  const mediaString = dataEl.getAttribute('data-photos');
  if (!mediaString) return;

  currentEventMedia = mediaString.split(',');
  currentMediaIndex = 0;

  const lightboxModal = document.getElementById('eventLightbox');
  if (lightboxModal) {
    lightboxModal.style.display = 'flex';
    displayCurrentMedia();
  }
}

function closeEventLightbox() {
  const lightboxModal = document.getElementById('eventLightbox');
  const videoPlayer = document.getElementById('lightboxVideo');

  if (videoPlayer) {
    videoPlayer.pause();
    videoPlayer.src = '';
  }

  if (lightboxModal) {
    lightboxModal.style.display = 'none';
  }
}

function displayCurrentMedia() {
  const imgPlayer = document.getElementById('lightboxImg');
  const videoPlayer = document.getElementById('lightboxVideo');
  const currentFile = currentEventMedia[currentMediaIndex].trim();

  // Check if file extension is a video format
  const isVideo = currentFile.endsWith('.mp4') || currentFile.endsWith('.webm') || currentFile.endsWith('.ogg');

  if (isVideo) {
    imgPlayer.style.display = 'none';
    videoPlayer.style.display = 'block';
    videoPlayer.src = currentFile;
    videoPlayer.play();
  } else {
    videoPlayer.pause();
    videoPlayer.style.display = 'none';
    imgPlayer.style.display = 'block';
    imgPlayer.src = currentFile;
  }
}

function changeLightboxImg(direction) {
  if (currentEventMedia.length <= 1) return;

  currentMediaIndex += direction;
  if (currentMediaIndex < 0) {
    currentMediaIndex = currentEventMedia.length - 1;
  }
  if (currentMediaIndex >= currentEventMedia.length) {
    currentMediaIndex = 0;
  }

  displayCurrentMedia();
}