<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(1);
$request = \Illuminate\Http\Request::create('/users', 'POST', [
    'name' => 'New User Test',
    'email' => 'newuser2@pmtracker.test',
    'status' => 'Active',
    'employee_id' => 'EMP-003',
    'password' => 'password123'
]);
$request->setUserResolver(function() use ($user) { return $user; });
$app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $app->handle($request);
echo 'STATUS: ' . $response->getStatusCode() . "\n";
echo 'CONTENT: ' . $response->getContent() . "\n";
