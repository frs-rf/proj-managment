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
            $tasksByPriority = Task::selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority');
            $tasksByStatus = Task::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');

            // Burn-down Data Logic
            $totalTasks = Task::count();
            
            // Simulating 5 days burn-down
            $burndownLabels = [];
            $idealLine = [];
            $actualLine = [];
            
            for ($i = 4; $i >= 0; $i--) {
                $date = \Carbon\Carbon::today()->subDays($i);
                $burndownLabels[] = $date->format('M d');
                
                // Ideal assumes linear completion
                $idealLine[] = max(0, round($totalTasks - (($totalTasks / 4) * (4 - $i))));
                
                // Actual assumes completed tasks on this date
                $completedOnDate = Task::where('status', 'Done')->whereDate('updated_at', '<=', $date)->count();
                $actualLine[] = max(0, $totalTasks - $completedOnDate);
            }
        }

        $projects = Project::select('id', 'name')->get();

        return view('dashboard.index', compact('kpi', 'selectedProject', 'sCurveData', 'health', 'projects', 'tasksByPriority', 'tasksByStatus', 'burndownLabels', 'idealLine', 'actualLine'));
    }
}
