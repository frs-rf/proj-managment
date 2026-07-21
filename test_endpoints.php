<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot application
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/login', 'GET')
);
echo "GET /login: " . $response->getStatusCode() . "\n";

// Authenticate as admin
$admin = \App\Models\User::where('email', 'admin@pmtracker.test')->first();

if (!$admin) {
    echo "Admin not found\n";
    exit;
}

auth()->login($admin);

// Test /users/data
$request = Illuminate\Http\Request::create('/users/data', 'GET', [], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
$request->setLaravelSession($app->make('session')->driver());
$response = $kernel->handle($request);
echo "GET /users/data (AJAX): " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() != 200) {
    echo substr($response->getContent(), 0, 500) . "\n";
} else {
    echo substr($response->getContent(), 0, 100) . "...\n";
}

// Test /projects/data
$request = Illuminate\Http\Request::create('/projects/data', 'GET', [], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
$request->setLaravelSession($app->make('session')->driver());
$response = $kernel->handle($request);
echo "GET /projects/data (AJAX): " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() != 200) {
    echo substr($response->getContent(), 0, 500) . "\n";
} else {
    echo substr($response->getContent(), 0, 100) . "...\n";
}
