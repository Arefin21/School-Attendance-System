<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkAttendanceRequest;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $query = Attendance::with(['student', 'recorder']);

        if ($request->has('date')) {
            $query->byDate($request->input('date'));
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }

        if ($request->has('status')) {
            $query->byStatus($request->input('status'));
        }

        if ($request->has('class')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class', $request->input('class'));
            });
        }

        $perPage = $request->input('per_page', 15);
        $attendances = $query->orderBy('date', 'desc')->paginate($perPage);

        return AttendanceResource::collection($attendances);
    }

    public function store(StoreAttendanceRequest $request)
    {
        $attendance = $this->attendanceService->recordAttendance(
            $request->validated(),
            auth()->id()
        );

        return (new AttendanceResource($attendance->load(['student', 'recorder'])))->response()->setStatusCode(201);
    }

    public function bulkStore(BulkAttendanceRequest $request)
    {
        $validated = $request->validated();
        $recorded = [];

        foreach ($validated['attendances'] as $data) {
            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $data['student_id'],
                    'date' => $validated['date'],
                ],
                [
                    'status' => $data['status'],
                    'note' => $data['note'] ?? null,
                    'recorded_by' => auth()->id(),
                ]
            );
            $recorded[] = $attendance;
        }

        return response()->json([
            'message' => 'Bulk attendance recorded successfully',
            'count' => count($recorded),
            'data' => AttendanceResource::collection(collect($recorded)),
        ], 201);
    }

    public function show(Attendance $attendance)
    {
        return new AttendanceResource($attendance->load(['student', 'recorder']));
    }

    public function update(StoreAttendanceRequest $request, Attendance $attendance)
    {
        $data = $request->validated();
        $attendance->update($data);

        return new AttendanceResource($attendance->load(['student', 'recorder']));
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return response()->json(['message' => 'Attendance deleted successfully'], 200);
    }

    /**
     * Get today's attendance summary (cached)
     */
    public function todaysSummary()
    {
        $summary = $this->attendanceService->getTodaysSummary();
        return response()->json($summary);
    }

    /**
     * Get attendance stats by date (cached)
     */
    public function statsByDate(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $stats = $this->attendanceService->getAttendanceStatsByDate($request->input('date'));
        return response()->json($stats);
    }

    /**
     * Get monthly report (with caching)
     */
    public function monthlyReport(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'class' => 'nullable|string',
        ]);

        $report = $this->attendanceService->getMonthlyReport(
            $request->input('year'),
            $request->input('month'),
            $request->input('class')
        );

        return response()->json($report);
    }
}