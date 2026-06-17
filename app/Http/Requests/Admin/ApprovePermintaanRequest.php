<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApprovePermintaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // jumlah_disetujui opsional; jika kosong, pakai jumlah_diminta
            'jumlah_disetujui' => ['nullable', 'integer', 'min:1'],
            'serial_number'    => ['nullable', 'string', 'max:100'],
            'catatan_admin'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'jumlah_disetujui.integer' => 'Jumlah disetujui harus berupa angka.',
            'jumlah_disetujui.min'     => 'Jumlah disetujui minimal 1.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'Validasi gagal.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
