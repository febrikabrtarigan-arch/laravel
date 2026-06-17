<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #000; line-height: 1.5; }
        
        /* Kop Surat */
        .header { margin-bottom: 20px; text-align: center; }
        .header-table { width: 100%; border-collapse: collapse; }
        .logo-td { width: 90px; text-align: center; vertical-align: middle; }
        .kop-text { text-align: center; vertical-align: middle; }
        .instansi { font-size: 16px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .departemen { font-size: 18px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .alamat { font-size: 11px; margin: 4px 0 0 0; }
        .garis-kop { border-bottom: 3px solid #000; margin-top: 10px; border-top: 1px solid #000; height: 2px; }

        /* Typography & Utility */
        .title { text-align: center; font-size: 14px; font-weight: bold; text-decoration: underline; margin-top: 10px; margin-bottom: 5px; text-transform: uppercase; }
        .subtitle { text-align: center; font-size: 11px; margin-bottom: 20px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        
        /* Table Styles */
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px; }
        .content-table th { background-color: #2c3e50; color: #fff; border: 1px solid #000; padding: 6px; text-align: center; text-transform: uppercase; }
        .content-table td { border: 1px solid #000; padding: 6px; vertical-align: middle; }
        .content-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        .info-table td { border: 1px solid #ddd; padding: 6px; }
        .info-table td.label { font-weight: bold; background-color: #f5f5f5; width: 150px; }
        
        /* Tanda Tangan */
        .footer-ttd { width: 100%; margin-top: 40px; font-size: 11px; }
        .ttd-table { width: 100%; text-align: center; border: none; }
        .ttd-table td { border: none; padding: 0; vertical-align: top; width: 50%; }
        .ttd-space { height: 60px; }
        .ttd-nama { font-weight: bold; text-decoration: underline; margin: 0; }
        .ttd-nip { margin: 0; }

        /* Page Footer */
        .page-footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 9px; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                @if($template['show_logo'] && $template['logo_path'])
                <td class="logo-td">
                    <img src="{{ public_path('storage/' . $template['logo_path']) }}" style="height: {{ $template['logo_size'] }}px">
                </td>
                @endif
                <td class="kop-text">
                    <p class="instansi">{{ $template['instansi_nama'] }}</p>
                    <p class="departemen">{{ $template['departemen_nama'] }}</p>
                    <p class="alamat">{!! nl2br(e($template['alamat'])) !!}</p>
                </td>
            </tr>
        </table>
        <div class="garis-kop"></div>
    </div>

    @yield('content')

    <div class="footer-ttd">
        <table class="ttd-table">
            <tr>
                <td>
                    <p>Yang Menyerahkan,</p>
                    <p>Pengelola Inventaris</p>
                    <div class="ttd-space"></div>
                    <p class="ttd-nama">{{ auth()->check() ? auth()->user()->name : 'Admin / Staff' }}</p>
                    <p class="ttd-nip">NIP. {{ auth()->check() ? auth()->user()->nip : '-' }}</p>
                </td>
                <td>
                    <p>Mengetahui,</p>
                    <p>{{ $template['ttd_jabatan'] ?: 'Kepala Dinas' }}</p>
                    <div class="ttd-space"></div>
                    <p class="ttd-nama">{{ $template['ttd_nama'] ?: '________________________' }}</p>
                    <p class="ttd-nip">NIP. {{ $template['ttd_nip'] ?: '________________' }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i:s') }} WIB <br>
        Sistem Inventaris {{ ucwords(strtolower($template['departemen_nama'])) }} v1.0
    </div>
</body>
</html>
