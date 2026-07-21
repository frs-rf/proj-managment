<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timesheet;
use App\Models\Task;

class TimesheetController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'notes' => 'nullable|string'
        ]);

        $start = \Carbon\Carbon::parse($request->start_time);
        $end = \Carbon\Carbon::parse($request->end_time);
        $duration = $end->diffInMinutes($start) / 60;

        $timesheet = Timesheet::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => round($duration, 2),
            'notes' => $request->notes
        ]);

        // Update task actual hours
        $task->actual_hours = $task->timesheets()->sum('duration');
        $task->save();

        return response()->json(['success' => true, 'data' => $timesheet, 'actual_hours' => $task->actual_hours]);
    }

    public function destroy(Task $task, Timesheet $timesheet)
    {
        if ($timesheet->user_id !== auth()->id() && !auth()->user()->hasRole('administrator')) {
            abort(403);
        }
        $timesheet->delete();

        // Update task actual hours
        $task->actual_hours = $task->timesheets()->sum('duration');
        $task->save();

        return response()->json(['success' => true, 'actual_hours' => $task->actual_hours]);
    }
}
