/* ===================================================
   PORTAL JAVASCRIPT — KSM School Portal
   Handles: localStorage CRUD, Auth, UI helpers, Toast
   =================================================== */

// ============================================================
// STORAGE HELPERS
// ============================================================
const DB = {
  get(key, fallback = []) {
    try {
      const v = localStorage.getItem('ksm_' + key);
      return v ? JSON.parse(v) : fallback;
    } catch { return fallback; }
  },
  set(key, value) {
    localStorage.setItem('ksm_' + key, JSON.stringify(value));
  },
  push(key, item) {
    const arr = DB.get(key);
    item.id = item.id || Date.now().toString();
    arr.push(item);
    DB.set(key, arr);
    return item;
  },
  update(key, id, updates) {
    const arr = DB.get(key);
    const idx = arr.findIndex(i => i.id === id);
    if (idx !== -1) { arr[idx] = { ...arr[idx], ...updates }; DB.set(key, arr); return arr[idx]; }
    return null;
  },
  delete(key, id) {
    const arr = DB.get(key).filter(i => i.id !== id);
    DB.set(key, arr);
  },
  clear(key) { localStorage.removeItem('ksm_' + key); }
};

// ============================================================
// SEED DEFAULT DATA (only if first visit)
// ============================================================
function seedData() {
  if (DB.get('seeded', false)) return;

  // Teachers
  DB.set('teachers', [
    { id: '1', name: 'Ms. Saadia Khan', role: 'Principal & Head Teacher', subject: 'Administration', emoji: '👩‍💼', bio: 'Certified Montessori educator with 15+ years of experience.' },
    { id: '2', name: 'Ms. Ayesha Raza', role: 'Senior Teacher', subject: 'Language & Literacy', emoji: '👩‍🏫', bio: 'Specializes in early childhood language development.' },
    { id: '3', name: 'Mr. Bilal Ahmed', role: 'Teacher', subject: 'Mathematics & Science', emoji: '👨‍🏫', bio: 'Passionate about making math fun for young learners.' },
    { id: '4', name: 'Ms. Fatima Malik', role: 'Teacher', subject: 'Art & Creativity', emoji: '👩‍🎨', bio: 'Art enthusiast promoting creative expression in children.' },
    { id: '5', name: 'Ms. Hira Yousuf', role: 'Teacher', subject: 'Physical Education', emoji: '🏃‍♀️', bio: 'Focused on gross motor development and healthy habits.' },
    { id: '6', name: 'Mr. Usman Tariq', role: 'Teaching Assistant', subject: 'General Support', emoji: '👨‍🎓', bio: 'Dedicated assistant supporting classroom activities.' },
  ]);

  // Gallery
  DB.set('gallery', [
    { id: '1', url: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600', caption: 'Children in the classroom' },
    { id: '2', url: 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?w=600', caption: 'Art and crafts session' },
    { id: '3', url: 'https://images.unsplash.com/photo-1551649001-7a2482d98d05?w=600', caption: 'Outdoor play time' },
    { id: '4', url: 'https://images.unsplash.com/photo-1567168539906-f3ae74aa64ec?w=600', caption: 'Reading circle time' },
    { id: '5', url: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=600', caption: 'Science experiments' },
    { id: '6', url: 'https://images.unsplash.com/photo-1534050359320-02900022671e?w=600', caption: 'Annual sports day' },
  ]);

  // News
  DB.set('news', [
    { id: '1', title: 'New Academic Year 2026-27 Begins!', body: 'We are thrilled to welcome all students back for an exciting new academic year. Classes begin September 15, 2026. New uniforms and stationery packs are available from the school office.', date: '2026-09-01', category: 'General' },
    { id: '2', title: 'Montessori Certification Achieved', body: 'Kindergarten Saadia\'s Montessori School has received renewed AMI (Association Montessori Internationale) certification for academic excellence.', date: '2026-08-20', category: 'Achievement' },
    { id: '3', title: 'Parent-Teacher Meeting Scheduled', body: 'The quarterly parent-teacher meeting is scheduled for September 25, 2026. Parents are requested to register their slots via the school office or admission desk.', date: '2026-08-10', category: 'Event' },
  ]);

  // Events
  DB.set('events', [
    { id: '1', title: 'Annual Sports Day', date: '2026-10-15', time: '8:00 AM', location: 'School Grounds', description: 'Join us for our exciting annual sports day with races, fun games, and prizes for all age groups.', category: 'Sports' },
    { id: '2', title: 'Science & Art Exhibition', date: '2026-11-05', time: '10:00 AM', location: 'Main Hall', description: 'Students showcase their science projects and art portfolios to parents and guests.', category: 'Academic' },
    { id: '3', title: 'Parents Orientation Day', date: '2026-09-20', time: '9:00 AM', location: 'Assembly Hall', description: 'Orientation session for parents of new admissions. Curriculum overview and Q&A.', category: 'Meeting' },
    { id: '4', title: 'Eid Celebration Event', date: '2026-12-10', time: '11:00 AM', location: 'Main Hall', description: 'A festive celebration with performances, food stalls, and fun activities for the whole family.', category: 'Cultural' },
  ]);

  // Admissions (empty — filled via form)
  DB.set('admissions', []);

  // Contact Messages (empty)
  DB.set('contacts', []);

  // Students (demo accounts)
  DB.set('students', [
    { id: '1', name: 'Ali Hassan', email: 'ali@student.ksm', phone: '+92 300 1234567', address: '123 Main St, Karachi', password: 'student123', class: 'Kindergarten A', rollNo: 'KA-001', parentName: 'Mr. Hassan Ali' },
    { id: '2', name: 'Zara Ahmed', email: 'zara@student.ksm', phone: '+92 321 7654321', address: '456 Elm St, Lahore', password: 'student123', class: 'Early Childhood B', rollNo: 'ECB-002', parentName: 'Mrs. Sana Ahmed' },
  ]);

  DB.set('seeded', true);
}

// ============================================================
// AUTH HELPERS
// ============================================================
const Auth = {
  ADMIN_USER: 'admin',
  ADMIN_PASS: 'admin123',

  loginAdmin(user, pass) {
    if (user === this.ADMIN_USER && pass === this.ADMIN_PASS) {
      sessionStorage.setItem('ksm_admin_auth', '1');
      return true;
    }
    return false;
  },

  isAdminLoggedIn() {
    return sessionStorage.getItem('ksm_admin_auth') === '1';
  },

  logoutAdmin() {
    sessionStorage.removeItem('ksm_admin_auth');
  },

  loginStudent(email, pass) {
    const students = DB.get('students');
    const student = students.find(s => s.email === email && s.password === pass);
    if (student) {
      sessionStorage.setItem('ksm_student_auth', JSON.stringify(student));
      return student;
    }
    return null;
  },

  getStudent() {
    try { return JSON.parse(sessionStorage.getItem('ksm_student_auth')); } catch { return null; }
  },

  isStudentLoggedIn() { return !!this.getStudent(); },

  logoutStudent() { sessionStorage.removeItem('ksm_student_auth'); }
};

// ============================================================
// TOAST NOTIFICATIONS
// ============================================================
function showToast(message, type = 'success', duration = 3000) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  const icons = {
    success: '✅',
    error: '❌',
    info: 'ℹ️',
    warning: '⚠️'
  };

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${icons[type] || '✅'}</span><span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('fade-out');
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// ============================================================
// SIDEBAR TOGGLE
// ============================================================
function initSidebar() {
  const toggle = document.getElementById('menuToggle');
  const sidebar = document.querySelector('.portal-sidebar');
  const overlay = document.getElementById('sidebarOverlay');

  if (toggle && sidebar) {
    toggle.addEventListener('click', () => {
      if (window.innerWidth <= 900) {
        sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('open');
      } else {
        document.body.classList.toggle('sidebar-closed');
      }
    });
  }
  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('open');
    });
  }

  // Highlight active link
  const links = document.querySelectorAll('.sidebar-link');
  const currentPage = window.location.pathname.split('/').pop();
  links.forEach(link => {
    const href = link.getAttribute('href');
    if (href && href.includes(currentPage)) {
      link.classList.add('active');
    }
  });
}

// ============================================================
// MODAL HELPERS
// ============================================================
function openModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
}

function closeModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
}

function initModals() {
  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal-overlay');
      if (modal) { modal.classList.remove('open'); document.body.style.overflow = ''; }
    });
  });

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) { overlay.classList.remove('open'); document.body.style.overflow = ''; }
    });
  });
}

// ============================================================
// DATES
// ============================================================
function formatDate(dateStr) {
  if (!dateStr) return '';
  try {
    return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
  } catch { return dateStr; }
}

function getMonthName(dateStr) {
  if (!dateStr) return '';
  try { return new Date(dateStr).toLocaleDateString('en-US', { month: 'short' }); } catch { return ''; }
}

function getDay(dateStr) {
  if (!dateStr) return '';
  try { return new Date(dateStr).getDate(); } catch { return ''; }
}

// ============================================================
// CONFIRM DELETE
// ============================================================
function confirmDelete(message, callback) {
  if (confirm(message || 'Are you sure you want to delete this item?')) {
    callback();
  }
}

// ============================================================
// INIT ON LOAD
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  seedData();
  initSidebar();
  initModals();
});
