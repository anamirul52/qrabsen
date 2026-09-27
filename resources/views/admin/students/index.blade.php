<x-layouts.app>
    <x-slot:title>Manajemen Siswa</x-slot:title>
    <x-slot:header>Data Siswa & Kartu QR</x-slot:header>

    <div class="space-y-4 sm:space-y-6" x-data="{ importModalOpen: false }">
        <!-- Preline Toolbar & Filters Card -->
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs flex flex-col gap-3">
            
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <!-- Filters Form -->
                <form action="{{ route('admin.students.index') }}" method="GET" class="flex-1 flex flex-wrap items-center gap-2">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[160px] sm:min-w-[220px]">
                        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3 text-gray-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIS, atau email..."
                               class="py-2 ps-9 pe-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Class Filter -->
                    <select name="class_id" onchange="this.form.submit()"
                            class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Kelas</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                                Kelas {{ $c->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Status Filter -->
                    <select name="status" onchange="this.form.submit()"
                            class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="graduated" {{ request('status') === 'graduated' ? 'selected' : '' }}>Lulus</option>
                    </select>

                    @if (request()->hasAny(['search', 'class_id', 'status']))
                        <a href="{{ route('admin.students.index') }}" class="py-2 px-2.5 inline-flex items-center text-gray-400 hover:text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs" title="Reset filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </form>

                <!-- Actions Toolbar -->
                <div class="flex items-center flex-wrap gap-2 shrink-0">
                    <!-- Template Excel -->
                    <a href="{{ route('admin.students.template') }}"
                       class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 shadow-2xs hover:bg-gray-50 transition">
                        <i data-lucide="download" class="w-4 h-4 text-gray-500"></i>
                        <span>Template Excel</span>
                    </a>

                    <!-- Export Excel -->
                    <a href="{{ route('admin.students.export', request()->all()) }}"
                       class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 shadow-2xs hover:bg-emerald-100 transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                        <span>Export Excel</span>
                    </a>

                    <!-- Import Excel -->
                    <button type="button" @click="importModalOpen = true"
                            class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 shadow-2xs hover:bg-gray-50 transition cursor-pointer">
                        <i data-lucide="upload" class="w-4 h-4 text-gray-500"></i>
                        <span>Import Excel</span>
                    </button>

                    <!-- Tambah Siswa -->
                    <a href="{{ route('admin.students.create') }}"
                       class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-2xs transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tambah Siswa</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Preline Data Table Card (360px Native Optimized) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <div class="min-w-full inline-block align-middle px-3 sm:px-0">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm text-start">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Siswa</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">NIS / NISN</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Kelas</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Status</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Kartu QR</th>
                                <th class="py-3 px-3 sm:px-4 text-end text-xs font-semibold uppercase tracking-wider text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($students as $student)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-3 sm:px-4">
                                        <div class="font-semibold text-gray-900">{{ $student->name }}</div>
                                        <div class="text-[11px] text-gray-500 truncate max-w-[180px] sm:max-w-xs">{{ $student->user?->email }}</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3 font-mono">
                                        <div class="text-gray-900 font-medium">{{ $student->nis }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $student->nisn ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center py-0.5 px-2 rounded-md bg-gray-100 font-semibold text-gray-800 text-xs border border-gray-200/60">
                                            {{ $student->currentClass?->name ?? 'Belum ada' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center gap-x-1 py-0.5 px-2 rounded-full text-[10px] font-semibold uppercase tracking-wide {{ $student->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $student->status === 'active' ? 'Aktif' : $student->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        @if ($student->activeQrToken)
                                            <span class="inline-flex items-center gap-x-1 text-emerald-700 bg-emerald-50 border border-emerald-200 py-0.5 px-2 rounded-md text-[11px] font-mono">
                                                <i data-lucide="check" class="w-3 h-3 text-emerald-600"></i>
                                                <span>Aktif</span>
                                            </span>
                                        @else
                                            <span class="text-rose-600 text-[11px] font-medium">Belum ada QR</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-end whitespace-nowrap">
                                        <div class="inline-flex items-center gap-x-1">
                                            <!-- Print QR Card -->
                                            <a href="{{ route('admin.students.print-qr', $student) }}" target="_blank"
                                               class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg inline-flex transition" title="Cetak Kartu QR">
                                                <i data-lucide="printer" class="w-4 h-4"></i>
                                            </a>

                                            <!-- Regenerate QR -->
                                            <form action="{{ route('admin.students.regenerate-qr', $student) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Regenerasi QR untuk {{ $student->name }}? QR code lama akan dinonaktifkan.');">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg inline-flex transition cursor-pointer" title="Regenerasi Token QR">
                                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                                </button>
                                            </form>

                                            <!-- Edit -->
                                            <a href="{{ route('admin.students.edit', $student) }}"
                                               class="p-1.5 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg inline-flex transition" title="Edit Data">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-gray-400">
                                        <i data-lucide="user-x" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                                        <p class="text-sm">Tidak ada data siswa ditemukan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Preline Pagination -->
            @if ($students->hasPages())
                <div class="py-3 px-4 border-t border-gray-200">
                    {{ $students->links() }}
                </div>
            @endif
        </div>

        <!-- Preline Import Excel Modal -->
        <div x-show="importModalOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
             style="display: none;">
            
            <div @click.away="importModalOpen = false"
                 class="bg-white border border-gray-200 rounded-2xl max-w-md w-full p-4 sm:p-6 shadow-xl">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-x-2">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5 text-emerald-600"></i>
                        <span>Import Data Siswa (Excel)</span>
                    </h3>
                    <button type="button" @click="importModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Pilih File Excel (.xlsx / .xls)
                        </label>
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required
                               class="py-2 px-3 block w-full text-xs text-gray-500 file:me-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-lg cursor-pointer">
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-600 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-800">Format Kolom Header Excel:</span>
                            <a href="{{ route('admin.students.template') }}" class="text-blue-600 hover:text-blue-700 font-semibold inline-flex items-center gap-1 text-[11px]">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                <span>Unduh Template</span>
                            </a>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-gray-200 font-mono text-[11px] text-gray-700 overflow-x-auto whitespace-nowrap">
                            nis, nisn, name, gender, class_name, email, phone
                        </div>
                        <ul class="text-[11px] text-gray-500 space-y-1 list-disc list-inside">
                            <li><strong>nis</strong>, <strong>name</strong>, &amp; <strong>email</strong> wajib diisi untuk tiap baris.</li>
                            <li><strong>gender</strong>: <code class="text-gray-700 font-mono">L</code> (Laki-laki) atau <code class="text-gray-700 font-mono">P</code> (Perempuan).</li>
                            <li>Password default akun siswa baru: <code class="font-mono text-gray-800 font-semibold">password</code>.</li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end gap-x-2 pt-2 border-t border-gray-100">
                        <button type="button" @click="importModalOpen = false"
                                class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2 px-3.5 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs cursor-pointer">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            <span>Unggah &amp; Proses</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
