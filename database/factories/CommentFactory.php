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
            'body' => fake()->paragraph(),
            'user_id' => User::inRandomOrder()->value('id'), // Reference an existing User ID
            'project_id' => Project::inRandomOrder()->value('id'), // Reference an existing Project ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
