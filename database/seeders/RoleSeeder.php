<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            'manage users',
            'manage roles',
            'generate reports',
            'edit assigned projects'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        $seoProviderRole = Role::firstOrCreate(['name' => 'seo provider']);
        $seoProviderRole->syncPermissions([
            'view customers',
            'view projects',
            'view seo logs',
            'create seo logs',
            'edit seo logs',
            'delete seo logs',
            'generate reports',
            'edit assigned projects'
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $customerRole->syncPermissions([
            'view customers',
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',
            'view seo logs'
        ]);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        // Create a demo SEO provider
        $seoProvider = User::firstOrCreate(
            ['email' => 'seo@example.com'],
            [
                'name' => 'SEO Provider',
                'password' => Hash::make('password'),
            ]
        );
        $seoProvider->assignRole('seo provider');

        // Create a demo customer
        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('password'),
            ]
        );
        $customer->assignRole('customer');
    }
} 