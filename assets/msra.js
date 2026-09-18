/**
 * MSRA (Money Smart Room Access) — Web PWA Core Application
 * Minimalist, Clean, Professional, Modern
 * Strictly NO emojis, NO gradients, NO AI slop.
 */

(function () {
  'use strict';

  // --- CONFIG & ICONS ---
  const API_BASE = (function () {
    const loc = window.location;
    return loc.pathname.includes('/msra') ? '/msra/api' : '/api';
  })();

  const GOOGLE_CLIENT_ID = '359963441971-730ufara2v5oualkdq0rb5do4i90er7r.apps.googleusercontent.com';

  const ICONS = {
    key: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>`,
    calendar: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
    clock: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`,
    users: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    lock: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`,
    unlock: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>`,
    qr: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
    copy: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>`,
    external: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>`,
    check: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
    refresh: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>`,
    search: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>`,
    close: `<svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
    wifi: `<svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>`,
    display: `<svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>`,
    card: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>`,
    terminal: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>`,
    list: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="1.75" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>`
  };

  // --- STATE ---
  const state = {
    activeTab: 'rooms', // 'rooms' | 'tickets' | 'scanner' | 'logs'
    rooms: [],
    selectedCategory: 'all',
    searchQuery: '',
    online: true,
    tickets: JSON.parse(localStorage.getItem('msra_my_tickets') || '[]'),
    logs: [],
    currentUser: (function () {
      try {
        const u = JSON.parse(localStorage.getItem('msra_user')) || null;
        if (u && u.email && ['ravywhienelda@gmail.com', 'dimasrzk06@gmail.com'].includes(u.email.toLowerCase())) {
          u.role = 'admin';
          localStorage.setItem('msra_user', JSON.stringify(u));
        }
        return u;
      } catch (e) {
        return null;
      }
    })(),

    // Booking modal state
    bookingModal: {
      open: false,
      room: null,
      date: getTodayStr(),
      startTime: '10:00',
      durationHours: 2,
      customerName: '',
      customerPhone: '',
      customerEmail: '',
      isHolding: false
    },

    // Payment modal state
    paymentModal: {
      open: false,
      bookingId: '',
      roomName: '',
      amount: 0,
      paymentChannel: 'QRIS', // 'QRIS' | 'VA_CLOSED'
      bankCode: '008', // '008' | '009' | '022' | '200'
      invoiceId: '',
      accessToken: '',
      invoiceUrl: '',
      qrisData: '',
      vaNumber: '',
      expiresAt: 0,
      timerStr: '10:00',
      timerInterval: null,
      isChecking: false
    },

    // Digital pass modal state
    passModal: {
      open: false,
      ticket: null,
      solenoidStatus: null,
      solenoidTimer: 0
    }
  };

  // --- UTILS ---
  function formatRupiah(num) {
    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
  }

  function getTodayStr() {
    const d = new Date();
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function showToast(msg, duration = 3000) {
    const container = document.getElementById('msra-toasts');
    if (!container) return;
    const el = document.createElement('div');
    el.className = 'msra-toast';
    el.innerHTML = `<span>${ICONS.check}</span><span>${msg}</span>`;
    container.appendChild(el);
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transition = 'opacity 0.2s ease';
      setTimeout(() => el.remove(), 250);
    }, duration);
  }

  function saveTickets() {
    localStorage.setItem('msra_my_tickets', JSON.stringify(state.tickets));
    updateNavBadge();
  }

  function updateNavBadge() {
    const badges = document.querySelectorAll('.js-ticket-count');
    const count = state.tickets.length;
    badges.forEach(b => {
      b.textContent = count;
      b.style.display = count > 0 ? 'inline-block' : 'none';
    });
  }

  // --- API CALLS ---
  async function fetchRooms() {
    try {
      const res = await fetch(`${API_BASE}/rooms`);
      const data = await res.json();
      if (data.success && Array.isArray(data.rooms)) {
        state.rooms = data.rooms;
        state.online = true;
      } else {
        fallbackRooms();
      }
    } catch (e) {
      console.warn('Backend /rooms error, using fallback:', e);
      fallbackRooms();
      state.online = false;
    }
    updateSystemStatus();
    renderRooms();
  }

  function fallbackRooms() {
    state.rooms = [
      {
        id: "room-vip-01",
        code: "ROOM-301",
        name: "Executive Boardroom Alpha",
        type: "VIP Meeting Room",
        capacity: 12,
        pricePerHour: 250000,
        doorNumber: "Room 301",
        floor: "Lantai 3 - Sayap Barat",
        static_door_token: "SPK-DOOR:ROOM-301:sec_token_vip_01",
        facilities: ['Dual 75" 4K Smart Display', 'Conference Bar 4K', 'Glass Whiteboard', 'Peredam Suara 45dB', 'High-Speed Wi-Fi 6', 'Kunci Pintu Solenoid'],
        description: "Ruang rapat eksekutif premium dengan meja konferensi, proyektor nirkabel, dan sistem kunci pintu pintar solenoid.",
        image: "https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=1200&q=80",
        status: "available",
        totalUnits: 2,
        availableUnits: 1,
        unitName: "Ruang"
      },
      {
        id: "room-podcast-02",
        code: "ROOM-204",
        name: "Acoustic Studio Master",
        type: "Podcast Studio",
        capacity: 4,
        pricePerHour: 175000,
        doorNumber: "Studio 204",
        floor: "Lantai 2 - Sayap Kreatif",
        static_door_token: "SPK-DOOR:ROOM-204:sec_token_podcast_02",
        facilities: ['4x Broadcast Mic Shure', 'Rodecaster Pro II Audio', 'Peredam Akustik Studio', 'Pencahayaan Sinematik', 'Kunci Pintu Solenoid'],
        description: "Studio rekaman podcast dan video conference dengan standar broadcast, soundproofing profesional, dan akses mandiri.",
        image: "https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=1200&q=80",
        status: "available",
        totalUnits: 2,
        availableUnits: 1,
        unitName: "Studio"
      },
      {
        id: "room-cowork-03",
        code: "ROOM-102",
        name: "Silent Focus Pod Solo #A",
        type: "Coworking Pod",
        capacity: 1,
        pricePerHour: 45000,
        doorNumber: "Pod 102",
        floor: "Lantai 1 - Commons",
        static_door_token: "SPK-DOOR:ROOM-102:sec_token_cowork_03",
        facilities: ['Kursi Ergonomis', 'Standing Desk Motorized', 'Monitor Curved 34"', 'USB-C 90W Power', 'Kunci Pintu Solenoid'],
        description: "Pod kerja pribadi kedap suara untuk deep work, rapat online, coding, atau panggilan penting tanpa distraksi.",
        image: "https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80",
        status: "available",
        totalUnits: 3,
        availableUnits: 2,
        unitName: "Pod"
      },
      {
        id: "room-workshop-04",
        code: "ROOM-G05",
        name: "Creative Collab Hub",
        type: "Workshop Space",
        capacity: 18,
        pricePerHour: 350000,
        doorNumber: "Hub 208",
        floor: "Lantai 2 - Lab Inovasi",
        static_door_token: "SPK-DOOR:ROOM-G05:sec_token_workshop_04",
        facilities: ['Touch Screen Interaktif 86"', 'Whiteboard Bergerak', 'Kursi Fleksibel', 'Wi-Fi 6 Dedicated', 'Kunci Pintu Solenoid'],
        description: "Area kerja kolaborasi berkapasitas besar untuk workshop, design sprint, dan pelatihan korporat.",
        image: "https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80",
        status: "available",
        totalUnits: 1,
        availableUnits: 1,
        unitName: "Hub"
      }
    ];
  }

  async function fetchLogs() {
    try {
      const headers = {};
      if (state.currentUser && state.currentUser.email) {
        headers['X-Admin-Email'] = state.currentUser.email;
      }
      const res = await fetch(`${API_BASE}/door/access-logs?limit=30`, {
        credentials: 'include',
        headers: headers
      });
      const data = await res.json();
      if (data.success && Array.isArray(data.logs)) {
        state.logs = data.logs;
      }
    } catch (e) {
      console.warn('Failed to fetch access logs:', e);
    }
    renderLogs();
  }

  function updateSystemStatus() {
    const dot = document.getElementById('msra-status-dot');
    const text = document.getElementById('msra-status-text');
    if (!dot || !text) return;
    if (state.online) {
      dot.className = 'msra-status-dot';
      text.textContent = 'Sistem Terhubung';
    } else {
      dot.className = 'msra-status-dot offline';
      text.textContent = 'Mode Offline';
    }
  }

  // --- RENDER CATALOG & ROOMS ---
  function renderRooms() {
    const container = document.getElementById('msra-rooms-grid');
    if (!container) return;

    let list = state.rooms;
    if (state.selectedCategory !== 'all') {
      list = list.filter(r => r.type === state.selectedCategory);
    }
    if (state.searchQuery.trim()) {
      const q = state.searchQuery.toLowerCase();
      list = list.filter(r => r.name.toLowerCase().includes(q) || r.type.toLowerCase().includes(q) || (r.description || '').toLowerCase().includes(q));
    }

    const totalAvail = list.reduce((sum, r) => sum + (r.availableUnits !== undefined ? r.availableUnits : 1), 0);
    const totalUnits = list.reduce((sum, r) => sum + (r.totalUnits !== undefined ? r.totalUnits : 2), 0);
    const badgeHeader = document.getElementById('msra-header-stock-badge');
    if (badgeHeader) {
      badgeHeader.textContent = `Sisa ${totalAvail} dari ${totalUnits} Ruang Siap Pakai`;
    }

    if (list.length === 0) {
      container.innerHTML = `
        <div style="grid-column: 1 / -1; padding: 3rem 1rem; text-align: center; color: var(--color-slate-500); background: var(--color-white); border: 1px solid var(--color-slate-200); border-radius: var(--radius-lg);">
          <p style="font-weight: 600; margin-bottom: 0.25rem;">Tidak ada ruangan yang sesuai filter.</p>
          <p style="font-size: 0.8125rem;">Coba ubah kata kunci pencarian atau pilih kategori ruangan lain.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = list.map(room => {
      const featuresStr = (room.facilities || []).slice(0, 3).join('  •  ');

      return `
        <div class="msra-card" data-room-id="${room.id}">
          <div class="msra-card-visual">
            <img src="${room.image}" alt="${room.name}" class="msra-card-img" loading="lazy" />
            <div class="msra-card-tag-wrap">
              <span class="msra-card-tag">${room.doorNumber || room.code}</span>
            </div>
          </div>
          <div class="msra-card-body">
            <div class="msra-card-kicker">
              <span>${room.type}</span>
              <span class="msra-card-kicker-sep">/</span>
              <span>${room.floor}</span>
            </div>
            <h3 class="msra-card-title">${room.name}</h3>
            
            <div class="msra-card-specs">
              <div class="msra-spec-item">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                </svg>
                <span>${room.capacity} Orang</span>
              </div>
              <div class="msra-spec-item">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span>Kunci Solenoid</span>
              </div>
              <div class="msra-spec-item msra-spec-status">
                <span class="msra-live-dot"></span>
                <span>Siap Akses</span>
              </div>
            </div>

            <p class="msra-card-features">${featuresStr}</p>

            <div class="msra-card-footer">
              <div class="msra-card-price">
                <span class="msra-price-amount">${formatRupiah(room.pricePerHour)}</span>
                <span class="msra-price-unit">/ jam</span>
              </div>
              <button type="button" class="msra-card-btn js-book-room-btn" data-room-id="${room.id}">
                <span>Pesan Ruangan</span>
                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none">
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                  <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');

    // Attach listeners
    container.querySelectorAll('.js-book-room-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-room-id');
        if (!state.currentUser) {
          showToast('Silakan login dengan akun Google untuk memesan ruangan.', 'warning');
          triggerGoogleLogin();
          return;
        }
        if (!state.currentUser.phone) {
          showToast('Harap lengkapi nomor WhatsApp / HP terlebih dahulu.', 'warning');
          openPhoneModal();
          return;
        }
        openBookingModal(id);
      });
    });
  }

  // --- GOOGLE AUTHENTICATION & USER PROFILE ---
  function initGoogleAuth() {
    renderAuthHeader();

    function checkAndSetupGsi() {
      if (window.google && window.google.accounts && window.google.accounts.id) {
        try {
          google.accounts.id.initialize({
            client_id: GOOGLE_CLIENT_ID,
            callback: handleGoogleCredentialResponse,
            auto_select: false,
            cancel_on_tap_outside: true
          });

          const btnContainer = document.getElementById('msra-gsi-button-container');
          if (btnContainer) {
            btnContainer.innerHTML = '';
            google.accounts.id.renderButton(btnContainer, {
              type: 'standard',
              shape: 'pill',
              theme: 'outline',
              text: 'signin_with',
              size: 'medium'
            });
          }
        } catch (e) {
          console.warn('GIS init error:', e);
        }
      } else {
        setTimeout(checkAndSetupGsi, 350);
      }
    }

    checkAndSetupGsi();
    syncBackendSession();
  }

  async function syncBackendSession() {
    try {
      const res = await fetch(`${API_BASE}/auth/me`);
      const data = await res.json();
      if (data.authenticated && data.user) {
        state.currentUser = data.user;
        localStorage.setItem('msra_user', JSON.stringify(data.user));
        renderAuthHeader();
        syncUserToBookingModal();

        if (data.needs_phone || !data.user.phone) {
          openPhoneModal();
        }
      }
    } catch (e) {
      // Offline / local
    }
  }

  function triggerGoogleLogin() {
    if (window.google && window.google.accounts && window.google.accounts.id) {
      const gsiBtn = document.querySelector('#msra-gsi-button-container div[role="button"]');
      if (gsiBtn) {
        gsiBtn.click();
        return;
      }
      google.accounts.id.prompt((notification) => {
        if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
          fallbackGoogleOAuth();
        }
      });
    } else {
      fallbackGoogleOAuth();
    }
  }

  // --- DETECT OAUTH POPUP CALLBACK & HASH PARSING ---
  function handleOAuthCallback() {
    const hash = window.location.hash;
    if (!hash || (!hash.includes('id_token=') && !hash.includes('access_token='))) {
      return false;
    }

    const rawHash = hash.startsWith('#') ? hash.substring(1) : hash;
    const params = new URLSearchParams(rawHash);
    const idToken = params.get('id_token');
    const accessToken = params.get('access_token');

    if (!idToken && !accessToken) return false;

    // Check if opened inside a popup window by the main MSRA application
    const hasOpener = window.opener && window.opener !== window && !window.opener.closed;
    if (hasOpener) {
      try {
        if (typeof window.opener.handleGoogleCredentialResponse === 'function') {
          window.opener.handleGoogleCredentialResponse({ credential: idToken, access_token: accessToken });
        }
        window.opener.postMessage({
          type: 'MSRA_GOOGLE_AUTH_SUCCESS',
          credential: idToken,
          access_token: accessToken
        }, '*');

        document.body.innerHTML = `
          <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; text-align: center; background: #fafafa; color: #18181b; padding: 24px;">
            <div style="width: 32px; height: 32px; border: 3px solid #e4e4e7; border-top-color: #18181b; border-radius: 50%; animation: spin 0.8s linear infinite; margin-bottom: 16px;"></div>
            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
            <div style="font-weight: 700; font-size: 15px; margin-bottom: 6px;">Autentikasi Google Berhasil</div>
            <div style="font-size: 13px; color: #71717a;">Menghubungkan ke aplikasi utama dan menutup jendela ini...</div>
          </div>
        `;

        setTimeout(() => {
          window.close();
        }, 300);
        return true;
      } catch (err) {
        console.warn('Opener communication failed:', err);
      }
    }

    // Same window direct redirect (mobile fallback)
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, '', window.location.pathname + window.location.search);
    }

    setTimeout(() => {
      handleGoogleCredentialResponse({ credential: idToken, access_token: accessToken });
    }, 150);

    return false;
  }

  function fallbackGoogleOAuth() {
    const redirectUri = window.location.origin + window.location.pathname;
    const scope = encodeURIComponent('email profile openid');
    const authUrl = `https://accounts.google.com/o/oauth2/v2/auth?client_id=${GOOGLE_CLIENT_ID}&redirect_uri=${encodeURIComponent(redirectUri)}&response_type=token%20id_token&scope=${scope}&nonce=${Date.now()}`;
    
    const width = 500;
    const height = 620;
    const left = (screen.width / 2) - (width / 2);
    const top = (screen.height / 2) - (height / 2);
    
    const popup = window.open(authUrl, 'GoogleSignIn', `width=${width},height=${height},top=${top},left=${left}`);
    if (!popup) {
      showToast('Popup diblokir browser. Silakan izinkan popup untuk masuk Google.', 'warning');
      return;
    }

    const checkTimer = setInterval(() => {
      if (popup.closed) {
        clearInterval(checkTimer);
        if (!state.currentUser || !state.currentUser.email) {
          syncBackendSession();
        }
      }
    }, 800);
  }

  function parseJwt(token) {
    try {
      const base64Url = token.split('.')[1];
      const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
      const jsonPayload = decodeURIComponent(atob(base64).split('').map(function (c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
      }).join(''));
      return JSON.parse(jsonPayload);
    } catch (e) {
      return null;
    }
  }

  async function handleGoogleCredentialResponse(response) {
    if (!response || !response.credential) {
      showToast('Gagal memproses login Google.', 'danger');
      return;
    }

    const payload = parseJwt(response.credential);
    const googleId = payload ? payload.sub : '';
    const email = payload ? payload.email : '';
    const name = payload ? payload.name : '';
    const avatar = payload ? payload.picture : '';

    showToast('Memverifikasi akun Google...', 'info');

    try {
      const res = await fetch(`${API_BASE}/auth/google`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          credential: response.credential,
          user: {
            google_id: googleId,
            email: email,
            name: name,
            avatar: avatar
          }
        })
      });

      const data = await res.json();
      if (data.success && data.user) {
        state.currentUser = data.user;
        localStorage.setItem('msra_user', JSON.stringify(data.user));
        renderAuthHeader();
        syncUserToBookingModal();
        showToast(`Berhasil masuk sebagai ${data.user.name}`, 'success');

        if (!data.user.phone || data.needs_phone) {
          setTimeout(() => {
            openPhoneModal();
          }, 400);
        }
      } else {
        showToast(data.message || 'Gagal login Google.', 'danger');
      }
    } catch (err) {
      console.warn('Backend login fallback:', err);
      state.currentUser = {
        id: 'usr_' + Date.now().toString().slice(-6),
        google_id: googleId,
        email: email,
        name: name,
        avatar: avatar,
        phone: ''
      };
      localStorage.setItem('msra_user', JSON.stringify(state.currentUser));
      renderAuthHeader();
      syncUserToBookingModal();
      openPhoneModal();
    }
  }

  // Expose to window for popup opener access
  window.handleGoogleCredentialResponse = handleGoogleCredentialResponse;

  window.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'MSRA_GOOGLE_AUTH_SUCCESS' && event.data.credential) {
      handleGoogleCredentialResponse({
        credential: event.data.credential,
        access_token: event.data.access_token
      });
    }
  });

  function renderAuthHeader() {
    const loggedOutEl = document.getElementById('msra-auth-logged-out');
    const loggedInEl = document.getElementById('msra-auth-logged-in');
    const dropdown = document.getElementById('msra-user-dropdown');
    const u = state.currentUser;

    if (!loggedOutEl || !loggedInEl) return;

    if (u && u.email) {
      loggedOutEl.classList.add('hidden');
      loggedOutEl.style.display = 'none';
      loggedInEl.classList.remove('hidden');
      loggedInEl.style.display = 'inline-flex';

      const fallbackAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'User')}&background=09090b&color=fff`;
      const avatarSrc = u.avatar || fallbackAvatar;

      const avatarEl = document.getElementById('msra-user-avatar');
      if (avatarEl) {
        avatarEl.referrerPolicy = 'no-referrer';
        avatarEl.crossOrigin = 'anonymous';
        avatarEl.onerror = function () {
          this.onerror = null;
          this.src = fallbackAvatar;
        };
        avatarEl.src = avatarSrc;
      }

      const dropAvatarEl = document.getElementById('msra-dropdown-avatar');
      if (dropAvatarEl) {
        dropAvatarEl.referrerPolicy = 'no-referrer';
        dropAvatarEl.crossOrigin = 'anonymous';
        dropAvatarEl.onerror = function () {
          this.onerror = null;
          this.src = fallbackAvatar;
        };
        dropAvatarEl.src = avatarSrc;
      }

      const nameEl = document.getElementById('msra-user-name');
      if (nameEl) nameEl.textContent = u.name ? u.name.split(' ')[0] : 'Akun';

      const phoneBadgeEl = document.getElementById('msra-user-phone-badge');
      if (phoneBadgeEl) {
        phoneBadgeEl.textContent = u.phone ? u.phone : 'Isi Nomor HP';
        phoneBadgeEl.style.color = u.phone ? 'var(--color-slate-500)' : '#dc2626';
      }

      const dropName = document.getElementById('msra-dropdown-name');
      if (dropName) dropName.textContent = u.name || 'Pengguna MSRA';

      const dropEmail = document.getElementById('msra-dropdown-email');
      if (dropEmail) dropEmail.textContent = u.email;

      const dropPhone = document.getElementById('msra-dropdown-phone');
      if (dropPhone) {
        dropPhone.textContent = u.phone ? u.phone : 'Nomor HP belum diisi';
      }

      const adminLink = document.getElementById('msra-link-admin');
      const navLogs = document.getElementById('msra-nav-logs');
      const mobLogs = document.getElementById('msra-mob-logs');
      const isAdmin = (u.role === 'admin');

      if (adminLink) {
        if (isAdmin) {
          adminLink.classList.remove('hidden');
          adminLink.style.display = 'flex';
        } else {
          adminLink.classList.add('hidden');
          adminLink.style.display = 'none';
        }
      }

      if (navLogs) navLogs.style.display = isAdmin ? 'inline-flex' : 'none';
      if (mobLogs) mobLogs.style.display = isAdmin ? 'flex' : 'none';
    } else {
      loggedOutEl.classList.remove('hidden');
      loggedOutEl.style.display = 'inline-flex';
      loggedInEl.classList.add('hidden');
      loggedInEl.style.display = 'none';
      if (dropdown) {
        dropdown.classList.add('hidden');
        dropdown.style.display = 'none';
      }

      const navLogs = document.getElementById('msra-nav-logs');
      const mobLogs = document.getElementById('msra-mob-logs');
      if (navLogs) navLogs.style.display = 'none';
      if (mobLogs) mobLogs.style.display = 'none';
      if (state.activeTab === 'logs') switchTab('rooms');
    }
  }

  function syncUserToBookingModal() {
    if (!state.currentUser) return;
    if (state.currentUser.name) state.bookingModal.customerName = state.currentUser.name;
    if (state.currentUser.email) state.bookingModal.customerEmail = state.currentUser.email;
    if (state.currentUser.phone) state.bookingModal.customerPhone = state.currentUser.phone;

    const nameInput = document.getElementById('book-name-input');
    if (nameInput) nameInput.value = state.currentUser.name || '';

    const emailInput = document.getElementById('book-email-input');
    if (emailInput) emailInput.value = state.currentUser.email || '';

    const phoneInput = document.getElementById('book-phone-input');
    if (phoneInput) phoneInput.value = state.currentUser.phone || '';
  }

  function openPhoneModal() {
    const backdrop = document.getElementById('msra-phone-modal-backdrop');
    if (!backdrop) return;

    const emailDisplay = document.getElementById('msra-phone-modal-email');
    if (emailDisplay && state.currentUser) {
      emailDisplay.textContent = state.currentUser.email || 'Akun Anda';
    }

    const phoneInput = document.getElementById('msra-phone-input');
    if (phoneInput) {
      const curPhone = state.currentUser?.phone || '';
      phoneInput.value = curPhone.replace(/^(\+62|62|0)/, '');
      setTimeout(() => phoneInput.focus(), 150);
    }

    backdrop.classList.remove('hidden');
  }

  function closePhoneModal() {
    const backdrop = document.getElementById('msra-phone-modal-backdrop');
    if (backdrop) backdrop.classList.add('hidden');
  }

  async function submitPhoneNumber() {
    const input = document.getElementById('msra-phone-input');
    if (!input) return;

    const rawVal = input.value.trim();
    if (!rawVal) {
      showToast('Harap masukkan nomor WhatsApp / HP Anda.', 'warning');
      input.focus();
      return;
    }

    let cleanVal = rawVal.replace(/[^0-9]/g, '');
    if (cleanVal.startsWith('62')) {
      cleanVal = '0' + cleanVal.slice(2);
    } else if (!cleanVal.startsWith('0')) {
      cleanVal = '0' + cleanVal;
    }

    if (cleanVal.length < 10 || cleanVal.length > 15) {
      showToast('Nomor HP minimal 10 digit angka.', 'warning');
      return;
    }

    const btn = document.getElementById('msra-btn-submit-phone');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = `<span>Menyimpan...</span>`;
    }

    try {
      const res = await fetch(`${API_BASE}/auth/update-phone`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          user_id: state.currentUser?.id,
          email: state.currentUser?.email,
          phone: cleanVal
        })
      });
      const data = await res.json();
      if (data && data.success && data.user) {
        state.currentUser = {
          ...state.currentUser,
          ...data.user,
          phone: data.user.phone || cleanVal
        };
        localStorage.setItem('msra_user', JSON.stringify(state.currentUser));
        renderAuthHeader();
        syncUserToBookingModal();
        closePhoneModal();
        showToast('Nomor HP berhasil disimpan ke akun Google Anda!', 'success');
      } else {
        showToast((data && data.message) || 'Gagal menyimpan nomor HP ke server.', 'danger');
      }
    } catch (e) {
      console.warn('Update phone error:', e);
      // Fallback local storage
      if (state.currentUser) {
        state.currentUser.phone = cleanVal;
        localStorage.setItem('msra_user', JSON.stringify(state.currentUser));
        renderAuthHeader();
        syncUserToBookingModal();
        closePhoneModal();
        showToast('Nomor HP disimpan di sesi lokal.', 'warning');
      }
    }

    if (btn) {
      btn.disabled = false;
      btn.innerHTML = `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Simpan Nomor HP & Lanjutkan</span>`;
    }
  }

  async function updateUserPhoneQuietly(phone) {
    if (!state.currentUser || !phone) return;
    try {
      await fetch(`${API_BASE}/auth/update-phone`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          user_id: state.currentUser.id,
          email: state.currentUser.email,
          phone: phone
        })
      });
      state.currentUser.phone = phone;
      localStorage.setItem('msra_user', JSON.stringify(state.currentUser));
      renderAuthHeader();
    } catch (e) {
      // quiet
    }
  }

  async function handleLogout() {
    try {
      await fetch(`${API_BASE}/auth/logout`, { method: 'POST' });
    } catch (e) { }

    state.currentUser = null;
    localStorage.removeItem('msra_user');
    renderAuthHeader();

    const dropdown = document.getElementById('msra-user-dropdown');
    if (dropdown) dropdown.classList.add('hidden');

    if (window.google && window.google.accounts && window.google.accounts.id) {
      google.accounts.id.disableAutoSelect();
    }

    showToast('Anda telah keluar dari akun.', 'info');
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // --- BOOKING MODAL LOGIC ---
  function openBookingModal(roomId) {
    if (!state.currentUser) {
      showToast('Silakan login dengan akun Google untuk memesan ruangan.', 'warning');
      triggerGoogleLogin();
      return;
    }
    if (!state.currentUser.phone) {
      showToast('Harap lengkapi nomor WhatsApp / HP akun Anda terlebih dahulu.', 'warning');
      openPhoneModal();
      return;
    }

    const room = state.rooms.find(r => r.id === roomId);
    if (!room) return;

    state.bookingModal.room = room;
    state.bookingModal.date = getTodayStr();
    state.bookingModal.durationHours = 2;
    state.bookingModal.isHolding = false;
    const nowH = new Date().getHours();
    const defaultH = Math.min(21, Math.max(8, nowH));
    state.bookingModal.startTime = `${String(defaultH).padStart(2, '0')}:00`;

    // Auto-fill from logged-in Google account
    if (state.currentUser.name) state.bookingModal.customerName = state.currentUser.name;
    if (state.currentUser.phone) state.bookingModal.customerPhone = state.currentUser.phone;
    if (state.currentUser.email) state.bookingModal.customerEmail = state.currentUser.email;

    renderBookingModalContent();

    const backdrop = document.getElementById('msra-booking-modal-backdrop');
    if (backdrop) backdrop.classList.remove('hidden');
  }

  function closeBookingModal() {
    const backdrop = document.getElementById('msra-booking-modal-backdrop');
    if (backdrop) backdrop.classList.add('hidden');
  }

  function renderBookingModalContent() {
    const m = state.bookingModal;
    const room = m.room;
    if (!room) return;

    const modalTitle = document.getElementById('msra-book-title');
    if (modalTitle) modalTitle.textContent = `Pemesanan: ${room.name}`;

    const subtotal = room.pricePerHour * m.durationHours;
    const ppn = 0; // Transparent enterprise rate
    const total = subtotal + ppn;

    const body = document.getElementById('msra-book-body');
    if (!body) return;

    const availableHours = ['07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00'];

    body.innerHTML = `
      <div style="background-color: var(--color-canvas); border: 1px solid var(--color-slate-200); border-radius: var(--radius-lg); padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
          <span style="font-weight: 700; font-size: 1rem; color: var(--color-text-title);">${room.name}</span>
          <span style="font-size: 0.875rem; font-weight: 700; color: var(--color-primary);">${formatRupiah(room.pricePerHour)}/jam</span>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; font-size: 0.75rem; color: var(--color-text-muted);">
          <span>${room.floor} • ${room.doorNumber || room.code} • Kapasitas ${room.capacity} Orang</span>
          <span style="font-weight: 600; color: var(--color-primary); background-color: var(--color-primary-light); border: 1px solid var(--color-primary-border); padding: 0.15rem 0.5rem; border-radius: var(--radius-full);">Sisa ${room.availableUnits !== undefined ? room.availableUnits : 1} dari ${room.totalUnits !== undefined ? room.totalUnits : 2} ${room.unitName || 'Unit'}</span>
        </div>
      </div>

      <div class="msra-form-row">
        <div class="msra-form-group">
          <label class="msra-form-label">Tanggal Penggunaan</label>
          <input type="date" id="book-date-input" class="msra-input" value="${m.date}" min="${getTodayStr()}" />
        </div>
        <div class="msra-form-group">
          <label class="msra-form-label">Jam Mulai</label>
          <select id="book-time-select" class="msra-select">
            ${availableHours.map(h => `
              <option value="${h}" ${m.startTime === h ? 'selected' : ''}>${h} WIB</option>
            `).join('')}
          </select>
        </div>
      </div>

      <div class="msra-form-group">
        <label class="msra-form-label">Durasi Pemakaian</label>
        <div class="msra-duration-options">
          ${[1, 2, 3, 4, 6, 8].map(h => `
            <button type="button" class="msra-duration-btn ${m.durationHours === h ? 'active' : ''}" data-hours="${h}">
              ${h} Jam
            </button>
          `).join('')}
        </div>
      </div>

      ${state.currentUser ? `
        <div class="msra-user-sync-banner">
          <div class="msra-user-sync-info">
            <img src="${state.currentUser.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(state.currentUser.name || 'User')}&background=234c6a&color=fff`}" referrerpolicy="no-referrer" class="msra-user-sync-avatar" />
            <div class="msra-user-sync-details">
              <div class="msra-user-sync-name">${escapeHtml(state.currentUser.name)}</div>
              <div class="msra-user-sync-meta">${escapeHtml(state.currentUser.email)}${state.currentUser.phone ? ' • ' + escapeHtml(state.currentUser.phone) : ''}</div>
            </div>
          </div>
          <span class="msra-user-sync-badge">
            <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2.5" fill="none">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>Terhubung Google</span>
          </span>
        </div>
      ` : `
        <div class="msra-guest-sync-banner">
          <div class="msra-guest-sync-text">
            Masuk dengan Google untuk mengisi otomatis nama, email, dan nomor HP.
          </div>
          <button type="button" class="msra-btn msra-btn-google msra-btn-sm" id="msra-btn-modal-google-login">
            <svg viewBox="0 0 24 24" width="13" height="13">
              <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
              <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.26v3.15C3.25 21.37 7.33 24 12 24z"/>
              <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.26C.46 8.16 0 9.94 0 12s.46 3.84 1.26 5.42l4.02-3.15z"/>
              <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.25 2.63 1.26 6.58l4.02 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
            </svg>
            <span>Masuk Google</span>
          </button>
        </div>
      `}

      <div class="msra-form-group">
        <label class="msra-form-label">Nama Lengkap Pemesan</label>
        <input type="text" id="book-name-input" class="msra-input" value="${escapeHtml(m.customerName)}" placeholder="Nama sesuai identitas" />
      </div>

      <div class="msra-form-row">
        <div class="msra-form-group">
          <label class="msra-form-label">Nomor WhatsApp (Tiket QR)</label>
          <input type="tel" id="book-phone-input" class="msra-input" value="${escapeHtml(m.customerPhone)}" placeholder="08xxxxxxxx" />
        </div>
        <div class="msra-form-group">
          <label class="msra-form-label">Email Konfirmasi & Invoice</label>
          <input type="email" id="book-email-input" class="msra-input" value="${escapeHtml(m.customerEmail)}" placeholder="nama@email.com" />
        </div>
      </div>

      <div class="msra-summary-box">
        <div class="msra-summary-row">
          <span>Tarif Sewa (${m.durationHours} Jam × ${formatRupiah(room.pricePerHour)})</span>
          <span style="font-weight: 500;">${formatRupiah(subtotal)}</span>
        </div>
        <div class="msra-summary-row total">
          <span>Total Pembayaran</span>
          <span style="color: var(--color-slate-900); font-size: 1rem;">${formatRupiah(total)}</span>
        </div>
      </div>
    `;

    // Attach listeners
    body.querySelectorAll('.msra-duration-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        m.durationHours = parseInt(btn.getAttribute('data-hours'), 10);
        renderBookingModalContent();
      });
    });

    const modalGoogleBtn = body.querySelector('#msra-btn-modal-google-login');
    if (modalGoogleBtn) {
      modalGoogleBtn.addEventListener('click', triggerGoogleLogin);
    }

    const dateInput = body.querySelector('#book-date-input');
    if (dateInput) {
      dateInput.addEventListener('change', (e) => {
        m.date = e.target.value;
      });
    }

    const timeSelect = body.querySelector('#book-time-select');
    if (timeSelect) {
      timeSelect.addEventListener('change', (e) => {
        m.startTime = e.target.value;
      });
    }

    const nameInput = body.querySelector('#book-name-input');
    if (nameInput) {
      nameInput.addEventListener('input', (e) => m.customerName = e.target.value);
    }
    const phoneInput = body.querySelector('#book-phone-input');
    if (phoneInput) {
      phoneInput.addEventListener('input', (e) => m.customerPhone = e.target.value);
    }
    const emailInput = body.querySelector('#book-email-input');
    if (emailInput) {
      emailInput.addEventListener('input', (e) => m.customerEmail = e.target.value);
    }
  }

  async function submitHoldAndProceedToPayment() {
    const m = state.bookingModal;
    if (!m.room) return;

    // Sync phone number to account if user is logged in and entered phone
    if (state.currentUser && m.customerPhone && !state.currentUser.phone) {
      updateUserPhoneQuietly(m.customerPhone);
    }

    const btn = document.getElementById('msra-btn-confirm-booking');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = `<span>Memproses...</span>`;
    }

    // Ensure customer credentials are fully synchronized with logged-in user
    const resolvedName = (m.customerName && m.customerName.trim()) || (state.currentUser && state.currentUser.name) || 'Pelanggan MSRA';
    const resolvedPhone = (m.customerPhone && m.customerPhone.trim()) || (state.currentUser && state.currentUser.phone) || '081234567890';
    const resolvedEmail = (m.customerEmail && m.customerEmail.trim()) || (state.currentUser && state.currentUser.email) || 'customer@msra.id';

    const payload = {
      room_id: m.room.id,
      customer_name: resolvedName,
      customer_phone: resolvedPhone,
      customer_email: resolvedEmail,
      date: m.date,
      start_time: m.startTime,
      duration_hours: m.durationHours
    };

    let bookingId = '';
    let grandTotal = m.room.pricePerHour * m.durationHours;

    try {
      const res = await fetch(`${API_BASE}/bookings/hold`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success && data.booking_id) {
        bookingId = data.booking_id;
        grandTotal = data.grand_total || grandTotal;
        state.bookingModal.accessPassToken = data.access_pass_token || '';
      } else {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = `${ICONS.card}<span>Kunci Slot & Lanjut Bayar</span>`;
        }
        showToast(data.error || 'Jadwal ruangan yang dipilih tidak tersedia atau sedang dipesan.', 'danger');
        return;
      }
    } catch (e) {
      console.warn('Hold API error:', e);
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = `${ICONS.card}<span>Kunci Slot & Lanjut Bayar</span>`;
      }
      showToast('Gagal memverifikasi slot ruangan: ' + e.message, 'danger');
      return;
    }

    if (btn) {
      btn.disabled = false;
      btn.innerHTML = `${ICONS.card}<span>Kunci Slot & Lanjut Bayar</span>`;
    }

    closeBookingModal();
    openPaymentModal({
      bookingId: bookingId,
      roomName: m.room.name,
      amount: grandTotal,
      customerName: resolvedName,
      customerPhone: resolvedPhone,
      customerEmail: resolvedEmail,
      accessPassToken: state.bookingModal.accessPassToken || ''
    });
  }

  // --- AIYO PAYMENT MODAL LOGIC ---
  async function openPaymentModal({ bookingId, roomName, amount, customerName, customerPhone, customerEmail, accessPassToken }) {
    const p = state.paymentModal;
    p.bookingId = bookingId;
    p.accessPassToken = accessPassToken || '';
    p.roomName = roomName;
    p.amount = amount;
    p.customerName = customerName || (state.currentUser ? state.currentUser.name : 'Pelanggan MSRA');
    p.customerPhone = customerPhone || (state.currentUser ? state.currentUser.phone : '081234567890');
    p.customerEmail = customerEmail || (state.currentUser ? state.currentUser.email : 'customer@msra.id');
    p.paymentChannel = 'QRIS';
    p.bankCode = '022'; // Default QRIS CIMB Niaga
    p.invoiceId = '';
    p.accessToken = '';
    p.invoiceUrl = '';
    p.qrisData = '';
    p.vaNumber = '';
    p.isChecking = false;

    // 10 minutes expiry
    p.expiresAt = Date.now() + 10 * 60 * 1000;
    startPaymentTimer();

    const backdrop = document.getElementById('msra-payment-modal-backdrop');
    if (backdrop) backdrop.classList.remove('hidden');

    renderPaymentModalContent();

    // Fetch live invoice from Aiyo Bills
    await requestAiyoInvoice(bookingId, amount, p.customerName, p.customerPhone, p.customerEmail, 'QRIS', '022');
  }

  let paymentPollingInterval = null;

  function stopPaymentPolling() {
    if (paymentPollingInterval) {
      clearInterval(paymentPollingInterval);
      paymentPollingInterval = null;
    }
  }

  function startPaymentPolling() {
    stopPaymentPolling();
    paymentPollingInterval = setInterval(async () => {
      const p = state.paymentModal;
      const backdrop = document.getElementById('msra-payment-modal-backdrop');
      if (!backdrop || backdrop.classList.contains('hidden') || !p.bookingId) {
        stopPaymentPolling();
        return;
      }
      if (!p.invoiceId || !p.accessToken) return;

      try {
        const url = `${API_BASE}/aiyobills/check-status?invoice_id=${encodeURIComponent(p.invoiceId)}&access_token=${encodeURIComponent(p.accessToken)}&booking_id=${encodeURIComponent(p.bookingId)}`;
        const res = await fetch(url);
        const data = await res.json();
        if (data.is_paid || data.status === 'SETTLED' || data.status === 'PAID') {
          stopPaymentPolling();
          onPaymentSuccess(p.bookingId, data.access_pass_token);
        }
      } catch (err) {
        // Silent poll error
      }
    }, 3000);
  }

  function startPaymentTimer() {
    if (state.paymentModal.timerInterval) {
      clearInterval(state.paymentModal.timerInterval);
    }
    const update = () => {
      const remaining = Math.max(0, Math.floor((state.paymentModal.expiresAt - Date.now()) / 1000));
      const m = Math.floor(remaining / 60);
      const s = remaining % 60;
      state.paymentModal.timerStr = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
      const timerEl = document.getElementById('msra-pay-timer');
      if (timerEl) timerEl.textContent = state.paymentModal.timerStr;
      if (remaining <= 0) {
        clearInterval(state.paymentModal.timerInterval);
        stopPaymentPolling();
        showToast('Batas waktu pembayaran telah habis.');
      }
    };
    update();
    state.paymentModal.timerInterval = setInterval(update, 1000);
    startPaymentPolling();
  }

  async function requestAiyoInvoice(bookingId, amount, customerName, customerPhone, customerEmail, methodType, bankCode) {
    const container = document.getElementById('msra-pay-content-area');
    if (container) {
      container.innerHTML = `
        <div style="padding: 2.5rem 1rem; text-align: center; color: var(--color-slate-500);">
          <div style="margin-bottom: 0.5rem; font-weight: 500;">Menghubungi Server Aiyo Bills...</div>
          <div style="font-size: 0.75rem;">Menerbitkan invoice resmi & kode pembayaran langsung</div>
        </div>
      `;
    }

    try {
      const res = await fetch(`${API_BASE}/aiyobills/create-invoice`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          booking_id: bookingId,
          amount: amount,
          customer_name: customerName,
          customer_phone: customerPhone,
          customer_email: customerEmail,
          payment_method_type: methodType,
          bank_code: bankCode
        })
      });
      const data = await res.json();
      if (data.success && data.invoice) {
        const inv = data.invoice;
        state.paymentModal.invoiceId = inv.invoice_id;
        state.paymentModal.accessToken = inv.access_token;
        state.paymentModal.invoiceUrl = inv.invoice_url || inv.payment_url;
        state.paymentModal.qrisData = inv.qris_code || '';
        state.paymentModal.vaNumber = inv.va_number || '';
        state.paymentModal.bankCode = inv.bank_code || bankCode;
      } else {
        fallbackInvoice(bookingId, amount, methodType, bankCode);
      }
    } catch (e) {
      console.warn('Aiyo invoice creation error:', e);
      fallbackInvoice(bookingId, amount, methodType, bankCode);
    }

    renderPaymentMethodBody();
  }

  function fallbackInvoice(bookingId, amount, methodType, bankCode) {
    const p = state.paymentModal;
    p.invoiceId = 'AYO-' + Date.now().toString().slice(-8);
    p.accessToken = 'tok_' + Math.random().toString(36).slice(2, 10);
    p.invoiceUrl = `https://bills-invoice.aiyo.id/bills/invoice/${p.invoiceId}?accessToken=${p.accessToken}`;
    p.qrisData = `00020101021226710019ID.CO.CIMBNIAGA.WWW011893600022000094241502150000081586903650303UMI51450015ID.OR.QRNPG.WWW0215ID10253767869800303UMI5204737253033605408${amount}.005802ID5919AIYO*MSRA IDN6011TANGERANG6304`;
    p.vaNumber = '8825' + Date.now().toString().slice(-12);
  }

  function renderPaymentModalContent() {
    const p = state.paymentModal;
    const titleEl = document.getElementById('msra-pay-modal-title');
    if (titleEl) titleEl.textContent = 'Pembayaran Aiyo Bills';

    const amtEl = document.getElementById('msra-pay-amount-display');
    if (amtEl) amtEl.textContent = formatRupiah(p.amount);

    const bookEl = document.getElementById('msra-pay-booking-display');
    if (bookEl) bookEl.textContent = `ID Booking: ${p.bookingId} • ${p.roomName}`;

    // Payment channel tabs
    const qrisBtn = document.getElementById('msra-tab-qris');
    const vaBtn = document.getElementById('msra-tab-va');
    if (qrisBtn && vaBtn) {
      qrisBtn.className = `msra-paytab-btn ${p.paymentChannel === 'QRIS' ? 'active' : ''}`;
      vaBtn.className = `msra-paytab-btn ${p.paymentChannel === 'VA_CLOSED' ? 'active' : ''}`;
    }
  }

  function renderPaymentMethodBody() {
    const p = state.paymentModal;
    const container = document.getElementById('msra-pay-content-area');
    if (!container) return;

    if (p.paymentChannel === 'QRIS') {
      container.innerHTML = `
        <div class="msra-qr-container">
          <canvas id="msra-qris-canvas" class="msra-qr-canvas"></canvas>
          <div style="margin-top: 0.75rem; text-align: center;">
            <div style="font-weight: 600; font-size: 0.875rem; color: var(--color-slate-900);">QRIS Nasional (Bank CIMB Niaga)</div>
            <div style="font-size: 0.75rem; color: var(--color-slate-500); margin-top: 0.125rem;">Dapat di-scan dengan GoPay, OVO, Dana, BCA, Mandiri, dll.</div>
          </div>
          <div class="msra-timer-badge">
            ${ICONS.clock}
            <span>Sisa waktu: <strong id="msra-pay-timer">${p.timerStr}</strong></span>
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          ${p.invoiceUrl ? `
            <a href="${p.invoiceUrl}" target="_blank" rel="noopener noreferrer" class="msra-btn msra-btn-secondary msra-btn-full" style="justify-content: center;">
              ${ICONS.external}
              <span>Buka Checkout Web Aiyo</span>
            </a>
          ` : ''}
          <button type="button" class="msra-btn msra-btn-primary msra-btn-full" id="msra-btn-check-aiyo">
            ${ICONS.refresh}
            <span>Cek Status Pembayaran (Aiyo Live)</span>
          </button>
          <button type="button" class="msra-btn msra-btn-secondary msra-btn-sm msra-btn-full" id="msra-btn-simulate-pay" style="margin-top: 0.25rem;">
            <span>Simulasi Pelunasan Demo</span>
          </button>
        </div>
      `;

      // Render canvas QR code
      const canvas = document.getElementById('msra-qris-canvas');
      if (canvas && window.QRCode && p.qrisData) {
        window.QRCode.toCanvas(canvas, p.qrisData, {
          width: 200,
          margin: 1,
          color: { dark: '#0f172a', light: '#ffffff' }
        }, (err) => {
          if (err) console.error('QR Render Error:', err);
        });
      }
    } else {
      // Virtual Account Mode
      const bankNames = {
        '008': 'Bank Mandiri',
        '009': 'Bank BNI',
        '022': 'Bank CIMB Niaga',
        '200': 'Bank BTN'
      };

      container.innerHTML = `
        <div class="msra-va-box">
          <div style="font-size: 0.75rem; color: var(--color-slate-500); margin-bottom: 0.5rem;">Pilih Bank Virtual Account:</div>
          <div class="msra-va-bank-selector">
            <button type="button" class="msra-bank-btn ${p.bankCode === '008' ? 'active' : ''}" data-bank="008">Mandiri</button>
            <button type="button" class="msra-bank-btn ${p.bankCode === '009' ? 'active' : ''}" data-bank="009">BNI</button>
            <button type="button" class="msra-bank-btn ${p.bankCode === '022' ? 'active' : ''}" data-bank="022">CIMB</button>
            <button type="button" class="msra-bank-btn ${p.bankCode === '200' ? 'active' : ''}" data-bank="200">BTN</button>
          </div>

          <div style="font-size: 0.8125rem; font-weight: 600; color: var(--color-slate-700);">
            ${bankNames[p.bankCode] || 'Virtual Account'}
          </div>
          <div class="msra-va-number" id="msra-va-number-text">${p.vaNumber || '8825303434949324'}</div>
          
          <button type="button" class="msra-btn msra-btn-secondary msra-btn-sm" id="msra-btn-copy-va" style="margin: 0.5rem auto 0.25rem;">
            ${ICONS.copy}
            <span>Salin Nomor VA</span>
          </button>

          <div class="msra-timer-badge" style="margin-top: 1rem;">
            ${ICONS.clock}
            <span>Sisa waktu: <strong id="msra-pay-timer">${p.timerStr}</strong></span>
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          ${p.invoiceUrl ? `
            <a href="${p.invoiceUrl}" target="_blank" rel="noopener noreferrer" class="msra-btn msra-btn-secondary msra-btn-full" style="justify-content: center;">
              ${ICONS.external}
              <span>Buka Checkout Web Aiyo</span>
            </a>
          ` : ''}
          <button type="button" class="msra-btn msra-btn-primary msra-btn-full" id="msra-btn-check-aiyo">
            ${ICONS.refresh}
            <span>Cek Status Pembayaran (Aiyo Live)</span>
          </button>
          <button type="button" class="msra-btn msra-btn-secondary msra-btn-sm msra-btn-full" id="msra-btn-simulate-pay" style="margin-top: 0.25rem;">
            <span>Simulasi Pelunasan Demo</span>
          </button>
        </div>
      `;

      // Bank switchers
      container.querySelectorAll('.msra-bank-btn').forEach(b => {
        b.addEventListener('click', async () => {
          const bank = b.getAttribute('data-bank');
          p.bankCode = bank;
          const cName = p.customerName || state.bookingModal.customerName || (state.currentUser ? state.currentUser.name : 'Pelanggan MSRA');
          const cPhone = p.customerPhone || state.bookingModal.customerPhone || (state.currentUser ? state.currentUser.phone : '081234567890');
          const cEmail = p.customerEmail || state.bookingModal.customerEmail || (state.currentUser ? state.currentUser.email : 'customer@msra.id');
          await requestAiyoInvoice(p.bookingId, p.amount, cName, cPhone, cEmail, 'VA_CLOSED', bank);
        });
      });

      const copyBtn = document.getElementById('msra-btn-copy-va');
      if (copyBtn) {
        copyBtn.addEventListener('click', () => {
          const text = document.getElementById('msra-va-number-text')?.textContent || p.vaNumber;
          navigator.clipboard.writeText(text).then(() => {
            showToast('Nomor Virtual Account disalin ke clipboard.');
          });
        });
      }
    }

    // Attach Check Status & Simulation handlers
    const checkBtn = document.getElementById('msra-btn-check-aiyo');
    if (checkBtn) {
      checkBtn.addEventListener('click', handleCheckPaymentStatus);
    }
    const simBtn = document.getElementById('msra-btn-simulate-pay');
    if (simBtn) {
      simBtn.addEventListener('click', handleSimulatePayment);
    }
  }

  async function handleCheckPaymentStatus() {
    const p = state.paymentModal;
    const btn = document.getElementById('msra-btn-check-aiyo');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = `<span>Memeriksa status Aiyo...</span>`;
    }

    try {
      const url = `${API_BASE}/aiyobills/check-status?invoice_id=${encodeURIComponent(p.invoiceId)}&access_token=${encodeURIComponent(p.accessToken)}&booking_id=${encodeURIComponent(p.bookingId)}`;
      const res = await fetch(url);
      const data = await res.json();

      if (data.is_paid || data.status === 'SETTLED' || data.status === 'PAID') {
        onPaymentSuccess(p.bookingId, data.access_pass_token);
        return;
      } else {
        showToast('Pembayaran belum terdeteksi. Silakan selesaikan transfer.');
      }
    } catch (e) {
      console.warn('Check payment status error:', e);
      showToast('Belum ada pelunasan dari perbankan.');
    }

    if (btn) {
      btn.disabled = false;
      btn.innerHTML = `${ICONS.refresh}<span>Cek Status Pembayaran (Aiyo Live)</span>`;
    }
  }

  async function handleSimulatePayment() {
    const p = state.paymentModal;
    const btn = document.getElementById('msra-btn-simulate-pay');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Memverifikasi pelunasan...';
    }

    try {
      const res = await fetch(`${API_BASE}/aiyobills/webhook`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          invoiceId: p.invoiceId,
          booking_id: p.bookingId,
          status: 'SETTLED',
          paidAmount: p.amount,
          settlementDate: new Date().toISOString()
        })
      });
      const data = await res.json();
      onPaymentSuccess(p.bookingId, data.access_pass_token);
    } catch (e) {
      console.warn('Webhook simulation error:', e);
      onPaymentSuccess(p.bookingId);
    }
  }

  function onPaymentSuccess(bookingId, passToken) {
    stopPaymentPolling();
    if (state.paymentModal.timerInterval) {
      clearInterval(state.paymentModal.timerInterval);
    }
    const p = state.paymentModal;
    const bModal = state.bookingModal;
    const room = bModal.room || state.rooms[0];

    const token = passToken || p.accessPassToken || ('MSRA-PASS-' + Math.random().toString(36).substring(2, 8).toUpperCase());

    const newTicket = {
      id: bookingId,
      room_id: room?.id || 'room-1',
      room_name: p.roomName || room?.name || 'Ruang Rapat MSRA',
      door_number: room?.doorNumber || 'DOOR-101',
      floor: room?.floor || 'Lantai 1',
      date: bModal.date || getTodayStr(),
      start_time: bModal.startTime || '10:00',
      duration_hours: bModal.durationHours || 2,
      access_pass_token: token,
      static_door_token: room?.static_door_token || `SPK-DOOR:${room?.code}:sec_token`,
      status: 'paid',
      created_at: new Date().toISOString()
    };

    // Save to device tickets
    state.tickets = [newTicket, ...state.tickets.filter(t => t.id !== bookingId)];
    saveTickets();

    // Close payment modal
    const payBackdrop = document.getElementById('msra-payment-modal-backdrop');
    if (payBackdrop) payBackdrop.classList.add('hidden');

    showToast('Pembayaran Berhasil! Tiket Akses Digital telah aktif.');

    // Switch to tickets tab and open Digital Access Pass
    switchTab('tickets');
    openPassModal(newTicket);
  }

  function closePaymentModal() {
    stopPaymentPolling();
    if (state.paymentModal.timerInterval) {
      clearInterval(state.paymentModal.timerInterval);
    }
    const backdrop = document.getElementById('msra-payment-modal-backdrop');
    if (backdrop) backdrop.classList.add('hidden');
  }

  // Also check immediately when user focuses back to tab
  window.addEventListener('focus', () => {
    const p = state.paymentModal;
    const backdrop = document.getElementById('msra-payment-modal-backdrop');
    if (backdrop && !backdrop.classList.contains('hidden') && p.bookingId && p.invoiceId && p.accessToken) {
      handleCheckPaymentStatus();
    }
  });

  // --- DIGITAL ACCESS PASS MODAL ---
  async function openPassModal(ticket) {
    state.passModal.ticket = ticket;
    state.passModal.solenoidStatus = null;

    const backdrop = document.getElementById('msra-pass-modal-backdrop');
    if (backdrop) backdrop.classList.remove('hidden');

    renderPassModalContent();

    // Query backend in background to ensure access_pass_token and schedule are synced
    if (ticket && ticket.id) {
      try {
        const res = await fetch(`${API_BASE}/bookings/${encodeURIComponent(ticket.id)}`);
        const data = await res.json();
        if (data.success && data.booking) {
          let updated = false;
          if (data.booking.access_pass_token && data.booking.access_pass_token !== ticket.access_pass_token) {
            ticket.access_pass_token = data.booking.access_pass_token;
            updated = true;
          }
          if (data.booking.static_door_token && data.booking.static_door_token !== ticket.static_door_token) {
            ticket.static_door_token = data.booking.static_door_token;
            updated = true;
          }
          if (data.booking.end_time && data.booking.end_time !== ticket.end_time) {
            ticket.end_time = data.booking.end_time;
            updated = true;
          }
          if (updated) {
            saveTickets();
            const canvas = document.getElementById('msra-pass-canvas');
            if (canvas && window.QRCode) {
              window.QRCode.toCanvas(canvas, ticket.access_pass_token, {
                width: 120,
                margin: 1,
                color: { dark: '#0f172a', light: '#ffffff' }
              });
            }
          }
        }
      } catch (err) {
        console.warn('Sync booking details note:', err);
      }
    }
  }

  function closePassModal() {
    const backdrop = document.getElementById('msra-pass-modal-backdrop');
    if (backdrop) backdrop.classList.add('hidden');
  }

  function renderPassModalContent() {
    const t = state.passModal.ticket;
    if (!t) return;

    const modalTitle = document.getElementById('msra-pass-modal-title');
    if (modalTitle) modalTitle.textContent = `Kunci Akses: ${t.room_name}`;

    const body = document.getElementById('msra-pass-modal-body');
    if (!body) return;

    body.innerHTML = `
      <div class="msra-pass-card">
        <div class="msra-pass-header">
          <div>
            <div style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-slate-400);">Kunci Akses Digital</div>
            <div style="font-size: 1rem; font-weight: 700; color: var(--color-white);">${t.room_name}</div>
          </div>
          <span class="msra-pill status-available">Aktif</span>
        </div>
        <div class="msra-pass-body">
          <div class="msra-pass-qr-wrap">
            <canvas id="msra-pass-canvas" style="width: 120px; height: 120px;"></canvas>
          </div>
          <div class="msra-pass-details">
            <div>
              <div class="msra-pass-item">Nomor Pintu & Lokasi</div>
              <div class="msra-pass-value">${t.door_number} • ${t.floor}</div>
            </div>
            <div>
              <div class="msra-pass-item">Jadwal Sesi</div>
              <div class="msra-pass-value">${t.date} (${t.start_time} WIB) • ${t.duration_hours} Jam</div>
            </div>
            <div>
              <div class="msra-pass-item">Kode Tiket</div>
              <div class="msra-pass-value" style="font-family: var(--font-mono);">${t.id}</div>
            </div>
          </div>
        </div>
      </div>

      <div style="margin-top: 1.25rem;">
        <button type="button" class="msra-btn msra-btn-primary msra-btn-full" id="msra-btn-unlock-solenoid" style="padding: 0.75rem 1rem;">
          ${ICONS.unlock}
          <span>Buka Pintu Solenoid Sekarang</span>
        </button>
      </div>

      <div id="msra-solenoid-result-area"></div>

      <div style="margin-top: 1.25rem; font-size: 0.75rem; color: var(--color-slate-500); text-align: center; line-height: 1.4;">
        Arahkan QR Code ke sensor kamera pintu atau tekan tombol di atas saat berada di depan pintu ruangan untuk melepaskan solenoid lock.
      </div>
    `;

    // Render Canvas Pass QR
    const canvas = document.getElementById('msra-pass-canvas');
    if (canvas && window.QRCode) {
      window.QRCode.toCanvas(canvas, t.access_pass_token, {
        width: 120,
        margin: 1,
        color: { dark: '#0f172a', light: '#ffffff' }
      });
    }

    // Attach direct solenoid unlock
    const unlockBtn = document.getElementById('msra-btn-unlock-solenoid');
    if (unlockBtn) {
      unlockBtn.addEventListener('click', () => triggerSolenoidUnlock(t));
    }
  }

  async function triggerSolenoidUnlock(ticket) {
    const area = document.getElementById('msra-solenoid-result-area');
    const btn = document.getElementById('msra-btn-unlock-solenoid');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = `<span>Memverifikasi izin akses...</span>`;
    }

    try {
      const res = await fetch(`${API_BASE}/door/verify-scan`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          booking_id: ticket.id,
          scanned_door_code: ticket.static_door_token || 'SPK-DOOR:ROOM-301:sec_token',
          access_pass_token: ticket.access_pass_token
        })
      });
      const data = await res.json();

      if (data.success && data.status === 'granted') {
        if (area) {
          area.innerHTML = `
            <div class="msra-solenoid-active">
              <div style="font-size: 1rem; margin-bottom: 0.25rem;">${ICONS.unlock} Solenoid Relay Aktif</div>
              <div style="font-size: 0.8125rem; font-weight: 400;">Pintu ${ticket.door_number} terbuka. Silakan dorong pintu masuk. (Kunci otomatis dalam 5s)</div>
            </div>
          `;
        }
        showToast(`Pintu ${ticket.door_number} Berhasil Dibuka!`);
      } else {
        if (area) {
          area.innerHTML = `
            <div style="background-color: var(--status-err-bg); border: 1px solid var(--status-err-bdr); color: var(--status-err-text); padding: 0.875rem; border-radius: var(--radius-md); text-align: center; font-size: 0.8125rem; margin-top: 1rem;">
              Akses Ditolak: ${data.reason || 'Sesi ruangan belum dimulai atau telah berakhir.'}
            </div>
          `;
        }
      }
    } catch (e) {
      console.warn('Door scan API error, running local simulation:', e);
      if (area) {
        area.innerHTML = `
          <div class="msra-solenoid-active">
            <div style="font-size: 1rem; margin-bottom: 0.25rem;">${ICONS.unlock} Solenoid Relay Aktif (Simulasi)</div>
            <div style="font-size: 0.8125rem; font-weight: 400;">Kunci pintu ${ticket.door_number} dilepaskan selama 5 detik.</div>
          </div>
        `;
      }
      showToast(`Pintu ${ticket.door_number} Terbuka.`);
    }

    if (btn) {
      btn.disabled = false;
      btn.innerHTML = `${ICONS.unlock}<span>Buka Pintu Solenoid Sekarang</span>`;
    }
  }

  // --- RENDER TICKETS TAB ---
  function renderTickets() {
    const container = document.getElementById('msra-tickets-container');
    if (!container) return;

    if (state.tickets.length === 0) {
      container.innerHTML = `
        <div style="padding: 3.5rem 1rem; text-align: center; background: var(--color-white); border: var(--border-subtle); border-radius: var(--radius-lg); width: 100%;">
          <div style="color: var(--color-slate-400); margin-bottom: 0.75rem;">${ICONS.key}</div>
          <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.375rem; color: var(--color-slate-900);">Belum Ada Kunci Akses Aktif</h3>
          <p style="font-size: 0.8125rem; color: var(--color-slate-500); margin-bottom: 1.25rem;">Pesan ruangan di katalog dan selesaikan pembayaran untuk mendapatkan kunci pintu digital mandiri.</p>
          <button type="button" class="msra-btn msra-btn-primary" id="msra-btn-explore-rooms">
            <span>Lihat Katalog Ruang</span>
          </button>
        </div>
      `;
      const btn = document.getElementById('msra-btn-explore-rooms');
      if (btn) btn.addEventListener('click', () => switchTab('rooms'));
      return;
    }

    container.innerHTML = `
      <div style="width: 100%; display: flex; flex-direction: column; gap: 1.25rem;">
        ${state.tickets.map(ticket => `
          <div class="msra-card" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem; width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <div>
                <span class="msra-pill status-available" style="margin-bottom: 0.375rem;">Akses Terverifikasi</span>
                <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--color-slate-900); margin-top: 0.25rem;">${ticket.room_name}</h3>
                <div style="font-size: 0.8125rem; color: var(--color-slate-500);">${ticket.door_number} • ${ticket.floor}</div>
              </div>
              <div style="text-align: right;">
                <div style="font-size: 0.75rem; color: var(--color-slate-400);">ID Pemesanan</div>
                <div style="font-family: var(--font-mono); font-size: 0.8125rem; font-weight: 600; color: var(--color-slate-700);">${ticket.id}</div>
              </div>
            </div>

            <div style="background-color: var(--color-slate-50); border: var(--border-subtle); border-radius: var(--radius-sm); padding: 0.75rem; display: flex; justify-content: space-between; font-size: 0.8125rem;">
              <span>Tanggal: <strong>${ticket.date}</strong></span>
              <span>Jam Mulai: <strong>${ticket.start_time} WIB</strong></span>
              <span>Durasi: <strong>${ticket.duration_hours} Jam</strong></span>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 0.25rem;">
              <button type="button" class="msra-btn msra-btn-secondary msra-btn-sm js-view-pass" data-ticket-id="${ticket.id}">
                ${ICONS.qr}
                <span>Tampilkan QR Akses</span>
              </button>
              <button type="button" class="msra-btn msra-btn-primary msra-btn-sm js-quick-unlock" data-ticket-id="${ticket.id}">
                ${ICONS.unlock}
                <span>Buka Solenoid</span>
              </button>
            </div>
          </div>
        `).join('')}
      </div>
    `;

    // Attach listeners
    container.querySelectorAll('.js-view-pass').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-ticket-id');
        const t = state.tickets.find(x => x.id === id);
        if (t) openPassModal(t);
      });
    });

    container.querySelectorAll('.js-quick-unlock').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-ticket-id');
        const t = state.tickets.find(x => x.id === id);
        if (t) triggerSolenoidUnlock(t);
      });
    });
  }

  // --- RENDER SCANNER / TERMINAL TAB ---
  function renderScanner() {
    const container = document.getElementById('msra-scanner-container');
    if (!container) return;

    const ticketsOptions = state.tickets.map(t => `
      <option value="${t.id}">${t.room_name} (${t.door_number}) — ID: ${t.id}</option>
    `).join('');

    container.innerHTML = `
      <div class="msra-terminal">
        <div style="margin-bottom: 1.25rem; text-align: center;">
          <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-slate-900); margin-bottom: 0.25rem;">Terminal Pindai Pintu Pintar</h3>
          <p style="font-size: 0.8125rem; color: var(--color-slate-500);">Uji coba pembaca QR pintu fisik IoT dan aktuator solenoid lock.</p>
        </div>

        <div class="msra-terminal-viewfinder">
          <div style="color: var(--color-slate-400); margin-bottom: 0.5rem;">${ICONS.qr}</div>
          <div style="font-weight: 600; font-size: 0.875rem; color: var(--color-slate-800);">Pindai Kode QR Pintu Fisik</div>
          <div style="font-size: 0.75rem; color: var(--color-slate-500); margin-top: 0.25rem;">Kamera otomatis membaca kode identifikasi ruangan (SPK-DOOR)</div>
        </div>

        <div class="msra-form-group">
          <label class="msra-form-label">Pilih Tiket Akses Aktif</label>
          <select id="msra-scanner-ticket-select" class="msra-select">
            ${state.tickets.length > 0 ? ticketsOptions : '<option value="">(Belum ada tiket aktif - lakukan pemesanan dahulu)</option>'}
          </select>
        </div>

        <div class="msra-form-group">
          <label class="msra-form-label">Kode Stiker Pintu Ruangan (Simulasi Scan)</label>
          <input type="text" id="msra-scanner-door-token" class="msra-input" value="SPK-DOOR:ROOM-301:sec_token_vip_01" style="font-family: var(--font-mono); font-size: 0.8125rem;" />
        </div>

        <button type="button" class="msra-btn msra-btn-primary msra-btn-full" id="msra-btn-scanner-submit">
          ${ICONS.unlock}
          <span>Verifikasi & Aktifkan Solenoid Pintu</span>
        </button>

        <div id="msra-scanner-output" style="margin-top: 1rem;"></div>
      </div>
    `;

    const submitBtn = document.getElementById('msra-btn-scanner-submit');
    if (submitBtn) {
      submitBtn.addEventListener('click', async () => {
        const ticketSelect = document.getElementById('msra-scanner-ticket-select');
        const tokenInput = document.getElementById('msra-scanner-door-token');
        const output = document.getElementById('msra-scanner-output');

        const ticketId = ticketSelect?.value;
        const doorToken = tokenInput?.value;

        if (!ticketId) {
          showToast('Silakan pilih atau buat tiket akses terlebih dahulu.');
          return;
        }

        const ticket = state.tickets.find(t => t.id === ticketId);
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memverifikasi kode pintu...';

        try {
          const res = await fetch(`${API_BASE}/door/verify-scan`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              booking_id: ticketId,
              scanned_door_code: doorToken,
              access_pass_token: ticket?.access_pass_token || 'SIM-PASS-TOKEN'
            })
          });
          const data = await res.json();
          if (data.success && data.status === 'granted') {
            output.innerHTML = `
              <div class="msra-solenoid-active">
                <div style="font-size: 1rem; margin-bottom: 0.25rem;">${ICONS.unlock} Akses Diberikan (Status: Granted)</div>
                <div style="font-size: 0.8125rem; font-weight: 400;">Relay solenoid diaktifkan selama 5000ms. Sinyal dikirim ke ESP32 Door Lock Controller.</div>
              </div>
            `;
            showToast('Solenoid Berhasil Terbuka!');
          } else {
            output.innerHTML = `
              <div style="background-color: var(--status-err-bg); border: 1px solid var(--status-err-bdr); color: var(--status-err-text); padding: 0.875rem; border-radius: var(--radius-md); text-align: center; font-size: 0.8125rem;">
                Akses Ditolak: ${data.reason || 'Kode pintu tidak sesuai dengan reservasi ruangan Anda.'}
              </div>
            `;
          }
        } catch (e) {
          console.warn('Scanner verify error:', e);
          output.innerHTML = `
            <div class="msra-solenoid-active">
              <div style="font-size: 1rem; margin-bottom: 0.25rem;">${ICONS.unlock} Akses Diberikan (Offline Demo)</div>
              <div style="font-size: 0.8125rem; font-weight: 400;">Relay solenoid terbuka.</div>
            </div>
          `;
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = `${ICONS.unlock}<span>Verifikasi & Aktifkan Solenoid Pintu</span>`;
      });
    }
  }

  // --- RENDER LOGS TAB ---
  function renderLogs() {
    const container = document.getElementById('msra-logs-container');
    if (!container) return;

    if (state.logs.length === 0) {
      container.innerHTML = `
        <div style="padding: 3rem 1rem; text-align: center; color: var(--color-slate-500); background: var(--color-white); border: var(--border-subtle); border-radius: var(--radius-lg);">
          <div style="margin-bottom: 0.5rem; font-weight: 600; color: var(--color-slate-800);">Memuat Audit Log Akses...</div>
          <div style="font-size: 0.75rem;">Mengambil rekaman aktivitas pintu fisik dari database.</div>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <div class="msra-table-wrap">
        <table class="msra-table">
          <thead>
            <tr>
              <th>Waktu Akses</th>
              <th>Ruangan</th>
              <th>Nomor Pintu</th>
              <th>Status Izin</th>
              <th>Alasan / Keterangan</th>
            </tr>
          </thead>
          <tbody>
            ${state.logs.map(log => {
              const isGranted = (log.status === 'granted') || (log.access_status === 'granted');
              const badge = isGranted
                ? `<span class="msra-pill status-available" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:700;">Granted</span>`
                : `<span class="msra-pill status-in-use" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-weight:700;">Denied</span>`;
              return `
                <tr>
                  <td style="font-family: var(--font-mono); font-size: 0.75rem;">${log.created_at || '-'}</td>
                  <td style="font-weight: 600;">${log.room_name || log.room_id}</td>
                  <td>${log.door_number || '-'}</td>
                  <td>${badge}</td>
                  <td style="font-size: 0.75rem; color: var(--color-slate-600);">${log.reason || 'Akses solenoid valid'}</td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
      </div>
    `;
  }

  // --- TAB SWITCHING ---
  function switchTab(tabId) {
    if (tabId === 'logs') {
      const isAdmin = state.currentUser && state.currentUser.role === 'admin';
      if (!isAdmin) {
        showToast('Audit Log Akses Pintu khusus untuk Administrator gedung.', 'warning');
        tabId = 'rooms';
      }
    }
    state.activeTab = tabId;

    // Update nav buttons
    document.querySelectorAll('.msra-nav-btn, .msra-mob-btn').forEach(btn => {
      const target = btn.getAttribute('data-tab');
      if (target === tabId) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });

    // Toggle view containers
    const sections = ['rooms', 'tickets', 'scanner', 'logs'];
    sections.forEach(s => {
      const el = document.getElementById(`msra-view-${s}`);
      if (el) {
        el.style.display = s === tabId ? 'block' : 'none';
      }
    });

    if (tabId === 'rooms') renderRooms();
    if (tabId === 'tickets') renderTickets();
    if (tabId === 'scanner') renderScanner();
    if (tabId === 'logs') fetchLogs();
  }

  // --- INITIALIZATION & EVENT BINDINGS ---
  function init() {
    // Check if current window is an OAuth popup callback
    if (handleOAuthCallback()) {
      return;
    }

    // Nav events
    document.querySelectorAll('.msra-nav-btn, .msra-mob-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const tab = btn.getAttribute('data-tab');
        if (tab) switchTab(tab);
      });
    });

    const brandEl = document.getElementById('msra-brand-home');
    if (brandEl) {
      brandEl.addEventListener('click', () => switchTab('rooms'));
    }

    // Filter tags
    document.querySelectorAll('.msra-tag-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.msra-tag-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        state.selectedCategory = btn.getAttribute('data-category') || 'all';
        renderRooms();
      });
    });

    // Search input
    const searchInput = document.getElementById('msra-search-input');
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        state.searchQuery = e.target.value;
        renderRooms();
      });
    }

    // Booking modal buttons
    const closeBookBtn = document.getElementById('msra-btn-close-book-modal');
    if (closeBookBtn) closeBookBtn.addEventListener('click', closeBookingModal);

    const cancelBookBtn = document.getElementById('msra-btn-cancel-book');
    if (cancelBookBtn) cancelBookBtn.addEventListener('click', closeBookingModal);

    const confirmBookBtn = document.getElementById('msra-btn-confirm-booking');
    if (confirmBookBtn) confirmBookBtn.addEventListener('click', submitHoldAndProceedToPayment);

    // Payment modal buttons
    const closePayBtn = document.getElementById('msra-btn-close-pay-modal');
    if (closePayBtn) closePayBtn.addEventListener('click', closePaymentModal);

    const qrisTabBtn = document.getElementById('msra-tab-qris');
    if (qrisTabBtn) {
      qrisTabBtn.addEventListener('click', () => {
        state.paymentModal.paymentChannel = 'QRIS';
        renderPaymentModalContent();
        renderPaymentMethodBody();
      });
    }

    const vaTabBtn = document.getElementById('msra-tab-va');
    if (vaTabBtn) {
      vaTabBtn.addEventListener('click', () => {
        state.paymentModal.paymentChannel = 'VA_CLOSED';
        renderPaymentModalContent();
        renderPaymentMethodBody();
      });
    }

    // Pass modal buttons
    const closePassBtn = document.getElementById('msra-btn-close-pass-modal');
    if (closePassBtn) closePassBtn.addEventListener('click', closePassModal);

    // Refresh button
    const refreshBtn = document.getElementById('msra-btn-refresh');
    if (refreshBtn) {
      refreshBtn.addEventListener('click', () => {
        fetchRooms();
        if (state.activeTab === 'logs') fetchLogs();
        showToast('Data diperbarui dari server.');
      });
    }

    // Google Auth & User Profile event bindings
    initGoogleAuth();

    const googleLoginBtn = document.getElementById('msra-btn-google-login');
    if (googleLoginBtn) googleLoginBtn.addEventListener('click', triggerGoogleLogin);

    const profileBtn = document.getElementById('msra-user-profile-btn');
    const userDropdown = document.getElementById('msra-user-dropdown');
    if (profileBtn && userDropdown) {
      profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isHidden = userDropdown.classList.contains('hidden') || userDropdown.style.display === 'none';
        if (isHidden) {
          userDropdown.classList.remove('hidden');
          userDropdown.style.display = 'block';
        } else {
          userDropdown.classList.add('hidden');
          userDropdown.style.display = 'none';
        }
      });
      document.addEventListener('click', (e) => {
        if (!userDropdown.contains(e.target) && !profileBtn.contains(e.target)) {
          userDropdown.classList.add('hidden');
          userDropdown.style.display = 'none';
        }
      });
    }

    const editPhoneBtn = document.getElementById('msra-btn-edit-phone');
    if (editPhoneBtn) {
      editPhoneBtn.addEventListener('click', () => {
        if (userDropdown) userDropdown.classList.add('hidden');
        openPhoneModal();
      });
    }

    const logoutBtn = document.getElementById('msra-btn-logout');
    if (logoutBtn) logoutBtn.addEventListener('click', handleLogout);

    const closePhoneBtn = document.getElementById('msra-btn-close-phone-modal');
    if (closePhoneBtn) closePhoneBtn.addEventListener('click', closePhoneModal);

    const submitPhoneBtn = document.getElementById('msra-btn-submit-phone');
    if (submitPhoneBtn) submitPhoneBtn.addEventListener('click', submitPhoneNumber);

    const phoneInputEl = document.getElementById('msra-phone-input');
    if (phoneInputEl) {
      phoneInputEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          submitPhoneNumber();
        }
      });
    }

    // Close on backdrop click
    ['msra-booking-modal-backdrop', 'msra-payment-modal-backdrop', 'msra-pass-modal-backdrop', 'msra-install-modal-backdrop', 'msra-phone-modal-backdrop'].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.addEventListener('click', (e) => {
          if (e.target === el) {
            el.classList.add('hidden');
          }
        });
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeBookingModal();
        closePaymentModal();
        closePassModal();
        closeInstallModal();
        closePhoneModal();
      }
    });

    // PWA Install bindings
    const installBtn = document.getElementById('msra-btn-install-pwa');
    if (installBtn) {
      if (isStandalone()) {
        installBtn.style.display = 'none';
      } else {
        installBtn.addEventListener('click', triggerInstallPwa);
      }
    }

    const closeInstallBtn = document.getElementById('msra-btn-close-install-modal');
    if (closeInstallBtn) closeInstallBtn.addEventListener('click', closeInstallModal);

    const directInstallBtn = document.getElementById('msra-btn-direct-install');
    if (directInstallBtn) directInstallBtn.addEventListener('click', triggerInstallPwa);

    // Update tickets badge
    updateNavBadge();

    // Initial fetch
    fetchRooms();
  }

  // --- PWA INSTALLATION WORKFLOW ---
  let deferredPrompt = null;

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    console.log('[PWA] beforeinstallprompt event captured');

    const directWrap = document.getElementById('msra-install-direct-wrap');
    if (directWrap) directWrap.style.display = 'block';
  });

  window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    showToast('Aplikasi MSRA berhasil dipasang!');
    closeInstallModal();
    const installBtn = document.getElementById('msra-btn-install-pwa');
    if (installBtn) installBtn.style.display = 'none';
  });

  function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  }

  async function triggerInstallPwa() {
    if (deferredPrompt) {
      deferredPrompt.prompt();
      try {
        const { outcome } = await deferredPrompt.userChoice;
        if (outcome === 'accepted') {
          showToast('Memasang aplikasi MSRA...');
        }
      } catch (err) {
        console.warn('Install prompt error:', err);
      }
      deferredPrompt = null;
    } else {
      openInstallModal();
    }
  }

  function openInstallModal() {
    const backdrop = document.getElementById('msra-install-modal-backdrop');
    if (backdrop) backdrop.classList.remove('hidden');
    const directWrap = document.getElementById('msra-install-direct-wrap');
    if (directWrap) {
      directWrap.style.display = deferredPrompt ? 'block' : 'none';
    }
  }

  function closeInstallModal() {
    const backdrop = document.getElementById('msra-install-modal-backdrop');
    if (backdrop) backdrop.classList.add('hidden');
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
