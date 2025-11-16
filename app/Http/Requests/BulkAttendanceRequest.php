<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent,late',
            'attendances.*.note' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'attendances.required' => 'At least one attendance record is required',
            'attendances.*.student_id.exists' => 'One or more student IDs are invalid',
            'attendances.*.status.in' => 'Status must be present, absent, or late',
        ];
    }
}