<?php

require_once __DIR__ . '/../../config/database.php';

class AuthController
{
    private PDO $db;
    private string $googleClientId;
    private string $googleClientSecret;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();

        $envFile = __DIR__ . '/../../.env';
        $env = [];
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
                [$k, $v] = explode('=', $line, 2);
                $env[trim($k)] = trim($v);
            }
        }

        $this->googleClientId = $env['GOOGLE_CLIENT_ID'] ?? (getenv('GOOGLE_CLIENT_ID') ?: '');
        $this->googleClientSecret = $env['GOOGLE_CLIENT_SECRET'] ?? (getenv('GOOGLE_CLIENT_SECRET') ?: '');

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * Handle Google Sign-In (Credential / Token / Profile)
     * POST /api/auth/google
     */
    public function googleLogin(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $credential = $input['credential'] ?? '';
        $userData = $input['user'] ?? null;

        $googleId = '';
        $email = '';
        $name = '';
        $avatar = '';

        // 1. If Google ID token (JWT) is provided, verify or decode it
        if (!empty($credential)) {
            $verified = $this->verifyGoogleToken($credential);
            if ($verified) {
                $googleId = $verified['sub'] ?? '';
                $email = $verified['email'] ?? '';
                $name = $verified['name'] ?? '';
                $avatar = $verified['picture'] ?? '';
            } else {
                // Fallback: decode unverified JWT payload safely
                $jwtData = $this->decodeJwtPayload($credential);
                if ($jwtData) {
                    $googleId = $jwtData['sub'] ?? '';
                    $email = $jwtData['email'] ?? '';
                    $name = $jwtData['name'] ?? '';
                    $avatar = $jwtData['picture'] ?? '';
                }
            }
        }

        // 2. Fallback to client-provided user data if still empty
        if (empty($email) && !empty($userData) && is_array($userData)) {
            $googleId = $userData['google_id'] ?? $userData['sub'] ?? '';
            $email = $userData['email'] ?? '';
            $name = $userData['name'] ?? '';
            $avatar = $userData['avatar'] ?? $userData['picture'] ?? '';
        }

        if (empty($email)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email pengguna Google tidak ditemukan.'
            ]);
            return;
        }

        $email = strtolower(trim($email));
        $name = trim($name) ?: explode('@', $email)[0];

        // Check if email is in admin whitelist
        $adminWhitelist = ['ravywhienelda@gmail.com', 'dimasrzk06@gmail.com'];
        $isAdmin = in_array($email, $adminWhitelist);

        // 3. Find or Create User in database
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email OR (google_id = :gid AND google_id IS NOT NULL)");
            $stmt->execute([
                ':email' => $email,
                ':gid' => $googleId ?: 'NOT_SET'
            ]);
            $user = $stmt->fetch();

            $now = date('Y-m-d H:i:s');

            if ($user) {
                $role = ($isAdmin || ($user['role'] ?? 'user') === 'admin') ? 'admin' : ($user['role'] ?? 'user');
                // Update existing user profile
                $updateStmt = $this->db->prepare("UPDATE users SET name = :name, avatar = :avatar, role = :role, google_id = COALESCE(:gid, google_id), updated_at = :updated_at WHERE id = :id");
                $updateStmt->execute([
                    ':name' => $name,
                    ':avatar' => $avatar,
                    ':role' => $role,
                    ':gid' => $googleId ?: null,
                    ':updated_at' => $now,
                    ':id' => $user['id']
                ]);

                // Re-fetch updated record
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute([':id' => $user['id']]);
                $user = $stmt->fetch();
            } else {
                // Insert new user
                $userId = 'usr_' . bin2hex(random_bytes(6));
                $role = $isAdmin ? 'admin' : 'user';
                $insertStmt = $this->db->prepare("INSERT INTO users (id, google_id, email, name, phone, avatar, role, created_at, updated_at) VALUES (:id, :gid, :email, :name, NULL, :avatar, :role, :created_at, :updated_at)");
                $insertStmt->execute([
                    ':id' => $userId,
                    ':gid' => $googleId ?: null,
                    ':email' => $email,
                    ':name' => $name,
                    ':avatar' => $avatar,
                    ':role' => $role,
                    ':created_at' => $now,
                    ':updated_at' => $now
                ]);

                $user = [
                    'id' => $userId,
                    'google_id' => $googleId,
                    'email' => $email,
                    'name' => $name,
                    'phone' => null,
                    'avatar' => $avatar,
                    'role' => $role,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            $userRole = $user['role'] ?? ($isAdmin ? 'admin' : 'user');

            // Store in PHP Session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'google_id' => $user['google_id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'phone' => $user['phone'],
                'avatar' => $user['avatar'],
                'role' => $userRole
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Login Google berhasil.',
                'user' => [
                    'id' => $user['id'],
                    'google_id' => $user['google_id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'phone' => $user['phone'] ?? '',
                    'avatar' => $user['avatar'] ?? '',
                    'role' => $userRole
                ],
                'needs_phone' => empty($user['phone'])
            ]);

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update Phone Number for user account
     * POST /api/auth/update-phone
     */
    public function updatePhone(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $phone = trim($input['phone'] ?? '');
        $userId = trim($input['user_id'] ?? ($_SESSION['user']['id'] ?? ''));
        $email = strtolower(trim($input['email'] ?? ($_SESSION['user']['email'] ?? '')));

        if (empty($phone)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Nomor HP tidak boleh kosong.'
            ]);
            return;
        }

        // Basic phone number cleaning
        $cleanedPhone = preg_replace('/[^0-9+]/', '', $phone);
        if (str_starts_with($cleanedPhone, '62')) {
            $cleanedPhone = '0' . substr($cleanedPhone, 2);
        } elseif (str_starts_with($cleanedPhone, '+62')) {
            $cleanedPhone = '0' . substr($cleanedPhone, 3);
        }

        try {
            $user = null;

            // 1. Try finding user by ID
            if (!empty($userId)) {
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute([':id' => $userId]);
                $user = $stmt->fetch();
            }

            // 2. Try finding user by Email if not found by ID
            if (!$user && !empty($email)) {
                $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();
            }

            // 3. Try finding user from Session email if still not found
            if (!$user && isset($_SESSION['user']['email'])) {
                $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->execute([':email' => strtolower($_SESSION['user']['email'])]);
                $user = $stmt->fetch();
            }

            $now = date('Y-m-d H:i:s');

            if ($user) {
                // Update existing user record
                $upStmt = $this->db->prepare("UPDATE users SET phone = :phone, updated_at = :updated_at WHERE id = :id");
                $upStmt->execute([
                    ':phone' => $cleanedPhone,
                    ':updated_at' => $now,
                    ':id' => $user['id']
                ]);
                $targetId = $user['id'];
            } elseif (!empty($email)) {
                // Create user record if not exists
                $targetId = 'usr_' . bin2hex(random_bytes(6));
                $name = explode('@', $email)[0];
                $inStmt = $this->db->prepare("INSERT INTO users (id, email, name, phone, role, created_at, updated_at) VALUES (:id, :email, :name, :phone, 'user', :c, :u)");
                $inStmt->execute([
                    ':id' => $targetId,
                    ':email' => $email,
                    ':name' => $name,
                    ':phone' => $cleanedPhone,
                    ':c' => $now,
                    ':u' => $now
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Identitas akun (ID atau Email) tidak ditemukan.'
                ]);
                return;
            }

            // Fetch latest record from DB
            $fetchStmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $fetchStmt->execute([':id' => $targetId]);
            $updatedUser = $fetchStmt->fetch();

            // Update session
            if (!isset($_SESSION['user'])) {
                $_SESSION['user'] = [];
            }
            $_SESSION['user']['id'] = $updatedUser['id'];
            $_SESSION['user']['email'] = $updatedUser['email'];
            $_SESSION['user']['name'] = $updatedUser['name'];
            $_SESSION['user']['phone'] = $cleanedPhone;
            $_SESSION['user']['avatar'] = $updatedUser['avatar'] ?? '';
            $_SESSION['user']['role'] = $updatedUser['role'] ?? 'user';

            echo json_encode([
                'success' => true,
                'message' => 'Nomor HP berhasil disimpan.',
                'user' => [
                    'id' => $updatedUser['id'],
                    'name' => $updatedUser['name'] ?? '',
                    'email' => $updatedUser['email'],
                    'phone' => $cleanedPhone,
                    'avatar' => $updatedUser['avatar'] ?? '',
                    'role' => $updatedUser['role'] ?? 'user'
                ]
            ]);

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal memperbarui nomor HP: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Current Authenticated User Profile
     * GET /api/auth/me
     */
    public function me(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sessionUser = $_SESSION['user'] ?? null;
        if (!$sessionUser) {
            echo json_encode([
                'authenticated' => false,
                'user' => null
            ]);
            return;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $sessionUser['id']]);
            $dbUser = $stmt->fetch();

            if ($dbUser) {
                $userRole = $dbUser['role'] ?? 'user';
                $_SESSION['user'] = [
                    'id' => $dbUser['id'],
                    'google_id' => $dbUser['google_id'],
                    'email' => $dbUser['email'],
                    'name' => $dbUser['name'],
                    'phone' => $dbUser['phone'],
                    'avatar' => $dbUser['avatar'],
                    'role' => $userRole
                ];

                echo json_encode([
                    'authenticated' => true,
                    'user' => [
                        'id' => $dbUser['id'],
                        'google_id' => $dbUser['google_id'],
                        'email' => $dbUser['email'],
                        'name' => $dbUser['name'],
                        'phone' => $dbUser['phone'] ?? '',
                        'avatar' => $dbUser['avatar'] ?? '',
                        'role' => $userRole
                    ],
                    'needs_phone' => empty($dbUser['phone'])
                ]);
                return;
            }
        } catch (Throwable $e) {
            // fallback to session user
        }

        echo json_encode([
            'authenticated' => true,
            'user' => $sessionUser,
            'needs_phone' => empty($sessionUser['phone'])
        ]);
    }

    /**
     * Logout
     * POST /api/auth/logout
     */
    public function logout(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $_SESSION['user'] = null;
        unset($_SESSION['user']);

        echo json_encode([
            'success' => true,
            'message' => 'Berhasil logout.'
        ]);
    }

    /**
     * Verify Google ID token using Google tokeninfo endpoint
     */
    private function verifyGoogleToken(string $idToken): ?array
    {
        try {
            $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $payload = json_decode($response, true);
                // Verify audience matches our Client ID
                if (isset($payload['aud']) && $payload['aud'] === $this->googleClientId) {
                    return $payload;
                }
            }
        } catch (Throwable $e) {
            // If network failure or offline, return null to fallback
        }
        return null;
    }

    /**
     * Safely decode payload from JWT without external library
     */
    private function decodeJwtPayload(string $jwt): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) >= 2) {
            $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
            if ($payload) {
                $data = json_decode($payload, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        }
        return null;
    }
}
