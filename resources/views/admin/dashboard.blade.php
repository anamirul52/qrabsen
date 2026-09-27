<x-layouts.app>
    <x-slot:title>Dashboard Administrator</x-slot:title>
    <x-slot:header>Ringkasan Sistem Presensi</x-slot:header>

    <div class="space-y-4 sm:space-y-6">
        <!-- Preline Announcement Hero Banner -->
        <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-2xl p-4 sm:p-6 text-white shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-x-2 py-1 px-2.5 rounded-full bg-white/10 text-white text-[11px] font-medium mb-2 border border-white/15">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Tahun Pelajaran 2025/2026 - Semester Ganjil</span>
                </div>
                <h2 class="text-lg sm:text-2xl font-bold tracking-tight">
                    SIPRES SMP NEGERI 2 MIJEN
                </h2>
                <p class="text-xs sm:text-sm text-blue-100 mt-1 max-w-xl">
                    Sistem pemantauan kehadiran siswa berbasis QR Code sekolah secara otomatis, real-time, dan terintegrasi Excel.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.attendance.index') }}"
                   class="py-2 px-3 sm:px-4 inline-flex items-center gap-x-2 text-xs sm:text-sm font-semibold rounded-lg bg-white text-blue-700 hover:bg-blue-50 shadow-2xs transition">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    <span>Lihat Rekap</span>
                </a>
                <a href="{{ route('admin.qr-cards.index') }}"
                   class="py-2 px-3 sm:px-4 inline-flex items-center gap-x-2 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600/80 hover:bg-blue-600 border border-white/20 text-white shadow-2xs transition">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span class="hidden xs:inline">Cetak Kartu</span>
                </a>
            </div>
        </div>

        <!-- Preline Stat Cards (KPI Metrics) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
            <!-- Total Siswa -->
            <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs">
                <div class="flex items-center justify-between text-gray-500 mb-2">
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-gray-500">Total Siswa</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-3xl font-bold text-gray-900">{{ number_format($totalStudents) }}</div>
                <div class="text-[10px] sm:text-[11px] text-gray-400 mt-1">Siswa aktif terdaftar</div>
            </div>

            <!-- Total Guru -->
            <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs">
                <div class="flex items-center justify-between text-gray-500 mb-2">
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-gray-500">Total Guru</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-3xl font-bold text-gray-900">{{ number_format($totalTeachers) }}</div>
                <div class="text-[10px] sm:text-[11px] text-gray-400 mt-1">Tenaga pendidik</div>
            </div>

            <!-- Total Kelas -->
            <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs">
                <div class="flex items-center justify-between text-gray-500 mb-2">
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-gray-500">Total Kelas</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="school" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-3xl font-bold text-gray-900">{{ number_format($totalClasses) }}</div>
                <div class="text-[10px] sm:text-[11px] text-gray-400 mt-1">Rombongan belajar</div>
            </div>

            <!-- Total Presensi Hari Ini -->
            <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs">
                <div class="flex items-center justify-between text-gray-500 mb-2">
                    <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-gray-500">Presensi Hari Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-3xl font-bold text-gray-900">{{ number_format($totalHadirToday) }}</div>
                <div class="text-[10px] sm:text-[11px] text-gray-500 mt-1 flex items-center gap-1 truncate">
                    <span class="text-emerald-600 font-semibold">{{ $hadirToday }} Tepat</span>
                    <span>•</span>
                    <span class="text-amber-600 font-semibold">{{ $terlambatToday }} Terlambat</span>
                </div>
            </div>
        </div>

        <!-- Preline Status Kehadiran Hari Ini Breakdown -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-3.5">
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-blue-600"></i>
                    <span>Status Kehadiran Siswa Hari Ini</span>
                </h3>
                <span class="text-[11px] text-gray-400 hidden sm:inline">Diperbarui real-time</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 sm:gap-3 text-center">
                <div class="p-3 rounded-lg bg-emerald-50/70 border border-emerald-100">
                    <div class="text-[11px] font-semibold text-emerald-800">HADIR</div>
                    <div class="text-lg sm:text-2xl font-bold text-emerald-700 mt-1">{{ $hadirToday }}</div>
                    <div class="text-[10px] text-emerald-600 mt-0.5">Tepat Waktu</div>
                </div>
                <div class="p-3 rounded-lg bg-amber-50/70 border border-amber-100">
                    <div class="text-[11px] font-semibold text-amber-800">TERLAMBAT</div>
                    <div class="text-lg sm:text-2xl font-bold text-amber-700 mt-1">{{ $terlambatToday }}</div>
                    <div class="text-[10px] text-amber-600 mt-0.5">> Batas Toleransi</div>
                </div>
                <div class="p-3 rounded-lg bg-blue-50/70 border border-blue-100">
                    <div class="text-[11px] font-semibold text-blue-800">IZIN</div>
                    <div class="text-lg sm:text-2xl font-bold text-blue-700 mt-1">{{ $izinToday }}</div>
                    <div class="text-[10px] text-blue-600 mt-0.5">Keterangan Izin</div>
                </div>
                <div class="p-3 rounded-lg bg-purple-50/70 border border-purple-100">
                    <div class="text-[11px] font-semibold text-purple-800">SAKIT</div>
                    <div class="text-lg sm:text-2xl font-bold text-purple-700 mt-1">{{ $sakitToday }}</div>
                    <div class="text-[10px] text-purple-600 mt-0.5">Surat Dokter</div>
                </div>
                <div class="p-3 rounded-lg bg-rose-50/70 border border-rose-100 col-span-2 sm:col-span-1">
                    <div class="text-[11px] font-semibold text-rose-800">ALPA</div>
                    <div class="text-lg sm:text-2xl font-bold text-rose-700 mt-1">{{ $alpaToday }}</div>
                    <div class="text-[10px] text-rose-600 mt-0.5">Tanpa Keterangan</div>
                </div>
            </div>
        </div>

        <!-- 2 Column Section: Active Sessions & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Active Sessions Ongoing -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 shadow-2xs flex flex-col">
                <div class="flex items-center justify-between mb-3.5">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span>Sesi Presensi Aktif</span>
                    </h3>
                    <span class="py-0.5 px-2 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                        {{ $activeSessions->count() }} Berlangsung
                    </span>
                </div>

                <div class="space-y-2.5 flex-1">
                    @forelse ($activeSessions as $act)
                        <div class="p-3 rounded-lg border border-emerald-200 bg-emerald-50/50 flex items-center justify-between gap-2">
                            <div class="min-w-0 pr-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900 text-xs sm:text-sm">Kelas {{ $act->schoolClass->name }}</span>
                                    <span class="py-0.5 px-2 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-semibold">Aktif</span>
                                </div>
                                <div class="text-xs text-gray-600 mt-1 truncate">
                                    {{ $act->subject->name }} • {{ $act->teacher->name }}
                                </div>
                                <div class="text-[10px] text-gray-400 mt-0.5">
                                    Dibuka: {{ $act->opened_at ? $act->opened_at->format('H:i') : '-' }} WIB
                                </div>
                            </div>
                            <span class="py-1 px-2.5 rounded-lg bg-white border border-emerald-200 text-emerald-700 text-xs font-semibold shrink-0 shadow-2xs">
                                Scanner ON
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <i data-lucide="camera-off" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                            <p class="text-xs">Tidak ada sesi presensi yang sedang aktif saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Attendance Activity -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 shadow-2xs flex flex-col">
                <div class="flex items-center justify-between mb-3.5">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4 text-blue-600"></i>
                        <span>Aktivitas Scan Terbaru</span>
                    </h3>
                    <a href="{{ route('admin.attendance.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="space-y-2 flex-1 overflow-y-auto max-h-72 no-scrollbar">
                    @forelse ($recentRecords as $rec)
                        <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-between text-xs gap-2">
                            <div class="min-w-0 pr-2">
                                <div class="font-semibold text-gray-900 truncate">{{ $rec->student?->name }}</div>
                                <div class="text-gray-500 text-[11px] truncate">
                                    Kelas {{ $rec->student?->currentClass?->name }} • NIS: {{ $rec->student?->nis }}
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="py-0.5 px-2 rounded-full font-bold text-[10px] {{ $rec->status === 'HADIR' ? 'bg-emerald-100 text-emerald-800' : ($rec->status === 'TERLAMBAT' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $rec->status }}
                                </span>
                                <div class="text-[10px] text-gray-400 mt-0.5 font-mono">
                                    {{ $rec->scanned_at ? $rec->scanned_at->format('H:i:s') : $rec->created_at->format('H:i:s') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <i data-lucide="inbox" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                            <p class="text-xs">Belum ada aktivitas presensi tercatat hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Preline Quick Actions Grid -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 shadow-2xs">
            <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider mb-3.5">
                Aksi Cepat Administrator
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
                <a href="{{ route('admin.students.create') }}"
                   class="p-3 sm:p-4 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-blue-50/60 hover:border-blue-300 transition text-start group shadow-2xs">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center mb-2.5 group-hover:scale-105 transition-transform">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </div>
                    <div class="text-xs font-bold text-gray-900">Tambah Siswa</div>
                    <div class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5">Pendaftaran siswa baru</div>
                </a>
                <a href="{{ route('admin.qr-cards.index') }}"
                   class="p-3 sm:p-4 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-blue-50/60 hover:border-blue-300 transition text-start group shadow-2xs">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center mb-2.5 group-hover:scale-105 transition-transform">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                    </div>
                    <div class="text-xs font-bold text-gray-900">Cetak Kartu QR</div>
                    <div class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5">Format cetak kartu A4</div>
                </a>
                <a href="{{ route('admin.attendance.export') }}"
                   class="p-3 sm:p-4 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-blue-50/60 hover:border-blue-300 transition text-start group shadow-2xs">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center mb-2.5 group-hover:scale-105 transition-transform">
                        <i data-lucide="download" class="w-4 h-4"></i>
                    </div>
                    <div class="text-xs font-bold text-gray-900">Ekspor Presensi</div>
                    <div class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5">Format Excel (.xlsx)</div>
                </a>
                <a href="{{ route('admin.audit-logs.index') }}"
                   class="p-3 sm:p-4 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-blue-50/60 hover:border-blue-300 transition text-start group shadow-2xs">
                    <div class="w-8 h-8 rounded-lg bg-gray-200 text-gray-700 flex items-center justify-center mb-2.5 group-hover:scale-105 transition-transform">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <div class="text-xs font-bold text-gray-900">Audit Log</div>
                    <div class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5">Catatan riwayat sistem</div>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
