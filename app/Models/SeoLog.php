<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SeoLog extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title',
        'content',
        'type',
        'meta_data',
        'project_id',
        'user_id',
    ];

    protected $casts = [
        'meta_data' => 'array'
    ];

    /**
     * Get the project that owns the SEO log.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that created the SEO log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'seo_analytics_reporting' => 'SEO Analytics & Reporting',
            'technical_seo' => 'Technical SEO',
            'on_page_seo' => 'On-Page SEO',
            'off_page_seo' => 'Off-Page SEO',
            'local_seo' => 'Local SEO',
            'content_seo' => 'Content SEO',
            default => 'Other',
        };
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk('public');
    }
} 