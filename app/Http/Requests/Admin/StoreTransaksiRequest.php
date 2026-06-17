<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barang_id'          => ['required', 'integer', 'exists:barangs,id'],
            'jenis_transaksi'    => ['required', 'in:masuk,keluar'],
            'jumlah'             => ['required', 'integer', 'min:1'],
            'serial_number'      => ['nullable', 'string', 'max:100'],
            'sumber_atau_tujuan' => ['nullable', 'string', 'max:255'],
            'keterangan'         => ['nullable', 'string'],
            'no_dokumen'         => ['nullable', 'string', 'max:100'],
            'tanggal_transaksi'  => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'barang_id.required'       => 'Barang wajib dipilih.',
            'barang_id.exists'         => 'Barang yang dipilih tidak ditemukan.',
            'jenis_transaksi.required' => 'Jenis transaksi wajib diisi.',
            'jenis_transaksi.in'       => 'Jenis transaksi harus masuk atau keluar.',
            'jumlah.required'          => 'Jumlah wajib diisi.',
            'jumlah.min'               => 'Jumlah minimal 1.',
            'tanggal_transaksi.required'        => 'Tanggal transaksi wajib diisi.',
            'tanggal_transaksi.before_or_equal' => 'Tanggal transaksi tidak boleh di masa depan.',
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
