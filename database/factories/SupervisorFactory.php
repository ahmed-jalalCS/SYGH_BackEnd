<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Role;

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
            'supervisorDgree' => fake()->optional()->randomElement(['PhD', 'Master', 'Bachelor']),
            'user_id' => User::where('role_id', Role::where('name', 'Supervisor')->value('id'))->inRandomOrder()->value('id'), // Reference an existing User ID with role 'Supervisor'
            'college_id' => College::inRandomOrder()->value('id'), // Reference an existing College ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
