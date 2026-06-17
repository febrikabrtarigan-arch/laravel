<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePermintaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barang_id'      => ['required', 'integer', 'exists:barangs,id'],
            'jumlah_diminta' => ['required', 'integer', 'min:1'],
            'keperluan'      => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'barang_id.required'      => 'Barang yang diminta wajib dipilih.',
            'barang_id.exists'        => 'Barang yang dipilih tidak ditemukan.',
            'jumlah_diminta.required' => 'Jumlah yang diminta wajib diisi.',
            'jumlah_diminta.min'      => 'Jumlah yang diminta minimal 1.',
            'keperluan.required'      => 'Keperluan/alasan permintaan wajib diisi.',
            'keperluan.min'           => 'Keperluan minimal 10 karakter agar jelas.',
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
