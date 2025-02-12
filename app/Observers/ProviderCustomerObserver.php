<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\User;

class ProviderCustomerObserver
{
    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        $this->syncProjects($customer);
    }

    /**
     * Handle provider assignments through the pivot table.
     */
    public function syncProjects(Customer $customer): void
    {
        // Get all providers assigned to this customer
        $providers = $customer->providers;
        
        // Get all projects of this customer
        $projects = $customer->projects;

        foreach ($providers as $provider) {
            // For each project, assign it to the provider if not already assigned
            foreach ($projects as $project) {
                // Check if the provider is already assigned to this project
                $existingAssignment = $project->providers()
                    ->where('users.id', $provider->id)
                    ->first();

                // Only assign if not already assigned
                if (!$existingAssignment) {
                    $project->providers()->attach($provider->id, [
                        'auto_assigned' => true
                    ]);
                }
            }
        }
    }
} 