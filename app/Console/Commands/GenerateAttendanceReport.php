<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;

class GenerateAttendanceReport extends Command
{
    protected $signature = 'attendance:generate-report {month} {class?}';
    protected $description = 'Generate monthly attendance report';

    public function handle(AttendanceService $attendanceService)
    {
        $monthInput = $this->argument('month');
        $class = $this->argument('class');

        if (preg_match('/^(\d{4})-(\d{1,2})$/', $monthInput, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
        } else {
            $this->error('Invalid format. Use YYYY-MM (e.g., 2025-11)');
            return 1;
        }

        $this->info("Generating report for " . date('F Y', mktime(0, 0, 0, $month, 1, $year)));
        
        $report = $attendanceService->getMonthlyReport($year, $month, $class);

        if (empty($report['students'])) {
            $this->warn('No attendance records found');
            return 0;
        }

        $this->table(
            ['Student ID', 'Name', 'Class', 'Total Days', 'Present', 'Absent', 'Late', 'Present %'],
            collect($report['students'])->map(function ($s) {
                return [
                    $s['student']->student_id,
                    $s['student']->name,
                    $s['student']->class . '-' . $s['student']->section,
                    $s['total_days'],
                    $s['present'],
                    $s['absent'],
                    $s['late'],
                    $s['present_percentage'] . '%',
                ];
            })
        );

        $this->info('Report generated successfully!');
        return 0;
    }
}