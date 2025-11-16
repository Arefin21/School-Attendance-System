<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->student = Student::factory()->create();
    }

    public function test_can_record_attendance()
    {
        $attendanceData = [
            'student_id' => $this->student->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'present',
            'note' => 'Test note',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/attendances', $attendanceData);

        $response->assertStatus(201)
            ->assertJsonFragment(['status' => 'present']);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);
    }

    public function test_can_record_bulk_attendance()
    {
        $students = Student::factory()->count(3)->create();
        
        $attendanceData = [
            'date' => now()->format('Y-m-d'),
            'attendances' => $students->map(function ($student) {
                return [
                    'student_id' => $student->id,
                    'status' => 'present',
                    'note' => null,
                ];
            })->toArray(),
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/attendances/bulk', $attendanceData);

        $response->assertStatus(201)
            ->assertJsonFragment(['count' => 3]);

        $this->assertDatabaseCount('attendances', 3);
    }

    public function test_validates_attendance_status()
    {
        $attendanceData = [
            'student_id' => $this->student->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/attendances', $attendanceData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_prevents_duplicate_attendance_for_same_day()
    {
        $attendanceData = [
            'student_id' => $this->student->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'present',
        ];

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/attendances', $attendanceData);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/attendances', $attendanceData);

        $this->assertDatabaseCount('attendances', 1);
    }
}