<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id'       => ['required', 'integer', 'exists:attendance_sessions,id'],
            'face_confidence'  => ['required', 'numeric', 'min:0', 'max:1'],
            'liveness_score'   => ['required', 'numeric', 'min:0', 'max:1'],
            'lat'              => ['required', 'numeric', 'min:-90', 'max:90'],
            'lng'              => ['required', 'numeric', 'min:-180', 'max:180'],
        ];
    }
}
