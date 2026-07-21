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
    'parent_id' => null,
    'assigned_to' => null,
    'name' => 'Test Task from Script',
    'weight' => '10',
    'start_date' => '2026-07-21',
    'end_date' => '2026-07-25',
    'status' => 'To Do',
]);

$request->setUserResolver(function() use ($user) { return $user; });

try {
    app()->instance('request', $request);
    $controller = new \App\Http\Controllers\TaskController();
    
    // Manually run validation since we bypass the FormRequest lifecycle
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $request->rules());
    $request->withValidator($validator);
    if ($validator->fails()) {
        echo "VALIDATION FAILED:\n";
        print_r($validator->errors()->toArray());
        exit;
    }
    
    // Mock the validated() method for the controller
    $request->setValidator($validator);
    
    $response = $controller->store($request);
    echo "SUCCESS: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
