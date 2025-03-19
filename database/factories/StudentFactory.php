<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Role;

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
            'studentUnid' => fake()->unique()->numberBetween(1000000, 9999999),
            'isTemLeder' => fake()->boolean(10),
            'user_id' => User::where('role_id', Role::where('name', 'Student')->value('id'))->inRandomOrder()->value('id'), // Reference an existing User ID with role 'Student'
            'department_id' => Department::inRandomOrder()->value('id'), // Reference an existing Department ID
            'project_id' => Project::inRandomOrder()->value('id'), // Reference an existing Project ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
