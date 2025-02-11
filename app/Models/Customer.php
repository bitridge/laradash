<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'address',
        'notes',
        'user_id',
        'logo_path',
    ];

    /**
     * Get the user that owns the customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the projects for the customer.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute(): string
    {
        // If we have a logo path and the file exists in storage
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return url('storage/' . $this->logo_path);
        }
        
        // Return the default logo
        return url('images/default-company-logo.svg');
    }
} 