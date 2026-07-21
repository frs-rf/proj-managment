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

$request = \App\Http\Requests\StoreTaskRequest::create('/tasks', 'POST', [
    'project_id' => $project->id,
    'name' => 'Advanced Task Test',
    'description' => 'This is an advanced task created by a script',
    'status' => 'To Do',
    'priority' => 'Urgent',
    'task_type' => 'Bug Fix',
    'module' => 'Authentication',
    'estimated_hours' => 5.5,
    'end_date' => '2026-07-25',
]);

$request->setUserResolver(function() use ($user) { return $user; });

try {
    app()->instance('request', $request);
    $controller = new \App\Http\Controllers\TaskController();
    
    // Manually run validation
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $request->rules());
    $request->withValidator($validator);
    if ($validator->fails()) {
        echo "VALIDATION FAILED:\n";
        print_r($validator->errors()->toArray());
        exit;
    }
    
    $request->setValidator($validator);
    
    $response = $controller->store($request);
    echo "SUCCESS: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
