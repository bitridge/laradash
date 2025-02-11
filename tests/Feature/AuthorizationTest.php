<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Customer;
use Tests\Feature\Traits\WithPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase, WithPermissions;

    protected $admin;
    protected $customer;
    protected $seoProvider;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->setUpPermissions();
        $this->createRolesAndPermissions();

        // Create users with different roles
        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');

        $this->customer = User::factory()->create(['email' => 'customer@test.com']);
        $this->customer->assignRole('customer');

        $this->seoProvider = User::factory()->create(['email' => 'seo@test.com']);
        $this->seoProvider->assignRole('seo provider');

        $this->regularUser = User::factory()->create(['email' => 'user@test.com']);
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

    public function test_admin_can_access_all_areas()
    {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create();

        $response = $this->actingAs($this->admin);

        // Test customer management access
        $response->get(route('customers.index'))->assertStatus(200);
        $response->get(route('customers.create'))->assertStatus(200);
        $response->get(route('customers.edit', $customer))->assertStatus(200);

        // Test project management access
        $response->get(route('projects.index'))->assertStatus(200);
        $response->get(route('projects.create'))->assertStatus(200);
        $response->get(route('projects.edit', $project))->assertStatus(200);
    }

    public function test_customer_has_limited_access()
    {
        $otherCustomer = Customer::factory()->create();
        $ownCustomer = Customer::factory()->create(['user_id' => $this->customer->id]);
        
        $response = $this->actingAs($this->customer);

        // Should be able to view own customer details
        $response->get(route('customers.show', $ownCustomer))->assertStatus(200);

        // Should not be able to view other customer details
        $response->get(route('customers.show', $otherCustomer))->assertStatus(403);

        // Should not be able to create new customers
        $response->get(route('customers.create'))->assertStatus(403);
    }

    public function test_seo_provider_has_appropriate_access()
    {
        $project = Project::factory()->create();
        
        $response = $this->actingAs($this->seoProvider);

        // Should be able to view projects
        $response->get(route('projects.index'))->assertStatus(200);
        $response->get(route('projects.show', $project))->assertStatus(200);

        // Should not be able to create or edit projects
        $response->get(route('projects.create'))->assertStatus(403);
        $response->get(route('projects.edit', $project))->assertStatus(403);

        // Should not be able to access customer management
        $response->get(route('customers.index'))->assertStatus(403);
    }

    public function test_regular_user_cannot_access_protected_routes()
    {
        $response = $this->actingAs($this->regularUser);

        // Should not be able to access any protected routes
        $response->get(route('customers.index'))->assertStatus(403);
        $response->get(route('projects.index'))->assertStatus(403);
    }

    public function test_guest_cannot_access_protected_routes()
    {
        // Attempt to access protected routes without authentication
        $this->get(route('customers.index'))->assertRedirect(route('login'));
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    public function test_role_assignment()
    {
        $user = User::factory()->create();

        // Test initial state
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('seo provider'));

        // Test role assignment
        $user->assignRole('customer');
        $this->assertTrue($user->hasRole('customer'));
        $this->assertTrue($user->hasPermissionTo('view projects'));
        $this->assertFalse($user->hasPermissionTo('manage roles'));

        // Test role removal
        $user->removeRole('customer');
        $this->assertFalse($user->hasRole('customer'));
    }

    public function test_permission_inheritance()
    {
        // Admin should have all permissions
        foreach (Permission::all() as $permission) {
            $this->assertTrue($this->admin->hasPermissionTo($permission));
        }

        // Customer should have specific permissions
        $this->assertTrue($this->customer->hasPermissionTo('view projects'));
        $this->assertFalse($this->customer->hasPermissionTo('manage roles'));

        // SEO Provider should have specific permissions
        $this->assertTrue($this->seoProvider->hasPermissionTo('view seo logs'));
        $this->assertFalse($this->seoProvider->hasPermissionTo('manage users'));
    }

    public function test_customer_project_ownership()
    {
        $customerUser = User::factory()->create();
        $customerUser->assignRole('customer');

        $ownCustomer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $ownProject = Project::factory()->create([
            'user_id' => $customerUser->id,
            'customer_id' => $ownCustomer->id
        ]);

        $otherProject = Project::factory()->create();

        $response = $this->actingAs($customerUser);

        // Should be able to view and edit own project
        $response->get(route('projects.show', $ownProject))->assertStatus(200);
        $response->get(route('projects.edit', $ownProject))->assertStatus(200);

        // Should not be able to view or edit other projects
        $response->get(route('projects.show', $otherProject))->assertStatus(403);
        $response->get(route('projects.edit', $otherProject))->assertStatus(403);
    }
} 