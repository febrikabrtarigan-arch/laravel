<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'barangs';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori_id',
        'merk',
        'satuan',
        'stok_saat_ini',
        'stok_minimum',
        'deskripsi',
        'lokasi_penyimpanan',
        'foto_barang',
        'harga_satuan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stok_saat_ini' => 'integer',
            'stok_minimum'  => 'integer',
            'harga_satuan'  => 'decimal:2',
            'is_active'     => 'boolean',
        ];
    }

    // ─── Appends ───────────────────────────────────────────────

    protected $appends = ['status_stok'];

    public function getStatusStokAttribute(): string
    {
        if ($this->stok_saat_ini === 0) {
            return 'habis';
        }
        if ($this->stok_saat_ini <= $this->stok_minimum) {
            return 'kritis';
        }
        return 'aman';
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeStokKritis($query)
    {
        return $query->whereColumn('stok_saat_ini', '<=', 'stok_minimum');
    }

    // ─── Relasi ────────────────────────────────────────────────

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'barang_id');
    }

    public function permintaans(): HasMany
    {
        return $this->hasMany(Permintaan::class, 'barang_id');
    }
}
