<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan template.
     */
    public function index()
    {
        $template = Setting::getByKey('report_template', [
            'instansi_nama' => 'PEMERINTAH KABUPATEN SERDANG BEDAGAI',
            'departemen_nama' => 'DINAS KOMUNIKASI DAN INFORMATIKA',
            'alamat' => '',
            'ttd_nama' => '',
            'ttd_nip' => '',
            'ttd_jabatan' => '',
            'logo_path' => null,
            'show_logo' => true,
            'logo_size' => 80,
        ]);

        return view('settings.index', compact('template'));
    }

    /**
     * Simpan perubahan pengaturan.
     */
    public function update(Request $request)
    {
        $current = Setting::getByKey('report_template', []);
        
        $data = $request->validate([
            'instansi_nama' => 'required|string|max:255',
            'departemen_nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'ttd_nama' => 'nullable|string|max:255',
            'ttd_nip' => 'nullable|string|max:255',
            'ttd_jabatan' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,png,svg|max:2048',
            'show_logo' => 'nullable|boolean',
            'logo_size' => 'nullable|integer|min:20|max:500',
        ]);

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if (isset($current['logo_path'])) {
                Storage::disk('public')->delete($current['logo_path']);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $path;
        } else {
            $data['logo_path'] = $current['logo_path'] ?? null;
        }

        // Clean up data for storage (remove the file object)
        unset($data['logo']);
        
        // Ensure boolean for show_logo
        $data['show_logo'] = $request->has('show_logo');

        Setting::setByKey('report_template', $data);

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
