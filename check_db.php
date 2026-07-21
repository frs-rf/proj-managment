<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tasks = \App\Models\Task::all();
foreach ($tasks as $task) {
    echo "Task ID: {$task->id}, Name: {$task->name}, Weight: {$task->weight}\n";
}
echo "Total tasks: " . $tasks->count() . "\n";
