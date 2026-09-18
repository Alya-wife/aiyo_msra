<?php
$res = file_get_contents('http://localhost/msra/api/door/access-logs?limit=6');
$d = json_decode($res, true);
foreach ($d['logs'] as $l) {
    echo "{$l['created_at']} | {$l['room_name']} | {$l['door_number']} | status: {$l['status']} | access_status: {$l['access_status']} | reason: {$l['reason']}\n";
}
