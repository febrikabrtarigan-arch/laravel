<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Template Laporan - Laravel Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="flex min-h-screen bg-[#f3f4f6]">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col">
        <div class="p-6 flex items-center gap-3">
            <div class="bg-indigo-600 p-2 rounded-lg text-white">
                <i class="fas fa-boxes"></i>
            </div>
            <div>
                <h1 class="font-bold text-gray-900 leading-tight">Laravel</h1>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest">Management System</p>
            </div>
        </div>
        
        <nav class="flex-1 px-4 py-4 space-y-1">
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-th-large w-5"></i> Dashboard
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-box w-5"></i> Data Barang
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-exchange-alt w-5"></i> Data Transaksi
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-tags w-5"></i> Kategori
            </a>
            <div class="pt-4">
                <p class="px-4 text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Laporan</p>
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-file-alt w-5"></i> Stok
                </a>
            </div>
            <div class="pt-4">
                <p class="px-4 text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Pengaturan</p>
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-2 bg-indigo-50 text-indigo-700 font-medium rounded-lg border border-indigo-100">
                    <i class="fas fa-cog w-5"></i> Template Laporan
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="w-10 h-10 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold">A</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">Admin</p>
                    <p class="text-xs text-gray-500 truncate">Administrator</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <i class="fas fa-home"></i>
                <span>/</span>
                <span>Laporan</span>
                <span>/</span>
                <span class="text-gray-900 font-medium">Pengaturan Template</span>
            </div>
            <div class="flex items-center gap-4">
                <button class="relative p-2 text-gray-500 hover:bg-gray-100 rounded-full">
                    <i class="far fa-bell text-lg"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                </button>
            </div>
        </header>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-6xl mx-auto">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Pengaturan Template Laporan & PDF</h2>
                        <p class="text-gray-500 mt-1">Kelola template, logo, dan format laporan PDF</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" form="settingsForm" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-semibold shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan Semua Perubahan
                        </button>
                        <a href="#" class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-2.5 rounded-xl font-semibold border border-gray-200 transition-all flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i> Kembali ke Laporan
                        </a>
                    </div>
                </div>

                @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Tabs Sidebar -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                            <button onclick="switchTab('logo')" id="tab-logo" class="tab-btn w-full text-left px-6 py-4 flex items-center gap-4 transition-all bg-indigo-600 text-white font-semibold">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <i class="fas fa-image"></i>
                                </div>
                                <div>
                                    <p class="text-sm">Logo Instansi</p>
                                    <p class="text-[10px] opacity-80 font-normal">Logo untuk kop surat</p>
                                </div>
                            </button>
                            <button onclick="switchTab('kop')" id="tab-kop" class="tab-btn w-full text-left px-6 py-4 flex items-center gap-4 transition-all text-gray-600 hover:bg-gray-50 border-t border-gray-100">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Kop Surat</p>
                                    <p class="text-[10px] text-gray-500">Data instansi dan departemen</p>
                                </div>
                            </button>
                            <button onclick="switchTab('ttd')" id="tab-ttd" class="tab-btn w-full text-left px-6 py-4 flex items-center gap-4 transition-all text-gray-600 hover:bg-gray-50 border-t border-gray-100">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">
                                    <i class="fas fa-signature"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Tanda Tangan</p>
                                    <p class="text-[10px] text-gray-500">Penanggung jawab laporan</p>
                                </div>
                            </button>
                            <button onclick="switchTab('format')" id="tab-format" class="tab-btn w-full text-left px-6 py-4 flex items-center gap-4 transition-all text-gray-600 hover:bg-gray-50 border-t border-gray-100">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">
                                    <i class="fas fa-sliders-h"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Format Laporan</p>
                                    <p class="text-[10px] text-gray-500">Pengaturan format PDF</p>
                                </div>
                            </button>
                        </div>

                        <div class="mt-8 p-6 bg-indigo-900 rounded-2xl text-white shadow-xl shadow-indigo-200">
                            <h4 class="font-bold mb-2">Tips & Info</h4>
                            <p class="text-xs text-indigo-200 leading-relaxed">Gunakan logo dengan format PNG transparan untuk hasil terbaik di file PDF.</p>
                        </div>
                    </div>

                    <!-- Main Form -->
                    <div class="lg:col-span-9">
                        <form id="settingsForm" action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Tab: Logo Instansi -->
                            <div id="content-logo" class="tab-content space-y-6">
                                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="px-8 py-6 border-b border-gray-100 flex items-center gap-3">
                                        <i class="fas fa-image text-indigo-600"></i>
                                        <h3 class="font-bold text-gray-900">Logo Instansi</h3>
                                    </div>
                                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-12">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-4">Logo Kop Surat</label>
                                            <div class="relative group">
                                                <div class="w-full h-64 border-2 border-dashed border-gray-200 rounded-2xl flex flex-col items-center justify-center bg-gray-50 transition-all group-hover:border-indigo-300 group-hover:bg-indigo-50/30 overflow-hidden">
                                                    @if($template['logo_path'])
                                                        <img src="{{ asset('storage/' . $template['logo_path']) }}" class="max-h-48 object-contain">
                                                    @else
                                                        <i class="fas fa-building text-5xl text-gray-300 mb-4"></i>
                                                        <p class="text-sm text-gray-500 font-medium">Belum ada logo</p>
                                                    @endif
                                                    <input type="file" name="logo" class="absolute inset-0 opacity-0 cursor-pointer">
                                                </div>
                                                <div class="mt-4 flex items-center justify-center">
                                                    <button type="button" class="text-indigo-600 font-semibold text-sm flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-all">
                                                        <i class="fas fa-upload"></i> Upload Logo
                                                    </button>
                                                </div>
                                                <p class="text-[11px] text-gray-400 mt-4 text-center">Format: JPG, PNG, SVG | Max: 2MB | Rekomendasi: 200×200px</p>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-4">Preview Kop Surat</label>
                                            <div class="p-6 border border-gray-100 rounded-2xl bg-white shadow-inner min-h-[160px] flex items-center justify-center">
                                                <div class="flex items-center gap-4 text-center">
                                                    @if($template['logo_path'])
                                                        <img src="{{ asset('storage/' . $template['logo_path']) }}" style="height: {{ $template['logo_size'] }}px">
                                                    @endif
                                                    <div class="text-left">
                                                        <p class="font-bold text-gray-900 uppercase leading-tight">{{ $template['instansi_nama'] }}</p>
                                                        <p class="font-bold text-gray-900 uppercase leading-tight">{{ $template['departemen_nama'] }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-8 space-y-6">
                                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-900">Tampilkan Logo</p>
                                                        <p class="text-xs text-gray-500">Tampilkan logo pada PDF</p>
                                                    </div>
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" name="show_logo" value="1" {{ $template['show_logo'] ? 'checked' : '' }} class="sr-only peer">
                                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                                    </label>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ukuran Logo (px)</label>
                                                    <input type="number" name="logo_size" value="{{ $template['logo_size'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Kop Surat -->
                            <div id="content-kop" class="tab-content hidden space-y-6">
                                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="px-8 py-6 border-b border-gray-100 flex items-center gap-3">
                                        <i class="fas fa-building text-indigo-600"></i>
                                        <h3 class="font-bold text-gray-900">Data Kop Surat</h3>
                                    </div>
                                    <div class="p-8 space-y-6">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Instansi</label>
                                            <input type="text" name="instansi_nama" value="{{ $template['instansi_nama'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" placeholder="Contoh: Pemerintah Kabupaten Serdang Bedagai">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Departemen / Dinas</label>
                                            <input type="text" name="departemen_nama" value="{{ $template['departemen_nama'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" placeholder="Contoh: Dinas Komunikasi dan Informatika">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Lengkap (Ditampilkan di bawah Kop)</label>
                                            <textarea name="alamat" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" placeholder="Masukkan alamat lengkap, email, dan website...">{{ $template['alamat'] }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Tanda Tangan -->
                            <div id="content-ttd" class="tab-content hidden space-y-6">
                                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="px-8 py-6 border-b border-gray-100 flex items-center gap-3">
                                        <i class="fas fa-signature text-indigo-600"></i>
                                        <h3 class="font-bold text-gray-900">Pengaturan Tanda Tangan</h3>
                                    </div>
                                    <div class="p-8 space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Pejabat</label>
                                                <input type="text" name="ttd_nama" value="{{ $template['ttd_nama'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" placeholder="Nama Lengkap & Gelar">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">NIP / ID</label>
                                                <input type="text" name="ttd_nip" value="{{ $template['ttd_nip'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" placeholder="Nomor Induk Pegawai">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                                            <input type="text" name="ttd_jabatan" value="{{ $template['ttd_jabatan'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" placeholder="Contoh: Kepala Dinas">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Format -->
                            <div id="content-format" class="tab-content hidden space-y-6">
                                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="px-8 py-6 border-b border-gray-100 flex items-center gap-3">
                                        <i class="fas fa-sliders-h text-indigo-600"></i>
                                        <h3 class="font-bold text-gray-900">Format PDF</h3>
                                    </div>
                                    <div class="p-8">
                                        <p class="text-gray-500 text-sm">Fitur pengaturan margin, ukuran kertas (A4/F4), dan warna tema PDF akan tersedia pada update berikutnya.</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            const target = document.getElementById('content-' + tabId);
            if (target) target.classList.remove('hidden');
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-indigo-600', 'text-white', 'font-semibold');
                btn.classList.add('text-gray-600');
                const icon = btn.querySelector('.rounded-xl');
                if (icon) {
                    icon.classList.remove('bg-white/20');
                    icon.classList.add('bg-gray-100');
                }
            });
            
            const active = document.getElementById('tab-' + tabId);
            if (active) {
                active.classList.add('bg-indigo-600', 'text-white', 'font-semibold');
                active.classList.remove('text-gray-600');
                const activeIcon = active.querySelector('.rounded-xl');
                if (activeIcon) {
                    activeIcon.classList.add('bg-white/20');
                    activeIcon.classList.remove('bg-gray-100');
                }
            }
        }
    </script>
</body>
</html>
