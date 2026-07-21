<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/tasks/calendar-data', 'GET');
$user = \App\Models\User::first();
$request->setUserResolver(function() use ($user) { return $user; });

app()->instance('request', $request);
$controller = new \App\Http\Controllers\TaskController();
$response = $controller->calendarData($request);
echo "CALENDAR: " . substr($response->getContent(), 0, 200) . "...\n";

$request = \Illuminate\Http\Request::create('/tasks/gantt-data', 'GET');
$request->setUserResolver(function() use ($user) { return $user; });
app()->instance('request', $request);
$response = $controller->ganttData($request);
echo "GANTT: " . substr($response->getContent(), 0, 200) . "...\n";
