<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel permintaan barang oleh staff.
     * CATATAN: File ini harus dijalankan SEBELUM transaksis.
     * Urutan file: ...000003_barangs > 000004_permintaans > 000005_transaksis
     */
    public function up(): void
    {
        Schema::create('permintaans', function (Blueprint $table) {
            $table->id();
            $table->string('no_permintaan', 50)->unique()
                  ->comment('Nomor unik permintaan, ex: PRM-20240101-001');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict')
                  ->comment('Staff yang mengajukan permintaan');
            $table->foreignId('barang_id')
                  ->constrained('barangs')
                  ->onUpdate('cascade')
                  ->onDelete('restrict')
                  ->comment('Barang yang diminta');
            $table->unsignedInteger('jumlah_diminta')
                  ->comment('Jumlah barang yang diminta oleh staff');
            $table->unsignedInteger('jumlah_disetujui')
                  ->nullable()
                  ->comment('Jumlah yang disetujui admin (bisa berbeda dengan yang diminta)');
            $table->text('keperluan')->comment('Alasan/keperluan pengajuan permintaan barang');
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])
                  ->default('pending')
                  ->comment('Status permintaan: pending (menunggu) | disetujui | ditolak');
            $table->text('catatan_admin')->nullable()
                  ->comment('Catatan dari admin saat menyetujui atau menolak permintaan');
            $table->foreignId('diproses_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->onDelete('set null')
                  ->comment('Admin yang memproses permintaan ini');
            $table->timestamp('tanggal_diproses')->nullable()
                  ->comment('Waktu admin memproses permintaan');
            $table->timestamps();
            $table->softDeletes();

            // Index untuk filter dan dashboard
            $table->index('status');
            $table->index('user_id');
            $table->index('barang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaans');
    }
};
