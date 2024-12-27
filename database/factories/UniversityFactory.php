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
            // 'name' => fake()->unique()->company(), // Generate a unique university name
            'name' => fake()->company(), // Generate a unique university name
            'address' => fake()->address(), // Generate a random address
            'image' => fake()->optional()->imageUrl(640, 480, 'education', true, 'university'), // Generate an optional image URL
            'created_at' => now(), // Set the current timestamp
            'updated_at' => now(), // Set the current timestamp

        ];
    }
}
