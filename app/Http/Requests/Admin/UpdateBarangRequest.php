<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $barangId = $this->route('id');

        return [
            'kode_barang'        => ['sometimes', 'required', 'string', 'max:50', "unique:barangs,kode_barang,{$barangId}"],
            'nama_barang'        => ['sometimes', 'required', 'string', 'max:255'],
            'kategori_id'        => ['sometimes', 'required', 'integer', 'exists:kategoris,id'],
            'merk'               => ['nullable', 'string', 'max:100'],
            'satuan'             => ['sometimes', 'required', 'string', 'max:30'],
            'stok_minimum'       => ['sometimes', 'required', 'integer', 'min:0'],
            'deskripsi'          => ['nullable', 'string'],
            'lokasi_penyimpanan' => ['nullable', 'string', 'max:255'],
            'harga_satuan'       => ['nullable', 'numeric', 'min:0'],
            'foto_barang'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'          => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_barang.unique'  => 'Kode barang sudah digunakan oleh barang lain.',
            'kategori_id.exists'  => 'Kategori yang dipilih tidak valid.',
            'stok_minimum.min'    => 'Stok minimum tidak boleh negatif.',
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
