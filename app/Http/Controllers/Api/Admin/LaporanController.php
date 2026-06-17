<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Permintaan;
use App\Models\Transaksi;
use App\Traits\ApiResponse;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LaporanController extends Controller
{
    use ApiResponse;

    /**
     * Laporan stok semua barang saat ini.
     */
    public function stok(Request $request): JsonResponse
    {
        $query = Barang::with('kategori:id,nama_kategori');

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('search')) {
            $query->where('nama_barang', 'like', "%{$request->search}%");
        }

        $barangs = $query->orderBy('nama_barang')
            ->paginate($request->integer('per_page', 50));

        return $this->successResponse($barangs, 'Laporan stok berhasil diambil.');
    }

    /**
     * Laporan transaksi masuk/keluar dengan filter.
     */
    public function transaksi(Request $request): JsonResponse
    {
        $query = Transaksi::with([
            'barang:id,kode_barang,nama_barang,satuan,harga_satuan',
            'user:id,name,nip',
        ]);

        // Terapkan filter umum (kecuali jenis)
        if ($request->filled('barang_id')) {
            $query->where('barang_id', $request->barang_id);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->sampai);
        }

        // Hitung Ringkasan berdasarkan filter umum
        $totalMasuk  = (clone $query)->where('jenis_transaksi', 'masuk')->sum('jumlah');
        $totalKeluar = (clone $query)->where('jenis_transaksi', 'keluar')->sum('jumlah');
        
        $nilaiMasuk = (clone $query)->where('jenis_transaksi', 'masuk')
            ->join('barangs', 'transaksis.barang_id', '=', 'barangs.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('transaksis.jumlah * COALESCE(barangs.harga_satuan, 0)'));
        $nilaiKeluar = (clone $query)->where('jenis_transaksi', 'keluar')
            ->join('barangs', 'transaksis.barang_id', '=', 'barangs.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('transaksis.jumlah * COALESCE(barangs.harga_satuan, 0)'));

        // Baru terapkan filter JENIS untuk data list (tabel)
        if ($request->filled('jenis')) {
            $query->where('jenis_transaksi', $request->jenis);
        }

        $transaksis = $query->orderByDesc('tanggal_transaksi')
                            ->orderByDesc('id')
                            ->paginate($request->integer('per_page', 50));

        return $this->successResponse([
            'ringkasan' => [
                'total_masuk'  => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'nilai_masuk'  => $nilaiMasuk,
                'nilai_keluar' => $nilaiKeluar,
            ],
            'data' => $transaksis,
        ], 'Laporan transaksi berhasil diambil.');
    }

    /**
     * Laporan permintaan barang.
     */
    public function permintaan(Request $request): JsonResponse
    {
        $query = Permintaan::with([
            'pemohon:id,name,nip,jabatan',
            'barang:id,nama_barang,satuan',
            'pemroses:id,name',
        ]);

        // Terapkan filter umum (kecuali status)
        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        // Hitung Ringkasan berdasarkan filter umum
        $ringkasan = [
            'total'     => (clone $query)->count(),
            'pending'   => (clone $query)->where('status', 'pending')->count(),
            'disetujui' => (clone $query)->where('status', 'disetujui')->count(),
            'ditolak'   => (clone $query)->where('status', 'ditolak')->count(),
        ];

        // Baru terapkan filter STATUS untuk data list (tabel)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $permintaans = $query->orderByDesc('created_at')
                             ->paginate($request->integer('per_page', 50));

        return $this->successResponse([
            'ringkasan' => $ringkasan,
            'data'      => $permintaans,
        ], 'Laporan permintaan berhasil diambil.');
    }

    /**
     * Daftar barang yang stoknya berada di bawah atau sama dengan stok minimum.
     */
    public function stokMinimum(): JsonResponse
    {
        $barangs = Barang::with('kategori:id,nama_kategori')
            ->whereColumn('stok_saat_ini', '<=', 'stok_minimum')
            ->orderBy('stok_saat_ini')
            ->get([
                'id', 'kode_barang', 'nama_barang', 'kategori_id',
                'satuan', 'stok_saat_ini', 'stok_minimum', 'lokasi_penyimpanan',
            ]);

        return $this->successResponse([
            'total'   => $barangs->count(),
            'barangs' => $barangs,
        ], 'Laporan barang stok minimum berhasil diambil.');
    }

    /**
     * Export laporan transaksi ke PDF.
     */
    public function exportTransaksiPdf(Request $request)
    {
        $query = Transaksi::with([
            'barang:id,kode_barang,nama_barang,satuan,harga_satuan',
            'user:id,name,nip',
        ]);

        if ($request->filled('barang_id')) {
            $query->where('barang_id', $request->barang_id);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->sampai);
        }

        $nilaiMasuk = (clone $query)->where('jenis_transaksi', 'masuk')
            ->join('barangs', 'transaksis.barang_id', '=', 'barangs.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('transaksis.jumlah * COALESCE(barangs.harga_satuan, 0)'));
        $nilaiKeluar = (clone $query)->where('jenis_transaksi', 'keluar')
            ->join('barangs', 'transaksis.barang_id', '=', 'barangs.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('transaksis.jumlah * COALESCE(barangs.harga_satuan, 0)'));

        // Ringkasan
        $ringkasan = [
            'total_masuk'  => (clone $query)->where('jenis_transaksi', 'masuk')->sum('jumlah'),
            'total_keluar' => (clone $query)->where('jenis_transaksi', 'keluar')->sum('jumlah'),
            'nilai_masuk'  => $nilaiMasuk,
            'nilai_keluar' => $nilaiKeluar,
        ];

        if ($request->filled('jenis')) {
            $query->where('jenis_transaksi', $request->jenis);
        }

        $transaksis = $query->orderByDesc('tanggal_transaksi')->get();

        $template = Setting::getByKey('report_template', [
            'instansi_nama' => 'PEMERINTAH KABUPATEN SERDANG BEDAGAI',
            'departemen_nama' => 'DINAS KOMUNIKASI DAN INFORMATIKA',
            'alamat' => "Jalan Negara No. 300 Sei Rampah, Serdang Bedagai, Sumatera Utara 20995\nTelepon 0621 - 442135, www.serdangbedagaikab.go.id\nPos-el diskominfo@serdangbedagaikab.go.id",
            'ttd_nama' => '',
            'ttd_nip' => '',
            'ttd_jabatan' => '',
            'logo_path' => null,
            'show_logo' => true,
            'logo_size' => 80,
        ]);

        $pdf = Pdf::loadView('pdf.transaksi', [
            'transaksis' => $transaksis,
            'ringkasan' => $ringkasan,
            'template' => $template,
            'filters' => $request->all(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-transaksi-' . now()->format('Y-m-d') . '.pdf');
    }
}
