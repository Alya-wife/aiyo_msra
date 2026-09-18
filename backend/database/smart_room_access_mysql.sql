-- ==========================================================
-- Database SQL Export for phpMyAdmin / MySQL / MariaDB
-- Database Name: smart_room_access
-- Project: SpaceKey - Commercial Smart Room Access & Booking
-- Generated: 2026-09-11
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- --------------------------------------------------------
-- 1. Buat & Gunakan Database
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `smart_room_access`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smart_room_access`;

-- --------------------------------------------------------
-- 2. Hapus Tabel Lama Jika Ada (Clean State)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `access_logs`;
DROP TABLE IF EXISTS `door_unlock_queue`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------
-- 2.1 Tabel `users` (Google Auth & Profile)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(50) NOT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`),
  UNIQUE KEY `uniq_users_google_id` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- 3. Struktur Tabel: `rooms` (Katalog Ruangan)
-- --------------------------------------------------------
CREATE TABLE `rooms` (
  `id` VARCHAR(50) NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `capacity` INT(11) NOT NULL DEFAULT 4,
  `price_per_hour` INT(11) NOT NULL,
  `facilities` TEXT NOT NULL,
  `image` TEXT NOT NULL,
  `door_number` VARCHAR(20) NOT NULL,
  `floor` VARCHAR(50) NOT NULL,
  `static_door_token` VARCHAR(64) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'available',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_rooms_code` (`code`),
  UNIQUE KEY `idx_rooms_token` (`static_door_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Struktur Tabel: `bookings` (Transaksi Reservasi & Pass)
-- --------------------------------------------------------
CREATE TABLE `bookings` (
  `id` VARCHAR(50) NOT NULL,
  `room_id` VARCHAR(50) NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(50) NOT NULL,
  `customer_email` VARCHAR(100) NOT NULL,
  `booking_date` DATE NOT NULL,
  `start_time` VARCHAR(10) NOT NULL,
  `duration_hours` INT(11) NOT NULL DEFAULT 1,
  `end_time` VARCHAR(10) NOT NULL,
  `start_timestamp` BIGINT(20) NOT NULL,
  `end_timestamp` BIGINT(20) NOT NULL,
  `base_price` INT(11) NOT NULL DEFAULT 0,
  `tax` INT(11) NOT NULL DEFAULT 0,
  `grand_total` INT(11) NOT NULL DEFAULT 0,
  `status` VARCHAR(30) NOT NULL DEFAULT 'pending_payment',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `lock_expires_at` BIGINT(20) NOT NULL,
  `access_pass_token` VARCHAR(64) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bookings_room` (`room_id`),
  KEY `idx_bookings_date` (`booking_date`),
  KEY `idx_bookings_status` (`status`),
  KEY `idx_bookings_token` (`access_pass_token`),
  CONSTRAINT `fk_bookings_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Struktur Tabel: `door_unlock_queue` (Antrean Solenoid IoT)
-- --------------------------------------------------------
CREATE TABLE `door_unlock_queue` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `room_id` VARCHAR(50) NOT NULL,
  `booking_id` VARCHAR(50) NOT NULL,
  `unlock_duration` INT(11) NOT NULL DEFAULT 5,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `created_at` BIGINT(20) NOT NULL,
  `expires_at` BIGINT(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_queue_room` (`room_id`),
  KEY `idx_queue_booking` (`booking_id`),
  KEY `idx_queue_status` (`status`),
  CONSTRAINT `fk_queue_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_queue_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Struktur Tabel: `access_logs` (Audit Log Akses Pintu)
-- --------------------------------------------------------
CREATE TABLE `access_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `booking_id` VARCHAR(50) DEFAULT NULL,
  `room_id` VARCHAR(50) NOT NULL,
  `scanned_qr` TEXT NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `reason` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_room` (`room_id`),
  KEY `idx_logs_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Data Awal: Katalog Ruangan (`rooms`)
-- --------------------------------------------------------
INSERT INTO `rooms` (`id`, `code`, `name`, `type`, `capacity`, `price_per_hour`, `facilities`, `image`, `door_number`, `floor`, `static_door_token`, `status`, `created_at`) VALUES
('room-vip-01', 'ROOM-301', 'Executive Boardroom Alpha', 'VIP Meeting Room', 12, 250000, 
 '[\"Dual 75\\\" 4K Smart Display\",\"4K AI Auto-framing Conference Bar\",\"Glass Whiteboard & Markers\",\"Soundproof Acoustic Wall 45dB\",\"Nespresso Bar & Mineral Water\",\"High-Speed Wi-Fi 6 (1 Gbps)\"]', 
 'https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=1200&q=80', 
 'Room 301', '3rd Floor - West Wing', 'SPK-DOOR:ROOM-301:sec_token_vip_01', 'available', NOW()),

('room-podcast-02', 'ROOM-204', 'Acoustic Studio Master', 'Podcast Studio', 4, 175000, 
 '[\"4x Shure SM7B Broadcast Mics\",\"Rodecaster Pro II Audio Mixer\",\"Sony FX3 4K Multi-Camera Setup\",\"Custom RGB Mood Lighting\",\"Sound Absorbing Baffles\",\"Pre-installed Logic & Premiere\"]', 
 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=1200&q=80', 
 'Studio 204', '2nd Floor - Creative Wing', 'SPK-DOOR:ROOM-204:sec_token_podcast_02', 'available', NOW()),

('room-cowork-03', 'ROOM-102', 'Silent Focus Pod Solo #A', 'Coworking Pod', 1, 45000, 
 '[\"Ergonomic Herman Miller Chair\",\"Motorized Standing Desk\",\"UltraWide 34\\\" Curved Monitor\",\"USB-C 90W Power Delivery\",\"Active Airflow Ventilation System\",\"Zero-Distraction Acoustic Glass\"]', 
 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80', 
 'Pod 102', '1st Floor - Commons', 'SPK-DOOR:ROOM-102:sec_token_cowork_03', 'available', NOW()),

('room-workshop-04', 'ROOM-G05', 'Innovation Sandbox & Workshop', 'Workshop Space', 25, 450000, 
 '[\"Dual Ceiling High-Lumen 4K Projectors\",\"Modular Castor Desks & Chairs\",\"Microphone Wireless Handheld & Clip-on\",\"Dedicated Barista Counter\",\"Gigabit Ethernet Ports per Desk\",\"Private Restroom & Breakout Lounge\"]', 
 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80', 
 'Hall G-05', 'Ground Floor - Main Atrium', 'SPK-DOOR:ROOM-G05:sec_token_workshop_04', 'available', NOW());

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
