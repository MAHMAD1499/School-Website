/**
 * Sidebar builder — call buildSidebar(role) in each page
 * role: 'portal' | 'admin' | 'student'
 */
function buildSidebar(role = 'portal') {
  const isAdmin = role === 'admin';
  const isStudent = role === 'student';
  const base = isAdmin ? '../' : isStudent ? '../' : '';
  const rootBase = isAdmin ? '../../' : isStudent ? '../../' : '../';

  const portalLinks = [
    { href: base + 'index.html', icon: 'home', label: 'Home' },
    { href: base + 'about.html', icon: 'info', label: 'About School' },
    { href: base + 'teachers.html', icon: 'users', label: 'Teachers & Staff' },
    { href: base + 'classes.html', icon: 'book-open', label: 'Classes & Subjects' },
    { href: base + 'gallery.html', icon: 'image', label: 'Gallery' },
    { href: base + 'news.html', icon: 'bell', label: 'News & Announcements' },
    { href: base + 'events.html', icon: 'calendar', label: 'Events' },
    { href: base + 'admissions.html', icon: 'file-text', label: 'Admissions' },
    { href: base + 'contact.html', icon: 'phone', label: 'Contact' },
  ];

  const adminLinks = [
    { href: 'dashboard.html', icon: 'layout', label: 'Dashboard' },
    { href: 'teachers.html', icon: 'users', label: 'Manage Teachers' },
    { href: 'gallery.html', icon: 'image', label: 'Manage Gallery' },
    { href: 'news.html', icon: 'bell', label: 'Manage News' },
    { href: 'events.html', icon: 'calendar', label: 'Manage Events' },
    { href: 'admissions.html', icon: 'file-text', label: 'Admissions' },
    { href: 'contacts.html', icon: 'mail', label: 'Contact Messages' },
  ];

  const studentLinks = [
    { href: 'dashboard.html', icon: 'home', label: 'My Dashboard' },
    { href: 'news.html', icon: 'bell', label: 'Announcements' },
    { href: 'events.html', icon: 'calendar', label: 'Events' },
    { href: 'classes.html', icon: 'book-open', label: 'Classes' },
    { href: 'gallery.html', icon: 'image', label: 'Gallery' }
  ];

  const svgIcons = {
    home: '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
    info: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
    users: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'book-open': '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
    image: '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
    bell: '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
    calendar: '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
    'file-text': '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
    phone: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.62 3.38 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6.06 6.06l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
    layout: '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>',
    mail: '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
    settings: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    'log-out': '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
    shield: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
  };

  function makeIcon(name) {
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><${svgIcons[name] ? '' : 'circle cx="12" cy="12" r="2"'}${svgIcons[name] || ''}/></svg>`.replace('</', svgIcons[name] ? '</' : '</');
  }

  function makeIconSVG(name) {
    const content = svgIcons[name] || '<circle cx="12" cy="12" r="2"/>';
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${content}</svg>`;
  }

  let links = isAdmin ? adminLinks : isStudent ? studentLinks : portalLinks;

  let linksHTML = links.map(l =>
    `<a href="${l.href}" class="sidebar-link">${makeIconSVG(l.icon)}<span>${l.label}</span></a>`
  ).join('');

  let logoutBtn = '';
  let userSection = '';

  if (isAdmin) {
    userSection = `<div class="sidebar-section-label">Admin Panel</div>`;
    logoutBtn = `<a href="javascript:void(0)" class="sidebar-link" onclick="Auth.logoutAdmin(); window.location='login.html';">
      ${makeIconSVG('log-out')}<span>Logout</span>
    </a>`;
  } else if (isStudent) {
    const student = Auth.getStudent();
    userSection = student ? `<div class="sidebar-section-label">Student Portal</div>` : '';
    logoutBtn = `<a href="javascript:void(0)" class="sidebar-link" onclick="Auth.logoutStudent(); window.location='login.html';">
      ${makeIconSVG('log-out')}<span>Logout</span>
    </a>`;
  } else {
    userSection = `<div class="sidebar-section-label">Navigation</div>`;
    logoutBtn = `<a href="${rootBase}index.html" class="sidebar-link">${makeIconSVG('home')}<span>Back to Website</span></a>`;
  }

  const sidebarHTML = `
    <div class="portal-sidebar" id="portalSidebar">
      <a href="${isAdmin ? '../index.html' : isStudent ? '../index.html' : 'index.html'}" class="sidebar-brand">
        <img src="${rootBase}assets/images/logo.svg" alt="KSM Logo" onerror="this.style.display='none'">
        <div class="sidebar-brand-text">
          <span class="brand-title">KSM Portal</span>
          <span class="brand-sub">${isAdmin ? 'Admin Panel' : isStudent ? 'Student Panel' : 'School Portal'}</span>
        </div>
      </a>
      ${userSection}
      <nav class="sidebar-nav">
        ${linksHTML}
      </nav>
      <div class="sidebar-section-label">${isAdmin || isStudent ? 'Account' : 'Quick Links'}</div>
      <div style="padding: 0 0.75rem 1rem;">
        ${logoutBtn}
      </div>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
  `;

  document.body.insertAdjacentHTML('afterbegin', sidebarHTML);

  // Re-highlight active link after insertion
  const currentPage = window.location.pathname.split('/').pop();
  document.querySelectorAll('.sidebar-link').forEach(link => {
    const href = link.getAttribute('href') || '';
    if (href && href !== 'javascript:void(0)' && href.split('/').pop() === currentPage) {
      link.classList.add('active');
    }
  });
}
