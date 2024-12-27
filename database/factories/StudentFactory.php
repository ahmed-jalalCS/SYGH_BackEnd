<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'studentUnid' => fake()->unique()->numberBetween(1000000, 9999999), // Random unique student ID
            'studentUnid' => fake()->numberBetween(1000000, 9999999), // Random unique student ID
            'isTemLeder' => fake()->boolean(10), // 10% chance of being a team leader
            'user_id' => User::factory(), // Associate with a User
            'department_id' => Department::factory(), // Associate with a Department
            'project_id' => Project::factory(), // Associate with a Project
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
