<?php
// =============================================================================
// app/Http/Requests/Leave/LeaveRequestStore.php
// =============================================================================
namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestStore extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'start_date'    => ['required', 'date_format:Y-m-d'],
            'end_date'      => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason'        => ['required', 'string', 'max:1000'],
            // requires_attachment divalidasi di service setelah cek LeaveType
            'attachment'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'leave_type_id.required'  => 'Jenis cuti wajib dipilih',
            'leave_type_id.exists'    => 'Jenis cuti tidak valid',
            'start_date.required'     => 'Tanggal mulai wajib diisi',
            'start_date.date_format'  => 'Format tanggal mulai harus Y-m-d',
            'end_date.required'       => 'Tanggal selesai wajib diisi',
            'end_date.date_format'    => 'Format tanggal selesai harus Y-m-d',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai',
            'reason.required'         => 'Alasan cuti wajib diisi',
            'attachment.mimes'        => 'Lampiran harus berupa file PDF, JPG, atau PNG',
            'attachment.max'          => 'Ukuran lampiran maksimal 5 MB',
        ];
    }
}
