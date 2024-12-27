<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'body' => fake()->paragraph(), // Generates a random comment body (paragraph)
            'user_id' => User::factory(), // Associate with a User
            'project_id' => Project::factory(), // Associate with a Project
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
