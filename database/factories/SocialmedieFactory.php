<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Socialmedie>
 */
class SocialmedieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'linkes' => fake()->url(), // Generates a random URL for social media link
            'student_id' => Student::factory(), // Associate with a Student
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
