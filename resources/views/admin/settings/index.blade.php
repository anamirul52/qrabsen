<x-layouts.app>
    <x-slot:title>Pengaturan Sekolah</x-slot:title>
    <x-slot:header>Pengaturan Profil &amp; Sistem Presensi</x-slot:header>

    <div class="max-w-2xl mx-auto space-y-4 sm:space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-2xs">
            <div class="pb-3 sm:pb-4 border-b border-gray-100 mb-4 sm:mb-5">
                <h2 class="text-sm sm:text-base font-bold text-gray-900">Identitas Sekolah &amp; Konfigurasi Presensi</h2>
                <p class="text-xs text-gray-500 mt-0.5">Konfigurasi aturan keterlambatan dan profil resmi SMP Negeri 2 Mijen</p>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-3.5 sm:space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Resmi Sekolah <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="school_name" value="{{ old('school_name', $settings['school_name'] ?? 'SMP NEGERI 2 MIJEN') }}" required
                           class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            NPSN <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="school_npsn" value="{{ old('school_npsn', $settings['school_npsn'] ?? '20317892') }}" required
                               class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg font-mono text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Batas Toleransi Terlambat (Menit) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" min="1" max="120" name="default_late_tolerance" value="{{ old('default_late_tolerance', $settings['default_late_tolerance'] ?? '15') }}" required
                               class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        <p class="text-[11px] text-gray-400 mt-1">Scan lewat dari menit ini dihitung TERLAMBAT secara otomatis oleh sistem.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                        Alamat Lengkap Sekolah <span class="text-red-500">*</span>
                    </label>
                    <textarea name="school_address" rows="2" required
                              class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">{{ old('school_address', $settings['school_address'] ?? 'Jl. Raya Mijen No. 45, Kec. Mijen, Demak, Jawa Tengah') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Nomor Telepon
                        </label>
                        <input type="text" name="school_phone" value="{{ old('school_phone', $settings['school_phone'] ?? '(0291) 685123') }}"
                               class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Email Resmi
                        </label>
                        <input type="email" name="school_email" value="{{ old('school_email', $settings['school_email'] ?? 'smpn2mijen@sekolah.id') }}"
                               class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex items-center justify-end pt-3 sm:pt-4 border-t border-gray-100">
                    <button type="submit"
                            class="py-2 px-4 inline-flex items-center gap-x-2 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
