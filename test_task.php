<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(1);
$project = \App\Models\Project::first();
if (!$project) {
    echo "NO PROJECT FOUND\n";
    exit;
}
$request = \Illuminate\Http\Request::create('/tasks', 'POST', [
    'project_id' => $project->id,
    'name' => 'New Task Test',
    'weight' => '10',
    'start_date' => '2026-07-21',
    'end_date' => '2026-07-22',
    'status' => 'To Do',
    'assigned_to' => ''
]);
$request->setUserResolver(function() use ($user) { return $user; });
$app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $app->handle($request);
echo 'STATUS: ' . $response->getStatusCode() . "\n";
echo 'CONTENT: ' . $response->getContent() . "\n";
