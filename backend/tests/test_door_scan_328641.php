<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/DoorAccessService.php';

$door = new DoorAccessService();
$res = $door->verifyDoorScan('MSRA-328641', 'SPK-DOOR:ROOM-550:06144c36cca7d060', 'SPK-PASS-MSRA328641');
print_r($res);
