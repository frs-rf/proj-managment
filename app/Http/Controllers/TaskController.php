<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        $users = \App\Models\User::all();
        return view('tasks.index', compact('projects', 'users'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $tasks = Task::with(['project', 'assignee'])->select('tasks.*');
            
            // if not admin or pm, maybe filter by assigned_to
            if (!auth()->user()->hasRole('administrator') && !auth()->user()->hasRole('project_manager')) {
                $tasks->where('assigned_to', auth()->id());
            }

            return DataTables::eloquent($tasks)
                ->addColumn('project_name', function (Task $task) {
                    return $task->project->name ?? '-';
                })
                ->addColumn('assignee_name', function (Task $task) {
                    return $task->assignee->name ?? 'Unassigned';
                })
                ->addColumn('action', function ($task) {
                    return '<button class="btn btn-sm btn-primary edit-task" data-id="'.$task->id.'">Edit</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();
        
        // Auto-generate task code
        $project = Project::find($data['project_id']);
        if ($project) {
            $latestTask = Task::where('project_id', $project->id)->orderBy('id', 'desc')->first();
            $nextNumber = 1;
            if ($latestTask && $latestTask->task_code) {
                $parts = explode('-', $latestTask->task_code);
                if (count($parts) > 1 && is_numeric(end($parts))) {
                    $nextNumber = intval(end($parts)) + 1;
                }
            }
            $data['task_code'] = $project->code . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        $task = Task::create($data);
        
        // Log activity
        \App\Models\TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'details' => ['new' => $task->toArray()]
        ]);

        return response()->json(['success' => true, 'message' => 'Task created successfully', 'data' => $task]);
    }

    public function show(Task $task)
    {
        $task->load(['comments.user', 'attachments.user', 'subtasks', 'parent', 'reporter', 'reviewer', 'activities.user', 'project', 'assignee', 'timesheets.user']);
        return response()->json(['success' => true, 'data' => $task]);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $oldData = $task->toArray();
        $task->update($request->validated());
        
        // Log activity
        \App\Models\TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'updated',
            'details' => [
                'old' => $oldData,
                'new' => $task->toArray()
            ]
        ]);

        return response()->json(['success' => true, 'message' => 'Task updated successfully', 'data' => $task]);
    }

    public function destroy(Task $task)
    {
        $this->authorize('task.assign'); // assuming only assigners can delete
        $task->delete();
        return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
    }

    public function kanban()
    {
        $query = Task::with(['project', 'assignee'])->whereNull('parent_id'); // Main tasks on Kanban
        
        if (!auth()->user()->hasRole('administrator') && !auth()->user()->hasRole('project_manager')) {
            $query->where('assigned_to', auth()->id());
        }
        
        $allTasks = $query->get();
        
        $tasks = [
            'To Do' => $allTasks->where('status', 'To Do'),
            'In Progress' => $allTasks->where('status', 'In Progress'),
            'Review' => $allTasks->where('status', 'Review'),
            'Done' => $allTasks->where('status', 'Done'),
        ];
        
        return view('tasks.kanban', compact('tasks'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'status' => 'required|in:To Do,In Progress,Review,Done'
        ]);

        $task = Task::findOrFail($request->task_id);
        
        if (!auth()->user()->can('task.assign') && $task->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $task->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }

    public function gantt()
    {
        return view('tasks.gantt');
    }

    public function ganttData(Request $request)
    {
        $query = Task::with('dependencies');
        
        if (!auth()->user()->hasRole('administrator') && !auth()->user()->hasRole('project_manager')) {
            $query->where('assigned_to', auth()->id());
        }
        
        $tasks = $query->get();
        
        $ganttTasks = $tasks->map(function ($task) {
            $progress = 0;
            if ($task->status === 'Done') $progress = 100;
            else if ($task->status === 'Review') $progress = 90;
            else if ($task->status === 'In Progress') $progress = 50;

            $deps = $task->dependencies->pluck('depends_on_task_id')->map(function($id) {
                return (string)$id;
            })->implode(',');

            return [
                'id' => (string)$task->id,
                'name' => ($task->task_code ? $task->task_code . ': ' : '') . $task->name,
                'start' => $task->start_date ? $task->start_date->format('Y-m-d') : ($task->created_at->format('Y-m-d')),
                'end' => $task->end_date ? $task->end_date->format('Y-m-d') : ($task->created_at->addDays(3)->format('Y-m-d')),
                'progress' => $progress,
                'dependencies' => $deps,
                'custom_class' => 'gantt-bar-' . strtolower(str_replace(' ', '-', $task->priority))
            ];
        })->values();
        
        return response()->json($ganttTasks);
    }

    public function calendar()
    {
        return view('tasks.calendar');
    }

    public function calendarData(Request $request)
    {
        $query = Task::with('project');
        
        if (!auth()->user()->hasRole('administrator') && !auth()->user()->hasRole('project_manager')) {
            $query->where('assigned_to', auth()->id());
        }
        
        $tasks = $query->get();
        
        $events = $tasks->map(function ($task) {
            $color = '#3788d8'; // default
            if ($task->priority === 'Urgent') $color = '#dc3545';
            else if ($task->priority === 'High') $color = '#fd7e14';
            else if ($task->status === 'Done') $color = '#198754';
            
            return [
                'id' => $task->id,
                'title' => ($task->task_code ? $task->task_code . ': ' : '') . $task->name,
                'start' => $task->start_date ? $task->start_date->format('Y-m-d') : ($task->end_date ? $task->end_date->format('Y-m-d') : null),
                'end' => $task->end_date ? $task->end_date->addDay()->format('Y-m-d') : null, // Fullcalendar end dates are exclusive
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'project' => $task->project ? $task->project->name : 'N/A'
                ]
            ];
        })->filter(function($event) {
            return $event['start'] !== null;
        })->values();
        
        return response()->json($events);
    }

    public function updateDates(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'start_date' => 'nullable|date',
            'end_date' => 'required|date'
        ]);

        $task = Task::findOrFail($request->task_id);
        
        if (!auth()->user()->can('task.assign') && $task->assigned_to !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $task->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        return response()->json(['success' => true, 'message' => 'Dates updated']);
    }

    public function export(Request $request)
    {
        $query = Task::with(['project', 'assignee']);
        
        if (!auth()->user()->hasRole('administrator') && !auth()->user()->hasRole('project_manager')) {
            $query->where('assigned_to', auth()->id());
        }
        
        $tasks = $query->get();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tasks.pdf', compact('tasks'));
        return $pdf->download('tasks_report_' . date('Y-m-d') . '.pdf');
    }
}
