<?php

namespace App\Policies;

use App\Models\SeoLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeoLogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $user->hasRole('admin') || 
               $project->user_id === $user->id || 
               ($user->hasRole('seo provider') && $project->seoLogs()->where('user_id', $user->id)->exists());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SeoLog $seoLog): bool
    {
        return $user->hasRole('admin') || 
               $seoLog->project->user_id === $user->id || 
               ($user->hasRole('seo provider') && $seoLog->user_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->hasRole('admin') || 
               $user->hasRole('seo provider');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SeoLog $seoLog): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('seo provider') && $seoLog->user_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SeoLog $seoLog): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('seo provider') && $seoLog->user_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SeoLog $seoLog): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SeoLog $seoLog): bool
    {
        return $user->hasRole('admin');
    }
} 