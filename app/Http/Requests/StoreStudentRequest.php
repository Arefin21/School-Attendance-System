<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|unique:students,student_id|max:50',
            'class' => 'required|string|max:50',
            'section' => 'required|string|max:50',
            'photo' => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Student name is required',
            'student_id.required' => 'Student ID is required',
            'student_id.unique' => 'This student ID already exists',
        ];
    }
}