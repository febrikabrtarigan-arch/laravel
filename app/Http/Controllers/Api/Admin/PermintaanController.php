<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApprovePermintaanRequest;
use App\Http\Requests\Admin\RejectPermintaanRequest;
use App\Models\Permintaan;
use App\Models\Transaksi;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermintaanController extends Controller
{
    use ApiResponse;

    /**
     * Tampilkan semua permintaan masuk dengan filter status.
     *
     * GET /api/admin/permintaans
     * Query params: status (pending|disetujui|ditolak), user_id, dari, sampai, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $query = Permintaan::with([
            'pemohon:id,name,nip,jabatan',
            'barang' => function($q) {
                $q->withTrashed();
            },
            'pemroses:id,name',
        ]);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pemohon
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter rentang tanggal
        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        $permintaans = $query->orderByDesc('created_at')
                             ->paginate($request->integer('per_page', 15));

        return $this->successResponse($permintaans, 'Daftar permintaan berhasil diambil.');
    }

    /**
     * Detail satu permintaan.
     *
     * GET /api/admin/permintaans/{id}
     */
    public function show(int $id): JsonResponse
    {
        $permintaan = Permintaan::with([
            'pemohon:id,name,nip,jabatan,no_hp',
            'barang' => function($q) {
                $q->withTrashed();
            },
            'pemroses:id,name',
            'transaksi:id,no_transaksi,tanggal_transaksi',
        ])->find($id);

        if (!$permintaan) {
            return $this->notFoundResponse('Permintaan tidak ditemukan.');
        }

        return $this->successResponse($permintaan);
    }

    /**
     * Setujui permintaan barang.
     * Jika disetujui → stok barang otomatis BERKURANG dan dicatat sebagai transaksi keluar.
     * Semua operasi dibungkus dalam DB Transaction untuk keamanan.
     *
     * PATCH /api/admin/permintaans/{id}/setujui
     */
    public function setujui(ApprovePermintaanRequest $request, int $id): JsonResponse
    {
        try {
            $result = DB::transaction(function () use ($request, $id) {
                // 1. Kunci baris permintaan agar tidak diproses ganda oleh admin lain
                $permintaan = Permintaan::with('barang')->lockForUpdate()->findOrFail($id);

                // 2. Validasi status harus tetap pending
                if (!$permintaan->isPending()) {
                    throw new \Exception("Permintaan ini sudah berstatus '{$permintaan->status}' dan tidak dapat diproses ulang.");
                }

                $barang = $permintaan->barang()->lockForUpdate()->first();

                if (!$barang) {
                    throw new \Exception("Data barang terkait permintaan ini tidak ditemukan di database.");
                }

                // Tentukan jumlah yang disetujui
                $jumlahDisetujui = $request->filled('jumlah_disetujui')
                    ? $request->jumlah_disetujui
                    : $permintaan->jumlah_diminta;

                // Validasi kecukupan stok
                if ($barang->stok_saat_ini < $jumlahDisetujui) {
                    throw new \Exception(
                        "Stok {$barang->nama_barang} tidak mencukupi untuk disetujui. " .
                        "Stok tersedia: {$barang->stok_saat_ini} {$barang->satuan}, " .
                        "jumlah disetujui: {$jumlahDisetujui} {$barang->satuan}."
                    );
                }

                // Hitung dan update stok
                $stokSebelum = $barang->stok_saat_ini;
                $stokSesudah = $stokSebelum - $jumlahDisetujui;
                $barang->update(['stok_saat_ini' => $stokSesudah]);

                // Catat transaksi keluar
                $transaksi = Transaksi::create([
                    'no_transaksi'       => Transaksi::generateNomor('keluar'),
                    'barang_id'          => $barang->id,
                    'jenis_transaksi'    => 'keluar',
                    'jumlah'             => $jumlahDisetujui,
                    'serial_number'      => $request->serial_number,
                    'stok_sebelum'       => $stokSebelum,
                    'stok_sesudah'       => $stokSesudah,
                    'sumber_atau_tujuan' => $permintaan->pemohon->name,
                    'keterangan'         => "Persetujuan permintaan #{$permintaan->no_permintaan}. Keperluan: {$permintaan->keperluan}",
                    'permintaan_id'      => $permintaan->id,
                    'user_id'            => $request->user()->id,
                    'tanggal_transaksi'  => now()->toDateString(),
                ]);

                // Update status permintaan
                $permintaan->update([
                    'status'            => 'disetujui',
                    'jumlah_disetujui'  => $jumlahDisetujui,
                    'catatan_admin'     => $request->catatan_admin,
                    'diproses_oleh'     => $request->user()->id,
                    'tanggal_diproses'  => now(),
                ]);

                return [
                    'permintaan' => $permintaan->fresh(['pemohon', 'barang', 'pemroses', 'transaksi']),
                    'transaksi'  => $transaksi,
                    'stok_terkini' => $stokSesudah,
                ];
            });

            return $this->successResponse(
                $result,
                'Permintaan berhasil disetujui dan stok barang telah dikurangi.'
            );

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    /**
     * Tolak permintaan barang.
     * Tidak ada perubahan stok. Stok tetap aman.
     *
     * PATCH /api/admin/permintaans/{id}/tolak
     */
    public function tolak(RejectPermintaanRequest $request, int $id): JsonResponse
    {
        try {
            $permintaan = DB::transaction(function () use ($request, $id) {
                // Kunci baris data
                $permintaan = Permintaan::lockForUpdate()->findOrFail($id);

                if (!$permintaan->isPending()) {
                    throw new \Exception("Permintaan ini sudah berstatus '{$permintaan->status}' dan tidak dapat diproses ulang.");
                }

                $permintaan->update([
                    'status'           => 'ditolak',
                    'catatan_admin'    => $request->catatan_admin,
                    'diproses_oleh'    => $request->user()->id,
                    'tanggal_diproses' => now(),
                ]);

                return $permintaan;
            });

            return $this->successResponse(
                $permintaan->fresh(['pemohon', 'barang', 'pemroses']),
                'Permintaan berhasil ditolak.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }
}
