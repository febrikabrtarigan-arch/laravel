<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Permintaan;
use App\Models\Transaksi;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Data ringkasan untuk halaman dashboard admin.
     *
     * GET /api/admin/dashboard
     */
    public function index(): JsonResponse
    {
        // Hitung statistik utama
        $totalBarang     = Barang::count();
        $barangStokHabis = Barang::where('stok_saat_ini', 0)->count();
        $barangStokKritis = Barang::whereColumn('stok_saat_ini', '<=', 'stok_minimum')
                                   ->where('stok_saat_ini', '>', 0)
                                   ->count();
        $totalKategori   = Kategori::where('is_active', true)->count();
        $totalStaff      = User::where('role', 'staff')->where('is_active', true)->count();

        // Permintaan
        $permintaanPending   = Permintaan::pending()->count();
        $permintaanHariIni   = Permintaan::whereDate('created_at', today())->count();

        // Transaksi bulan ini
        $transaksiMasukBulanIni  = Transaksi::where('jenis_transaksi', 'masuk')
                                             ->whereMonth('tanggal_transaksi', now()->month)
                                             ->whereYear('tanggal_transaksi', now()->year)
                                             ->sum('jumlah');
        $transaksiKeluarBulanIni = Transaksi::where('jenis_transaksi', 'keluar')
                                             ->whereMonth('tanggal_transaksi', now()->month)
                                             ->whereYear('tanggal_transaksi', now()->year)
                                             ->sum('jumlah');

        // 5 permintaan terbaru
        $permintaanTerbaru = Permintaan::with([
            'pemohon:id,name,jabatan',
            'barang:id,nama_barang,satuan',
        ])->orderByDesc('created_at')->limit(5)->get();

        // 5 barang dengan stok kritis
        $barangKritis = Barang::with('kategori:id,nama_kategori')
            ->whereColumn('stok_saat_ini', '<=', 'stok_minimum')
            ->orderBy('stok_saat_ini')
            ->limit(5)
            ->get(['id', 'kode_barang', 'nama_barang', 'stok_saat_ini', 'stok_minimum', 'satuan', 'kategori_id']);

        return $this->successResponse([
            'statistik' => [
                'total_barang'             => $totalBarang,
                'total_kategori'           => $totalKategori,
                'total_staff_aktif'        => $totalStaff,
                'barang_stok_habis'        => $barangStokHabis,
                'barang_stok_kritis'       => $barangStokKritis,
                'permintaan_pending'       => $permintaanPending,
                'permintaan_hari_ini'      => $permintaanHariIni,
                'transaksi_masuk_bulan_ini'  => $transaksiMasukBulanIni,
                'transaksi_keluar_bulan_ini' => $transaksiKeluarBulanIni,
            ],
            'permintaan_terbaru' => $permintaanTerbaru,
            'barang_stok_kritis' => $barangKritis,
        ]);
    }
}
