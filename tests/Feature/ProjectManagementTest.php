<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create([
            'user_id' => $this->user->id
        ]);
    }

    public function test_user_can_view_projects_list()
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('projects.index'));

        $response->assertStatus(200)
            ->assertViewIs('projects.index')
            ->assertSee($project->name)
            ->assertSee($project->customer->name);
    }

    public function test_user_can_create_project()
    {
        $projectData = [
            'name' => 'Test Project',
            'description' => 'Test Description',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'pending',
            'customer_id' => $this->customer->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('projects.store'), $projectData);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
        ]);
    }

    public function test_user_can_view_project_details()
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('projects.show', $project));

        $response->assertStatus(200)
            ->assertViewIs('projects.show')
            ->assertSee($project->name)
            ->assertSee($project->description);
    }

    public function test_user_can_update_project()
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id
        ]);

        $updatedData = [
            'name' => 'Updated Project',
            'description' => 'Updated Description',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'in_progress',
            'customer_id' => $this->customer->id,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('projects.update', $project), $updatedData);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project',
            'status' => 'in_progress',
        ]);
    }

    public function test_user_can_delete_project()
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('projects.destroy', $project));

        $response->assertRedirect(route('projects.index'));
        $this->assertSoftDeleted($project);
    }

    public function test_user_cannot_access_other_users_projects()
    {
        $otherUser = User::factory()->create();
        $otherCustomer = Customer::factory()->create(['user_id' => $otherUser->id]);
        $project = Project::factory()->create([
            'user_id' => $otherUser->id,
            'customer_id' => $otherCustomer->id
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('projects.show', $project));

        $response->assertStatus(403);
    }

    public function test_user_cannot_create_project_for_other_users_customer()
    {
        $otherUser = User::factory()->create();
        $otherCustomer = Customer::factory()->create(['user_id' => $otherUser->id]);

        $projectData = [
            'name' => 'Test Project',
            'description' => 'Test Description',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'pending',
            'customer_id' => $otherCustomer->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('projects.store'), $projectData);

        $response->assertStatus(403);
    }

    public function test_project_requires_valid_dates()
    {
        $invalidData = [
            'name' => 'Test Project',
            'description' => 'Test Description',
            'start_date' => now()->addMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'), // End date before start date
            'status' => 'pending',
            'customer_id' => $this->customer->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('projects.store'), $invalidData);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_project_requires_valid_status()
    {
        $invalidData = [
            'name' => 'Test Project',
            'description' => 'Test Description',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'status' => 'invalid_status',
            'customer_id' => $this->customer->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('projects.store'), $invalidData);

        $response->assertSessionHasErrors('status');
    }
} 