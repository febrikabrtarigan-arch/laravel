<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Periksa apakah user yang terautentikasi memiliki role yang sesuai.
     * Usage di routes: middleware('role:admin') atau middleware('role:staff')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        if ($user->role !== $role) {
            return response()->json([
                'status'  => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses sumber daya ini.',
            ], 403);
        }

        // Cek apakah akun aktif
        if (!$user->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ], 403);
        }

        return $next($request);
    }
}
