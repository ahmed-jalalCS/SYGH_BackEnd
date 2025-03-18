<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LibrarayStaff>
 */
class LibrarayStaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'isSuperAdmin' => fake()->boolean(20),
            'user_id' => User::where('role', 'Library Staff')->inRandomOrder()->value('id'), // Reference an existing User ID with role 'Library Staff'
            'college_id' => College::inRandomOrder()->value('id'), // Reference an existing College ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
