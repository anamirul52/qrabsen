<x-layouts.app>
    <x-slot:title>Riwayat Presensi Mengajar</x-slot:title>
    <x-slot:header>Riwayat Presensi Guru</x-slot:header>

    <div class="space-y-4 sm:space-y-6">
        <!-- Preline Filter Bar -->
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs flex flex-wrap items-center justify-between gap-3">
            <form action="{{ route('teacher.attendance.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <input type="date" name="date" value="{{ request('date') }}"
                       class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">

                <select name="class_id" onchange="this.form.submit()"
                        class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Kelas</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                            Kelas {{ $c->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()"
                        class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="HADIR" {{ request('status') === 'HADIR' ? 'selected' : '' }}>HADIR</option>
                    <option value="TERLAMBAT" {{ request('status') === 'TERLAMBAT' ? 'selected' : '' }}>TERLAMBAT</option>
                    <option value="IZIN" {{ request('status') === 'IZIN' ? 'selected' : '' }}>IZIN</option>
                    <option value="SAKIT" {{ request('status') === 'SAKIT' ? 'selected' : '' }}>SAKIT</option>
                    <option value="ALPA" {{ request('status') === 'ALPA' ? 'selected' : '' }}>ALPA</option>
                </select>

                @if (request()->hasAny(['date', 'class_id', 'status']))
                    <a href="{{ route('teacher.attendance.index') }}" class="py-2 px-2.5 inline-flex items-center text-gray-400 hover:text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs" title="Reset filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Preline Attendance Table (360px Native Optimized) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <div class="min-w-full inline-block align-middle px-3 sm:px-0">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm text-start">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Nama Siswa</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Kelas</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Tanggal &amp; Jam</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Mata Pelajaran</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Status</th>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($records as $r)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-3 sm:px-4">
                                        <div class="font-bold text-gray-900">{{ $r->student?->name }}</div>
                                        <div class="text-[11px] font-mono text-gray-400">NIS: {{ $r->student?->nis }}</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3 font-semibold text-gray-800">
                                        {{ $r->attendanceSession?->schoolClass?->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <div class="text-gray-900 font-medium">{{ $r->attendanceSession?->date?->format('d/m/Y') }}</div>
                                        <div class="text-[11px] font-mono text-gray-400">{{ $r->scanned_at ? $r->scanned_at->format('H:i:s') : '-' }} WIB</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3 font-medium text-gray-800">
                                        {{ $r->attendanceSession?->subject?->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center py-0.5 px-2 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            {{ $r->status === 'HADIR' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $r->status === 'TERLAMBAT' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ $r->status === 'IZIN' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $r->status === 'SAKIT' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $r->status === 'ALPA' ? 'bg-rose-100 text-rose-800' : '' }}">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-gray-500 text-xs">
                                        {{ $r->notes ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-gray-400">
                                        <i data-lucide="clipboard-x" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                                        <p class="text-sm font-medium">Belum ada data presensi yang sesuai kriteria.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($records->hasPages())
                <div class="py-3 px-4 border-t border-gray-200">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
