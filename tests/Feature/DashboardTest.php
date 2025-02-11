<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\SeoLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Feature\Traits\WithPermissions;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    public function test_admin_can_view_dashboard()
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas(['stats', 'charts']);
    }

    public function test_seo_provider_can_view_dashboard()
    {
        $seoProvider = $this->createSeoProvider();
        $response = $this->actingAs($seoProvider)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas(['stats', 'charts']);
    }

    public function test_customer_can_view_dashboard()
    {
        $customer = $this->createCustomer();
        $response = $this->actingAs($customer)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas(['stats', 'charts']);
    }

    public function test_guest_cannot_view_dashboard()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_data_is_cached()
    {
        $admin = $this->createAdmin();
        Cache::shouldReceive('remember')
            ->twice() // Once for stats, once for charts
            ->andReturn(['test' => 'data']);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_admin_sees_all_statistics()
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $seoProvider = $this->createSeoProvider();

        $project = Project::factory()->create(['user_id' => $customer->id]);
        $seoLog = SeoLog::factory()->create([
            'project_id' => $project->id,
            'user_id' => $seoProvider->id
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $stats = $response->viewData('stats');
        $this->assertEquals(1, $stats['total_customers']);
        $this->assertEquals(1, $stats['total_projects']);
        $this->assertEquals(1, $stats['total_seo_logs']);
    }

    public function test_seo_provider_sees_only_their_statistics()
    {
        $seoProvider = $this->createSeoProvider();
        $customer = $this->createCustomer();
        
        $project1 = Project::factory()->create(['user_id' => $customer->id]);
        $project2 = Project::factory()->create(['user_id' => $customer->id]);
        
        // SEO logs for the provider
        SeoLog::factory()->create([
            'project_id' => $project1->id,
            'user_id' => $seoProvider->id
        ]);
        
        // SEO logs for another provider
        SeoLog::factory()->create([
            'project_id' => $project2->id,
            'user_id' => $this->createSeoProvider()->id
        ]);

        $response = $this->actingAs($seoProvider)->get(route('dashboard'));
        $response->assertStatus(200);

        $stats = $response->viewData('stats');
        $this->assertEquals(1, $stats['total_projects']);
        $this->assertEquals(1, $stats['total_seo_logs']);
    }

    public function test_customer_sees_only_their_statistics()
    {
        $customer = $this->createCustomer();
        $otherCustomer = $this->createCustomer();
        $seoProvider = $this->createSeoProvider();
        
        // Customer's project
        $project1 = Project::factory()->create(['user_id' => $customer->id]);
        SeoLog::factory()->create([
            'project_id' => $project1->id,
            'user_id' => $seoProvider->id
        ]);
        
        // Other customer's project
        $project2 = Project::factory()->create(['user_id' => $otherCustomer->id]);
        SeoLog::factory()->create([
            'project_id' => $project2->id,
            'user_id' => $seoProvider->id
        ]);

        $response = $this->actingAs($customer)->get(route('dashboard'));
        $response->assertStatus(200);

        $stats = $response->viewData('stats');
        $this->assertEquals(1, $stats['total_projects']);
        $this->assertEquals(1, $stats['total_seo_logs']);
    }

    public function test_chart_data_format()
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $charts = $response->viewData('charts');
        $this->assertArrayHasKey('projects', $charts);
        $this->assertArrayHasKey('seo_logs', $charts);
        
        foreach (['projects', 'seo_logs'] as $chartType) {
            $this->assertArrayHasKey('labels', $charts[$chartType]);
            $this->assertArrayHasKey('data', $charts[$chartType]);
            $this->assertIsArray($charts[$chartType]['labels']);
            $this->assertIsArray($charts[$chartType]['data']);
            $this->assertCount(count($charts[$chartType]['labels']), $charts[$chartType]['data']);
        }
    }
} 