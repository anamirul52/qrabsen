<x-layouts.app>
    <x-slot:title>Rekap Presensi</x-slot:title>
    <x-slot:header>Rekap &amp; Laporan Presensi Siswa</x-slot:header>

    <div class="space-y-4 sm:space-y-6">
        <!-- Preline Filter & Action Card -->
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs flex flex-col gap-3">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <form action="{{ route('admin.attendance.index') }}" method="GET" class="flex-1 flex flex-wrap items-center gap-2">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[150px] sm:min-w-[180px]">
                        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3 text-gray-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..."
                               class="py-2 ps-9 pe-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Date Range -->
                    <div class="flex items-center gap-1.5 flex-wrap sm:flex-nowrap">
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                               class="py-2 px-2.5 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <span class="text-xs text-gray-400 font-medium">s/d</span>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                               class="py-2 px-2.5 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Class -->
                    <select name="class_id" onchange="this.form.submit()"
                            class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Kelas</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                                Kelas {{ $c->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Status -->
                    <select name="status" onchange="this.form.submit()"
                            class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="HADIR" {{ request('status') === 'HADIR' ? 'selected' : '' }}>HADIR</option>
                        <option value="TERLAMBAT" {{ request('status') === 'TERLAMBAT' ? 'selected' : '' }}>TERLAMBAT</option>
                        <option value="IZIN" {{ request('status') === 'IZIN' ? 'selected' : '' }}>IZIN</option>
                        <option value="SAKIT" {{ request('status') === 'SAKIT' ? 'selected' : '' }}>SAKIT</option>
                        <option value="ALPA" {{ request('status') === 'ALPA' ? 'selected' : '' }}>ALPA</option>
                    </select>

                    <button type="submit" class="py-2 px-3 inline-flex items-center text-xs font-semibold rounded-lg bg-gray-800 text-white hover:bg-gray-900 transition cursor-pointer">
                        Filter
                    </button>

                    @if (request()->hasAny(['search', 'start_date', 'end_date', 'class_id', 'status']))
                        <a href="{{ route('admin.attendance.index') }}" class="py-2 px-2.5 inline-flex items-center text-gray-400 hover:text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs" title="Reset filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </form>

                <!-- Export Excel Button -->
                <a href="{{ route('admin.attendance.export', request()->query()) }}"
                   class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 shadow-2xs hover:bg-emerald-100 transition shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                    <span>Export Excel (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- Preline Statistics Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 sm:gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-3.5 shadow-2xs text-center">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase">Hadir Tepat</span>
                <div class="text-lg sm:text-xl font-bold text-emerald-600 mt-0.5">{{ $totalHadir }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-3.5 shadow-2xs text-center">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase">Terlambat</span>
                <div class="text-lg sm:text-xl font-bold text-amber-600 mt-0.5">{{ $totalTerlambat }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-3.5 shadow-2xs text-center">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase">Izin</span>
                <div class="text-lg sm:text-xl font-bold text-blue-600 mt-0.5">{{ $totalIzin }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-3.5 shadow-2xs text-center">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase">Sakit</span>
                <div class="text-lg sm:text-xl font-bold text-purple-600 mt-0.5">{{ $totalSakit }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-3.5 shadow-2xs text-center col-span-2 sm:col-span-1">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase">Alpa</span>
                <div class="text-lg sm:text-xl font-bold text-rose-600 mt-0.5">{{ $totalAlpa }}</div>
            </div>
        </div>

        <!-- Preline Attendance Records Table (360px Native Optimized) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <div class="min-w-full inline-block align-middle px-3 sm:px-0">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm text-start">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Siswa</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Kelas</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Tanggal &amp; Waktu</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Mapel &amp; Guru</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Status</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Metode</th>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($records as $r)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-3 sm:px-4">
                                        <div class="font-semibold text-gray-900">{{ $r->student?->name }}</div>
                                        <div class="text-[11px] text-gray-400 font-mono">NIS: {{ $r->student?->nis }}</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center py-0.5 px-2 rounded-md bg-gray-100 font-semibold text-gray-800 text-xs border border-gray-200/60">
                                            {{ $r->session?->schoolClass?->name ?? ($r->student?->currentClass?->name ?? '-') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <div class="text-gray-900 font-medium">{{ $r->session?->date ? $r->session->date->format('d/m/Y') : '-' }}</div>
                                        <div class="text-[11px] text-gray-400 font-mono">
                                            {{ $r->scanned_at ? $r->scanned_at->format('H:i:s') : $r->created_at->format('H:i:s') }} WIB
                                        </div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <div class="font-medium text-gray-900">{{ $r->session?->subject?->name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-400 truncate max-w-[150px] sm:max-w-xs">{{ $r->session?->teacher?->name ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        @if ($r->status === 'HADIR')
                                            <span class="inline-flex items-center gap-1 py-0.5 px-2 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                HADIR
                                            </span>
                                        @elseif ($r->status === 'TERLAMBAT')
                                            <span class="inline-flex items-center gap-1 py-0.5 px-2 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                TERLAMBAT
                                            </span>
                                        @elseif ($r->status === 'IZIN')
                                            <span class="inline-flex items-center gap-1 py-0.5 px-2 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                                IZIN
                                            </span>
                                        @elseif ($r->status === 'SAKIT')
                                            <span class="inline-flex items-center gap-1 py-0.5 px-2 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                                                SAKIT
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 py-0.5 px-2 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                ALPA
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center py-0.5 px-1.5 rounded text-[10px] font-mono font-semibold {{ $r->method === 'QR' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $r->method ?? 'QR' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-gray-500 text-[11px]">
                                        {{ $r->notes ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-10 text-gray-400">
                                        <i data-lucide="clipboard-x" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                                        <p class="text-sm font-medium">Tidak ada data presensi yang sesuai kriteria.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Preline Pagination -->
            @if ($records->hasPages())
                <div class="py-3 px-4 border-t border-gray-200">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
