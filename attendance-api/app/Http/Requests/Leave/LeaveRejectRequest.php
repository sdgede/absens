<?php
// =============================================================================
// app/Http/Requests/Leave/LeaveRejectRequest.php
// =============================================================================
namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi',
            'rejection_reason.max'      => 'Alasan penolakan maksimal 500 karakter',
        ];
    }
}
