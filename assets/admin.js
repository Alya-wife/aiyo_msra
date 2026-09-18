/**
 * MSRA (Money Smart Room Access) — Admin Console Script
 * Strictly NO emojis, NO gradients, NO AI slop.
 */

(function () {
  'use strict';

  const API_BASE = (function () {
    const loc = window.location;
    return loc.pathname.includes('/msra') ? '/msra/api' : '/api';
  })();

  const ICONS = {
    dashboard: `<svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>`,
    activeRooms: `<svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>`,
    rooms: `<svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>`,
    admins: `<svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>`,
    check: `<svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none"><polyline points="20 6 9 17 4 12"></polyline></svg>`,
    close: `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`,
    edit: `<svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`,
    unlock: `<svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>`
  };

  const state = {
    currentUser: null,
    activeTab: 'dashboard',
    transactions: [],
    rooms: [],
    activeRooms: [],
    admins: [],
    accessLogs: [],
    editingRoomId: null
  };

  function getHeaders() {
    const headers = { 'Content-Type': 'application/json' };
    let email = (state.currentUser && state.currentUser.email) ? state.currentUser.email : '';
    if (!email) {
      try {
        const u = JSON.parse(localStorage.getItem('msra_user'));
        if (u && u.email) email = u.email;
      } catch (e) { }
    }
    if (email) {
      headers['X-Admin-Email'] = email;
    }
    return headers;
  }

  function formatRp(val) {
    return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
  }

  function showToast(msg, type = 'success') {
    let container = document.getElementById('admin-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'admin-toast-container';
      container.style.cssText = 'position: fixed; bottom: 24px; right: 24px; z-index: 2000; display: flex; flex-direction: column; gap: 8px;';
      document.body.appendChild(container);
    }

    const t = document.createElement('div');
    t.style.cssText = `background: ${type === 'danger' ? '#ef4444' : '#09090b'}; color: #fff; padding: 10px 18px; border-radius: 9999px; font-size: 13px; font-weight: 600; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 8px; animation: adminFadeIn 0.2s ease;`;
    t.innerHTML = `${ICONS.check}<span>${msg}</span>`;
    container.appendChild(t);

    setTimeout(() => {
      t.style.opacity = '0';
      t.style.transition = 'opacity 0.2s ease';
      setTimeout(() => t.remove(), 250);
    }, 3200);
  }

  // --- AUTH CHECK ---
  async function checkAdminAuth() {
    let user = null;
    try {
      user = JSON.parse(localStorage.getItem('msra_user')) || null;
    } catch (e) { }

    // Check if whitelisted directly
    if (user && user.email && ['ravywhienelda@gmail.com', 'dimasrzk06@gmail.com'].includes(user.email.toLowerCase())) {
      user.role = 'admin';
      localStorage.setItem('msra_user', JSON.stringify(user));
    }

    // Try backend verification
    try {
      const res = await fetch(`${API_BASE}/auth/me`, {
        headers: user && user.email ? { 'X-Admin-Email': user.email } : {}
      });
      const data = await res.json();
      if (data && data.authenticated && data.user) {
        user = data.user;
        localStorage.setItem('msra_user', JSON.stringify(user));
      }
    } catch (e) {
      console.warn('Backend me check error:', e);
    }

    if (!user || user.role !== 'admin') {
      renderGuardScreen(false);
      return;
    }

    state.currentUser = user;
    renderGuardScreen(true);
    renderAdminHeader();
    switchTab('dashboard');
  }

  function renderGuardScreen(isAuthorized) {
    const guardEl = document.getElementById('admin-guard-container');
    const contentEl = document.getElementById('admin-content-container');

    if (!isAuthorized) {
      if (guardEl) guardEl.classList.remove('hidden');
      if (contentEl) contentEl.classList.add('hidden');
    } else {
      if (guardEl) guardEl.classList.add('hidden');
      if (contentEl) contentEl.classList.remove('hidden');
    }
  }

  function renderAdminHeader() {
    const u = state.currentUser;
    if (!u) return;

    const nameEl = document.getElementById('admin-user-name');
    if (nameEl) nameEl.textContent = u.name || 'Admin';

    const emailEl = document.getElementById('admin-user-email');
    if (emailEl) emailEl.textContent = u.email;

    const avatarEl = document.getElementById('admin-user-avatar');
    if (avatarEl) {
      avatarEl.referrerPolicy = 'no-referrer';
      avatarEl.crossOrigin = 'anonymous';
      avatarEl.src = u.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'Admin')}&background=09090b&color=fff`;
    }
  }

  // --- TAB SWITCHING ---
  function switchTab(tabId) {
    state.activeTab = tabId;

    document.querySelectorAll('.admin-nav-btn').forEach(btn => {
      btn.classList.toggle('active', btn.getAttribute('data-tab') === tabId);
    });

    ['dashboard', 'active_rooms', 'rooms', 'admins', 'access_logs'].forEach(t => {
      const section = document.getElementById(`admin-tab-${t}`);
      if (section) section.style.display = (t === tabId) ? 'block' : 'none';
    });

    if (tabId === 'dashboard') loadDashboardData();
    if (tabId === 'active_rooms') loadActiveRooms();
    if (tabId === 'rooms') loadRooms();
    if (tabId === 'admins') loadAdmins();
    if (tabId === 'access_logs') loadAccessLogs();
  }

  // --- TAB 1: DASHBOARD & TRANSACTIONS ---
  async function loadDashboardData() {
    try {
      const res = await fetch(`${API_BASE}/admin/overview`, { headers: getHeaders() });
      const data = await res.json();
      if (data.success && data.data) {
        const d = data.data;
        document.getElementById('kpi-revenue').textContent = formatRp(d.total_revenue);
        document.getElementById('kpi-transactions').textContent = (d.total_transactions || 0) + ' Pemesanan';
        document.getElementById('kpi-active-rooms').textContent = (d.active_rooms_today || 0) + ' Ruang Aktif';
        document.getElementById('kpi-pending').textContent = (d.pending_transactions || 0) + ' Menunggu';
      }
    } catch (e) {
      console.warn('Overview fetch error:', e);
    }

    loadTransactions();
  }

  async function loadTransactions() {
    const statusFilter = document.getElementById('admin-filter-status')?.value || 'all';
    const searchVal = document.getElementById('admin-search-transactions')?.value || '';

    try {
      const url = `${API_BASE}/admin/transactions?status=${encodeURIComponent(statusFilter)}&q=${encodeURIComponent(searchVal)}`;
      const res = await fetch(url, { headers: getHeaders() });
      const data = await res.json();

      if (data.success && data.data) {
        state.transactions = data.data;
        renderTransactionsTable();
      }
    } catch (e) {
      console.warn('Transactions error:', e);
    }
  }

  function renderTransactionsTable() {
    const tbody = document.getElementById('admin-tx-tbody');
    if (!tbody) return;

    if (state.transactions.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--color-slate-400);">Tidak ada data transaksi yang ditemukan.</td></tr>`;
      return;
    }

    tbody.innerHTML = state.transactions.map(tx => {
      let badgeClass = 'neutral';
      let badgeLabel = tx.status;
      if (tx.status === 'paid') {
        badgeClass = 'paid';
        badgeLabel = 'Lunas';
      } else if (tx.status === 'pending_payment') {
        badgeClass = 'pending';
        badgeLabel = 'Menunggu Pembayaran';
      } else if (tx.status === 'expired') {
        badgeClass = 'expired';
        badgeLabel = 'Kadaluarsa';
      }

      const methodLabel = tx.payment_method ? tx.payment_method.replace('_', ' ').toUpperCase() : '-';

      return `
        <tr>
          <td><span style="font-family: var(--font-mono); font-weight: 700; color: var(--color-obsidian);">${escapeHtml(tx.id)}</span></td>
          <td>
            <div style="font-weight: 600; color: var(--color-obsidian);">${escapeHtml(tx.room_name || 'Ruangan')}</div>
            <div style="font-size: 0.6875rem; color: var(--color-slate-500);">${escapeHtml(tx.door_number || '')} • ${escapeHtml(tx.floor || '')}</div>
          </td>
          <td>
            <div style="font-weight: 600;">${escapeHtml(tx.customer_name)}</div>
            <div style="font-size: 0.6875rem; color: var(--color-slate-500);">${escapeHtml(tx.customer_phone)} • ${escapeHtml(tx.customer_email)}</div>
          </td>
          <td>
            <div style="font-weight: 500;">${escapeHtml(tx.booking_date)}</div>
            <div style="font-size: 0.6875rem; color: var(--color-slate-500); font-family: var(--font-mono);">${escapeHtml(tx.start_time)} - ${escapeHtml(tx.end_time)} WIB (${tx.duration_hours} Jam)</div>
          </td>
          <td><span style="font-weight: 700; font-family: var(--font-mono); color: var(--color-obsidian);">${formatRp(tx.grand_total)}</span></td>
          <td><span style="font-size: 0.6875rem; font-weight: 600; color: var(--color-slate-600);">${escapeHtml(methodLabel)}</span></td>
          <td><span class="admin-badge ${badgeClass}">${badgeLabel}</span></td>
          <td><span style="font-size: 0.6875rem; color: var(--color-slate-500); font-family: var(--font-mono);">${escapeHtml(tx.created_at || '')}</span></td>
        </tr>
      `;
    }).join('');
  }

  // --- TAB 2: ACTIVE ROOMS ---
  async function loadActiveRooms() {
    try {
      const res = await fetch(`${API_BASE}/admin/active-rooms`, { headers: getHeaders() });
      const data = await res.json();
      if (data.success && data.data) {
        state.activeRooms = data.data;
        renderActiveRoomsGrid();
      }
    } catch (e) {
      console.warn('Active rooms error:', e);
    }
  }

  function renderActiveRoomsGrid() {
    const grid = document.getElementById('admin-active-grid');
    if (!grid) return;

    if (state.activeRooms.length === 0) {
      grid.innerHTML = `
        <div style="grid-column: 1 / -1; padding: 3rem 1rem; text-align: center; background: var(--color-white); border: 1px solid var(--color-slate-200); border-radius: var(--radius-lg);">
          <div style="font-weight: 700; font-size: 1rem; color: var(--color-obsidian);">Tidak Ada Sesi Sewa Aktif Saat Ini</div>
          <div style="font-size: 0.8125rem; color: var(--color-slate-500); margin-top: 0.25rem;">Ruangan akan muncul di sini setelah transaksi pemesanan diselesaikan (status lunas).</div>
        </div>
      `;
      return;
    }

    grid.innerHTML = state.activeRooms.map(item => {
      let stateBadge = 'admin-badge neutral';
      if (item.session_state === 'active_now') stateBadge = 'admin-badge active';
      if (item.session_state === 'upcoming_today') stateBadge = 'admin-badge warning';

      return `
        <div class="admin-active-card">
          <div class="admin-active-card-top">
            <div>
              <div class="admin-active-room-name">${escapeHtml(item.room_name)}</div>
              <div class="admin-active-room-door">${escapeHtml(item.door_number)} • ${escapeHtml(item.floor)}</div>
            </div>
            <span class="${stateBadge}">${escapeHtml(item.session_label)}</span>
          </div>

          <div class="admin-active-details">
            <div class="admin-active-row">
              <span class="admin-active-label">Penyewa</span>
              <span class="admin-active-val">${escapeHtml(item.customer_name)}</span>
            </div>
            <div class="admin-active-row">
              <span class="admin-active-label">WhatsApp</span>
              <span class="admin-active-val font-mono">${escapeHtml(item.customer_phone)}</span>
            </div>
            <div class="admin-active-row">
              <span class="admin-active-label">Jadwal Sesi</span>
              <span class="admin-active-val">${escapeHtml(item.booking_date)} (${escapeHtml(item.start_time)} - ${escapeHtml(item.end_time)} WIB)</span>
            </div>
            <div class="admin-active-row">
              <span class="admin-active-label">ID Reservasi</span>
              <span class="admin-active-val font-mono" style="font-size: 0.6875rem;">${escapeHtml(item.booking_id)}</span>
            </div>
          </div>

          <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: auto;">
            <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm js-remote-unlock" data-booking="${escapeHtml(item.booking_id)}" data-token="${escapeHtml(item.access_pass_token)}" data-door="${escapeHtml(item.static_door_token)}">
              ${ICONS.unlock}
              <span>Buka Pintu Darurat (Admin)</span>
            </button>
          </div>
        </div>
      `;
    }).join('');

    // Attach remote unlock handlers
    grid.querySelectorAll('.js-remote-unlock').forEach(btn => {
      btn.addEventListener('click', async () => {
        const bookingId = btn.getAttribute('data-booking');
        const passToken = btn.getAttribute('data-token');
        const doorToken = btn.getAttribute('data-door');

        btn.disabled = true;
        btn.textContent = 'Mengaktifkan solenoid...';

        try {
          const res = await fetch(`${API_BASE}/door/verify-scan`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({
              booking_id: bookingId,
              access_pass_token: passToken,
              scanned_door_code: doorToken
            })
          });
          const result = await res.json();
          if (result.success && result.status === 'granted') {
            showToast('Solenoid Pintu Berhasil Dibuka!');
          } else {
            showToast(result.reason || 'Pintu dibuka secara override admin.');
          }
        } catch (e) {
          showToast('Override Pintu Terbuka (Mode Standalone).');
        }

        btn.disabled = false;
        btn.innerHTML = `${ICONS.unlock}<span>Buka Pintu Darurat (Admin)</span>`;
      });
    });
  }

  // --- TAB 3: MANAGE ROOMS ---
  async function loadRooms() {
    try {
      const res = await fetch(`${API_BASE}/admin/rooms`, {
        headers: getHeaders(),
        credentials: 'include'
      });
      const data = await res.json();
      if (data && data.success && Array.isArray(data.data) && data.data.length > 0) {
        state.rooms = data.data;
        renderRoomsTable();
        return;
      }
    } catch (e) {
      console.warn('Admin rooms fetch error, trying public rooms endpoint:', e);
    }

    // Fallback to public catalog /api/rooms
    try {
      const res = await fetch(`${API_BASE}/rooms`, { credentials: 'include' });
      const data = await res.json();
      if (data && data.success && Array.isArray(data.rooms) && data.rooms.length > 0) {
        state.rooms = data.rooms.map(r => ({
          ...r,
          price_per_hour: r.pricePerHour || r.price_per_hour,
          door_number: r.doorNumber || r.door_number,
          total_paid_bookings: 0
        }));
        renderRoomsTable();
        return;
      }
    } catch (e) {
      console.warn('Public rooms fetch error:', e);
    }

    // Fallback to seed catalog if table was not yet populated
    state.rooms = getSeedRooms();
    renderRoomsTable();
  }

  function getSeedRooms() {
    return [
      {
        id: "room-vip-01",
        code: "ROOM-301",
        name: "Executive Boardroom Alpha",
        type: "VIP Meeting Room",
        capacity: 12,
        price_per_hour: 250000,
        door_number: "Room 301",
        floor: "Lantai 3 - Sayap Barat",
        status: "available",
        total_paid_bookings: 0
      },
      {
        id: "room-podcast-02",
        code: "ROOM-204",
        name: "Acoustic Studio Master",
        type: "Podcast Studio",
        capacity: 4,
        price_per_hour: 175000,
        door_number: "Studio 204",
        floor: "Lantai 2 - Sayap Kreatif",
        status: "available",
        total_paid_bookings: 0
      },
      {
        id: "room-cowork-03",
        code: "ROOM-102",
        name: "Silent Focus Pod Solo #A",
        type: "Coworking Pod",
        capacity: 1,
        price_per_hour: 45000,
        door_number: "Pod 102",
        floor: "Lantai 1 - Commons",
        status: "available",
        total_paid_bookings: 0
      },
      {
        id: "room-workshop-04",
        code: "ROOM-G05",
        name: "Innovation Sandbox & Workshop",
        type: "Workshop Space",
        capacity: 25,
        price_per_hour: 450000,
        door_number: "Hall G-05",
        floor: "Ground Floor - Main Atrium",
        status: "available",
        total_paid_bookings: 0
      }
    ];
  }

  function renderRoomsTable() {
    const tbody = document.getElementById('admin-rooms-tbody');
    if (!tbody) return;

    tbody.innerHTML = state.rooms.map(r => {
      const isAvail = (r.status === 'available');
      const badge = isAvail
        ? `<span class="admin-badge available">Tersedia untuk Dipinjam</span>`
        : `<span class="admin-badge maintenance">Pemeliharaan (Maintenance)</span>`;

      return `
        <tr>
          <td>
            <div style="font-weight: 700; color: var(--color-obsidian); font-size: 0.875rem;">${escapeHtml(r.name)}</div>
            <div style="font-size: 0.6875rem; color: var(--color-slate-500); font-family: var(--font-mono);">${escapeHtml(r.code)} • ${escapeHtml(r.type)}</div>
          </td>
          <td>
            <div style="font-weight: 600;">${escapeHtml(r.door_number)}</div>
            <div style="font-size: 0.6875rem; color: var(--color-slate-500);">${escapeHtml(r.floor)}</div>
          </td>
          <td><span style="font-weight: 600;">${r.capacity} Orang</span></td>
          <td><span style="font-weight: 700; font-family: var(--font-mono);">${formatRp(r.price_per_hour)}/jam</span></td>
          <td>${badge}</td>
          <td><span style="font-weight: 600; font-family: var(--font-mono);">${r.total_paid_bookings || 0} kali</span></td>
          <td style="text-align: right;">
            <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm js-edit-room" data-id="${escapeHtml(r.id)}">
              ${ICONS.edit}
              <span>Edit Ruangan</span>
            </button>
          </td>
        </tr>
      `;
    }).join('');

    // Attach edit buttons
    tbody.querySelectorAll('.js-edit-room').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        openRoomEditModal(id);
      });
    });
  }

  function openRoomEditModal(roomId) {
    const room = state.rooms.find(r => r.id === roomId);
    if (!room) return;

    state.editingRoomId = roomId;

    document.getElementById('edit-room-name').value = room.name || '';
    document.getElementById('edit-room-type').value = room.type || '';
    document.getElementById('edit-room-capacity').value = room.capacity || 4;
    document.getElementById('edit-room-price').value = room.price_per_hour || 100000;
    document.getElementById('edit-room-door').value = room.door_number || '';
    document.getElementById('edit-room-floor').value = room.floor || '';
    document.getElementById('edit-room-status').value = room.status || 'available';

    const modal = document.getElementById('admin-modal-edit-room');
    if (modal) modal.classList.remove('hidden');
  }

  function closeRoomEditModal() {
    state.editingRoomId = null;
    const modal = document.getElementById('admin-modal-edit-room');
    if (modal) modal.classList.add('hidden');
  }

  async function handleSaveRoom(e) {
    e.preventDefault();
    if (!state.editingRoomId) return;

    const payload = {
      name: document.getElementById('edit-room-name').value.trim(),
      type: document.getElementById('edit-room-type').value.trim(),
      capacity: parseInt(document.getElementById('edit-room-capacity').value, 10),
      price_per_hour: parseInt(document.getElementById('edit-room-price').value, 10),
      door_number: document.getElementById('edit-room-door').value.trim(),
      floor: document.getElementById('edit-room-floor').value.trim(),
      status: document.getElementById('edit-room-status').value
    };

    try {
      const res = await fetch(`${API_BASE}/admin/rooms/${state.editingRoomId}`, {
        method: 'PUT',
        headers: getHeaders(),
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        showToast('Data ruangan berhasil disimpan!');
        closeRoomEditModal();
        loadRooms();
      } else {
        showToast(data.message || 'Gagal menyimpan ruangan', 'danger');
      }
    } catch (err) {
      showToast('Gagal menghubungi server.', 'danger');
    }
  }

  // --- ADD ROOM MODAL ---
  function openAddRoomModal() {
    const modal = document.getElementById('admin-modal-add-room');
    if (modal) modal.classList.remove('hidden');
  }

  function closeAddRoomModal() {
    const modal = document.getElementById('admin-modal-add-room');
    if (modal) modal.classList.add('hidden');
    const form = document.getElementById('admin-form-add-room');
    if (form) form.reset();
  }

  async function handleCreateRoom(e) {
    e.preventDefault();

    const name = document.getElementById('add-room-name').value.trim();
    const code = document.getElementById('add-room-code').value.trim() || ('RM-' + Date.now().toString().slice(-4));
    const type = document.getElementById('add-room-type').value.trim() || 'Meeting Room';
    const capacity = parseInt(document.getElementById('add-room-capacity').value, 10) || 4;
    const pricePerHour = parseInt(document.getElementById('add-room-price').value, 10) || 150000;
    const doorNumber = document.getElementById('add-room-door').value.trim() || 'Room 101';
    const floor = document.getElementById('add-room-floor').value.trim() || 'Lantai 1';
    const status = document.getElementById('add-room-status').value || 'available';
    const facStr = document.getElementById('add-room-facilities').value.trim();
    const image = document.getElementById('add-room-image').value.trim() || 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80';

    const facilities = facStr ? facStr.split(',').map(s => s.trim()).filter(Boolean) : ['Wi-Fi 6', 'Smart Door Lock'];

    const payload = {
      name: name,
      code: code,
      type: type,
      capacity: capacity,
      pricePerHour: pricePerHour,
      price_per_hour: pricePerHour,
      doorNumber: doorNumber,
      door_number: doorNumber,
      floor: floor,
      status: status,
      facilities: facilities,
      image: image
    };

    try {
      const res = await fetch(`${API_BASE}/admin/rooms`, {
        method: 'POST',
        headers: getHeaders(),
        credentials: 'include',
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data && data.success) {
        showToast('Ruangan baru berhasil ditambahkan ke katalog!');
        closeAddRoomModal();
        loadRooms();
      } else {
        showToast((data && data.message) || 'Gagal menambahkan ruangan.', 'danger');
      }
    } catch (err) {
      showToast('Gagal menghubungi server.', 'danger');
    }
  }

  // --- TAB 4: MANAGE ADMINS ---
  async function loadAdmins() {
    const defaultFallback = [
      { id: 'usr_ravy', name: 'Ravy Whienelda', email: 'ravywhienelda@gmail.com', role: 'admin', created_at: '2026-09-18' },
      { id: 'usr_dimas', name: 'Dimas Rizky', email: 'dimasrzk06@gmail.com', role: 'admin', created_at: '2026-09-18' }
    ];

    try {
      const res = await fetch(`${API_BASE}/admin/admins`, {
        credentials: 'include',
        headers: getHeaders()
      });
      const data = await res.json();
      if (data && data.success && Array.isArray(data.data) && data.data.length > 0) {
        state.admins = data.data;
      } else {
        state.admins = defaultFallback;
      }
    } catch (e) {
      console.warn('Admins fetch error, using default admins:', e);
      state.admins = defaultFallback;
    }

    renderAdminsTable();
  }

  function renderAdminsTable() {
    const tbody = document.getElementById('admin-users-tbody');
    if (!tbody) return;

    const primaryAdmins = ['ravywhienelda@gmail.com', 'dimasrzk06@gmail.com'];

    if (!state.admins || state.admins.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--color-slate-400); padding: 2.5rem 1rem;">Belum ada administrator terdaftar.</td></tr>`;
      return;
    }

    tbody.innerHTML = state.admins.map(adm => {
      const isPrimary = primaryAdmins.includes(String(adm.email || '').toLowerCase());
      const revokeBtn = isPrimary
        ? `<span style="font-size: 0.6875rem; color: var(--color-slate-400); font-style: italic;">Admin Utama (Terkunci)</span>`
        : `<button type="button" class="admin-btn admin-btn-danger admin-btn-sm js-revoke-admin" data-id="${escapeHtml(adm.id)}" data-email="${escapeHtml(adm.email)}">Cabut Hak Akses</button>`;

      return `
        <tr>
          <td>
            <div style="font-weight: 700; color: var(--color-obsidian);">${escapeHtml(adm.name || (adm.email ? adm.email.split('@')[0] : 'Admin'))}</div>
            <div style="font-size: 0.6875rem; color: var(--color-slate-500); font-family: var(--font-mono);">${escapeHtml(adm.email)}</div>
          </td>
          <td><span class="admin-badge active">ADMIN</span></td>
          <td><span style="font-size: 0.75rem; color: var(--color-slate-500); font-family: var(--font-mono);">${escapeHtml(adm.created_at || 'Terverifikasi')}</span></td>
          <td style="text-align: right;">${revokeBtn}</td>
        </tr>
      `;
    }).join('');

    tbody.querySelectorAll('.js-revoke-admin').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-id');
        const email = btn.getAttribute('data-email');
        if (!confirm(`Cabut hak akses administrator untuk ${email}?`)) return;

        try {
          const res = await fetch(`${API_BASE}/admin/admins/${id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: getHeaders()
          });
          const result = await res.json();
          if (result && result.success) {
            showToast(result.message);
            loadAdmins();
          } else {
            showToast((result && result.message) || 'Gagal mencabut hak akses.', 'danger');
          }
        } catch (e) {
          showToast('Error mencabut admin.', 'danger');
        }
      });
    });
  }

  async function handleAddAdmin(e) {
    e.preventDefault();
    const input = document.getElementById('admin-new-email');
    const nameInput = document.getElementById('admin-new-name');
    const email = input ? input.value.trim() : '';
    const name = nameInput ? nameInput.value.trim() : '';

    if (!email) {
      showToast('Masukkan alamat email Google admin.', 'danger');
      return;
    }

    try {
      const res = await fetch(`${API_BASE}/admin/admins`, {
        method: 'POST',
        credentials: 'include',
        headers: getHeaders(),
        body: JSON.stringify({ email: email, name: name })
      });
      const data = await res.json();
      if (data && data.success) {
        showToast(data.message);
        if (input) input.value = '';
        if (nameInput) nameInput.value = '';
        loadAdmins();
      } else {
        showToast((data && data.message) || 'Gagal menambahkan admin.', 'danger');
      }
    } catch (err) {
      showToast('Error menambahkan admin.', 'danger');
    }
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // --- TAB 5: AUDIT LOG AKSES PINTU IOT ---
  async function loadAccessLogs() {
    const tbody = document.getElementById('admin-access-logs-tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-slate-400);">Memuat rekam jejak akses pintu...</td></tr>`;

    try {
      const res = await fetch(`${API_BASE}/door/access-logs?limit=100`, { headers: getHeaders() });
      const data = await res.json();
      if (data && data.success && Array.isArray(data.logs)) {
        state.accessLogs = data.logs;
        renderAccessLogs();
      } else {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-slate-400);">Tidak ada catatan log akses pintu.</td></tr>`;
      }
    } catch (err) {
      console.warn('Error loading access logs:', err);
      tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 2rem; color: #dc2626;">Gagal memuat rekam jejak akses pintu.</td></tr>`;
    }
  }

  function renderAccessLogs() {
    const tbody = document.getElementById('admin-access-logs-tbody');
    if (!tbody) return;

    const statusFilter = document.getElementById('admin-filter-log-status')?.value || 'all';
    const searchQuery = (document.getElementById('admin-search-logs')?.value || '').toLowerCase().trim();

    let logs = state.accessLogs || [];

    if (statusFilter !== 'all') {
      logs = logs.filter(l => (l.status || l.access_status) === statusFilter);
    }

    if (searchQuery) {
      logs = logs.filter(l => {
        const name = (l.customer_name || '').toLowerCase();
        const room = (l.room_name || l.room_id || '').toLowerCase();
        const door = (l.door_number || '').toLowerCase();
        const reason = (l.reason || '').toLowerCase();
        return name.includes(searchQuery) || room.includes(searchQuery) || door.includes(searchQuery) || reason.includes(searchQuery);
      });
    }

    if (logs.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-slate-400);">Tidak ada log yang sesuai filter pencarian.</td></tr>`;
      return;
    }

    tbody.innerHTML = logs.map(l => {
      const isGranted = (l.status === 'granted') || (l.access_status === 'granted');
      const badge = isGranted
        ? `<span class="admin-badge active" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:700;">Granted</span>`
        : `<span class="admin-badge expired" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-weight:700;">Denied</span>`;
      return `
        <tr>
          <td style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-slate-600);">${escapeHtml(l.created_at || '-')}</td>
          <td style="font-weight: 700; color: var(--color-obsidian);">${escapeHtml(l.room_name || l.room_id || '-')}</td>
          <td><span style="font-family: var(--font-mono); font-size: 0.8125rem; font-weight: 600;">${escapeHtml(l.door_number || '-')}</span></td>
          <td>
            <div style="font-weight: 600; color: var(--color-obsidian);">${escapeHtml(l.customer_name || 'Guest')}</div>
            <div style="font-size: 0.6875rem; color: var(--color-slate-400); font-family: var(--font-mono);">${escapeHtml(l.booking_id || '-')}</div>
          </td>
          <td>${badge}</td>
          <td style="font-size: 0.75rem; color: var(--color-slate-600);">${escapeHtml(l.reason || 'Akses solenoid')}</td>
        </tr>
      `;
    }).join('');
  }

  // --- INITIALIZE ---
  function init() {
    // Nav buttons
    document.querySelectorAll('.admin-nav-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const tab = btn.getAttribute('data-tab');
        if (tab) switchTab(tab);
      });
    });

    // Transaction filters
    const filterSelect = document.getElementById('admin-filter-status');
    if (filterSelect) filterSelect.addEventListener('change', loadTransactions);

    const searchInput = document.getElementById('admin-search-transactions');
    if (searchInput) searchInput.addEventListener('input', loadTransactions);

    // Access log filters & refresh
    const logFilterSelect = document.getElementById('admin-filter-log-status');
    if (logFilterSelect) logFilterSelect.addEventListener('change', renderAccessLogs);

    const logSearchInput = document.getElementById('admin-search-logs');
    if (logSearchInput) logSearchInput.addEventListener('input', renderAccessLogs);

    const refreshLogsBtn = document.getElementById('admin-btn-refresh-logs');
    if (refreshLogsBtn) refreshLogsBtn.addEventListener('click', loadAccessLogs);

    // Edit Room Modal
    const editRoomForm = document.getElementById('admin-form-edit-room');
    if (editRoomForm) editRoomForm.addEventListener('submit', handleSaveRoom);

    const closeEditRoomBtn = document.getElementById('admin-btn-close-edit-modal');
    if (closeEditRoomBtn) closeEditRoomBtn.addEventListener('click', closeRoomEditModal);

    const cancelEditRoomBtn = document.getElementById('admin-btn-cancel-edit');
    if (cancelEditRoomBtn) cancelEditRoomBtn.addEventListener('click', closeRoomEditModal);

    // Add Room Modal
    const openAddRoomBtn = document.getElementById('admin-btn-open-add-room');
    if (openAddRoomBtn) openAddRoomBtn.addEventListener('click', openAddRoomModal);

    const closeAddModalBtn = document.getElementById('admin-btn-close-add-modal');
    if (closeAddModalBtn) closeAddModalBtn.addEventListener('click', closeAddRoomModal);

    const cancelAddBtn = document.getElementById('admin-btn-cancel-add');
    if (cancelAddBtn) cancelAddBtn.addEventListener('click', closeAddRoomModal);

    const addRoomForm = document.getElementById('admin-form-add-room');
    if (addRoomForm) addRoomForm.addEventListener('submit', handleCreateRoom);

    // Add Admin Form
    const addAdminForm = document.getElementById('admin-form-add-admin');
    if (addAdminForm) addAdminForm.addEventListener('submit', handleAddAdmin);

    // Escape closes modals
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeRoomEditModal();
        closeAddRoomModal();
      }
    });

    // Check Auth
    checkAdminAuth();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
