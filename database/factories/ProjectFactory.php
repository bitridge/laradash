<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->optional()->dateTimeBetween('+2 months', '+6 months'),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'on_hold']),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the project is completed.
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'end_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * Indicate that the project is in progress.
     */
    public function inProgress()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'in_progress',
            ];
        });
    }
} 