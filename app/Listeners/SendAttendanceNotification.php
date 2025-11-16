<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use Illuminate\Support\Facades\Log;

class SendAttendanceNotification
{
    public function handle(AttendanceRecorded $event): void
    {
        $attendance = $event->attendance;
        
        Log::info('Attendance recorded', [
            'student_id' => $attendance->student_id,
            'date' => $attendance->date,
            'status' => $attendance->status,
        ]);
    }
}