<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evaluate>
 */
class EvaluateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rating' => fake()->numberBetween(1, 5),
            'user_id' => User::inRandomOrder()->value('id'), // Reference an existing User ID
            'project_id' => Project::inRandomOrder()->value('id'), // Reference an existing Project ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
