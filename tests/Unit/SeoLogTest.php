<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\SeoLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SeoLogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $project;
    protected $seoLog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->create();
        $this->seoLog = SeoLog::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
        ]);
    }

    public function test_seo_log_belongs_to_project()
    {
        $this->assertInstanceOf(Project::class, $this->seoLog->project);
        $this->assertEquals($this->project->id, $this->seoLog->project->id);
    }

    public function test_seo_log_belongs_to_user()
    {
        $this->assertInstanceOf(User::class, $this->seoLog->user);
        $this->assertEquals($this->user->id, $this->seoLog->user->id);
    }

    public function test_seo_log_has_correct_type_label()
    {
        $seoLog = SeoLog::factory()->create(['type' => 'analysis']);
        $this->assertEquals('Analysis', $seoLog->type_label);

        $seoLog->type = 'optimization';
        $this->assertEquals('Optimization', $seoLog->type_label);

        $seoLog->type = 'report';
        $this->assertEquals('Report', $seoLog->type_label);

        $seoLog->type = 'other';
        $this->assertEquals('Other', $seoLog->type_label);
    }

    public function test_seo_log_can_have_media_attachments()
    {
        $this->assertTrue(method_exists($this->seoLog, 'media'));
        $this->assertTrue(method_exists($this->seoLog, 'addMedia'));
    }

    public function test_seo_log_uses_soft_deletes()
    {
        $seoLog = SeoLog::factory()->create();
        $seoLog->delete();

        $this->assertSoftDeleted($seoLog);
    }

    public function test_seo_log_meta_data_is_json_castable()
    {
        $metaData = [
            'keywords' => ['seo', 'optimization'],
            'rankings' => [
                'google' => 5,
                'bing' => 3
            ]
        ];

        $seoLog = SeoLog::factory()->create(['meta_data' => $metaData]);
        $this->assertIsArray($seoLog->meta_data);
        $this->assertEquals($metaData, $seoLog->meta_data);
    }

    public function test_seo_log_has_required_fields()
    {
        $seoLog = new SeoLog();
        $fillable = $seoLog->getFillable();

        $this->assertTrue(in_array('title', $fillable));
        $this->assertTrue(in_array('content', $fillable));
        $this->assertTrue(in_array('type', $fillable));
        $this->assertTrue(in_array('meta_data', $fillable));
        $this->assertTrue(in_array('project_id', $fillable));
        $this->assertTrue(in_array('user_id', $fillable));
    }

    public function test_seo_log_timestamps_are_datetime()
    {
        $this->assertInstanceOf(\Carbon\Carbon::class, $this->seoLog->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $this->seoLog->updated_at);
    }
} 