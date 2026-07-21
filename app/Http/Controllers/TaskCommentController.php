<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Comment added', 
            'data' => $comment->load('user')
        ]);
    }

    public function destroy(Task $task, TaskComment $comment)
    {
        if ($comment->user_id !== auth()->id() && !auth()->user()->hasRole('administrator')) {
            abort(403);
        }

        $comment->delete();
        return response()->json(['success' => true, 'message' => 'Comment deleted']);
    }
}
