// ============================================================
// KSM Portal — Inline PHP API Bridge
// Each page handles its own AJAX via embedded PHP at the top.
// We send X-Requested-With header so PHP detects AJAX mode.
// ============================================================

// Resolve the current page path (used as the API endpoint for self)
function ksm_self_url() {
  return window.location.pathname.replace(/\.php$/, '.php');
}

// Thin fetch wrapper - POSTs to current page or a given URL
async function apiCall(url, method, body) {
  const opts = {
    method: method || 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(url, opts);
  try { return await res.json(); }
  catch { return { success: false, message: 'Server error' }; }
}

// Helper to call a specific .php file in the api/ folder (kept for fallback)
function apiPath(file) {
  const path = window.location.pathname;
  if (path.includes('/admin/') || path.includes('/student/') || path.includes('/staff/')) {
    return '../api/' + file;
  }
  return 'api/' + file;
}

// Helper to call the CURRENT page with _api param
function selfApi(method, body, extra) {
  let url = window.location.pathname;
  if (extra) url += '?' + extra;
  return apiCall(url, method, body);
}

// ============================================================
// API Object — each method targets the relevant .php page
// ============================================================
function makePageUrl(page, sub) {
  // Build URL relative to current path
  const curr = window.location.pathname;
  const isSubdir = curr.includes('/admin/') || curr.includes('/student/') || curr.includes('/staff/');
  const base = isSubdir ? '../' : '';
  return base + (sub ? sub + '/' : '') + page + '.php';
}

const API = {
  // AUTH - handled by each login page itself
  async adminLogin(u, p) { return apiCall(makePageUrl('login','admin'), 'POST', {action:'admin_login',username:u,password:p}); },
  async studentLogin(e, p) { return apiCall(makePageUrl('login','student'), 'POST', {email:e,password:p}); },
  async staffLogin(e, p) { return apiCall(makePageUrl('login','staff'), 'POST', {email:e,password:p}); },

  // TEACHERS (admin/teachers.php or portal/teachers.php)
  async getTeachers() { return apiCall(makePageUrl('teachers','admin'), 'GET'); },
  async addTeacher(d) { return apiCall(makePageUrl('teachers','admin'), 'POST', d); },
  async updateTeacher(d) { return apiCall(makePageUrl('teachers','admin'), 'PUT', d); },
  async deleteTeacher(id) { return apiCall(makePageUrl('teachers','admin') + '?id=' + id, 'DELETE'); },

  // NEWS
  async getNews() { return apiCall(makePageUrl('news','admin'), 'GET'); },
  async addNews(d) { return apiCall(makePageUrl('news','admin'), 'POST', d); },
  async updateNews(d) { return apiCall(makePageUrl('news','admin'), 'PUT', d); },
  async deleteNews(id) { return apiCall(makePageUrl('news','admin') + '?id=' + id, 'DELETE'); },

  // EVENTS
  async getEvents() { return apiCall(makePageUrl('events','admin'), 'GET'); },
  async addEvent(d) { return apiCall(makePageUrl('events','admin'), 'POST', d); },
  async updateEvent(d) { return apiCall(makePageUrl('events','admin'), 'PUT', d); },
  async deleteEvent(id) { return apiCall(makePageUrl('events','admin') + '?id=' + id, 'DELETE'); },

  // GALLERY
  async getGallery() { return apiCall(makePageUrl('gallery','admin'), 'GET'); },
  async addGalleryImage(d) { return apiCall(makePageUrl('gallery','admin'), 'POST', d); },
  async updateGalleryImage(d) { return apiCall(makePageUrl('gallery','admin'), 'PUT', d); },
  async deleteGalleryImage(id) { return apiCall(makePageUrl('gallery','admin') + '?id=' + id, 'DELETE'); },

  // ADMISSIONS
  async getAdmissions() { return apiCall(makePageUrl('admissions','admin'), 'GET'); },
  async submitAdmission(d) { return apiCall(makePageUrl('admissions',''), 'POST', d); },
  async updateAdmissionStatus(id, status) { return apiCall(makePageUrl('admissions','admin'), 'PUT', {id,status}); },
  async deleteAdmission(id) { return apiCall(makePageUrl('admissions','admin') + '?id=' + id, 'DELETE'); },

  // CONTACTS
  async getContacts() { return apiCall(makePageUrl('contacts','admin'), 'GET'); },
  async submitContact(d) { return apiCall(makePageUrl('contact',''), 'POST', d); },
  async updateContactStatus(id, status) { return apiCall(makePageUrl('contacts','admin'), 'PUT', {id,status}); },
  async deleteContact(id) { return apiCall(makePageUrl('contacts','admin') + '?id=' + id, 'DELETE'); },

  // HOMEWORK
  async getHomework(cls) { const q = cls ? '?class=' + encodeURIComponent(cls) : ''; return apiCall(makePageUrl('homework','staff') + q, 'GET'); },
  async addHomework(d) { return apiCall(makePageUrl('homework','staff'), 'POST', d); },
  async updateHomework(d) { return apiCall(makePageUrl('homework','staff'), 'PUT', d); },
  async deleteHomework(id) { return apiCall(makePageUrl('homework','staff') + '?id=' + id, 'DELETE'); },

  // ATTENDANCE
  async getAttendance(params) { const q = params ? '?' + new URLSearchParams(params).toString() : ''; return apiCall(makePageUrl('attendance','staff') + q, 'GET'); },
  async saveAttendance(records) { return apiCall(makePageUrl('attendance','staff'), 'POST', {records}); },

  // STUDENTS
  async getStudents() { return apiCall(makePageUrl('credentials','admin') + '?_api&type=students', 'GET'); },
  async getStudent(id) { return apiCall(makePageUrl('profile','student') + '?id=' + id, 'GET'); },
  async updateStudent(d) { return apiCall(makePageUrl('profile','student'), 'PUT', d); },

  // STAFF
  async getStaff() { return apiCall(makePageUrl('credentials','admin') + '?_api&type=staff', 'GET'); },
  async getStaffMember(id) { return apiCall(makePageUrl('profile','staff') + '?id=' + id, 'GET'); },
  async updateStaff(d) { return apiCall(makePageUrl('profile','staff'), 'PUT', d); },

  // DASHBOARD
  async getAdminDashboard() { return apiCall(makePageUrl('dashboard','admin'), 'GET'); },
  async getStudentDashboard(student_id) { return apiCall(makePageUrl('dashboard','student') + '?student_id=' + student_id, 'GET'); },
  async getStaffDashboard(staff_id) { return apiCall(makePageUrl('dashboard','staff') + '?staff_id=' + staff_id, 'GET'); },
};
