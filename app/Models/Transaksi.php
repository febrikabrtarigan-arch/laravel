<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis';

    protected $fillable = [
        'no_transaksi',
        'barang_id',
        'jenis_transaksi',
        'jumlah',
        'serial_number',
        'stok_sebelum',
        'stok_sesudah',
        'sumber_atau_tujuan',
        'keterangan',
        'no_dokumen',
        'permintaan_id',
        'user_id',
        'tanggal_transaksi',
    ];

    protected function casts(): array
    {
        return [
            'jumlah'           => 'integer',
            'stok_sebelum'     => 'integer',
            'stok_sesudah'     => 'integer',
            'tanggal_transaksi' => 'date',
        ];
    }

    // ─── Relasi ────────────────────────────────────────────────

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id')->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(Permintaan::class, 'permintaan_id');
    }

    // ─── Helper ────────────────────────────────────────────────

    /**
     * Generate nomor transaksi otomatis.
     * Format: TRX-MSK-YYYYMMDD-XXXX atau TRX-KLR-YYYYMMDD-XXXX
     */
    public static function generateNomor(string $jenis): string
    {
        $prefix = $jenis === 'masuk' ? 'MSK' : 'KLR';
        $date   = now()->format('YmdHis'); // Menambahkan jam, menit, detik
        $random = strtoupper(bin2hex(random_bytes(2))); // Menambahkan 4 karakter acak
        
        return "TRX-{$prefix}-{$date}-{$random}";
    }
}
