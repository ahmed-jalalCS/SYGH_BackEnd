<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supervisor>
 */
class SupervisorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supervisorDgree' => fake()->optional()->randomElement(['PhD', 'Master', 'Bachelor']), // Random degree or null
            'user_id' => User::factory(), // Associate with a User
            'college_id' => College::factory(), // Associate with a College
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
