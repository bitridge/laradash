<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use App\Models\SeoLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeoLogFactory extends Factory
{
    protected $model = SeoLog::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'type' => $this->faker->randomElement([
                'seo_analytics_reporting',
                'technical_seo',
                'on_page_seo',
                'off_page_seo',
                'local_seo',
                'content_seo'
            ]),
            'meta_data' => [
                'keywords' => $this->faker->words(5),
                'rankings' => [
                    'google' => $this->faker->numberBetween(1, 100),
                    'bing' => $this->faker->numberBetween(1, 100)
                ]
            ],
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the SEO log is an analysis.
     */
    public function analysis()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'analysis',
            ];
        });
    }

    /**
     * Indicate that the SEO log is an optimization.
     */
    public function optimization()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'optimization',
            ];
        });
    }

    /**
     * Indicate that the SEO log is a report.
     */
    public function report()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'report',
            ];
        });
    }
} 