<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel master data barang inventaris.
     * Stok dihitung dari transaksis (masuk - keluar) secara real-time
     * ATAU bisa disimpan di kolom stok_saat_ini sebagai cache.
     * Pendekatan ini menggunakan kolom stok_saat_ini agar query lebih cepat.
     */
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang', 50)->unique()->comment('Kode unik barang, ex: BRG-2024-001');
            $table->string('nama_barang')->comment('Nama lengkap barang');
            $table->foreignId('kategori_id')
                  ->constrained('kategoris')
                  ->onUpdate('cascade')
                  ->onDelete('restrict')
                  ->comment('Relasi ke tabel kategoris');
            $table->string('merk')->nullable()->comment('Merk/brand barang');
            $table->string('satuan', 30)->default('unit')->comment('Satuan barang: unit, pcs, rim, box, dll');
            $table->unsignedInteger('stok_saat_ini')->default(0)->comment('Jumlah stok tersedia saat ini');
            $table->unsignedInteger('stok_minimum')->default(5)->comment('Batas minimum stok, untuk notifikasi');
            $table->text('deskripsi')->nullable()->comment('Deskripsi/spesifikasi barang');
            $table->string('lokasi_penyimpanan')->nullable()->comment('Lokasi fisik penyimpanan, ex: Rak A1, Gudang 2');
            $table->string('foto_barang')->nullable()->comment('Path foto barang');
            $table->decimal('harga_satuan', 15, 2)->nullable()->comment('Harga per satuan (opsional)');
            $table->boolean('is_active')->default(true)->comment('Status barang aktif/nonaktif');
            $table->timestamps();
            $table->softDeletes();

            // Index untuk pencarian dan filter
            $table->index('nama_barang');
            $table->index('kategori_id');
            $table->index('stok_saat_ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
