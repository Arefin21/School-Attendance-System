<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkAttendanceRequest;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
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
        $data = $request->validated();
        $data['recorded_by'] = auth()->id();

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'date' => $data['date'],
            ],
            $data
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
}