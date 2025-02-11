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
            'view seo logs',
            'create seo logs',
            'edit seo logs',
            'delete seo logs',
            'manage users',
            'manage roles',
            'generate reports'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create admin role with all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // Create SEO provider role with specific permissions
        $seoProviderRole = Role::firstOrCreate(['name' => 'seo provider', 'guard_name' => 'web']);
        $seoProviderRole->syncPermissions([
            'view customers',
            'view projects',
            'view seo logs',
            'create seo logs',
            'edit seo logs',
            'generate reports'
        ]);

        // Create customer role with limited permissions
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $customerRole->syncPermissions([
            'view projects',
            'view seo logs',
            'generate reports'
        ]);
    }
} 