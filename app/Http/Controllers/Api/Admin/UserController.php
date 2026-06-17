<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    use ApiResponse;
    /**
     * Daftar semua user dengan filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('name')
                       ->paginate($request->integer('per_page', 15));

        return $this->successResponse($users, 'Daftar user berhasil diambil.');
    }

    /**
     * Tambah user baru oleh admin.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'nip'       => ['required', 'string', 'max:50', 'unique:users,nip'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', Password::defaults()],
            'role'      => ['required', 'in:admin,staff'],
            'jabatan'   => ['nullable', 'string', 'max:255'],
            'no_hp'     => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);

        return $this->successResponse($user, 'User berhasil dibuat.', 201);
    }

    /**
     * Detail user (termasuk yang sudah dihapus).
     */
    public function show($id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        return $this->successResponse($user, 'Detail user berhasil diambil.');
    }

    /**
     * Update data user oleh admin.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'name'      => ['sometimes', 'required', 'string', 'max:255'],
            'nip'       => ['sometimes', 'required', 'string', 'max:50', "unique:users,nip,{$id}"],
            'email'     => ['sometimes', 'required', 'string', 'email', 'max:255', "unique:users,email,{$id}"],
            'password'  => ['nullable', Password::defaults()],
            'role'      => ['sometimes', 'required', 'in:admin,staff'],
            'jabatan'   => ['nullable', 'string', 'max:255'],
            'no_hp'     => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Proteksi: Admin tidak boleh mengubah role atau status diri sendiri lewat sini
        if ($user->id === auth()->id()) {
            if (isset($validated['role']) && $validated['role'] !== $user->role) {
                return $this->errorResponse('Anda tidak bisa mengubah Role Anda sendiri.', 403);
            }
            if (isset($validated['is_active']) && $validated['is_active'] == 0) {
                return $this->errorResponse('Anda tidak bisa menonaktifkan akun Anda sendiri.', 403);
            }
        }

        $user->update($validated);

        return $this->successResponse($user, 'Data user berhasil diperbarui.');
    }

    /**
     * Hapus user (Soft Delete).
     */
    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        // Proteksi agar admin tidak menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return $this->errorResponse('Anda tidak bisa menghapus akun Anda sendiri.', 403);
        }

         // Rename email agar bisa dipakai lagi setelah dihapus
        $user->email = $user->email . '_deleted_' . time();
        $user->save();

        // Soft delete permintaan terkait dulu
         DB::table('permintaans')->where('user_id', $user->id)->delete();

        $user->delete();

        return $this->successResponse(null, 'User berhasil dihapus.');
    }

    /**
     * Toggle status aktif/nonaktif user (Mendukung Restore).
     */
    public function toggleStatus($id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->id === auth()->id()) {
            return $this->errorResponse('Anda tidak bisa menonaktifkan akun Anda sendiri.', 403);
        }

        // Jika user terhapus, restore dulu
        if ($user->trashed()) {
            $user->restore();
            $user->update(['is_active' => true]);
            return $this->successResponse($user, "User berhasil dipulihkan dan diaktifkan kembali.");
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return $this->successResponse($user, "User berhasil $status.");
    }
}
