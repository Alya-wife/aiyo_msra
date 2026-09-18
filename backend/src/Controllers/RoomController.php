<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Router.php';
require_once __DIR__ . '/../Services/SlotLockService.php';

class RoomController
{
    private PDO $db;
    private SlotLockService $slotLockService;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->slotLockService = new SlotLockService();
    }

    public function index(): void
    {
        $this->slotLockService->releaseExpiredHolds();

        $stmt = $this->db->query("SELECT * FROM rooms ORDER BY id ASC");
        $rooms = $stmt->fetchAll();

        $unitInventory = [
            'room-cowork-03' => ['total' => 3, 'available' => 2, 'unit_name' => 'Pod'],
            'room-podcast-02' => ['total' => 2, 'available' => 1, 'unit_name' => 'Studio'],
            'room-vip-01' => ['total' => 2, 'available' => 1, 'unit_name' => 'Ruang'],
            'room-workshop-04' => ['total' => 1, 'available' => 1, 'unit_name' => 'Hub'],
        ];

        foreach ($rooms as &$room) {
            $room['facilities'] = json_decode($room['facilities'], true) ?: [];
            $room['capacity'] = (int) $room['capacity'];
            $room['pricePerHour'] = (int) $room['price_per_hour'];
            $room['doorNumber'] = $room['door_number'];
            $inv = $unitInventory[$room['id']] ?? ['total' => 2, 'available' => 1, 'unit_name' => 'Unit'];
            $room['totalUnits'] = $inv['total'];
            $room['availableUnits'] = $inv['available'];
            $room['unitName'] = $inv['unit_name'];
        }

        Router::json(['success' => true, 'rooms' => $rooms]);
    }

    public function availability(array $params): void
    {
        $roomId = $params['id'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d');

        $this->slotLockService->releaseExpiredHolds();

        $stmt = $this->db->prepare("
            SELECT id, booking_date, start_time, end_time, duration_hours, status
            FROM bookings 
            WHERE room_id = :room_id 
              AND booking_date = :date
              AND status IN ('paid', 'pending_payment')
            ORDER BY start_time ASC
        ");
        $stmt->execute(['room_id' => $roomId, 'date' => $date]);
        $slots = $stmt->fetchAll();

        Router::json([
            'success' => true,
            'room_id' => $roomId,
            'date' => $date,
            'booked_slots' => $slots,
        ]);
    }

    public function qrStickers(): void
    {
        $stmt = $this->db->query("SELECT id, code, name, type, floor, door_number, static_door_token FROM rooms ORDER BY id ASC");
        $rooms = $stmt->fetchAll();

        Router::json([
            'success' => true,
            'stickers' => $rooms,
        ]);
    }

    public function create(): void
    {
        $data = Router::getJsonBody();
        $id = $data['id'] ?? ('room-' . bin2hex(random_bytes(4)));
        $code = $data['code'] ?? strtoupper('RM-' . substr($id, -4));
        $name = $data['name'] ?? 'Ruangan Baru';
        $type = $data['type'] ?? 'VIP Meeting Room';
        $capacity = (int) ($data['capacity'] ?? 4);
        $pricePerHour = (int) ($data['pricePerHour'] ?? ($data['price_per_hour'] ?? 150000));
        $facilities = json_encode($data['facilities'] ?? ['Wi-Fi 6 Gigabit', 'Smart Door Lock']);
        $image = $data['image'] ?? 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80';
        $doorNumber = $data['doorNumber'] ?? ($data['door_number'] ?? 'DOOR-99');
        $floor = $data['floor'] ?? 'Lantai 1';
        $staticDoorToken = $data['static_door_token'] ?? ("SPK-DOOR:{$code}:" . bin2hex(random_bytes(8)));
        $status = $data['status'] ?? 'available';

        $stmt = $this->db->prepare("
            INSERT INTO rooms (id, code, name, type, capacity, price_per_hour, facilities, image, door_number, floor, static_door_token, status)
            VALUES (:id, :code, :name, :type, :capacity, :price_per_hour, :facilities, :image, :door_number, :floor, :static_door_token, :status)
        ");
        $stmt->execute([
            'id' => $id,
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'capacity' => $capacity,
            'price_per_hour' => $pricePerHour,
            'facilities' => $facilities,
            'image' => $image,
            'door_number' => $doorNumber,
            'floor' => $floor,
            'static_door_token' => $staticDoorToken,
            'status' => $status,
        ]);

        Router::json(['success' => true, 'message' => 'Room created successfully in database.', 'room_id' => $id]);
    }

    public function update(array $params): void
    {
        $id = $params['id'] ?? '';
        $data = Router::getJsonBody();

        $stmt = $this->db->prepare("
            UPDATE rooms SET
                name = COALESCE(:name, name),
                type = COALESCE(:type, type),
                capacity = COALESCE(:capacity, capacity),
                price_per_hour = COALESCE(:price_per_hour, price_per_hour),
                facilities = COALESCE(:facilities, facilities),
                image = COALESCE(:image, image),
                door_number = COALESCE(:door_number, door_number),
                floor = COALESCE(:floor, floor),
                status = COALESCE(:status, status)
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'] ?? null,
            'type' => $data['type'] ?? null,
            'capacity' => isset($data['capacity']) ? (int) $data['capacity'] : null,
            'price_per_hour' => isset($data['pricePerHour']) ? (int) $data['pricePerHour'] : (isset($data['price_per_hour']) ? (int) $data['price_per_hour'] : null),
            'facilities' => isset($data['facilities']) ? (is_array($data['facilities']) ? json_encode($data['facilities']) : $data['facilities']) : null,
            'image' => $data['image'] ?? null,
            'door_number' => $data['doorNumber'] ?? ($data['door_number'] ?? null),
            'floor' => $data['floor'] ?? null,
            'status' => $data['status'] ?? null,
        ]);

        Router::json(['success' => true, 'message' => 'Room updated successfully in database.']);
    }

    public function delete(array $params): void
    {
        $id = $params['id'] ?? '';
        $stmt = $this->db->prepare("DELETE FROM rooms WHERE id = :id");
        $stmt->execute(['id' => $id]);
        Router::json(['success' => true, 'message' => 'Room deleted successfully from database.']);
    }

    public function resetData(): void
    {
        require_once __DIR__ . '/../../database/seed.php';
        Router::json(['success' => true, 'message' => 'Demo data reset successfully.']);
    }
}
