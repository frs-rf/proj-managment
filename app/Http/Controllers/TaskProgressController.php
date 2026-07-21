<?php

namespace App\Http\Controllers;

use App\Models\TaskProgress;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTaskProgressRequest;

class TaskProgressController extends Controller
{
    public function store(StoreTaskProgressRequest $request)
    {
        $validated = $request->validated();
        $validated['reported_by'] = auth()->id();

        $progress = TaskProgress::create($validated);

        // Update task status if 100%
        if ($progress->progress_percent >= 100) {
            $task = Task::find($validated['task_id']);
            if ($task && $task->status !== 'Done') {
                $task->update(['status' => 'Review']); // Need review before Done
            }
        } elseif ($progress->progress_percent > 0) {
            $task = Task::find($validated['task_id']);
            if ($task && $task->status === 'To Do') {
                $task->update(['status' => 'In Progress']);
            }
        }

        return response()->json(['success' => true, 'message' => 'Progress reported successfully', 'data' => $progress]);
    }

    public function history(Task $task)
    {
        $this->authorize('progress.view'); // Assuming team members and PMs can view
        $history = $task->progress()->with('reporter')->orderBy('report_date', 'desc')->get();
        return response()->json(['success' => true, 'data' => $history]);
    }
}
