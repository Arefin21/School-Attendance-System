<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $user = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@school.com',
            'password' => bcrypt('password123'),
        ]);

        // Create 50 students
        $students = \App\Models\Student::factory(50)->create();

        // Create 30 days of attendance for each student
        foreach ($students as $student) {
            for ($i = 0; $i < 30; $i++) {
                \App\Models\Attendance::factory()->create([
                    'student_id' => $student->id,
                    'date' => now()->subDays($i)->format('Y-m-d'),
                    'status' => fake()->randomElement(['present', 'present', 'present', 'absent', 'late']),
                    'recorded_by' => $user->id,
                ]);
            }
        }
    }
}