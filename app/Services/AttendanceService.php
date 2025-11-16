<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\Cache;

class AttendanceService
{
    public function recordAttendance(array $data, int $userId): Attendance
    {
        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'date' => $data['date'],
            ],
            [
                'status' => $data['status'],
                'note' => $data['note'] ?? null,
                'recorded_by' => $userId,
            ]
        );

        Cache::forget("attendance_stats_{$data['student_id']}");
        Cache::forget("attendance_today_" . date('Y-m-d'));

        return $attendance;
    }

    public function getAttendanceStatsByDate(string $date): array
    {
        return Cache::remember("attendance_date_{$date}", 3600, function () use ($date) {
            $total = Attendance::byDate($date)->count();
            $present = Attendance::byDate($date)->byStatus('present')->count();
            $absent = Attendance::byDate($date)->byStatus('absent')->count();
            $late = Attendance::byDate($date)->byStatus('late')->count();

            return [
                'date' => $date,
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'present_percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ];
        });
    }

    public function getMonthlyReport(int $year, int $month, ?string $class = null): array
    {
        $query = Attendance::query()
            ->with(['student', 'recorder'])
            ->byMonth($year, $month);

        if ($class) {
            $query->whereHas('student', function ($q) use ($class) {
                $q->where('class', $class);
            });
        }

        $attendances = $query->get();

        $studentStats = [];
        foreach ($attendances as $attendance) {
            $studentId = $attendance->student_id;
            
            if (!isset($studentStats[$studentId])) {
                $studentStats[$studentId] = [
                    'student' => $attendance->student,
                    'total_days' => 0,
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                ];
            }

            $studentStats[$studentId]['total_days']++;
            $studentStats[$studentId][$attendance->status]++;
        }

        foreach ($studentStats as &$stats) {
            $total = $stats['total_days'];
            $stats['present_percentage'] = $total > 0 ? round(($stats['present'] / $total) * 100, 2) : 0;
        }

        return [
            'year' => $year,
            'month' => $month,
            'class' => $class,
            'students' => array_values($studentStats),
        ];
    }

    public function getTodaysSummary(): array
    {
        $today = date('Y-m-d');
        return $this->getAttendanceStatsByDate($today);
    }
}