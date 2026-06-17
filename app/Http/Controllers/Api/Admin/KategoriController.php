<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    use ApiResponse;

    /**
     * Daftar semua kategori.
     *
     * GET /api/admin/kategoris
     */
    public function index(Request $request): JsonResponse
    {
        $query = Kategori::withCount('barangs');

        if ($request->filled('search')) {
            $query->where('nama_kategori', 'like', "%{$request->search}%");
        }

        if ($request->boolean('aktif', false)) {
            $query->where('is_active', true);
        }

        $kategoris = $query->orderBy('nama_kategori')->get();

        return $this->successResponse($kategoris, 'Daftar kategori berhasil diambil.');
    }

    /**
     * Tambah kategori baru.
     *
     * POST /api/admin/kategoris
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => ['required', 'string', 'max:100', 'unique:kategoris,nama_kategori'],
            'deskripsi'     => ['nullable', 'string'],
            'kode_kategori' => ['nullable', 'string', 'max:20', 'unique:kategoris,kode_kategori'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah digunakan.',
            'kode_kategori.unique'   => 'Kode kategori sudah digunakan.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', $validator->errors(), 422);
        }

        $kategori = Kategori::create($validator->validated());

        return $this->successResponse($kategori, 'Kategori berhasil ditambahkan.', 201);
    }

    /**
     * Detail satu kategori.
     *
     * GET /api/admin/kategoris/{id}
     */
    public function show(int $id): JsonResponse
    {
        $kategori = Kategori::withCount('barangs')->find($id);

        if (!$kategori) {
            return $this->notFoundResponse('Kategori tidak ditemukan.');
        }

        return $this->successResponse($kategori);
    }

    /**
     * Update kategori.
     *
     * PUT /api/admin/kategoris/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return $this->notFoundResponse('Kategori tidak ditemukan.');
        }

        $validator = Validator::make($request->all(), [
            'nama_kategori' => ['sometimes', 'required', 'string', 'max:100',
                Rule::unique('kategoris', 'nama_kategori')->ignore($id)],
            'deskripsi'     => ['nullable', 'string'],
            'kode_kategori' => ['nullable', 'string', 'max:20',
                Rule::unique('kategoris', 'kode_kategori')->ignore($id)],
            'is_active'     => ['sometimes', 'boolean'],
        ], [
            'nama_kategori.unique'   => 'Nama kategori sudah digunakan.',
            'kode_kategori.unique'   => 'Kode kategori sudah digunakan.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', $validator->errors(), 422);
        }

        $kategori->update($validator->validated());

        return $this->successResponse($kategori, 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori (soft delete).
     * Hanya bisa dihapus jika tidak ada barang yang menggunakannya.
     *
     * DELETE /api/admin/kategoris/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $kategori = Kategori::withCount('barangs')->find($id);

        if (!$kategori) {
            return $this->notFoundResponse('Kategori tidak ditemukan.');
        }

        if ($kategori->barangs_count > 0) {
            return $this->errorResponse(
                "Kategori tidak dapat dihapus karena masih digunakan oleh {$kategori->barangs_count} barang.",
                null,
                422
            );
        }

        $kategori->delete();

        return $this->successResponse(null, 'Kategori berhasil dihapus.');
    }
}
