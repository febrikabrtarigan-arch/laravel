<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\Permintaan;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Ringkasan status permintaan untuk staff yang sedang login.
     *
     * GET /api/staff/dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $totalPermintaan   = Permintaan::where('user_id', $userId)->count();
        $pending           = Permintaan::where('user_id', $userId)->pending()->count();
        $disetujui         = Permintaan::where('user_id', $userId)->disetujui()->count();
        $ditolak           = Permintaan::where('user_id', $userId)->ditolak()->count();

        // 5 permintaan terbaru milik staff ini
        $riwayatTerbaru = Permintaan::with('barang:id,nama_barang,satuan')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'no_permintaan', 'barang_id', 'jumlah_diminta', 'jumlah_disetujui', 'status', 'created_at']);

        return $this->successResponse([
            'user'       => $request->user()->only(['id', 'name', 'nip', 'jabatan']),
            'statistik'  => [
                'total_permintaan' => $totalPermintaan,
                'pending'          => $pending,
                'disetujui'        => $disetujui,
                'ditolak'          => $ditolak,
            ],
            'riwayat_terbaru' => $riwayatTerbaru,
        ]);
    }
}
