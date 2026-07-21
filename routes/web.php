<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('home');
    });

    Route::get('/home', [\App\Http\Controllers\DashboardController::class, 'index'])->name('home');

    // Roles
    Route::get('roles/data', [\App\Http\Controllers\RoleController::class, 'data'])->name('roles.data');
    Route::resource('roles', \App\Http\Controllers\RoleController::class);

    // Users
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::resource('users', UserController::class);

    // Workload
    Route::get('workload', [\App\Http\Controllers\WorkloadController::class, 'index'])->name('workload.index');

    // Projects
    Route::get('projects/data', [\App\Http\Controllers\ProjectController::class, 'data'])->name('projects.data');
    Route::resource('projects', \App\Http\Controllers\ProjectController::class);

    // Notifications
    Route::get('notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');

    // Tasks
    Route::get('tasks/data', [\App\Http\Controllers\TaskController::class, 'data'])->name('tasks.data');
    Route::get('tasks/kanban', [\App\Http\Controllers\TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::get('tasks/calendar', [\App\Http\Controllers\TaskController::class, 'calendar'])->name('tasks.calendar');
    Route::get('tasks/calendar-data', [\App\Http\Controllers\TaskController::class, 'calendarData'])->name('tasks.calendar_data');
    Route::get('tasks/gantt', [\App\Http\Controllers\TaskController::class, 'gantt'])->name('tasks.gantt');
    Route::get('tasks/gantt-data', [\App\Http\Controllers\TaskController::class, 'ganttData'])->name('tasks.gantt_data');
    Route::get('tasks/export', [\App\Http\Controllers\TaskController::class, 'export'])->name('tasks.export');
    Route::post('tasks/update-status', [\App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.update_status');
    Route::post('tasks/update-dates', [\App\Http\Controllers\TaskController::class, 'updateDates'])->name('tasks.update_dates');
    Route::resource('tasks', \App\Http\Controllers\TaskController::class);
    
    // Task Comments & Attachments
    Route::post('tasks/{task}/comments', [\App\Http\Controllers\TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::delete('tasks/{task}/comments/{comment}', [\App\Http\Controllers\TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');
    
    Route::post('tasks/{task}/attachments', [\App\Http\Controllers\TaskAttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::get('tasks/{task}/attachments/{attachment}', [\App\Http\Controllers\TaskAttachmentController::class, 'show'])->name('tasks.attachments.show');
    Route::delete('tasks/{task}/attachments/{attachment}', [\App\Http\Controllers\TaskAttachmentController::class, 'destroy'])->name('tasks.attachments.destroy');

    // Timesheets
    Route::post('tasks/{task}/timesheets', [\App\Http\Controllers\TimesheetController::class, 'store'])->name('tasks.timesheets.store');
    Route::delete('tasks/{task}/timesheets/{timesheet}', [\App\Http\Controllers\TimesheetController::class, 'destroy'])->name('tasks.timesheets.destroy');

    // Task Progress
    Route::post('tasks/progress', [\App\Http\Controllers\TaskProgressController::class, 'store'])->name('tasks.progress.store');
    Route::get('tasks/{task}/progress', [\App\Http\Controllers\TaskProgressController::class, 'history'])->name('tasks.progress.history');
});


