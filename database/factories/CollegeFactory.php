<?php

namespace Database\Factories;

use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\College>
 */
class CollegeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'name' => fake()->unique()->word() . ' College', // Generate a unique college name
            'name' => fake()->word() . ' College', // Generate a unique college name
            'universitie_id' => University::factory(), // Associate with a University
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
