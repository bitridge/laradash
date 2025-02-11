<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
            'manage roles',
            'manage users',
            'view seo logs',
            'create seo logs',
            'edit seo logs',
            'delete seo logs'
        ];

        // Create roles
        $roles = [
            'admin' => $permissions,
            'customer' => [
                'view customers',
                'view projects',
                'create projects',
                'edit projects',
                'delete projects',
                'view seo logs'
            ],
            'seo provider' => [
                'view projects',
                'view seo logs',
                'create seo logs',
                'edit seo logs',
                'delete seo logs'
            ]
        ];

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions first
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName);
            $role->givePermissionTo($rolePermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove all roles and permissions
        Role::whereIn('name', ['admin', 'customer', 'seo provider'])->delete();
        Permission::whereIn('name', [
            'view customers', 'create customers', 'edit customers', 'delete customers',
            'view projects', 'create projects', 'edit projects', 'delete projects',
            'view seo logs', 'create seo logs', 'edit seo logs', 'delete seo logs', 'generate reports',
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
        ])->delete();
    }
}; 