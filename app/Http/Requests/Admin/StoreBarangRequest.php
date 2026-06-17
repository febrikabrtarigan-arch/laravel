<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_barang'        => ['required', 'string', 'max:50', 'unique:barangs,kode_barang'],
            'nama_barang'        => ['required', 'string', 'max:255'],
            'kategori_id'        => ['required', 'integer', 'exists:kategoris,id'],
            'merk'               => ['nullable', 'string', 'max:100'],
            'satuan'             => ['required', 'string', 'max:30'],
            'stok_saat_ini'      => ['required', 'integer', 'min:0'],
            'stok_minimum'       => ['required', 'integer', 'min:0'],
            'deskripsi'          => ['nullable', 'string'],
            'lokasi_penyimpanan' => ['nullable', 'string', 'max:255'],
            'harga_satuan'       => ['nullable', 'numeric', 'min:0'],
            'foto_barang'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_barang.required'   => 'Kode barang wajib diisi.',
            'kode_barang.unique'     => 'Kode barang sudah digunakan.',
            'nama_barang.required'   => 'Nama barang wajib diisi.',
            'kategori_id.required'   => 'Kategori wajib dipilih.',
            'kategori_id.exists'     => 'Kategori yang dipilih tidak valid.',
            'satuan.required'        => 'Satuan wajib diisi.',
            'stok_saat_ini.required' => 'Stok awal wajib diisi.',
            'stok_saat_ini.min'      => 'Stok tidak boleh negatif.',
            'stok_minimum.required'  => 'Stok minimum wajib diisi.',
            'foto_barang.image'      => 'File foto harus berupa gambar.',
            'foto_barang.max'        => 'Ukuran foto maksimal 2MB.',
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
