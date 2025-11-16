<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => \App\Models\Student::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement(['present', 'absent', 'late']),
            'note' => fake()->optional()->sentence(),
            'recorded_by' => 1,
        ];
    }
}