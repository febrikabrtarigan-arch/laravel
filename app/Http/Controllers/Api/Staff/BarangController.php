<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    use ApiResponse;

    /**
     * Tampilkan daftar barang yang aktif dan tersedia untuk staff.
     * Staff hanya bisa melihat, tidak bisa CRUD.
     *
     * GET /api/staff/barangs
     * Query params: search, kategori_id, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $query = Barang::with('kategori:id,nama_kategori,kode_kategori')
                        ->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $barangs = $query->select([
                'id', 'kode_barang', 'nama_barang', 'kategori_id',
                'merk', 'satuan', 'stok_saat_ini', 'stok_minimum',
                'deskripsi', 'lokasi_penyimpanan', 'foto_barang', 'harga_satuan',
            ])
            ->orderBy('nama_barang')
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse($barangs, 'Daftar barang berhasil diambil.');
    }

    /**
     * Detail satu barang.
     *
     * GET /api/staff/barangs/{id}
     */
    public function show(int $id): JsonResponse
    {
        $barang = Barang::with('kategori:id,nama_kategori')
            ->where('is_active', true)
            ->find($id);

        if (!$barang) {
            return $this->notFoundResponse('Barang tidak ditemukan atau tidak tersedia.');
        }

        return $this->successResponse($barang);
    }
}
