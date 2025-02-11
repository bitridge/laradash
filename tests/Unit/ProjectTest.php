<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_belongs_to_customer()
    {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(Customer::class, $project->customer);
        $this->assertEquals($customer->id, $project->customer->id);
    }

    public function test_project_belongs_to_user()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $project->user);
        $this->assertEquals($user->id, $project->user->id);
    }

    public function test_project_dates_are_carbon_instances()
    {
        $project = Project::factory()->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $project->start_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $project->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $project->updated_at);

        if ($project->end_date) {
            $this->assertInstanceOf(\Carbon\Carbon::class, $project->end_date);
        }
    }

    public function test_project_has_correct_status_label()
    {
        $project = Project::factory()->create(['status' => 'pending']);
        $this->assertEquals('Pending', $project->status_label);

        $project->status = 'in_progress';
        $this->assertEquals('In Progress', $project->status_label);

        $project->status = 'completed';
        $this->assertEquals('Completed', $project->status_label);

        $project->status = 'on_hold';
        $this->assertEquals('On Hold', $project->status_label);
    }

    public function test_project_has_correct_status_color()
    {
        $project = Project::factory()->create(['status' => 'pending']);
        $this->assertEquals('yellow', $project->status_color);

        $project->status = 'in_progress';
        $this->assertEquals('blue', $project->status_color);

        $project->status = 'completed';
        $this->assertEquals('green', $project->status_color);

        $project->status = 'on_hold';
        $this->assertEquals('red', $project->status_color);
    }

    public function test_completed_factory_state()
    {
        $project = Project::factory()->completed()->create();

        $this->assertEquals('completed', $project->status);
        $this->assertNotNull($project->end_date);
        $this->assertTrue($project->end_date->isPast());
    }

    public function test_in_progress_factory_state()
    {
        $project = Project::factory()->inProgress()->create();

        $this->assertEquals('in_progress', $project->status);
    }
} 