<x-layouts.app>
    <x-slot:title>Audit Log Sistem</x-slot:title>
    <x-slot:header>Riwayat Aktivitas &amp; Audit Log</x-slot:header>

    <div class="space-y-4 sm:space-y-6">
        <!-- Preline Filter Bar -->
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs flex flex-wrap items-center justify-between gap-3">
            <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <div class="relative flex-1 min-w-[160px] sm:min-w-[220px]">
                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3 text-gray-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari keterangan aktivitas..."
                           class="py-2 ps-9 pe-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <select name="action" onchange="this.form.submit()"
                        class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Jenis Aksi</option>
                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="scan_qr" {{ request('action') === 'scan_qr' ? 'selected' : '' }}>Scan QR</option>
                    <option value="create_session" {{ request('action') === 'create_session' ? 'selected' : '' }}>Buka Sesi</option>
                    <option value="close_session" {{ request('action') === 'close_session' ? 'selected' : '' }}>Tutup Sesi</option>
                    <option value="regenerate_qr" {{ request('action') === 'regenerate_qr' ? 'selected' : '' }}>Regenerasi QR</option>
                </select>

                @if (request()->hasAny(['search', 'action']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="py-2 px-2.5 inline-flex items-center text-gray-400 hover:text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs" title="Reset filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Preline Log Table (360px Native Optimized) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <div class="min-w-full inline-block align-middle px-3 sm:px-0">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm text-start">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Waktu</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Pengguna</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Aksi</th>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Keterangan</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-3 sm:px-4 font-mono text-[11px] text-gray-500 whitespace-nowrap">
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <div class="font-bold text-gray-900">{{ $log->user?->name ?? 'Sistem' }}</div>
                                        <div class="text-[10px] text-gray-400 capitalize">{{ $log->user?->role ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center py-0.5 px-2 rounded-md text-[10px] font-mono font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-gray-700 text-xs">
                                        {{ $log->description }}
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3 font-mono text-[11px] text-gray-500 whitespace-nowrap">
                                        {{ $log->ip_address ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-gray-400">
                                        <i data-lucide="inbox" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                                        <p class="text-sm font-medium">Belum ada catatan aktivitas.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($logs->hasPages())
                <div class="py-3 px-4 border-t border-gray-200">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
