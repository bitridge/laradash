<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\SeoLog;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ReportGenerationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $customer;
    protected $seoProvider;
    protected $project;
    protected $seoLogs;

    protected function setUp(): void
    {
        parent::setUp();
        
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

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $seoProviderRole = Role::firstOrCreate(['name' => 'seo provider', 'guard_name' => 'web']);
        $seoProviderRole->syncPermissions([
            'view customers',
            'view projects',
            'view seo logs',
            'create seo logs',
            'edit seo logs',
            'generate reports'
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $customerRole->syncPermissions([
            'view projects',
            'view seo logs',
            'generate reports'
        ]);

        // Create users with different roles
        $this->admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->admin->assignRole('admin');

        $this->customer = User::factory()->create(['email' => 'customer@example.com']);
        $this->customer->assignRole('customer');

        $this->seoProvider = User::factory()->create(['email' => 'seo@example.com']);
        $this->seoProvider->assignRole('seo provider');

        // Create a project with customer
        $customerModel = Customer::factory()->create(['user_id' => $this->customer->id]);
        $this->project = Project::factory()->create([
            'customer_id' => $customerModel->id,
            'user_id' => $this->customer->id,
            'status' => 'in_progress'
        ]);

        // Create some SEO logs
        $this->seoLogs = SeoLog::factory()->count(3)->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id,
            'type' => 'analysis'
        ]);

        // Disable CSRF token verification for tests
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_admin_can_access_report_generation_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.index'));

        $response->assertStatus(200)
            ->assertViewIs('reports.index')
            ->assertSee('Generate Report')
            ->assertSee($this->project->name);
    }

    public function test_customer_can_access_report_generation_page()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('reports.index'));

        $response->assertStatus(200)
            ->assertViewIs('reports.index')
            ->assertSee('Generate Report')
            ->assertSee($this->project->name);
    }

    public function test_seo_provider_can_access_report_generation_page()
    {
        $response = $this->actingAs($this->seoProvider)
            ->get(route('reports.index'));

        $response->assertStatus(200)
            ->assertViewIs('reports.index')
            ->assertSee('Generate Report')
            ->assertSee($this->project->name);
    }

    public function test_guest_cannot_access_report_generation_page()
    {
        $response = $this->get(route('reports.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_can_generate_pdf_report()
    {
        Storage::fake('local');

        $response = $this->actingAs($this->admin)
            ->post(route('reports.generate'), [
                'project_id' => $this->project->id,
                'seo_logs' => $this->seoLogs->pluck('id')->toArray()
            ]);

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');

        // Verify the response is actually a PDF
        $this->assertTrue(str_starts_with($response->getContent(), '%PDF-'));
    }

    public function test_cannot_generate_report_with_invalid_project()
    {
        $response = $this->actingAs($this->admin)
            ->from(route('reports.index'))
            ->post(route('reports.generate'), [
                'project_id' => 999999,
                'seo_logs' => $this->seoLogs->pluck('id')->toArray()
            ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['project_id']);
    }

    public function test_cannot_generate_report_with_invalid_seo_logs()
    {
        $response = $this->actingAs($this->admin)
            ->from(route('reports.index'))
            ->post(route('reports.generate'), [
                'project_id' => $this->project->id,
                'seo_logs' => [999999]
            ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['seo_logs.0']);
    }

    public function test_report_requires_at_least_one_seo_log()
    {
        $response = $this->actingAs($this->admin)
            ->from(route('reports.index'))
            ->post(route('reports.generate'), [
                'project_id' => $this->project->id,
                'seo_logs' => []
            ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['seo_logs']);
    }

    public function test_report_contains_expected_content()
    {
        // Create a SEO log with specific content for testing
        $testLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id,
            'title' => 'Test SEO Analysis',
            'content' => 'Specific test content for verification',
            'type' => 'analysis'
        ]);

        Storage::fake('local');

        $response = $this->actingAs($this->admin)
            ->post(route('reports.generate'), [
                'project_id' => $this->project->id,
                'seo_logs' => [$testLog->id]
            ]);

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');

        $pdfContent = $response->getContent();

        // Verify PDF structure
        $this->assertTrue(str_starts_with($pdfContent, '%PDF-')); // PDF header
        $this->assertTrue(str_contains($pdfContent, '/Type')); // PDF type information
        $this->assertTrue(str_contains($pdfContent, '/Page')); // PDF page information
        $this->assertTrue(str_contains($pdfContent, '%%EOF')); // PDF end of file marker
    }
} 