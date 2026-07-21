<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@pmtracker.test')->first();
$project = \App\Models\Project::first();

if (!$project) {
    echo "NO PROJECT FOUND\n";
    exit;
}

$request = \Illuminate\Http\Request::create('/tasks', 'POST', [
    'project_id' => $project->id,
    'parent_id' => '',
    'assigned_to' => '',
    'name' => 'Test Task from Script',
    'weight' => '10',
    'start_date' => '2026-07-21',
    'end_date' => '2026-07-25',
    'status' => 'To Do',
]);

$request->headers->set('Accept', 'application/json');
$request->setUserResolver(function() use ($user) { return $user; });

// Disable CSRF for testing
$app->make(\Illuminate\Contracts\Http\Kernel::class);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

try {
    $response = app()->handle($request);
    echo 'STATUS: ' . $response->getStatusCode() . "\n";
    echo 'CONTENT: ' . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . "\n";
}
