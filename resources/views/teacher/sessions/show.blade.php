<x-layouts.app>
    <x-slot:title>Detail Sesi Presensi</x-slot:title>
    <x-slot:header>Ringkasan Sesi Presensi</x-slot:header>

    <div class="space-y-4 sm:space-y-6">
        <!-- Preline Session Header Card -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="inline-flex items-center py-0.5 px-2 rounded-md bg-blue-600 text-white font-bold text-xs">
                        Kelas {{ $session->schoolClass->name }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full font-bold text-xs {{ $session->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                        @if ($session->status === 'active')
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Sedang Berlangsung</span>
                        @else
                            <span>Sesi Ditutup</span>
                        @endif
                    </span>
                </div>
                <h1 class="text-lg sm:text-2xl font-bold text-gray-900">
                    {{ $session->subject->name }}
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    Tanggal: {{ $session->date?->isoFormat('dddd, D MMMM Y') }} • Jam {{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if ($session->status === 'active')
                    <a href="{{ route('teacher.sessions.scanner', $session) }}"
                       class="py-2 px-3.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        <span>Buka Scanner</span>
                    </a>
                @endif
                <a href="{{ route('teacher.sessions.index') }}"
                   class="py-2 px-3.5 rounded-lg border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 shadow-2xs transition">
                    Kembali
                </a>
            </div>
        </div>

        <!-- Preline Session Statistics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 sm:gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 shadow-2xs text-center">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase">Hadir Tepat</span>
                <div class="text-lg sm:text-2xl font-bold text-emerald-600 mt-0.5">{{ $stats['present_count'] }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 shadow-2xs text-center">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase">Terlambat</span>
                <div class="text-lg sm:text-2xl font-bold text-amber-600 mt-0.5">{{ $stats['late_count'] }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 shadow-2xs text-center">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase">Izin</span>
                <div class="text-lg sm:text-2xl font-bold text-blue-600 mt-0.5">{{ $stats['permission_count'] }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 shadow-2xs text-center">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase">Sakit</span>
                <div class="text-lg sm:text-2xl font-bold text-purple-600 mt-0.5">{{ $stats['sick_count'] }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 shadow-2xs text-center col-span-2 sm:col-span-1">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase">Alpa</span>
                <div class="text-lg sm:text-2xl font-bold text-rose-600 mt-0.5">{{ $stats['alpha_count'] }}</div>
            </div>
        </div>

        <!-- Preline Attendance List for this Session (360px Native Optimized) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="p-3.5 sm:p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider">
                    Daftar Presensi Seluruh Siswa Kelas {{ $session->schoolClass->name }}
                </h3>
                <span class="text-xs text-gray-500 font-medium">
                    Total: {{ $students->count() }} Siswa
                </span>
            </div>

            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <div class="min-w-full inline-block align-middle px-3 sm:px-0">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm text-start">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Nama Siswa</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">NIS</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Waktu Presensi</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Status</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Metode</th>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($students as $st)
                                @php
                                    $rec = $recordsMap->get($st->id);
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-3 sm:px-4 font-semibold text-gray-900">
                                        {{ $st->name }}
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3 font-mono text-gray-600">
                                        {{ $st->nis }}
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3 font-mono text-xs text-gray-600">
                                        {{ $rec && $rec->scanned_at ? $rec->scanned_at->format('H:i:s') . ' WIB' : '-' }}
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        @if ($rec)
                                            @if ($rec->status === 'HADIR')
                                                <span class="inline-flex items-center py-0.5 px-2 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    HADIR
                                                </span>
                                            @elseif ($rec->status === 'TERLAMBAT')
                                                <span class="inline-flex items-center py-0.5 px-2 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    TERLAMBAT
                                                </span>
                                            @elseif ($rec->status === 'IZIN')
                                                <span class="inline-flex items-center py-0.5 px-2 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                                    IZIN
                                                </span>
                                            @elseif ($rec->status === 'SAKIT')
                                                <span class="inline-flex items-center py-0.5 px-2 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                                                    SAKIT
                                                </span>
                                            @else
                                                <span class="inline-flex items-center py-0.5 px-2 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                    ALPA
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center py-0.5 px-2 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                ALPA
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center py-0.5 px-1.5 rounded text-[10px] font-mono font-semibold {{ $rec && $rec->method === 'QR' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $rec->method ?? 'AUTO' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-gray-500 text-[11px]">
                                        {{ $rec->notes ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
