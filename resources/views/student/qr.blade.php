<x-layouts.app>
    <x-slot:title>QR Presensi Saya</x-slot:title>
    <x-slot:header>QR Code Presensi Pribadi</x-slot:header>

    <div x-data="{ fullscreen: false }" class="max-w-md mx-auto space-y-4 sm:space-y-6">
        
        <!-- Preline Main QR Card -->
        <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-7 shadow-2xs text-center">
            
            <div class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold mb-4 border border-blue-200/60">
                <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i>
                <span>QR CODE RESMI SIPRES</span>
            </div>

            <!-- Big QR Display Container (360px Native fit) -->
            <div class="p-3 sm:p-4 bg-white border-2 border-gray-900 rounded-2xl mx-auto w-56 h-56 sm:w-64 sm:h-64 flex items-center justify-center shadow-xs">
                <div class="w-full h-full flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                    {!! $qrSvg !!}
                </div>
            </div>

            <!-- Student Profile Information -->
            <div class="mt-4 sm:mt-5 space-y-1">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 leading-tight">
                    {{ $student->name }}
                </h2>
                <div class="text-xs font-medium text-gray-500">
                    Kelas <strong class="text-gray-900">{{ $student->currentClass?->name ?? '-' }}</strong> • NISN: <span class="font-mono font-bold text-blue-600">{{ $student->nisn ?: $student->nis }}</span> • <span>{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                </div>
                <div class="pt-1.5">
                    <span class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-full bg-emerald-50 text-emerald-800 text-xs font-semibold border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Siswa Aktif</span>
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-center gap-2">
                <button type="button" @click="fullscreen = true"
                        class="py-2 px-3.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-2xs transition inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                    <span>Layar Penuh</span>
                </button>

                <button type="button" onclick="window.print()"
                        class="py-2 px-3.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs sm:text-sm font-semibold transition inline-flex items-center gap-1.5 shadow-2xs cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
                    <span>Cetak Kartu</span>
                </button>
            </div>

            <!-- Safety Note -->
            <p class="text-[11px] text-gray-400 mt-4 leading-relaxed">
                <i data-lucide="lock" class="w-3.5 h-3.5 inline mr-1 text-gray-400"></i>
                QR code ini hanya menyimpan token terenkripsi sistem dan tidak menyebarkan data pribadi Anda. Tunjukkan layar HP Anda tepat di depan kamera guru saat presensi.
            </p>
        </div>

        <!-- Fullscreen Mode Overlay (Modal) -->
        <div x-show="fullscreen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             class="fixed inset-0 z-50 bg-white flex flex-col items-center justify-between p-4 sm:p-8 select-none"
             style="display: none;">
            
            <div class="w-full flex items-center justify-between">
                <div>
                    <div class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider">SMP NEGERI 2 MIJEN</div>
                    <div class="text-base sm:text-lg font-bold text-gray-900">{{ $student->name }}</div>
                </div>
                <button type="button" @click="fullscreen = false"
                        class="py-2 px-3 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 font-semibold flex items-center gap-1 text-xs sm:text-sm cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    <span>Tutup</span>
                </button>
            </div>

            <!-- Huge QR in Fullscreen (360px safe) -->
            <div class="p-4 sm:p-6 bg-white border-4 border-gray-950 rounded-2xl w-64 h-64 sm:w-80 sm:h-80 flex items-center justify-center shadow-xl [&>svg]:w-full [&>svg]:h-full my-auto">
                {!! $qrSvg !!}
            </div>

            <div class="text-center pb-2">
                <div class="text-xs sm:text-sm font-bold text-gray-900">
                    Kelas {{ $student->currentClass?->name ?? '-' }} • NIS {{ $student->nis }}
                </div>
                <div class="text-[11px] text-gray-500 mt-0.5">
                    Arahkan ke kamera scanner pengajar
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
