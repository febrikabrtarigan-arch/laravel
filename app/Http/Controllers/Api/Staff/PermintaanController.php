<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StorePermintaanRequest;
use App\Models\Barang;
use App\Models\Permintaan;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermintaanController extends Controller
{
    use ApiResponse;

    /**
     * Tampilkan semua permintaan milik staff yang sedang login.
     *
     * GET /api/staff/permintaans
     * Query params: status, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $query = Permintaan::with([
            'barang:id,kode_barang,nama_barang,satuan',
            'pemroses:id,name',
        ])->where('user_id', $request->user()->id);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $permintaans = $query->orderByDesc('created_at')
                             ->paginate($request->integer('per_page', 15));

        return $this->successResponse($permintaans, 'Daftar permintaan Anda berhasil diambil.');
    }

    /**
     * Ajukan permintaan barang baru.
     * Staff hanya bisa mengajukan permintaan untuk barang yang AKTIF dan stoknya > 0.
     *
     * POST /api/staff/permintaans
     */
    public function store(StorePermintaanRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Pastikan barang aktif dan tersedia
        $barang = Barang::where('id', $validated['barang_id'])
                        ->where('is_active', true)
                        ->first();

        if (!$barang) {
            return $this->errorResponse(
                'Barang yang dipilih tidak aktif atau tidak tersedia.',
                null,
                422
            );
        }

        if ($barang->stok_saat_ini === 0) {
            return $this->errorResponse(
                "Maaf, stok {$barang->nama_barang} sedang habis. Silakan hubungi admin.",
                null,
                422
            );
        }

        // Buat permintaan
        $permintaan = Permintaan::create([
            'no_permintaan'  => Permintaan::generateNomor(),
            'user_id'        => $request->user()->id,
            'barang_id'      => $validated['barang_id'],
            'jumlah_diminta' => $validated['jumlah_diminta'],
            'keperluan'      => $validated['keperluan'],
            'status'         => 'pending',
        ]);

        $permintaan->load(['barang:id,nama_barang,satuan,stok_saat_ini']);

        return $this->successResponse(
            $permintaan,
            'Permintaan barang berhasil dikirim. Silakan tunggu persetujuan admin.',
            201
        );
    }

    /**
     * Detail satu permintaan milik staff yang sedang login.
     *
     * GET /api/staff/permintaans/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $permintaan = Permintaan::with([
            'barang:id,kode_barang,nama_barang,satuan',
            'pemroses:id,name,jabatan',
            'transaksi:id,no_transaksi,tanggal_transaksi',
        ])->where('user_id', $request->user()->id)->find($id);

        if (!$permintaan) {
            return $this->notFoundResponse('Permintaan tidak ditemukan.');
        }

        return $this->successResponse($permintaan);
    }

    /**
     * Batalkan permintaan.
     * Hanya bisa membatalkan permintaan MILIK SENDIRI yang masih berstatus 'pending'.
     *
     * DELETE /api/staff/permintaans/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $permintaan = Permintaan::where('user_id', $request->user()->id)->find($id);

        if (!$permintaan) {
            return $this->notFoundResponse('Permintaan tidak ditemukan.');
        }

        if (!$permintaan->isPending()) {
            return $this->errorResponse(
                "Permintaan tidak dapat dibatalkan karena sudah berstatus '{$permintaan->status}'.",
                null,
                422
            );
        }

        $permintaan->delete(); // SoftDelete

        return $this->successResponse(null, 'Permintaan berhasil dibatalkan.');
    }
}
