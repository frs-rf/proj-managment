<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000/tasks");
curl_setopt($ch, CURLOPT_POST, 1);

// Similar to $(this).serialize()
$data = http_build_query([
    'project_id' => '1',
    'parent_id' => '',
    'assigned_to' => '',
    'name' => 'Task via Curl',
    'weight' => '10',
    'start_date' => '2026-07-21',
    'end_date' => '2026-07-25',
    'status' => 'To Do'
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
]);

$server_output = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpcode\n";
echo "Response: $server_output\n";
