<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\University>
 */
class UniversityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
            return [
                'name' => fake()->company(),
                'address' => fake()->address(),
                'image' => fake()->optional()->imageUrl(640, 480, 'education', true, 'university'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
    
    }
}
