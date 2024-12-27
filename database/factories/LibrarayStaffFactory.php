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
            'isSuperAdmin' => fake()->boolean(20), // 20% chance of being a super admin
            'user_id' => User::factory(), // Associate with a User
            'college_id' => College::factory(), // Associate with a College
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
