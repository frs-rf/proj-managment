<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'task_code',
        'project_id',
        'parent_id',
        'name',
        'description',
        'acceptance_criteria',
        'assigned_to',
        'reporter_id',
        'reviewer_id',
        'watchers',
        'priority',
        'task_type',
        'module',
        'tags',
        'weight',
        'start_date',
        'end_date',
        'status',
        'estimated_hours',
        'actual_hours'
    ];

    protected $casts = [
        'weight' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
        'watchers' => 'array',
        'tags' => 'array',
        'estimated_hours' => 'float',
        'actual_hours' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function progress()
    {
        return $this->hasMany(TaskProgress::class);
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function dependentOn()
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
    }

    public function activities()
    {
        return $this->hasMany(TaskActivity::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }
}
