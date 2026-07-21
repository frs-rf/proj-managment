<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\EVMService;

class DashboardController extends Controller
{
    public function index(Request $request, EVMService $evmService)
    {
        $kpi = [
            'total_projects' => Project::count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'Done')->count(),
            'overdue_tasks' => Task::where('end_date', '<', now())->where('status', '!=', 'Done')->count(),
            'active_resources' => User::count(),
        ];

        // S-Curve data for a specific project
        // If a project_id is provided, use it. Otherwise, use the first active project.
        $selectedProject = null;
        $sCurveData = [];
        $health = null;
        $tasksByPriority = collect();
        $tasksByStatus = collect();
        $burndownLabels = [];
        $idealLine = [];
        $actualLine = [];

        if ($request->has('project_id')) {
            $selectedProject = Project::find($request->project_id);
        } else {
            $selectedProject = Project::where('status', '!=', 'Completed')->orderBy('created_at', 'desc')->first();
        }

        if ($selectedProject) {
            $sCurveData = $evmService->getSCurveData($selectedProject);
            $health = $evmService->getProjectHealth($selectedProject);
        }

        // --- NEW TRACKCORP DASHBOARD DATA ---
        $totalProjects = Project::count();
        $activeTasks = Task::whereIn('status', ['To Do', 'In Progress', 'Review'])->count();
        $upcomingDeadlines = Task::whereNotIn('status', ['Done'])->whereNotNull('end_date')
                                 ->where('end_date', '<=', \Carbon\Carbon::now()->addDays(7))->count();
        
        // Mock team capacity (or calculate based on active tasks per user)
        $teamCapacity = 86; // static for now to match UI

        // Task Completion Rate (This week vs Last week)
        $thisWeek = [];
        $lastWeek = [];
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        foreach (range(1, 7) as $day) {
            // Mock data to match UI shape for now
            $thisWeek[] = rand(10, 40);
            $lastWeek[] = rand(5, 30);
        }

        // Recent Activity (mocked or fetched from TaskActivity if exists)
        $recentActivities = \App\Models\TaskActivity::with('user', 'task')->latest()->take(3)->get();
        
        // Team Workload
        $teamWorkload = User::withCount(['tasks' => function($q) {
            $q->whereIn('status', ['To Do', 'In Progress']);
        }])->get()->map(function($user) {
            return [
                'name' => $user->name,
                'role' => $user->roles->first() ? $user->roles->first()->name : 'Member',
                'focus' => 'Assorted Tasks', // Can be derived from most frequent project
                'workload_pct' => min(100, $user->tasks_count * 10),
                'efficiency' => rand(80, 99) . '%',
                'status' => $user->tasks_count > 5 ? 'Overloaded' : 'Focused'
            ];
        })->take(4);

        $projects = Project::select('id', 'name')->get();

        return view('dashboard.index', compact(
            'kpi', 'selectedProject', 'sCurveData', 'health', 'projects',
            'totalProjects', 'activeTasks', 'upcomingDeadlines', 'teamCapacity',
            'days', 'thisWeek', 'lastWeek', 'recentActivities', 'teamWorkload'
        ));
    }
}
