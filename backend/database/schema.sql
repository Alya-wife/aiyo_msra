CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    google_id VARCHAR(100) UNIQUE NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NULL,
    avatar TEXT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rooms (
    id VARCHAR(50) PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    capacity INTEGER NOT NULL DEFAULT 4,
    price_per_hour INTEGER NOT NULL,
    facilities TEXT NOT NULL,
    image TEXT NOT NULL,
    door_number VARCHAR(20) NOT NULL,
    floor VARCHAR(20) NOT NULL,
    static_door_token VARCHAR(64) UNIQUE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
    id VARCHAR(50) PRIMARY KEY,
    room_id VARCHAR(50) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    booking_date DATE NOT NULL,
    start_time VARCHAR(10) NOT NULL,
    duration_hours INTEGER NOT NULL DEFAULT 1,
    end_time VARCHAR(10) NOT NULL,
    start_timestamp BIGINT NOT NULL,
    end_timestamp BIGINT NOT NULL,
    base_price INTEGER NOT NULL DEFAULT 0,
    tax INTEGER NOT NULL DEFAULT 0,
    grand_total INTEGER NOT NULL DEFAULT 0,
    status VARCHAR(30) NOT NULL DEFAULT 'pending_payment',
    payment_method VARCHAR(50) NULL,
    lock_expires_at BIGINT NOT NULL,
    access_pass_token VARCHAR(64) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS door_unlock_queue (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id VARCHAR(50) NOT NULL,
    booking_id VARCHAR(50) NOT NULL,
    unlock_duration INTEGER NOT NULL DEFAULT 5,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at BIGINT NOT NULL,
    expires_at BIGINT NOT NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS access_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id VARCHAR(50) NULL,
    room_id VARCHAR(50) NOT NULL,
    scanned_qr TEXT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL,
    reason TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
