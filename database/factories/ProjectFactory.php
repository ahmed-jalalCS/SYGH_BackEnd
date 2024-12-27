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
            // 'title' => fake()->unique()->sentence(3), // Unique project title
            'title' => fake()->sentence(3), // Unique project title
            'description' => fake()->paragraph(), // Random project description
            'videoUrl' => fake()->optional()->url(), // Optional video URL
            'lbraryStatus' => fake()->boolean(50), // Random library status with 50% chance
            'supervisorStatus' => fake()->boolean(50), // Random supervisor status with 50% chance
            // 'projectYear' => Carbon::createFromDate(Carbon::now()->year, 1, 1)->toDateString(),// Current year for project
            'projectYear' => fake()->date("Y-m-d"),// Current year for project
            'department_id' => Department::factory(), // Associate with a Department
            'supervisor_id' => Supervisor::factory(), // Associate with a Supervisor
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
