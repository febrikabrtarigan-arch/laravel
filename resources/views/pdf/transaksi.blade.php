@extends('pdf.layout')

@section('content')
    <div class="text-right" style="margin-bottom: 5px;">
        Sei Rampah, {{ now()->translatedFormat('d F Y') }}
    </div>

    <div class="title">LAPORAN TRANSAKSI BARANG</div>
    
    @if(isset($filters['dari']) && isset($filters['sampai']))
    <div class="subtitle">
        Periode: {{ \Carbon\Carbon::parse($filters['dari'])->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($filters['sampai'])->translatedFormat('d F Y') }}
        @if(isset($filters['jenis'])) | Jenis: {{ ucfirst($filters['jenis']) }} @endif
    </div>
    @elseif(isset($filters['jenis']))
    <div class="subtitle">
        Jenis Transaksi: {{ ucfirst($filters['jenis']) }}
    </div>
    @endif

    <table class="content-table">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th>TANGGAL</th>
                <th>KODE BARANG</th>
                <th>NAMA BARANG</th>
                <th>JENIS</th>
                <th>JUMLAH</th>
                <th>SATUAN</th>
                <th>HARGA SATUAN</th>
                <th>TOTAL HARGA</th>
                <th>OPERATOR</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksis as $index => $t)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($t->tanggal_transaksi)->format('d/m/Y') }}</td>
                <td class="text-center">{{ $t->barang?->kode_barang ?? '-' }}</td>
                <td>{{ $t->barang?->nama_barang ?? 'Barang Terhapus' }}</td>
                <td class="text-center">{{ ucfirst($t->jenis_transaksi) }}</td>
                <td class="text-center">{{ number_format($t->jumlah) }}</td>
                <td class="text-center">{{ $t->barang?->satuan ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($t->barang?->harga_satuan ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format(($t->jumlah * ($t->barang?->harga_satuan ?? 0)), 0, ',', '.') }}</td>
                <td>{{ $t->user?->name ?? 'User Terhapus' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px;">Tidak ada data transaksi.</td>
            </tr>
            @endforelse
            <tr>
                <td colspan="5" class="text-right text-bold" style="background-color: #f2f2f2;">TOTAL TRANSAKSI:</td>
                <td colspan="5" class="text-left text-bold" style="background-color: #f2f2f2;">
                    Masuk: {{ number_format($ringkasan['total_masuk']) }} | Keluar: {{ number_format($ringkasan['total_keluar']) }}
                </td>
            </tr>
            <tr>
                <td colspan="5" class="text-right text-bold" style="background-color: #f2f2f2;">TOTAL NILAI TRANSAKSI:</td>
                <td colspan="5" class="text-left text-bold" style="background-color: #f2f2f2;">
                    Masuk: Rp {{ number_format($ringkasan['nilai_masuk'] ?? 0, 0, ',', '.') }} | Keluar: Rp {{ number_format($ringkasan['nilai_keluar'] ?? 0, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
@endsection
