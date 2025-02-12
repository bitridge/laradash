<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Customer;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure roles exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $seoProviderRole = Role::firstOrCreate(['name' => 'seo provider']);

        // Find users who should be admins (you might want to adjust this logic)
        $adminEmails = ['admin@example.com']; // Add any known admin emails
        User::whereIn('email', $adminEmails)
            ->each(function ($user) use ($adminRole) {
                $user->assignRole($adminRole);
            });

        // Find users who are customers (based on relationship with Customer model)
        $customerUsers = User::whereHas('customers')
            ->each(function ($user) use ($customerRole) {
                $user->assignRole($customerRole);
            });

        // Find users who are SEO providers (based on SEO logs)
        $seoProviderUsers = User::whereHas('seoLogs')
            ->each(function ($user) use ($seoProviderRole) {
                $user->assignRole($seoProviderRole);
            });
    }

    public function down(): void
    {
        // Remove all role assignments (if needed)
        DB::table('model_has_roles')->truncate();
    }
}; 