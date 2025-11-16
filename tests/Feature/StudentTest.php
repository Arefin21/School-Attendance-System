<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_students()
    {
        Student::factory()->count(5)->create();

        $response = $this->getJson('/api/students');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_can_filter_students_by_class()
    {
        Student::factory()->create(['class' => '5']);
        Student::factory()->create(['class' => '6']);
        Student::factory()->create(['class' => '5']);

        $response = $this->getJson('/api/students?class=5');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_student()
    {
        $studentData = [
            'name' => 'Test Student',
            'student_id' => 'STU1234',
            'class' => '5',
            'section' => 'A',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/students', $studentData);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Student']);

        $this->assertDatabaseHas('students', ['student_id' => 'STU1234']);
    }

    public function test_requires_authentication_to_create_student()
    {
        $studentData = [
            'name' => 'Test Student',
            'student_id' => 'STU1234',
            'class' => '5',
            'section' => 'A',
        ];

        $response = $this->postJson('/api/students', $studentData);

        $response->assertStatus(401);
    }

    public function test_validates_student_data()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/students', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'student_id', 'class', 'section']);
    }
}