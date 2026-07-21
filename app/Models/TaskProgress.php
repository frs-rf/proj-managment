<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskProgress extends Model
{
    protected $fillable = [
        'task_id',
        'reported_by',
        'progress_percent',
        'report_date',
        'notes',
    ];

    protected $casts = [
        'progress_percent' => 'float',
        'report_date' => 'date',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
