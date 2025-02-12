<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'overview',
        'sections',
        'included_logs',
        'file_path',
        'generated_at',
    ];

    protected $casts = [
        'sections' => 'array',
        'included_logs' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the report.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that generated the report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 