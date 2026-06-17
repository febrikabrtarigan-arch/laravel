<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTransaksiRequest;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    use ApiResponse;

    /**
     * Daftar semua transaksi dengan filter.
     *
     * GET /api/admin/transaksis
     * Query params: jenis (masuk|keluar), barang_id, dari, sampai, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaksi::with([
            'barang' => function($q) {
                $q->withTrashed();
            },
            'user:id,name,nip',
        ]);

        // Filter jenis transaksi
        if ($request->filled('jenis')) {
            $query->where('jenis_transaksi', $request->jenis);
        }

        // Filter barang tertentu
        if ($request->filled('barang_id')) {
            $query->where('barang_id', $request->barang_id);
        }

        // Filter pencarian (No Transaksi, Nama Barang, Kode Barang, Keterangan)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_transaksi', 'like', "%$search%")
                  ->orWhere('keterangan', 'like', "%$search%")
                  ->orWhere('sumber_atau_tujuan', 'like', "%$search%")
                  ->orWhereHas('barang', function($qb) use ($search) {
                      $qb->where('nama_barang', 'like', "%$search%")
                         ->orWhere('kode_barang', 'like', "%$search%");
                  });
            });
        }

        // Filter rentang tanggal
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->sampai);
        }

        $transaksis = $query->orderByDesc('tanggal_transaksi')
                            ->orderByDesc('id')
                            ->paginate($request->integer('per_page', 15));

        return $this->successResponse($transaksis, 'Daftar transaksi berhasil diambil.');
    }

    /**
     * Catat transaksi barang masuk atau keluar.
     * Stok barang akan otomatis diperbarui menggunakan Database Transaction
     * untuk menjaga konsistensi data.
     *
     * POST /api/admin/transaksis
     */
    public function store(StoreTransaksiRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Gunakan DB Transaction agar stok dan transaksi selalu konsisten
        try {
            $result = DB::transaction(function () use ($validated, $request) {

                // 1. Kunci baris barang agar tidak ada race condition (lockForUpdate)
                $barang = Barang::lockForUpdate()->findOrFail($validated['barang_id']);

                // 2. Validasi stok jika transaksi keluar
                if ($validated['jenis_transaksi'] === 'keluar') {
                    if ($barang->stok_saat_ini < $validated['jumlah']) {
                        throw new \Exception(
                            "Stok {$barang->nama_barang} tidak mencukupi. " .
                            "Stok tersedia: {$barang->stok_saat_ini} {$barang->satuan}."
                        );
                    }
                }

                // 3. Hitung stok baru
                $stokSebelum = $barang->stok_saat_ini;
                $stokSesudah = $validated['jenis_transaksi'] === 'masuk'
                    ? $stokSebelum + $validated['jumlah']
                    : $stokSebelum - $validated['jumlah'];

                // 4. Update stok barang
                $barang->update(['stok_saat_ini' => $stokSesudah]);

                // 5. Catat transaksi
                $transaksi = Transaksi::create([
                    'no_transaksi'       => Transaksi::generateNomor($validated['jenis_transaksi']),
                    'barang_id'          => $barang->id,
                    'jenis_transaksi'    => $validated['jenis_transaksi'],
                    'jumlah'             => $validated['jumlah'],
                    'serial_number'      => $validated['serial_number'] ?? null,
                    'stok_sebelum'       => $stokSebelum,
                    'stok_sesudah'       => $stokSesudah,
                    'sumber_atau_tujuan' => $validated['sumber_atau_tujuan'] ?? null,
                    'keterangan'         => $validated['keterangan'] ?? null,
                    'no_dokumen'         => $validated['no_dokumen'] ?? null,
                    'user_id'            => $request->user()->id,
                    'tanggal_transaksi'  => $validated['tanggal_transaksi'],
                ]);

                return [
                    'transaksi' => $transaksi->load([
                        'barang:id,kode_barang,nama_barang,satuan,stok_saat_ini',
                        'user:id,name,nip',
                    ]),
                    'stok_terkini' => $stokSesudah,
                ];
            });

            return $this->successResponse(
                $result,
                'Transaksi berhasil dicatat. Stok barang telah diperbarui.',
                201
            );

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    /**
     * Detail satu transaksi.
     *
     * GET /api/admin/transaksis/{id}
     */
    public function show(int $id): JsonResponse
    {
        $transaksi = Transaksi::with([
            'barang' => function($q) {
                $q->withTrashed();
            },
            'user:id,name,nip,jabatan',
            'permintaan:id,no_permintaan,keperluan',
        ])->find($id);

        if (!$transaksi) {
            return $this->notFoundResponse('Transaksi tidak ditemukan.');
        }

        return $this->successResponse($transaksi);
    }
}
