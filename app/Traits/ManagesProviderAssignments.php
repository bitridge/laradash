<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ManagesProviderAssignments
{
    /**
     * Get all users with the SEO provider role.
     */
    protected function getAllProviders()
    {
        return User::role('seo provider')->get();
    }

    /**
     * Sync providers for a model (Customer or Project).
     */
    protected function syncProviders(Model $model, array $providerIds)
    {
        // Filter out non-provider users
        $validProviderIds = User::role('seo provider')
            ->whereIn('id', $providerIds)
            ->pluck('id');

        // For projects, we want to preserve the auto_assigned flag
        if (get_class($model) === 'App\Models\Project') {
            $existingProviders = $model->providers()
                ->wherePivot('auto_assigned', true)
                ->pluck('users.id');

            // Create an array with the auto_assigned flag preserved for existing auto-assigned providers
            $syncData = collect($validProviderIds)->mapWithKeys(function ($id) use ($existingProviders) {
                return [$id => ['auto_assigned' => $existingProviders->contains($id)]];
            })->all();

            $model->providers()->sync($syncData);
        } else {
            // For customers, just sync the providers without any additional data
            $model->providers()->sync($validProviderIds);
        }
    }

    /**
     * Get assigned provider IDs for a model.
     */
    protected function getAssignedProviderIds(Model $model)
    {
        return $model->providers()->pluck('users.id')->toArray();
    }
} 