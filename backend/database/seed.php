<?php

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();

    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $schemaSql = file_get_contents(__DIR__ . '/smart_room_access_mysql.sql');
    } else {
        $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    }
    try {
        $db->exec($schemaSql);
    } catch (Throwable $e) {
        // Table may already exist
    }

    // Seed initial rooms if empty
    $stmt = $db->query("SELECT COUNT(*) FROM rooms");
    $count = (int) $stmt->fetchColumn();

    if ($count === 0) {
        $rooms = [
            [
                'id' => 'room-vip-01',
                'code' => 'ROOM-301',
                'name' => 'Executive Boardroom Alpha',
                'type' => 'VIP Meeting Room',
                'capacity' => 12,
                'price_per_hour' => 250000,
                'facilities' => json_encode([
                    'Dual 75" 4K Smart Display',
                    '4K AI Auto-framing Conference Bar',
                    'Glass Whiteboard & Markers',
                    'Soundproof Acoustic Wall 45dB',
                    'Nespresso Bar & Mineral Water',
                    'High-Speed Wi-Fi 6 (1 Gbps)'
                ]),
                'image' => 'https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=1200&q=80',
                'door_number' => 'Room 301',
                'floor' => '3rd Floor - West Wing',
                'static_door_token' => 'SPK-DOOR:ROOM-301:sec_token_vip_01',
                'status' => 'available'
            ],
            [
                'id' => 'room-podcast-02',
                'code' => 'ROOM-204',
                'name' => 'Acoustic Studio Master',
                'type' => 'Podcast Studio',
                'capacity' => 4,
                'price_per_hour' => 175000,
                'facilities' => json_encode([
                    '4x Shure SM7B Broadcast Mics',
                    'Rodecaster Pro II Audio Mixer',
                    'Sony FX3 4K Multi-Camera Setup',
                    'Custom RGB Mood Lighting',
                    'Sound Absorbing Baffles',
                    'Pre-installed Logic & Premiere'
                ]),
                'image' => 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=1200&q=80',
                'door_number' => 'Studio 204',
                'floor' => '2nd Floor - Creative Wing',
                'static_door_token' => 'SPK-DOOR:ROOM-204:sec_token_podcast_02',
                'status' => 'available'
            ],
            [
                'id' => 'room-cowork-03',
                'code' => 'ROOM-102',
                'name' => 'Silent Focus Pod Solo #A',
                'type' => 'Coworking Pod',
                'capacity' => 1,
                'price_per_hour' => 45000,
                'facilities' => json_encode([
                    'Ergonomic Herman Miller Chair',
                    'Motorized Standing Desk',
                    'UltraWide 34" Curved Monitor',
                    'USB-C 90W Power Delivery',
                    'Active Airflow Ventilation System',
                    'Zero-Distraction Acoustic Glass'
                ]),
                'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
                'door_number' => 'Pod 102',
                'floor' => '1st Floor - Commons',
                'static_door_token' => 'SPK-DOOR:ROOM-102:sec_token_cowork_03',
                'status' => 'available'
            ],
            [
                'id' => 'room-workshop-04',
                'code' => 'ROOM-G05',
                'name' => 'Innovation Sandbox & Workshop',
                'type' => 'Workshop Space',
                'capacity' => 25,
                'price_per_hour' => 450000,
                'facilities' => json_encode([
                    'Dual Ceiling High-Lumen 4K Projectors',
                    'Modular Castor Desks & Chairs',
                    'Microphone Wireless Handheld & Clip-on',
                    'Dedicated Barista Counter',
                    'Gigabit Ethernet Ports per Desk',
                    'Private Restroom & Breakout Lounge'
                ]),
                'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80',
                'door_number' => 'Hall G-05',
                'floor' => 'Ground Floor - Main Atrium',
                'static_door_token' => 'SPK-DOOR:ROOM-G05:sec_token_workshop_04',
                'status' => 'available'
            ],
        ];

        $insertSql = "INSERT INTO rooms (id, code, name, type, capacity, price_per_hour, facilities, image, door_number, floor, static_door_token, status) 
                      VALUES (:id, :code, :name, :type, :capacity, :price_per_hour, :facilities, :image, :door_number, :floor, :static_door_token, :status)";
        $stmtInsert = $db->prepare($insertSql);

        foreach ($rooms as $room) {
            $stmtInsert->execute($room);
        }

        echo "Seeded " . count($rooms) . " rooms successfully.\n";
    } else {
        echo "Database already contains {$count} rooms.\n";
    }

    echo "Schema and seeding completed successfully.\n";
} catch (Exception $e) {
    echo "Error seeding database: " . $e->getMessage() . "\n";
    exit(1);
}
