<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        // store in local disk for now since private disk is not explicitly configured
        $path = $file->store('tasks/attachments', 'local');

        $attachment = $task->attachments()->create([
            'user_id' => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'File uploaded', 
            'data' => $attachment->load('user')
        ]);
    }

    public function show(Task $task, TaskAttachment $attachment)
    {
        if (!Storage::disk('local')->exists($attachment->file_path)) {
            abort(404);
        }
        
        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name);
    }

    public function destroy(Task $task, TaskAttachment $attachment)
    {
        if ($attachment->user_id !== auth()->id() && !auth()->user()->hasRole('administrator')) {
            abort(403);
        }

        Storage::disk('local')->delete($attachment->file_path);
        $attachment->delete();
        return response()->json(['success' => true, 'message' => 'Attachment deleted']);
    }
}
