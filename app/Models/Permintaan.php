<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permintaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permintaans';

    protected $fillable = [
        'no_permintaan',
        'user_id',
        'barang_id',
        'jumlah_diminta',
        'jumlah_disetujui',
        'keperluan',
        'status',
        'catatan_admin',
        'diproses_oleh',
        'tanggal_diproses',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_diminta'    => 'integer',
            'jumlah_disetujui'  => 'integer',
            'tanggal_diproses'  => 'datetime',
        ];
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }

    // ─── Helpers ───────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Generate nomor permintaan otomatis.
     * Format: PRM-YYYYMMDD-XXXX
     */
    public static function generateNomor(): string
    {
        $date   = now()->format('Ymd');
        $lastNo = static::withTrashed()->whereDate('created_at', today())->count() + 1;

        return sprintf('PRM-%s-%04d', $date, $lastNo);
    }

    // ─── Relasi ────────────────────────────────────────────────

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id')->withTrashed();
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function transaksi(): HasOne
    {
        return $this->hasOne(Transaksi::class, 'permintaan_id');
    }
}
