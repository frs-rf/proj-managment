<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'pm_id',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'budget',
        'status',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function pm()
    {
        return $this->belongsTo(User::class, 'pm_id');
    }

    public function baselines()
    {
        return $this->hasMany(ProjectBaseline::class);
    }
}
