<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectBaseline extends Model
{
    protected $fillable = [
        'project_id',
        'version_name',
        'snapshot',
        'is_active',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
