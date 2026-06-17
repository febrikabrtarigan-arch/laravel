<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponse;

class SettingController extends Controller
{
    use ApiResponse;

    /**
     * Get template settings
     */
    public function getTemplate()
    {
        $template = Setting::getByKey('report_template', [
            'instansi_nama' => 'PEMERINTAH KABUPATEN SERDANG BEDAGAI',
            'departemen_nama' => 'DINAS KOMUNIKASI DAN INFORMATIKA',
            'alamat' => "Jalan Negara No. 300 Sei Rampah, Serdang Bedagai, Sumatera Utara 20995\nTelepon 0621 - 442135, www.serdangbedagaikab.go.id\nPos-el diskominfo@serdangbedagaikab.go.id",
            'ttd_nama' => '',
            'ttd_nip' => '',
            'ttd_jabatan' => '',
            'ttd_kiri_nama' => '',
            'ttd_kiri_nip' => '',
            'ttd_kiri_jabatan' => 'Pengelola Inventaris',
            'logo_path' => null,
            'show_logo' => true,
            'logo_size' => 80,
        ]);

        return $this->successResponse($template, 'Pengaturan template berhasil diambil.');
    }

    /**
     * Update template settings
     */
    public function updateTemplate(Request $request)
    {
        $current = Setting::getByKey('report_template', []);

        $data = $request->validate([
            'instansi_nama' => 'required|string|max:255',
            'departemen_nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'ttd_nama' => 'nullable|string|max:255',
            'ttd_nip' => 'nullable|string|max:255',
            'ttd_jabatan' => 'nullable|string|max:255',
            'ttd_kiri_nama' => 'nullable|string|max:255',
            'ttd_kiri_nip' => 'nullable|string|max:255',
            'ttd_kiri_jabatan' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,png,svg|max:2048',
            'show_logo' => 'nullable|boolean',
            'logo_size' => 'nullable|integer|min:20|max:500',
        ]);

        // Handle Logo Upload via API
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if (isset($current['logo_path']) && Storage::disk('public')->exists($current['logo_path'])) {
                Storage::disk('public')->delete($current['logo_path']);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $path;
        } else {
            // Jika dikirim string kosong untuk hapus logo? (optional, saat ini kita pertahankan logo lama jika tidak di-upload baru)
            $data['logo_path'] = $current['logo_path'] ?? null;
        }

        // Clean up data for storage
        unset($data['logo']);

        // Convert boolean-like values from frontend
        if ($request->has('show_logo')) {
            $val = $request->show_logo;
            $data['show_logo'] = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        } else {
            $data['show_logo'] = $current['show_logo'] ?? true;
        }

        Setting::setByKey('report_template', $data);

        return $this->successResponse($data, 'Pengaturan template berhasil diperbarui.');
    }
}
