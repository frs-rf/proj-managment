<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;

class WorkloadController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasRole('administrator') && !auth()->user()->hasRole('project_manager')) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::with(['tasks' => function($q) {
            $q->whereIn('status', ['To Do', 'In Progress', 'Review']);
        }])->get();

        $workload = $users->map(function($user) {
            $activeTasks = $user->tasks->count();
            $estimatedHours = $user->tasks->sum('weight'); // Assuming weight is used as estimated hours
            $actualHours = $user->tasks->sum('actual_hours');
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active_tasks' => $activeTasks,
                'estimated_hours' => $estimatedHours,
                'actual_hours' => $actualHours,
                // Status based on active tasks
                'status' => $activeTasks > 5 ? 'Overloaded' : ($activeTasks == 0 ? 'Idle' : 'Optimal'),
                'color' => $activeTasks > 5 ? 'danger' : ($activeTasks == 0 ? 'secondary' : 'success')
            ];
        });

        return view('workload.index', compact('workload'));
    }
}
