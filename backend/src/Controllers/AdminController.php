<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Router.php';

class AdminController
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Check if request is authenticated as administrator
     */
    private function requireAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if ($user && ($user['role'] ?? '') === 'admin') {
            return true;
        }

        // Check header (case-insensitive via getallheaders if available)
        $email = '';
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach ($headers as $k => $v) {
                if (strtolower((string)$k) === 'x-admin-email') {
                    $email = trim((string)$v);
                    break;
                }
            }
        }
        if (empty($email)) {
            $email = $_SERVER['HTTP_X_ADMIN_EMAIL'] ?? ($_GET['admin_email'] ?? '');
        }

        if (!empty($email)) {
            $adminWhitelist = ['ravywhienelda@gmail.com', 'dimasrzk06@gmail.com'];
            if (in_array(strtolower($email), $adminWhitelist)) {
                return true;
            }

            try {
                $stmt = $this->db->prepare("SELECT role FROM users WHERE email = :email");
                $stmt->execute([':email' => strtolower($email)]);
                $dbUser = $stmt->fetch();
                if ($dbUser && ($dbUser['role'] ?? '') === 'admin') {
                    return true;
                }
            } catch (Throwable $e) {
                // fall through
            }
        }

        Router::json([
            'success' => false,
            'message' => 'Akses ditolak: Hanya akun dengan role administrator yang diizinkan.'
        ], 403);
        return false;
    }

    /**
     * Dashboard KPI & Overview
     * GET /api/admin/overview
     */
    public function overview(): void
    {
        if (!$this->requireAdmin()) return;

        try {
            $this->reconcileAiyoPayments();
            $today = date('Y-m-d');

            // 1. Total Omset Lunas
            $revStmt = $this->db->query("SELECT COALESCE(SUM(grand_total), 0) as total_revenue FROM bookings WHERE status = 'paid'");
            $totalRevenue = (int) ($revStmt->fetch()['total_revenue'] ?? 0);

            // 2. Count bookings by status
            $statusStmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_count,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN status = 'pending_payment' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_count
                FROM bookings
            ");
            $statusCounts = $statusStmt->fetch() ?: [];

            // 3. Active rooms today
            $activeStmt = $this->db->prepare("
                SELECT COUNT(DISTINCT room_id) as active_rooms_count
                FROM bookings
                WHERE booking_date = :today AND status = 'paid'
            ");
            $activeStmt->execute([':today' => $today]);
            $activeRoomsCount = (int) ($activeStmt->fetch()['active_rooms_count'] ?? 0);

            // 4. Total rooms in catalog
            $totalRoomsStmt = $this->db->query("SELECT COUNT(*) as total_rooms, SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_rooms FROM rooms");
            $roomsCounts = $totalRoomsStmt->fetch() ?: [];

            // 5. Recent 5 transactions
            $recentStmt = $this->db->query("
                SELECT b.id, b.customer_name, b.customer_email, b.grand_total, b.status, b.payment_method, b.created_at, b.booking_date, b.start_time, b.end_time, r.name as room_name, r.door_number
                FROM bookings b
                LEFT JOIN rooms r ON b.room_id = r.id
                ORDER BY b.created_at DESC
                LIMIT 5
            ");
            $recent = $recentStmt->fetchAll();

            Router::json([
                'success' => true,
                'data' => [
                    'total_revenue' => $totalRevenue,
                    'total_transactions' => (int) ($statusCounts['total_count'] ?? 0),
                    'paid_transactions' => (int) ($statusCounts['paid_count'] ?? 0),
                    'pending_transactions' => (int) ($statusCounts['pending_count'] ?? 0),
                    'expired_transactions' => (int) ($statusCounts['expired_count'] ?? 0),
                    'active_rooms_today' => $activeRoomsCount,
                    'total_rooms' => (int) ($roomsCounts['total_rooms'] ?? 0),
                    'available_rooms' => (int) ($roomsCounts['available_rooms'] ?? 0),
                    'recent_transactions' => $recent
                ]
            ]);
        } catch (Throwable $e) {
            Router::json(['success' => false, 'message' => 'Error fetching overview: ' . $e->getMessage()], 500);
        }
    }

    /**
     * List all transactions with search & filter
     * GET /api/admin/transactions
     */
    public function transactions(): void
    {
        if (!$this->requireAdmin()) return;

        try {
            $this->reconcileAiyoPayments();
            $status = $_GET['status'] ?? '';
            $search = $_GET['q'] ?? '';

            $sql = "
                SELECT b.*, r.name as room_name, r.door_number, r.floor
                FROM bookings b
                LEFT JOIN rooms r ON b.room_id = r.id
                WHERE 1=1
            ";
            $params = [];

            if (!empty($status) && $status !== 'all') {
                $sql .= " AND b.status = :status";
                $params[':status'] = $status;
            }

            if (!empty($search)) {
                $sql .= " AND (b.customer_name LIKE :q OR b.customer_email LIKE :q OR b.customer_phone LIKE :q OR b.id LIKE :q OR r.name LIKE :q)";
                $params[':q'] = "%{$search}%";
            }

            $sql .= " ORDER BY b.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $transactions = $stmt->fetchAll();

            Router::json([
                'success' => true,
                'total' => count($transactions),
                'data' => $transactions
            ]);
        } catch (Throwable $e) {
            Router::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Active rooms after payment (Live Sessions)
     * GET /api/admin/active-rooms
     */
    public function activeRooms(): void
    {
        if (!$this->requireAdmin()) return;

        try {
            $today = date('Y-m-d');
            $nowTime = date('H:i');

            // Find all paid bookings for today or upcoming
            $stmt = $this->db->prepare("
                SELECT 
                    b.id as booking_id,
                    b.room_id,
                    b.customer_name,
                    b.customer_phone,
                    b.customer_email,
                    b.booking_date,
                    b.start_time,
                    b.end_time,
                    b.duration_hours,
                    b.status as payment_status,
                    b.access_pass_token,
                    b.created_at,
                    r.name as room_name,
                    r.code as room_code,
                    r.door_number,
                    r.floor,
                    r.static_door_token
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                WHERE b.status = 'paid'
                  AND b.booking_date >= :today
                ORDER BY b.booking_date ASC, b.start_time ASC
            ");
            $stmt->execute([':today' => $today]);
            $activeList = $stmt->fetchAll();

            // Decorate with live access state
            $currentTimeStr = date('H:i');
            foreach ($activeList as &$item) {
                $isToday = ($item['booking_date'] === $today);
                if ($isToday) {
                    if ($currentTimeStr >= $item['start_time'] && $currentTimeStr <= $item['end_time']) {
                        $item['session_state'] = 'active_now';
                        $item['session_label'] = 'Sesi Sedang Berlangsung';
                    } elseif ($currentTimeStr < $item['start_time']) {
                        $item['session_state'] = 'upcoming_today';
                        $item['session_label'] = 'Akan Datang Hari Ini';
                    } else {
                        $item['session_state'] = 'completed_today';
                        $item['session_label'] = 'Sesi Selesai';
                    }
                } else {
                    $item['session_state'] = 'upcoming_future';
                    $item['session_label'] = 'Jadwal Masa Mendatang';
                }
            }

            Router::json([
                'success' => true,
                'count' => count($activeList),
                'data' => $activeList
            ]);
        } catch (Throwable $e) {
            Router::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * List all rooms with booking statistics
     * GET /api/admin/rooms
     */
    public function rooms(): void
    {
        if (!$this->requireAdmin()) return;

        try {
            $stmt = $this->db->query("
                SELECT r.*, 
                       (SELECT COUNT(*) FROM bookings b WHERE b.room_id = r.id AND b.status = 'paid') as total_paid_bookings,
                       (SELECT COALESCE(SUM(grand_total), 0) FROM bookings b WHERE b.room_id = r.id AND b.status = 'paid') as total_revenue_generated
                FROM rooms r
                ORDER BY r.id ASC
            ");
            $rooms = $stmt->fetchAll();

            foreach ($rooms as &$r) {
                if (!empty($r['facilities']) && is_string($r['facilities'])) {
                    $decoded = json_decode($r['facilities'], true);
                    $r['facilities_list'] = is_array($decoded) ? $decoded : [$r['facilities']];
                } else {
                    $r['facilities_list'] = [];
                }
            }

            Router::json([
                'success' => true,
                'data' => $rooms
            ]);
        } catch (Throwable $e) {
            Router::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update room details & availability status
     * PUT /api/admin/rooms/{id}
     */
    public function updateRoom(array $params): void
    {
        if (!$this->requireAdmin()) return;

        $id = $params['id'] ?? '';
        if (empty($id)) {
            Router::json(['success' => false, 'message' => 'ID ruangan tidak valid.'], 400);
            return;
        }

        $data = Router::getJsonBody();

        try {
            $stmt = $this->db->prepare("
                UPDATE rooms SET
                    name = COALESCE(:name, name),
                    type = COALESCE(:type, type),
                    capacity = COALESCE(:capacity, capacity),
                    price_per_hour = COALESCE(:price_per_hour, price_per_hour),
                    facilities = COALESCE(:facilities, facilities),
                    door_number = COALESCE(:door_number, door_number),
                    floor = COALESCE(:floor, floor),
                    status = COALESCE(:status, status)
                WHERE id = :id
            ");

            $facilities = isset($data['facilities']) 
                ? (is_array($data['facilities']) ? json_encode($data['facilities']) : $data['facilities']) 
                : null;

            $stmt->execute([
                'id' => $id,
                'name' => $data['name'] ?? null,
                'type' => $data['type'] ?? null,
                'capacity' => isset($data['capacity']) ? (int) $data['capacity'] : null,
                'price_per_hour' => isset($data['price_per_hour']) ? (int) $data['price_per_hour'] : (isset($data['pricePerHour']) ? (int) $data['pricePerHour'] : null),
                'facilities' => $facilities,
                'door_number' => $data['door_number'] ?? ($data['doorNumber'] ?? null),
                'floor' => $data['floor'] ?? null,
                'status' => $data['status'] ?? null,
            ]);

            Router::json([
                'success' => true,
                'message' => 'Data ruangan berhasil diperbarui.'
            ]);
        } catch (Throwable $e) {
            Router::json(['success' => false, 'message' => 'Gagal memperbarui ruangan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * List all administrator accounts
     * GET /api/admin/admins
     */
    public function admins(): void
    {
        if (!$this->requireAdmin()) return;

        try {
            // Ensure default admins exist in DB
            $defaults = [
                ['email' => 'ravywhienelda@gmail.com', 'name' => 'Ravy Whienelda'],
                ['email' => 'dimasrzk06@gmail.com', 'name' => 'Dimas Rizky'],
            ];
            foreach ($defaults as $def) {
                $chk = $this->db->prepare("SELECT id FROM users WHERE email = :email");
                $chk->execute([':email' => $def['email']]);
                if (!$chk->fetch()) {
                    $ins = $this->db->prepare("INSERT INTO users (id, email, name, role) VALUES (:id, :email, :name, 'admin')");
                    $ins->execute([
                        ':id' => 'usr_' . substr(md5($def['email']), 0, 8),
                        ':email' => $def['email'],
                        ':name' => $def['name']
                    ]);
                } else {
                    $upd = $this->db->prepare("UPDATE users SET role = 'admin' WHERE email = :email");
                    $upd->execute([':email' => $def['email']]);
                }
            }

            $stmt = $this->db->query("
                SELECT id, email, name, phone, avatar, role, created_at, updated_at
                FROM users
                WHERE role = 'admin'
                ORDER BY created_at ASC
            ");
            $admins = $stmt->fetchAll();

            Router::json([
                'success' => true,
                'data' => $admins
            ]);
        } catch (Throwable $e) {
            Router::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Register a new admin by Google Email
     * POST /api/admin/admins
     */
    public function addAdmin(): void
    {
        if (!$this->requireAdmin()) return;

        $data = Router::getJsonBody();
        $email = strtolower(trim($data['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Router::json(['success' => false, 'message' => 'Format email tidak valid.'], 400);
            return;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $existing = $stmt->fetch();

            $now = date('Y-m-d H:i:s');

            if ($existing) {
                $upStmt = $this->db->prepare("UPDATE users SET role = 'admin', updated_at = :u WHERE id = :id");
                $upStmt->execute([':u' => $now, ':id' => $existing['id']]);

                Router::json([
                    'success' => true,
                    'message' => "Pengguna {$existing['name']} ({$email}) berhasil diangkat menjadi Admin MSRA."
                ]);
            } else {
                $userId = 'usr_' . bin2hex(random_bytes(6));
                $name = trim($data['name'] ?? '') ?: explode('@', $email)[0];

                $inStmt = $this->db->prepare("
                    INSERT INTO users (id, email, name, role, created_at, updated_at)
                    VALUES (:id, :email, :name, 'admin', :c, :u)
                ");
                $inStmt->execute([
                    ':id' => $userId,
                    ':email' => $email,
                    ':name' => $name,
                    ':c' => $now,
                    ':u' => $now
                ]);

                Router::json([
                    'success' => true,
                    'message' => "Email {$email} telah didaftarkan sebagai Admin. Saat login Google, pengguna otomatis berstatus Admin."
                ]);
            }
        } catch (Throwable $e) {
            Router::json(['success' => false, 'message' => 'Gagal menambahkan admin: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Revoke administrator role
     * DELETE /api/admin/admins/{id}
     */
    public function removeAdmin(array $params): void
    {
        if (!$this->requireAdmin()) return;

        $target = $params['id'] ?? '';
        if (empty($target)) {
            Router::json(['success' => false, 'message' => 'ID admin tidak valid.'], 400);
            return;
        }

        $primaryAdmins = ['ravywhienelda@gmail.com', 'dimasrzk06@gmail.com'];

        try {
            $stmt = $this->db->prepare("SELECT id, email, name FROM users WHERE id = :target OR email = :target");
            $stmt->execute([':target' => $target]);
            $admin = $stmt->fetch();

            if (!$admin) {
                Router::json(['success' => false, 'message' => 'Akun admin tidak ditemukan.'], 404);
                return;
            }

            if (in_array(strtolower($admin['email']), $primaryAdmins)) {
                Router::json([
                    'success' => false,
                    'message' => "Akun {$admin['email']} adalah Admin Utama dan tidak dapat dicabut hak aksesnya."
                ], 400);
                return;
            }

            $demoteStmt = $this->db->prepare("UPDATE users SET role = 'user', updated_at = :u WHERE id = :id");
            $demoteStmt->execute([':u' => date('Y-m-d H:i:s'), ':id' => $admin['id']]);

            Router::json([
                'success' => true,
                'message' => "Hak akses Admin untuk {$admin['email']} telah dicabut."
            ]);
        } catch (Throwable $e) {
            Router::json(['success' => false, 'message' => 'Gagal mencabut admin: ' . $e->getMessage()], 500);
        }
    }

    private function reconcileAiyoPayments(): void
    {
        try {
            $this->db->query("
                UPDATE bookings b
                INNER JOIN fintek.transaksi t ON (t.remarks LIKE CONCAT('%', b.id, '%'))
                SET b.status = 'paid', b.payment_method = 'aiyo_bills', b.lock_expires_at = 0
                WHERE b.status = 'pending_payment' AND t.status IN ('PAID', 'SETTLED', 'SUCCESS')
            ");
        } catch (Throwable $e) {
            // Non-fatal if cross-database query is unavailable
        }
    }
}
