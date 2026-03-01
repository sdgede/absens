<?php

namespace App\Http\Requests\Face;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterFaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // user_id opsional — hanya admin yang perlu kirim ini
            'user_id'    => ['nullable', 'integer', 'exists:users,id'],

            // Embedding: array tepat 128 elemen numerik
            'embedding'          => ['required', 'array', 'size:128'],
            'embedding.*'        => ['required', 'numeric', 'between:-1,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'embedding.required' => 'Data embedding wajah wajib dikirim.',
            'embedding.array'    => 'Embedding harus berupa array.',
            'embedding.size'     => 'Embedding harus berisi tepat 128 elemen.',
            'embedding.*.numeric'  => 'Setiap nilai embedding harus berupa angka.',
            'embedding.*.between'  => 'Setiap nilai embedding harus dalam range -1.0 sampai 1.0.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
