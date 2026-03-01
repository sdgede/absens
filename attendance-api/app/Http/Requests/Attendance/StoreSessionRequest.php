<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'branch_id'        => ['required', 'integer', 'exists:tenant_branches,id'],
            'date'             => ['required', 'date_format:Y-m-d'],
            'check_in_start'   => ['required', 'date_format:H:i'],
            'check_in_end'     => ['required', 'date_format:H:i', 'after:check_in_start'],
            'late_after'       => ['required', 'date_format:H:i', 'after_or_equal:check_in_start', 'before_or_equal:check_in_end'],
            'check_out_start'  => ['nullable', 'date_format:H:i'],
            'check_out_end'    => ['nullable', 'date_format:H:i', 'after:check_out_start'],
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Tambahan validasi: cek overlap sesi di branch + tanggal yang sama.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->failed()) {
                return;
            }

            $data = $this->validated();

            $overlap = \App\Models\AttendanceSession::where('branch_id', $data['branch_id'])
                ->where('date', $data['date'])
                ->where('is_active', true)
                ->where(function ($q) use ($data) {
                    // Overlap jika rentang baru bertabrakan dengan rentang yang sudah ada
                    $q->where('check_in_start', '<', $data['check_in_end'])
                        ->where('check_in_end', '>', $data['check_in_start']);
                })
                ->when($this->route('id'), fn($q) => $q->where('id', '!=', $this->route('id')))
                ->exists();

            if ($overlap) {
                $validator->errors()->add('check_in_start', 'Terdapat sesi lain yang overlap pada branch dan tanggal yang sama.');
            }
        });
    }
}
