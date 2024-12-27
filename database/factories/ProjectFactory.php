<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Supervisor;
use Carbon\Carbon as CarbonCarbon;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'videoUrl' => fake()->optional()->url(),
            'lbraryStatus' => fake()->boolean(50),
            'supervisorStatus' => fake()->boolean(50),
            'projectYear' => fake()->date("Y-m-d"),
            'department_id' => Department::inRandomOrder()->value('id'), // Reference an existing Department ID
            'supervisor_id' => Supervisor::inRandomOrder()->value('id'), // Reference an existing Supervisor ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
