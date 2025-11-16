<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student');
        
        return [
            'name' => 'sometimes|required|string|max:255',
            'student_id' => 'sometimes|required|string|max:50|unique:students,student_id,' . $studentId,
            'class' => 'sometimes|required|string|max:50',
            'section' => 'sometimes|required|string|max:50',
            'photo' => 'nullable|image|max:2048',
        ];
    }
}