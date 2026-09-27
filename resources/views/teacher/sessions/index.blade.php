<x-layouts.app>
    <x-slot:title>Daftar Sesi Presensi</x-slot:title>
    <x-slot:header>Sesi Presensi Mengajar</x-slot:header>

    <div class="space-y-4 sm:space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider">Semua Sesi Mengajar</h3>
                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Daftar sesi presensi yang telah Anda buat dan buka</p>
            </div>
            <a href="{{ route('teacher.sessions.create') }}"
               class="py-2 px-3.5 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition self-start sm:self-auto">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buka Sesi Baru</span>
            </a>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <div class="min-w-full inline-block align-middle px-3 sm:px-0">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm text-start">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Tanggal &amp; Jam</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Kelas</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Mata Pelajaran</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Status Sesi</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Kehadiran</th>
                                <th class="py-3 px-3 sm:px-4 text-end text-xs font-semibold uppercase tracking-wider text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($sessions as $ses)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-3 sm:px-4">
                                        <div class="font-bold text-gray-900">{{ $ses->date?->format('d/m/Y') }}</div>
                                        <div class="text-[11px] font-mono text-gray-400">
                                            {{ $ses->opened_at ? $ses->opened_at->format('H:i') : substr($ses->start_time, 0, 5) }} WIB
                                        </div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center py-0.5 px-2 rounded-md bg-blue-50 text-blue-700 font-bold text-xs border border-blue-200/60">
                                            Kelas {{ $ses->schoolClass->name }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3 font-semibold text-gray-800">
                                        {{ $ses->subject->name }}
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        @if ($ses->status === 'active')
                                            <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                                <span>Aktif</span>
                                            </span>
                                        @elseif ($ses->status === 'closed')
                                            <span class="inline-flex items-center py-0.5 px-2 rounded-full bg-gray-100 text-gray-700 font-medium text-[10px]">
                                                Ditutup
                                            </span>
                                        @else
                                            <span class="inline-flex items-center py-0.5 px-2 rounded-full bg-amber-100 text-amber-800 font-medium text-[10px]">
                                                {{ $ses->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="font-bold text-gray-900">{{ $ses->records->count() }}</span> Siswa
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-end whitespace-nowrap">
                                        @if ($ses->status === 'active')
                                            <a href="{{ route('teacher.sessions.scanner', $ses) }}"
                                               class="py-1.5 px-3 rounded-lg bg-emerald-600 text-white font-semibold text-xs shadow-2xs hover:bg-emerald-700 transition inline-flex items-center gap-1">
                                                <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                                                <span>Scanner</span>
                                            </a>
                                        @else
                                            <a href="{{ route('teacher.sessions.show', $ses) }}"
                                               class="py-1.5 px-2.5 rounded-lg bg-white border border-gray-200 text-gray-700 font-semibold text-xs hover:bg-gray-50 shadow-2xs transition inline-flex items-center gap-1">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                <span>Detail</span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-gray-400">
                                        <i data-lucide="inbox" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                                        <p class="text-sm font-medium">Belum ada sesi presensi yang dibuat.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($sessions->hasPages())
                <div class="py-3 px-4 border-t border-gray-200">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
