<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        $classes = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
        $sections = ['A', 'B', 'C', 'D'];

        return [
            'name' => fake()->name(),
            'student_id' => 'STU' . fake()->unique()->numberBetween(1000, 9999),
            'class' => fake()->randomElement($classes),
            'section' => fake()->randomElement($sections),
            'photo' => null,
        ];
    }
}