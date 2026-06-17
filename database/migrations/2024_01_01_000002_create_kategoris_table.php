<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel kategori barang (misal: ATK, Komputer, Jaringan, dll).
     */
    public function up(): void
    {
        Schema::create('kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori')->unique()->comment('Nama kategori barang, ex: ATK, Komputer, Elektronik');
            $table->text('deskripsi')->nullable()->comment('Deskripsi singkat kategori');
            $table->string('kode_kategori', 20)->unique()->nullable()->comment('Kode singkat, ex: ATK-01');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategoris');
    }
};
