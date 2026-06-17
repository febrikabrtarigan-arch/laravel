<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Admin ────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@kominfo-serdangbedagai.go.id'],
            [
                'name'      => 'Administrator Sistem',
                'nip'       => '19800101200901001',
                'email'     => 'admin@kominfo-serdangbedagai.go.id',
                'password'  => Hash::make('Admin@12345'),
                'role'      => 'admin',
                'jabatan'   => 'Kepala Bidang Infrastruktur',
                'no_hp'     => '081234567890',
                'is_active' => true,
            ]
        );

        // Tambahan Admin Simple
        User::updateOrCreate(
            ['email' => 'admin@simbar.com'],
            [
                'name'      => 'Admin Simple',
                'nip'       => '111111',
                'email'     => 'admin@simbar.com',
                'password'  => Hash::make('admin123'),
                'role'      => 'admin',
                'jabatan'   => 'Admin',
                'is_active' => true,
            ]
        );

        // ─── Staff Contoh ─────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'staff@kominfo-serdangbedagai.go.id'],
            [
                'name'      => 'Staff Inventaris',
                'nip'       => '19900202201001002',
                'email'     => 'staff@kominfo-serdangbedagai.go.id',
                'password'  => Hash::make('Staff@12345'),
                'role'      => 'staff',
                'jabatan'   => 'Staf Pengolah Data',
                'no_hp'     => '082345678901',
                'is_active' => true,
            ]
        );

        // Tambahan Staff Simple
        User::updateOrCreate(
            ['email' => 'staff@simbar.com'],
            [
                'name'      => 'Staff Simple',
                'nip'       => '222222',
                'email'     => 'staff@simbar.com',
                'password'  => Hash::make('staff123'),
                'role'      => 'staff',
                'jabatan'   => 'Staff',
                'is_active' => true,
            ]
        );

        // Tambahan Staff dengan nama & jabatan yang sangat panjang
        User::updateOrCreate(
            ['email' => 'long@simbar.com'],
            [
                'name'      => 'Raden Mas Haji Muhammad Syarifuddin Kartowijoyo Senopati Pamungkas Ningrat Ke-XVII',
                'nip'       => '19900202201001002-SUPER-LONG-NIP',
                'email'     => 'long@simbar.com',
                'password'  => Hash::make('Staff@12345'),
                'role'      => 'staff',
                'jabatan'   => 'Kepala Sub Bagian Pengendalian Administrasi, Monitoring, Evaluasi, dan Pelaporan Data Publik Kominfo',
                'no_hp'     => '089876543210',
                'is_active' => true,
            ]
        );

        // ─── Kategori Barang ──────────────────────────────────
        $kategoris = [
            ['nama_kategori' => 'Alat Tulis Kantor (ATK)',  'kode_kategori' => 'ATK', 'deskripsi' => 'Kertas, pulpen, stapler, dll.'],
            ['nama_kategori' => 'Perangkat Komputer',        'kode_kategori' => 'PKM', 'deskripsi' => 'PC, laptop, monitor, keyboard, mouse.'],
            ['nama_kategori' => 'Perangkat Jaringan',        'kode_kategori' => 'PJR', 'deskripsi' => 'Router, switch, kabel UTP, access point.'],
            ['nama_kategori' => 'Perangkat Elektronik',      'kode_kategori' => 'ELK', 'deskripsi' => 'Proyektor, printer, scanner, UPS.'],
            ['nama_kategori' => 'Furnitur & Perlengkapan',   'kode_kategori' => 'FRN', 'deskripsi' => 'Meja, kursi, lemari arsip, dll.'],
            ['nama_kategori' => 'Pengadaan Alat Tulis Kantor, Kertas, Dokumen, dan Perlengkapan Administrasi Perkantoran Skala Besar Kabupaten Serdang Bedagai',  'kode_kategori' => 'ATK-ADMINISTRASI-SUPER-LONG-CODE-TESTING', 'deskripsi' => 'Pengadaan ATK skala besar.'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::updateOrCreate(
                ['kode_kategori' => $kategori['kode_kategori']],
                $kategori
            );
        }

        // ─── Data Barang Contoh ───────────────────────────────
        $atk = Kategori::where('kode_kategori', 'ATK')->first();
        $pkm = Kategori::where('kode_kategori', 'PKM')->first();
        $longKat = Kategori::where('kode_kategori', 'ATK-ADMINISTRASI-SUPER-LONG-CODE-TESTING')->first();

        if ($atk && $pkm && $longKat) {
            \App\Models\Barang::updateOrCreate(
                ['kode_barang' => 'BRG-ATK-001'],
                [
                    'kategori_id' => $atk->id,
                    'nama_barang' => 'Kertas HVS A4 80gr',
                    'merk' => 'PaperOne',
                    'satuan' => 'rim',
                    'stok_saat_ini' => 50,
                    'stok_minimum' => 10,
                ]
            );

            \App\Models\Barang::updateOrCreate(
                ['kode_barang' => 'BRG-ATK-002'],
                [
                    'kategori_id' => $atk->id,
                    'nama_barang' => 'Pulpen Hitam',
                    'merk' => 'Snowman',
                    'satuan' => 'kotak',
                    'stok_saat_ini' => 15,
                    'stok_minimum' => 5,
                ]
            );

            \App\Models\Barang::updateOrCreate(
                ['kode_barang' => 'BRG-PKM-001'],
                [
                    'kategori_id' => $pkm->id,
                    'nama_barang' => 'Laptop Core i5',
                    'merk' => 'Lenovo Thinkpad',
                    'satuan' => 'unit',
                    'stok_saat_ini' => 3,
                    'stok_minimum' => 5,
                ]
            );

            \App\Models\Barang::updateOrCreate(
                ['kode_barang' => 'BRG-PKM-002'],
                [
                    'kategori_id' => $pkm->id,
                    'nama_barang' => 'Printer Inkjet Color',
                    'merk' => 'Epson L3110',
                    'satuan' => 'unit',
                    'stok_saat_ini' => 0,
                    'stok_minimum' => 2,
                ]
            );

            // Barang dengan nama, kode, dan stok sangat panjang
            \App\Models\Barang::updateOrCreate(
                ['kode_barang' => 'BRG-PKM-LAPTOP-ASUS-ROG-STRIX-SCAR-EXTREME-ULTIMATE-SUPER-LONG-CODE-TESTING'],
                [
                    'kategori_id' => $longKat->id,
                    'nama_barang' => 'Laptop Asus ROG Strix Scar Intel Core i9 Gen 14th 64GB DDR5 2TB SSD Dual Screen Super Pro Gaming Extreme Plus Ultimate Edition',
                    'merk' => 'Asus ROG',
                    'satuan' => 'unit-box-pro-ultimate-packaging',
                    'stok_saat_ini' => 15000000,
                    'stok_minimum' => 5000000,
                    'harga_satuan' => 120000000, // Rp 120.000.000 (untuk test FittedBox)
                ]
            );
        }
    }
}

