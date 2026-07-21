<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(1);
$request = \Illuminate\Http\Request::create('/users/1', 'POST', [
    '_method' => 'PUT',
    'name' => 'Administrator',
    'email' => 'admin@pmtracker.test',
    'status' => 'Active',
    'employee_id' => 'EMP-001'
]);
$request->setUserResolver(function() use ($user) { return $user; });

$response = app()->handle($request);
echo 'STATUS: ' . $response->getStatusCode() . "\n";
echo 'CONTENT: ' . $response->getContent() . "\n";
