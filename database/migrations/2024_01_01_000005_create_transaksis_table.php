<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel transaksi pergerakan stok barang (masuk & keluar).
     * Setiap insert/update di sini harus diikuti update stok_saat_ini di tabel barangs.
     * Gunakan Observer atau Database Transaction untuk menjaga konsistensi data.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi', 50)->unique()->comment('Nomor unik transaksi, ex: TRX-MSK-20240101-001');
            $table->foreignId('barang_id')
                  ->constrained('barangs')
                  ->onUpdate('cascade')
                  ->onDelete('restrict')
                  ->comment('Relasi ke tabel barangs');
            $table->enum('jenis_transaksi', ['masuk', 'keluar'])
                  ->comment('masuk: penambahan stok | keluar: pengurangan stok');
            $table->unsignedInteger('jumlah')->comment('Jumlah barang yang masuk atau keluar');
            $table->unsignedInteger('stok_sebelum')->comment('Stok barang sebelum transaksi (snapshot)');
            $table->unsignedInteger('stok_sesudah')->comment('Stok barang sesudah transaksi (snapshot)');
            $table->string('sumber_atau_tujuan')->nullable()
                  ->comment('Untuk masuk: nama supplier/sumber. Untuk keluar: nama penerima/tujuan');
            $table->text('keterangan')->nullable()->comment('Catatan tambahan transaksi');
            $table->string('no_dokumen', 100)->nullable()
                  ->comment('Nomor dokumen referensi (BAST, Faktur, dll)');
            $table->foreignId('permintaan_id')
                  ->nullable()
                  ->constrained('permintaans')
                  ->onUpdate('cascade')
                  ->onDelete('set null')
                  ->comment('Relasi ke permintaan (jika transaksi keluar berasal dari permintaan)');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict')
                  ->comment('Admin yang melakukan transaksi');
            $table->date('tanggal_transaksi')->comment('Tanggal efektif transaksi');
            $table->timestamps();

            // Index untuk laporan dan filter
            $table->index('jenis_transaksi');
            $table->index('tanggal_transaksi');
            $table->index('barang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
