<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\SeoLog;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Traits\WithPermissions;

class SeoLogManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithPermissions;

    protected $admin;
    protected $seoProvider;
    protected $customer;
    protected $project;
    protected $seoLog;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up permissions and roles
        $this->setUpPermissions();
        $this->createRolesAndPermissions();

        // Create users with different roles
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->seoProvider = User::factory()->create();
        $this->seoProvider->assignRole('seo provider');

        $this->customer = User::factory()->create();
        $this->customer->assignRole('customer');

        // Create a customer record and project
        $customerRecord = Customer::factory()->create(['user_id' => $this->customer->id]);
        $this->project = Project::factory()->create([
            'customer_id' => $customerRecord->id,
            'user_id' => $this->customer->id
        ]);

        // Create a sample SEO log
        $this->seoLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id
        ]);

        // Configure storage for media library
        Storage::fake('public');
        config(['filesystems.disks.media' => [
            'driver' => 'local',
            'root' => storage_path('app/public/media'),
            'url' => env('APP_URL').'/storage/media',
        ]]);
    }

    public function test_seo_provider_can_create_seo_log()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->seoProvider)
            ->post(route('projects.seo-logs.store', $this->project), [
                'title' => 'Test SEO Log',
                'content' => 'Test content for SEO log',
                'type' => 'analysis',
                'meta_data' => [
                    'keywords' => ['test', 'seo'],
                    'rankings' => ['google' => 1, 'bing' => 2]
                ],
                'attachments' => [$file]
            ]);

        $response->assertRedirect(route('projects.seo-logs.index', $this->project));
        
        $seoLog = SeoLog::where('title', 'Test SEO Log')->first();
        $this->assertNotNull($seoLog);
        $this->assertDatabaseHas('seo_logs', [
            'title' => 'Test SEO Log',
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id
        ]);

        $this->assertTrue($seoLog->media()->exists());
        $this->assertEquals(1, $seoLog->media()->count());
    }

    public function test_seo_provider_can_update_own_seo_log()
    {
        $seoLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id
        ]);

        $response = $this->actingAs($this->seoProvider)
            ->put(route('projects.seo-logs.update', [$this->project, $seoLog]), [
                'title' => 'Updated SEO Log',
                'content' => 'Updated content',
                'type' => 'optimization',
                'meta_data' => ['keywords' => ['updated']]
            ]);

        $response->assertRedirect(route('projects.seo-logs.index', $this->project));
        $this->assertDatabaseHas('seo_logs', [
            'id' => $seoLog->id,
            'title' => 'Updated SEO Log'
        ]);
    }

    public function test_seo_provider_cannot_update_others_seo_log()
    {
        $otherProvider = User::factory()->create();
        $otherProvider->assignRole('seo provider');

        $seoLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $otherProvider->id
        ]);

        $response = $this->actingAs($this->seoProvider)
            ->put(route('projects.seo-logs.update', [$this->project, $seoLog]), [
                'title' => 'Updated SEO Log',
                'content' => 'Updated content',
                'type' => 'optimization'
            ]);

        $response->assertStatus(403);
    }

    public function test_customer_can_view_project_seo_logs()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('projects.seo-logs.index', $this->project));

        $response->assertStatus(200)
            ->assertViewIs('seo-logs.index')
            ->assertSee($this->seoLog->title);
    }

    public function test_customer_cannot_create_seo_logs()
    {
        $response = $this->actingAs($this->customer)
            ->post(route('projects.seo-logs.store', $this->project), [
                'title' => 'Test SEO Log',
                'content' => 'Test content',
                'type' => 'analysis'
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_manage_all_seo_logs()
    {
        // Test viewing
        $response = $this->actingAs($this->admin)
            ->get(route('projects.seo-logs.index', $this->project));

        $response->assertStatus(200)
            ->assertViewIs('seo-logs.index')
            ->assertSee($this->seoLog->title);

        // Test creating
        $response = $this->actingAs($this->admin)
            ->post(route('projects.seo-logs.store', $this->project), [
                'title' => 'Admin SEO Log',
                'content' => 'Admin test content',
                'type' => 'analysis',
                'meta_data' => ['keywords' => ['admin', 'test']]
            ]);

        $response->assertRedirect(route('projects.seo-logs.index', $this->project));
        $this->assertDatabaseHas('seo_logs', [
            'title' => 'Admin SEO Log',
            'user_id' => $this->admin->id,
            'project_id' => $this->project->id
        ]);

        // Test updating any log
        $response = $this->actingAs($this->admin)
            ->put(route('projects.seo-logs.update', [$this->project, $this->seoLog]), [
                'title' => 'Updated by Admin',
                'content' => 'Updated content',
                'type' => 'optimization',
                'meta_data' => ['keywords' => ['updated']]
            ]);

        $response->assertRedirect(route('projects.seo-logs.index', $this->project));
        $this->assertDatabaseHas('seo_logs', [
            'id' => $this->seoLog->id,
            'title' => 'Updated by Admin'
        ]);
    }

    public function test_seo_log_validation()
    {
        $response = $this->actingAs($this->seoProvider)
            ->post(route('projects.seo-logs.store', $this->project), [
                'title' => '',
                'content' => '',
                'type' => 'invalid_type'
            ]);

        $response->assertSessionHasErrors(['title', 'content', 'type']);
    }

    public function test_seo_log_with_attachments()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->seoProvider)
            ->post(route('projects.seo-logs.store', $this->project), [
                'title' => 'Test with Attachment',
                'content' => 'Test content',
                'type' => 'analysis',
                'attachments' => [$file]
            ]);

        $response->assertRedirect(route('projects.seo-logs.index', $this->project));
        
        $seoLog = SeoLog::where('title', 'Test with Attachment')->first();
        $this->assertNotNull($seoLog);
        $this->assertTrue($seoLog->media()->exists());
        $this->assertEquals(1, $seoLog->media()->count());
        $this->assertEquals('document.pdf', $seoLog->media->first()->file_name);
    }

    public function test_deleting_seo_log_with_attachments()
    {
        $seoLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $seoLog->addMedia($file)->toMediaCollection('attachments');

        $this->assertTrue($seoLog->media()->exists());
        $this->assertEquals(1, $seoLog->media()->count());

        $response = $this->actingAs($this->seoProvider)
            ->delete(route('projects.seo-logs.destroy', [$this->project, $seoLog]));

        $response->assertRedirect(route('projects.seo-logs.index', $this->project));
        $this->assertSoftDeleted($seoLog);
        $this->assertFalse($seoLog->fresh()->media()->exists());
    }
} 