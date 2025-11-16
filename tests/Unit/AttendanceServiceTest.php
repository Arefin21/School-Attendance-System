<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $attendanceService;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendanceService = new AttendanceService();
        $this->user = User::factory()->create();
    }

    public function test_can_record_single_attendance()
    {
        $student = Student::factory()->create();

        $data = [
            'student_id' => $student->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'present',
            'note' => 'Test note',
        ];

        $attendance = $this->attendanceService->recordAttendance($data, $this->user->id);

        $this->assertInstanceOf(Attendance::class, $attendance);
        $this->assertEquals('present', $attendance->status);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'status' => 'present',
        ]);
    }

    public function test_can_get_todays_summary()
    {
        $students = Student::factory()->count(5)->create();
        
        foreach ($students as $student) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'date' => now()->format('Y-m-d'),
                'status' => 'present',
                'recorded_by' => $this->user->id,
            ]);
        }

        $summary = $this->attendanceService->getTodaysSummary();

        $this->assertArrayHasKey('total', $summary);
        $this->assertArrayHasKey('present', $summary);
        $this->assertEquals(5, $summary['present']);
    }
}