<?php

namespace Database\Factories;

use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'name' => fake()->unique()->word() . ' Department', // Generate a unique department name
            'name' => fake()->word() . ' Department', // Generate a unique department name
            'college_id' => College::factory(), // Associate with a College
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
