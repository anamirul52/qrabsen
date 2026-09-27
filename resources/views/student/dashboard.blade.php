<x-layouts.app>
    <x-slot:title>Dashboard Siswa</x-slot:title>
    <x-slot:header>Presensi Saya</x-slot:header>

    <div class="space-y-4 sm:space-y-6 max-w-4xl mx-auto">
        <!-- Preline Student Hero Banner -->
        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-900 rounded-2xl p-4 sm:p-7 text-white shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-full bg-white/20 text-white text-[11px] font-semibold mb-2 border border-white/20">
                    Kelas {{ $student->currentClass?->name ?? '-' }} • NIS {{ $student->nis }}
                </span>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight">
                    Halo, {{ $student->name }}!
                </h1>
                <p class="text-xs sm:text-sm text-blue-100 mt-1 max-w-md">
                    Tunjukkan Kartu QR pribadi Anda ke kamera guru pengajar saat jam pelajaran untuk mencatat kehadiran secara otomatis.
                </p>
            </div>

            <!-- Big QR Quick Button -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('student.qr') }}"
                   class="w-full sm:w-auto py-2.5 px-4 rounded-lg bg-white text-blue-700 font-bold text-xs sm:text-sm shadow-2xs hover:bg-blue-50 transition inline-flex items-center justify-center gap-2">
                    <i data-lucide="qr-code" class="w-4 h-4 text-blue-600"></i>
                    <span>Buka Kartu QR Saya</span>
                </a>
            </div>
        </div>

        <!-- Preline Attendance Stats Overview (360px Native) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4">
            <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-4 shadow-2xs">
                <div class="text-[10px] sm:text-[11px] font-semibold uppercase text-gray-500">Persentase Hadir</div>
                <div class="text-xl sm:text-3xl font-bold text-blue-600 mt-1">{{ $presentPercentage }}%</div>
                <div class="text-[10px] text-gray-400 mt-0.5">Dari total sesi</div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-4 shadow-2xs">
                <div class="text-[10px] sm:text-[11px] font-semibold uppercase text-gray-500">Hadir Tepat</div>
                <div class="text-xl sm:text-3xl font-bold text-emerald-600 mt-1">{{ $hadirCount }}</div>
                <div class="text-[10px] text-gray-400 mt-0.5">Sesi pembelajaran</div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-4 shadow-2xs">
                <div class="text-[10px] sm:text-[11px] font-semibold uppercase text-gray-500">Terlambat</div>
                <div class="text-xl sm:text-3xl font-bold text-amber-600 mt-1">{{ $lateCount }}</div>
                <div class="text-[10px] text-gray-400 mt-0.5">> Batas toleransi</div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-4 shadow-2xs">
                <div class="text-[10px] sm:text-[11px] font-semibold uppercase text-gray-500">Izin / Sakit / Alpa</div>
                <div class="text-xl sm:text-3xl font-bold text-gray-800 mt-1">
                    {{ $izinCount + $sakitCount }} <span class="text-xs text-rose-500 font-semibold">({{ $alpaCount }} Alpa)</span>
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5">Ketidakhadiran</div>
            </div>
        </div>

        <!-- Today's Attendance Activity -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-2xs">
            <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider mb-3.5 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>Status Kehadiran Hari Ini</span>
            </h3>

            @if ($todayAttendances->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <i data-lucide="clock" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                    <p class="text-xs text-gray-500">Belum ada catatan presensi untuk hari ini.</p>
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach ($todayAttendances as $att)
                        <div class="p-3 rounded-lg border border-gray-200 bg-gray-50/50 flex items-center justify-between gap-2">
                            <div class="min-w-0 pr-2">
                                <div class="font-bold text-gray-900 text-xs sm:text-sm truncate">
                                    {{ $att->attendanceSession?->subject?->name }}
                                </div>
                                <div class="text-[11px] text-gray-500 mt-0.5 truncate">
                                    Guru: {{ $att->attendanceSession?->teacher?->name }} • Jam: <span class="font-mono text-gray-700 font-medium">{{ $att->scanned_at ? $att->scanned_at->format('H:i:s') : '-' }} WIB</span>
                                </div>
                            </div>

                            <span class="py-0.5 px-2 rounded-full text-[10px] font-bold uppercase tracking-wider shrink-0
                                {{ $att->status === 'HADIR' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $att->status === 'TERLAMBAT' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $att->status === 'IZIN' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $att->status === 'SAKIT' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $att->status === 'ALPA' ? 'bg-rose-100 text-rose-800' : '' }}">
                                {{ $att->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
