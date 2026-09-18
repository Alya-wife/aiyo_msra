<?php

date_default_timezone_set('Asia/Jakarta');

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $envFile = __DIR__ . '/../.env';
            $env = [];
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                        continue;
                    }
                    [$k, $v] = explode('=', $line, 2);
                    $key = trim($k);
                    $val = trim($v);
                    $env[$key] = $val;
                    putenv("{$key}={$val}");
                    $_ENV[$key] = $val;
                }
            }

            $driver = $env['DB_CONNECTION'] ?? 'sqlite';

            $connected = false;
            if ($driver === 'mysql') {
                try {
                    $host = $env['DB_HOST'] ?? '127.0.0.1';
                    $port = $env['DB_PORT'] ?? '3306';
                    $dbname = $env['DB_DATABASE'] ?? 'smart_room_access';
                    $user = $env['DB_USERNAME'] ?? 'root';
                    $pass = $env['DB_PASSWORD'] ?? '';

                    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                    self::$instance = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                    $connected = true;
                } catch (PDOException $e) {
                    error_log("MySQL connection failed: " . $e->getMessage() . ". Falling back to SQLite.");
                    $connected = false;
                }
            }

            if (!$connected) {
                $dbPath = $env['DB_SQLITE_PATH'] ?? (__DIR__ . '/../database/database.sqlite');
                $dir = dirname($dbPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                $dsn = "sqlite:{$dbPath}";
                self::$instance = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                // Enable foreign keys in SQLite
                self::$instance->exec('PRAGMA foreign_keys = ON;');
            }

            self::ensureUsersTable(self::$instance);
        }

        return self::$instance;
    }

    private static function ensureUsersTable(PDO $pdo): void
    {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id VARCHAR(50) PRIMARY KEY,
                google_id VARCHAR(100) UNIQUE,
                email VARCHAR(100) UNIQUE NOT NULL,
                name VARCHAR(100) NOT NULL,
                phone VARCHAR(30),
                avatar TEXT,
                role VARCHAR(20) DEFAULT 'user',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            // Check if 'role' column exists in existing table
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $cols = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
                $hasRole = false;
                foreach ($cols as $c) {
                    if (strtolower($c['name'] ?? '') === 'role') {
                        $hasRole = true;
                        break;
                    }
                }
                if (!$hasRole) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
                }
            } else {
                $check = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'role'")->fetch();
                if (!$check) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `role` VARCHAR(20) DEFAULT 'user'");
                }
            }

            // Ensure initial admin emails have role = 'admin'
            $pdo->exec("UPDATE users SET role = 'admin' WHERE email IN ('ravywhienelda@gmail.com', 'dimasrzk06@gmail.com')");
        } catch (Throwable $e) {
            error_log("Failed to ensure users table: " . $e->getMessage());
        }
    }
}

