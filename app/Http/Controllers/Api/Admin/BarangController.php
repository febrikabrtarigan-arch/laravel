<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBarangRequest;
use App\Http\Requests\Admin\UpdateBarangRequest;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    use ApiResponse;

    /**
     * Tampilkan daftar semua barang dengan filter dan pagination.
     *
     * GET /api/admin/barangs
     * Query params: search, kategori_id, status_stok (aman|kritis|habis), per_page
     */
    public function index(Request $request): JsonResponse
    {
        $query = Barang::with('kategori')
                        ->withTrashed(false); // Hanya barang aktif (tidak soft-deleted)

        // Filter pencarian nama/kode
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('merk', 'like', "%{$search}%");
            });
        }

        // Filter kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Filter status stok
        if ($request->filled('status_stok')) {
            match ($request->status_stok) {
                'habis'  => $query->where('stok_saat_ini', 0),
                'kritis' => $query->whereColumn('stok_saat_ini', '<=', 'stok_minimum')
                                  ->where('stok_saat_ini', '>', 0),
                'aman'   => $query->whereColumn('stok_saat_ini', '>', 'stok_minimum'),
                default  => null,
            };
        }

        // Filter is_active
        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = $request->integer('per_page', 15);
        $barangs = $query->withCount('transaksis')
                        ->orderBy('nama_barang')
                        ->paginate($perPage);

        return $this->successResponse($barangs, 'Daftar barang berhasil diambil.');
    }

    /**
     * Simpan barang baru ke database.
     *
     * POST /api/admin/barangs
     */
    public function store(StoreBarangRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $barang = DB::transaction(function () use ($data, $request) {
                // Proses upload foto jika ada
                if ($request->hasFile('foto_barang')) {
                    $data['foto_barang'] = $request->file('foto_barang')
                        ->store('barangs', 'public');
                }

                $barang = Barang::create($data);

                // Catat transaksi awal jika stok > 0
                if ($barang->stok_saat_ini > 0) {
                    Transaksi::create([
                        'no_transaksi'       => Transaksi::generateNomor('masuk'),
                        'barang_id'          => $barang->id,
                        'jenis_transaksi'    => 'masuk',
                        'jumlah'             => $barang->stok_saat_ini,
                        'stok_sebelum'       => 0,
                        'stok_sesudah'       => $barang->stok_saat_ini,
                        'sumber_atau_tujuan' => 'Stok Awal Sistem',
                        'keterangan'         => 'Saldo awal saat pendaftaran barang baru.',
                        'user_id'            => $request->user()->id,
                        'tanggal_transaksi'  => now()->toDateString(),
                    ]);
                }

                return $barang;
            });

            $barang->load('kategori');
            return $this->successResponse($barang, 'Barang berhasil ditambahkan.', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menambahkan barang: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Tampilkan detail satu barang.
     *
     * GET /api/admin/barangs/{id}
     */
    public function show(int $id): JsonResponse
    {
        $barang = Barang::with('kategori')->find($id);

        if (!$barang) {
            return $this->notFoundResponse('Barang tidak ditemukan.');
        }

        return $this->successResponse($barang);
    }

    /**
     * Update data barang.
     * CATATAN: stok_saat_ini tidak bisa diubah langsung di sini.
     * Stok hanya bisa berubah via TransaksiController untuk menjaga integritas audit.
     *
     * PUT /api/admin/barangs/{id}
     */
    public function update(UpdateBarangRequest $request, int $id): JsonResponse
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return $this->notFoundResponse('Barang tidak ditemukan.');
        }

        $data = $request->validated();

        // Proses upload foto baru jika ada
        if ($request->hasFile('foto_barang')) {
            // Hapus foto lama
            if ($barang->foto_barang) {
                Storage::disk('public')->delete($barang->foto_barang);
            }
            $data['foto_barang'] = $request->file('foto_barang')
                ->store('barangs', 'public');
        }

        // Pastikan stok_saat_ini tidak ikut diubah lewat endpoint ini
        unset($data['stok_saat_ini']);

        $barang->update($data);
        $barang->load('kategori');

        return $this->successResponse($barang, 'Data barang berhasil diperbarui.');
    }

    /**
     * Soft-delete barang (tidak benar-benar dihapus).
     * Barang yang sudah memiliki transaksi tidak bisa dihapus.
     *
     * DELETE /api/admin/barangs/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return $this->notFoundResponse('Barang tidak ditemukan.');
        }

        // Cek kecukupan stok (Hanya boleh hapus jika stok 0)
        if ($barang->stok_saat_ini > 0) {
            return $this->errorResponse(
                "Barang tidak dapat dihapus karena masih ada stok tersisa: {$barang->stok_saat_ini} {$barang->satuan}.",
                null,
                422
            );
        }

        // Hapus barang (SoftDelete)
        // Data tetap ada di database dan riwayat transaksi tetap aman
        // karena relasi sudah menggunakan withTrashed()

        $barang->delete(); // SoftDelete

        return $this->successResponse(null, 'Barang berhasil dihapus.');
    }

    /**
     * Riwayat transaksi masuk/keluar untuk satu barang.
     *
     * GET /api/admin/barangs/{id}/riwayat
     */
    public function riwayat(Request $request, int $id): JsonResponse
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return $this->notFoundResponse('Barang tidak ditemukan.');
        }

        $transaksis = $barang->transaksis()
            ->with('user:id,name,nip')
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_transaksi', $request->jenis))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal_transaksi', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal_transaksi', '<=', $request->sampai))
            ->orderByDesc('tanggal_transaksi')
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse([
            'barang'     => $barang->only(['id', 'kode_barang', 'nama_barang', 'stok_saat_ini', 'satuan', 'transaksis_count']),
            'transaksis' => $transaksis,
        ]);
    }
}
