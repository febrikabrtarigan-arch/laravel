<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Login untuk Admin dan Staff.
     * Mengembalikan Sanctum token beserta informasi role user.
     *
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // 1. Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // 2. Validasi user dan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse(
                'Email atau password yang Anda masukkan salah.',
                null,
                401
            );
        }

        // 3. Cek apakah akun aktif
        if (!$user->is_active) {
            return $this->errorResponse(
                'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                null,
                403
            );
        }

        // 4. Hapus token lama (single session per user)
        $user->tokens()->delete();

        // 5. Buat token baru dengan nama device berdasarkan role
        $tokenName = "simbar-{$user->role}-token";
        $token = $user->createToken($tokenName)->plainTextToken;

        return $this->successResponse(
            [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'nip'     => $user->nip,
                    'email'   => $user->email,
                    'role'    => $user->role,
                    'jabatan' => $user->jabatan,
                    'no_hp'   => $user->no_hp,
                ],
            ],
            'Login berhasil. Selamat datang, ' . $user->name . '!'
        );
    }

    /**
     * Register untuk Staff.
     * Secara otomatis memberikan role 'staff' dan status 'aktif'.
     *
     * POST /api/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        Log::info('Register request received:', $request->validated());

        try {
            $user = User::create([
                'name'      => $request->name,
                'nip'       => $request->nip,
                'email'     => $request->email,
                'password'  => $request->password, // Akan otomatis di-hash oleh model cast
                'role'      => 'staff',
                'jabatan'   => $request->jabatan,
                'no_hp'     => $request->no_hp,
                'is_active' => true,
            ]);

            $tokenName = "simbar-{$user->role}-token";
            $token = $user->createToken($tokenName)->plainTextToken;

            return $this->successResponse(
                [
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'user'       => [
                        'id'      => $user->id,
                        'name'    => $user->name,
                        'nip'     => $user->nip,
                        'email'   => $user->email,
                        'role'    => $user->role,
                        'jabatan' => $user->jabatan,
                        'no_hp'   => $user->no_hp,
                    ],
                ],
                'Pendaftaran berhasil. Selamat datang, ' . $user->name . '!',
                201
            );
        } catch (\Exception $e) {
            Log::error('Register failed: ' . $e->getMessage());
            return $this->errorResponse('Gagal melakukan pendaftaran: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Logout — cabut token aktif saat ini.
     *
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        // Hapus hanya token yang digunakan saat ini (bukan semua token)
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil.');
    }

    /**
     * Kembalikan profil user yang sedang login.
     *
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load([]);

        return $this->successResponse([
            'id'        => $user->id,
            'name'      => $user->name,
            'nip'       => $user->nip,
            'email'     => $user->email,
            'role'      => $user->role,
            'jabatan'   => $user->jabatan,
            'no_hp'     => $user->no_hp,
            'is_active' => $user->is_active,
        ]);
    }
}
