<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\Project;
use App\Models\SeoLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $seoProviderRole = Role::firstOrCreate(['name' => 'seo provider']);

        // Create permissions
        $permissions = [
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',
            'view seo logs',
            'create seo logs',
            'edit seo logs',
            'delete seo logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        $customerRole->givePermissionTo([
            'view customers',
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',
            'view seo logs'
        ]);
        $seoProviderRole->givePermissionTo([
            'view projects',
            'view seo logs',
            'create seo logs',
            'edit seo logs',
            'delete seo logs'
        ]);

        // Create Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('admin');

        // Create SEO Providers
        $seoProviders = [];
        for ($i = 1; $i <= 3; $i++) {
            $provider = User::firstOrCreate(
                ['email' => "provider{$i}@example.com"],
                [
                    'name' => "SEO Provider {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $provider->assignRole('seo provider');
            $seoProviders[] = $provider;
        }

        // Create Customers with their Users
        for ($i = 1; $i <= 5; $i++) {
            // Create customer user
            $customerUser = User::firstOrCreate(
                ['email' => "customer{$i}@example.com"],
                [
                    'name' => "Customer {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $customerUser->assignRole('customer');

            // Create customer record
            $customer = Customer::firstOrCreate(
                ['email' => "customer{$i}@example.com"],
                [
                    'name' => "Customer {$i} Company",
                    'phone' => "+" . fake()->numerify('###########'),
                    'company_name' => fake()->company,
                    'address' => fake()->address,
                    'notes' => fake()->paragraph,
                    'user_id' => $customerUser->id,
                ]
            );

            // Create projects for each customer
            for ($j = 1; $j <= rand(2, 4); $j++) {
                $project = Project::create([
                    'name' => "Project {$j} for {$customer->company_name}",
                    'description' => fake()->paragraph,
                    'start_date' => fake()->dateTimeBetween('-6 months', 'now'),
                    'end_date' => fake()->optional(0.7)->dateTimeBetween('now', '+6 months'),
                    'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'on_hold']),
                    'customer_id' => $customer->id,
                    'user_id' => $customerUser->id,
                ]);

                // Create SEO logs for each project
                for ($k = 1; $k <= rand(3, 6); $k++) {
                    $seoLog = SeoLog::create([
                        'title' => fake()->sentence,
                        'content' => fake()->paragraphs(3, true),
                        'type' => fake()->randomElement(['analysis', 'optimization', 'report', 'other']),
                        'meta_data' => [
                            'keywords' => fake()->words(5),
                            'rankings' => [
                                'google' => fake()->numberBetween(1, 100),
                                'bing' => fake()->numberBetween(1, 100)
                            ]
                        ],
                        'project_id' => $project->id,
                        'user_id' => $seoProviders[array_rand($seoProviders)]->id,
                    ]);

                    // Simulate file attachments (if needed)
                    // $seoLog->addMediaFromUrl('https://picsum.photos/800/600')->toMediaCollection('attachments');
                }
            }
        }
    }
} 