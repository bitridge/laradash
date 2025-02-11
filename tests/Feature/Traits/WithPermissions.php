<?php

namespace Tests\Feature\Traits;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

trait WithPermissions
{
    protected function setUpPermissions(): void
    {
        // Reset cached roles and permissions
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Register middleware
        $this->app['router']->aliasMiddleware('permission', \Spatie\Permission\Middleware\PermissionMiddleware::class);
        $this->app['router']->aliasMiddleware('role', \Spatie\Permission\Middleware\RoleMiddleware::class);
        $this->app['router']->aliasMiddleware('role_or_permission', \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class);
    }

    protected function createRolesAndPermissions(): void
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

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $customerRole->syncPermissions([
            'view customers',
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',
            'view seo logs'
        ]);

        $seoProviderRole = Role::firstOrCreate(['name' => 'seo provider', 'guard_name' => 'web']);
        $seoProviderRole->syncPermissions([
            'view projects',
            'view seo logs',
            'create seo logs',
            'edit seo logs',
            'delete seo logs'
        ]);
    }
} 