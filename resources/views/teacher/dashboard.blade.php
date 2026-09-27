<x-layouts.app>
    <x-slot:title>Dashboard Guru</x-slot:title>
    <x-slot:header>Presensi &amp; Jadwal Mengajar</x-slot:header>

    <div class="space-y-4 sm:space-y-6">
        <!-- Action Highlight: Ongoing Active Session (Preline Hero Alert) -->
        @if ($activeSession)
            <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 rounded-2xl p-4 sm:p-6 text-white shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
                <div>
                    <div class="inline-flex items-center gap-x-2 py-0.5 px-2.5 rounded-full bg-white/20 text-white text-[11px] font-semibold mb-2 border border-white/20">
                        <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                        <span>SESI PRESENSI AKTIF</span>
                    </div>
                    <h2 class="text-lg sm:text-2xl font-bold tracking-tight">
                        Kelas {{ $activeSession->schoolClass->name }} — {{ $activeSession->subject->name }}
                    </h2>
                    <p class="text-xs sm:text-sm text-emerald-100 mt-1">
                        Dibuka pukul {{ $activeSession->opened_at ? $activeSession->opened_at->format('H:i') : '-' }} WIB • Toleransi: {{ $activeSession->late_tolerance_minutes }} menit
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('teacher.sessions.scanner', $activeSession) }}"
                       class="py-2.5 px-4 rounded-lg bg-white text-emerald-800 text-xs sm:text-sm font-bold shadow-2xs hover:bg-emerald-50 transition inline-flex items-center gap-2">
                        <i data-lucide="camera" class="w-4 h-4 text-emerald-600"></i>
                        <span>Buka Kamera Scanner</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- Today's Teaching Schedule (Preline List Card) -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 sm:pb-4 border-b border-gray-100 mb-4 gap-2">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-2">
                        <i data-lucide="calendar" class="w-4 h-4 text-blue-600"></i>
                        <span>Jadwal Mengajar Hari Ini ({{ $todayName }})</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pilih jadwal di bawah untuk langsung membuka sesi presensi dan mengaktifkan scanner.</p>
                </div>
                <a href="{{ route('teacher.sessions.create') }}"
                   class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200/60 shadow-2xs transition self-start sm:self-auto">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Sesi Diluar Jadwal</span>
                </a>
            </div>

            <div class="space-y-2.5 sm:space-y-3">
                @forelse ($todaySchedules as $sch)
                    @php
                        $hasSessionToday = $todaySessions->has($sch->id);
                        $sessionToday = $todaySessions->get($sch->id);
                    @endphp
                    <div class="p-3.5 sm:p-4 rounded-xl border {{ $hasSessionToday && $sessionToday->status === 'active' ? 'border-emerald-300 bg-emerald-50/40' : 'border-gray-200 bg-gray-50/50' }} flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 transition">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="py-0.5 px-2 rounded-md bg-blue-100 text-blue-800 font-bold text-xs">
                                    Kelas {{ $sch->schoolClass->name }}
                                </span>
                                <span class="font-bold text-gray-900 text-xs sm:text-sm">
                                    {{ $sch->subject->name }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 flex items-center gap-2 flex-wrap">
                                <span class="font-mono font-medium text-gray-700 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-gray-400"></i>
                                    {{ $sch->formatted_time }} WIB
                                </span>
                                <span>•</span>
                                <span>Toleransi: {{ $sch->late_tolerance_minutes }} menit</span>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-2">
                            @if ($hasSessionToday)
                                @if ($sessionToday->status === 'active')
                                    <a href="{{ route('teacher.sessions.scanner', $sessionToday) }}"
                                       class="py-2 px-3.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5">
                                        <i data-lucide="camera" class="w-4 h-4"></i>
                                        <span>Lanjutkan Scanner</span>
                                    </a>
                                @else
                                    <span class="py-1.5 px-2.5 rounded-lg bg-gray-100 text-gray-600 text-xs font-semibold inline-flex items-center gap-1 border border-gray-200">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>Selesai</span>
                                    </span>
                                    <a href="{{ route('teacher.sessions.show', $sessionToday) }}"
                                       class="py-1.5 px-2 text-blue-600 hover:text-blue-800 text-xs font-semibold hover:underline">
                                        Lihat Rekap
                                    </a>
                                @endif
                            @else
                                <!-- Primary CTA to Start Session -->
                                <form action="{{ route('teacher.sessions.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="class_id" value="{{ $sch->class_id }}">
                                    <input type="hidden" name="subject_id" value="{{ $sch->subject_id }}">
                                    <input type="hidden" name="schedule_id" value="{{ $sch->id }}">
                                    <input type="hidden" name="late_tolerance_minutes" value="{{ $sch->late_tolerance_minutes }}">
                                    
                                    <button type="submit"
                                            class="py-2 px-3.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="play" class="w-3.5 h-3.5 fill-white"></i>
                                        <span>Mulai Presensi</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <i data-lucide="calendar-check" class="w-10 h-10 mx-auto stroke-1 mb-2 text-gray-300"></i>
                        <p class="text-sm font-medium text-gray-700">Tidak ada jadwal pelajaran terjadwal untuk hari ini.</p>
                        <p class="text-xs text-gray-400 mt-1">Anda tetap dapat membuka sesi presensi secara mandiri dengan tombol di atas.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Completed Sessions by Teacher -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-2xs">
            <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-gray-100 mb-3 sm:mb-4">
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-gray-400"></i>
                    <span>Riwayat Sesi Presensi Anda</span>
                </h3>
                <a href="{{ route('teacher.sessions.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                    Lihat Semua
                </a>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse ($recentSessions as $ses)
                    <div class="py-3 flex items-center justify-between gap-2">
                        <div class="min-w-0 pr-2">
                            <div class="font-bold text-gray-900 text-xs sm:text-sm truncate">
                                Kelas {{ $ses->schoolClass->name }} — {{ $ses->subject->name }}
                            </div>
                            <div class="text-[11px] text-gray-500 mt-0.5">
                                {{ $ses->date?->format('d/m/Y') }} • Jam {{ substr($ses->start_time, 0, 5) }} - {{ substr($ses->end_time, 0, 5) }} WIB
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs font-bold text-gray-700 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded-md">
                                {{ $ses->records->count() }} Siswa
                            </span>
                            <a href="{{ route('teacher.sessions.show', $ses) }}"
                               class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                                Rekap
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 py-4 text-center">Belum ada riwayat sesi presensi.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
