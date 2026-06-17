<?php

use Illuminate\Support\Facades\Route;

// ============================================================
// Controllers - Auth
// ============================================================
use App\Http\Controllers\Api\AuthController;

// ============================================================
// Controllers - Admin
// ============================================================
use App\Http\Controllers\Api\Admin\KategoriController;
use App\Http\Controllers\Api\Admin\BarangController;
use App\Http\Controllers\Api\Admin\TransaksiController;
use App\Http\Controllers\Api\Admin\PermintaanController as AdminPermintaanController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\LaporanController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;

// ============================================================
// Controllers - Staff
// ============================================================
use App\Http\Controllers\Api\Staff\BarangController as StaffBarangController;
use App\Http\Controllers\Api\Staff\PermintaanController as StaffPermintaanController;
use App\Http\Controllers\Api\Staff\DashboardController as StaffDashboardController;

// ============================================================
//  ROUTE: AUTH (Public - Tidak perlu token)
// ============================================================
Route::prefix('auth')->group(function () {
    // POST /api/auth/login
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    // POST /api/auth/register
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
});

// ============================================================
//  ROUTE: PROTECTED (Membutuhkan token Sanctum)
// ============================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- Auth ---
    // POST /api/auth/logout
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    // GET  /api/auth/me
    Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');

    // ========================================================
    //  ADMIN ROUTES
    //  Middleware: role 'admin' (cek via CheckRole middleware)
    // ========================================================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        // --- Dashboard ---
        // GET /api/admin/dashboard
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // --- Manajemen User (Admin) ---
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',         [UserController::class, 'index'])->name('index');       // GET    /api/admin/users
            Route::post('/',        [UserController::class, 'store'])->name('store');       // POST   /api/admin/users
            Route::get('{id}',      [UserController::class, 'show'])->name('show');         // GET    /api/admin/users/{id}
            Route::put('{id}',      [UserController::class, 'update'])->name('update');     // PUT    /api/admin/users/{id}
            Route::delete('{id}',   [UserController::class, 'destroy'])->name('destroy');   // DELETE /api/admin/users/{id}
            Route::patch('{id}/toggle-status', [UserController::class, 'toggleStatus'])    // PATCH  /api/admin/users/{id}/toggle-status
                ->name('toggle-status');
        });

        // --- Manajemen Kategori ---
        Route::prefix('kategoris')->name('kategoris.')->group(function () {
            Route::get('/',         [KategoriController::class, 'index'])->name('index');   // GET    /api/admin/kategoris
            Route::post('/',        [KategoriController::class, 'store'])->name('store');   // POST   /api/admin/kategoris
            Route::get('{id}',      [KategoriController::class, 'show'])->name('show');     // GET    /api/admin/kategoris/{id}
            Route::put('{id}',      [KategoriController::class, 'update'])->name('update'); // PUT    /api/admin/kategoris/{id}
            Route::delete('{id}',   [KategoriController::class, 'destroy'])->name('destroy'); // DELETE /api/admin/kategoris/{id}
        });

        // --- Manajemen Barang ---
        Route::prefix('barangs')->name('barangs.')->group(function () {
            Route::get('/',         [BarangController::class, 'index'])->name('index');     // GET    /api/admin/barangs
            Route::post('/',        [BarangController::class, 'store'])->name('store');     // POST   /api/admin/barangs
            Route::get('{id}',      [BarangController::class, 'show'])->name('show');       // GET    /api/admin/barangs/{id}
            Route::put('{id}',      [BarangController::class, 'update'])->name('update');   // PUT    /api/admin/barangs/{id}
            Route::delete('{id}',   [BarangController::class, 'destroy'])->name('destroy'); // DELETE /api/admin/barangs/{id}
            Route::get('{id}/riwayat', [BarangController::class, 'riwayat'])               // GET    /api/admin/barangs/{id}/riwayat
                ->name('riwayat');
        });

        // --- Manajemen Transaksi (Stok Masuk/Keluar) ---
        Route::prefix('transaksis')->name('transaksis.')->group(function () {
            Route::get('/',         [TransaksiController::class, 'index'])->name('index');  // GET    /api/admin/transaksis
            Route::post('/',        [TransaksiController::class, 'store'])->name('store');  // POST   /api/admin/transaksis
            Route::get('{id}',      [TransaksiController::class, 'show'])->name('show');    // GET    /api/admin/transaksis/{id}
            // Transaksi tidak boleh diupdate/delete untuk menjaga integritas audit.
            // Gunakan transaksi koreksi jika terjadi kesalahan input.
        });

        // --- Manajemen Permintaan Barang (Persetujuan Admin) ---
        Route::prefix('permintaans')->name('permintaans.')->group(function () {
            Route::get('/',         [AdminPermintaanController::class, 'index'])->name('index');   // GET    /api/admin/permintaans
            Route::get('{id}',      [AdminPermintaanController::class, 'show'])->name('show');     // GET    /api/admin/permintaans/{id}
            // PATCH untuk persetujuan/penolakan agar tidak memerlukan seluruh body
            Route::patch('{id}/setujui',  [AdminPermintaanController::class, 'setujui'])          // PATCH  /api/admin/permintaans/{id}/setujui
                ->name('setujui');
            Route::patch('{id}/tolak',    [AdminPermintaanController::class, 'tolak'])            // PATCH  /api/admin/permintaans/{id}/tolak
                ->name('tolak');
        });

        // --- Laporan Stok ---
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('stok',          [LaporanController::class, 'stok'])->name('stok');         // GET /api/admin/laporan/stok
            Route::get('transaksi',     [LaporanController::class, 'transaksi'])->name('transaksi'); // GET /api/admin/laporan/transaksi
            Route::get('permintaan',    [LaporanController::class, 'permintaan'])->name('permintaan'); // GET /api/admin/laporan/permintaan
            Route::get('stok-minimum',  [LaporanController::class, 'stokMinimum'])->name('stok-minimum'); // GET /api/admin/laporan/stok-minimum
            Route::get('transaksi/export-pdf', [LaporanController::class, 'exportTransaksiPdf'])->name('transaksi.export-pdf');
        });

        // --- Pengaturan Template ---
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('template', [\App\Http\Controllers\Api\Admin\SettingController::class, 'getTemplate'])->name('template.get');
            Route::post('template', [\App\Http\Controllers\Api\Admin\SettingController::class, 'updateTemplate'])->name('template.update');
        });
    });

    // ========================================================
    //  STAFF ROUTES
    //  Middleware: role 'staff'
    // ========================================================
    Route::middleware('role:staff')->prefix('staff')->name('staff.')->group(function () {

        // --- Dashboard Staff ---
        // GET /api/staff/dashboard
        Route::get('dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

        // --- Lihat Data Barang & Stok (Read Only) ---
        Route::prefix('barangs')->name('barangs.')->group(function () {
            Route::get('/',         [StaffBarangController::class, 'index'])->name('index');  // GET /api/staff/barangs
            Route::get('{id}',      [StaffBarangController::class, 'show'])->name('show');    // GET /api/staff/barangs/{id}
        });

        // --- Permintaan Barang (Kelola milik sendiri) ---
        Route::prefix('permintaans')->name('permintaans.')->group(function () {
            Route::get('/',         [StaffPermintaanController::class, 'index'])->name('index');   // GET    /api/staff/permintaans
            Route::post('/',        [StaffPermintaanController::class, 'store'])->name('store');   // POST   /api/staff/permintaans
            Route::get('{id}',      [StaffPermintaanController::class, 'show'])->name('show');     // GET    /api/staff/permintaans/{id}
            // Staff hanya bisa membatalkan permintaan yang masih 'pending'
            Route::delete('{id}',   [StaffPermintaanController::class, 'destroy'])->name('destroy'); // DELETE /api/staff/permintaans/{id}
        });
    });
});
