<x-layouts.app>
    <x-slot:title>Mata Pelajaran</x-slot:title>
    <x-slot:header>Data Mata Pelajaran</x-slot:header>

    <div class="space-y-4 sm:space-y-6" x-data="{
        createModalOpen: false,
        editModalOpen: false,
        importModalOpen: false,
        editForm: {
            id: '',
            code: '',
            name: '',
            actionUrl: ''
        },
        openEdit(s) {
            this.editForm = {
                id: s.id,
                code: s.code,
                name: s.name,
                actionUrl: '{{ url('admin/subjects') }}/' + s.id
            };
            this.editModalOpen = true;
        }
    }">

        <!-- Preline Toolbar & Filter Card -->
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs flex flex-col gap-3">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <!-- Search Form -->
                <form action="{{ route('admin.subjects.index') }}" method="GET" class="flex-1 flex flex-wrap items-center gap-2">
                    <div class="relative flex-1 min-w-[160px] sm:min-w-[220px]">
                        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3 text-gray-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama mata pelajaran..."
                               class="py-2 ps-9 pe-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    @if (request('search'))
                        <a href="{{ route('admin.subjects.index') }}" class="py-2 px-2.5 inline-flex items-center text-gray-400 hover:text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs" title="Reset filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </form>

                <!-- Actions Toolbar -->
                <div class="flex items-center flex-wrap gap-2 shrink-0">
                    <!-- Template Excel -->
                    <a href="{{ route('admin.subjects.template') }}"
                       class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 shadow-2xs hover:bg-gray-50 transition">
                        <i data-lucide="download" class="w-4 h-4 text-gray-500"></i>
                        <span>Template Excel</span>
                    </a>

                    <!-- Export Excel -->
                    <a href="{{ route('admin.subjects.export') }}"
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

                    <!-- Tambah Mapel -->
                    <button type="button" @click="createModalOpen = true"
                            class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-2xs transition cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tambah Mapel</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Preline Subjects Table Card (360px Native Optimized) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="p-3.5 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider">Kurikulum &amp; Mata Pelajaran</h3>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Total {{ $subjects->total() }} mata pelajaran aktif di SMP Negeri 2 Mijen</p>
                </div>
            </div>

            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <div class="min-w-full inline-block align-middle px-3 sm:px-0">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm text-start">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Kode Mapel</th>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Nama Mata Pelajaran</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Jumlah Jadwal</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Total Sesi Presensi</th>
                                <th class="py-3 px-3 sm:px-4 text-end text-xs font-semibold uppercase tracking-wider text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($subjects as $s)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-3 sm:px-4 font-mono font-bold">
                                        <span class="inline-flex items-center py-0.5 px-2 rounded-md bg-blue-50 text-blue-700 border border-blue-200/60 font-mono text-xs">
                                            {{ $s->code }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 font-semibold text-gray-900">
                                        {{ $s->name }}
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3 text-gray-600">
                                        <span class="font-medium text-gray-900">{{ $s->schedules_count }}</span> Jadwal Aktif
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3 text-gray-600">
                                        <span class="font-medium text-gray-900">{{ $s->attendance_sessions_count }}</span> Sesi Selesai
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-end whitespace-nowrap">
                                        <div class="inline-flex items-center gap-x-1">
                                            <!-- Edit Button -->
                                            <button type="button"
                                                    @click="openEdit({{ json_encode([
                                                        'id' => $s->id,
                                                        'code' => $s->code,
                                                        'name' => $s->name
                                                    ]) }})"
                                                    class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg inline-flex cursor-pointer transition"
                                                    title="Edit Mapel">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>

                                            <!-- Delete Button -->
                                            <form action="{{ route('admin.subjects.destroy', $s) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran {{ addslashes($s->name) }} ({{ $s->code }})?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg inline-flex cursor-pointer transition"
                                                        title="Hapus Mapel">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-gray-400">
                                        <i data-lucide="book-x" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                                        <p class="text-sm font-medium">Tidak ada mata pelajaran ditemukan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($subjects->hasPages())
                <div class="py-3 px-4 border-t border-gray-200">
                    {{ $subjects->links() }}
                </div>
            @endif
        </div>

        <!-- ================= PRELINE MODAL TAMBAH MAPEL ================= -->
        <div x-show="createModalOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
             style="display: none;">
            
            <div @click.away="createModalOpen = false"
                 class="bg-white border border-gray-200 rounded-2xl max-w-md w-full p-4 sm:p-6 shadow-xl">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-x-2">
                        <i data-lucide="book-plus" class="w-5 h-5 text-blue-600"></i>
                        <span>Tambah Mata Pelajaran Baru</span>
                    </h3>
                    <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Kode Mapel <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="code" required placeholder="Contoh: IPA-7, MTK-8, BIG-9"
                               class="py-2 px-3 block w-full border border-gray-200 rounded-lg font-mono text-xs sm:text-sm text-gray-900 uppercase focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="Contoh: Ilmu Pengetahuan Alam"
                               class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center justify-end gap-x-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="createModalOpen = false"
                                class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2 px-3.5 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs cursor-pointer">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Simpan Mapel</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= PRELINE MODAL EDIT MAPEL ================= -->
        <div x-show="editModalOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
             style="display: none;">
            
            <div @click.away="editModalOpen = false"
                 class="bg-white border border-gray-200 rounded-2xl max-w-md w-full p-4 sm:p-6 shadow-xl">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-x-2">
                        <i data-lucide="edit" class="w-5 h-5 text-blue-600"></i>
                        <span>Edit Mata Pelajaran</span>
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="editForm.actionUrl" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Kode Mapel <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="code" x-model="editForm.code" required
                               class="py-2 px-3 block w-full border border-gray-200 rounded-lg font-mono text-xs sm:text-sm text-gray-900 uppercase focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="editForm.name" required
                               class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center justify-end gap-x-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="editModalOpen = false"
                                class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2 px-3.5 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs cursor-pointer">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Perbarui Mapel</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= PRELINE MODAL IMPORT EXCEL MAPEL ================= -->
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
                        <span>Import Mata Pelajaran (Excel)</span>
                    </h3>
                    <button type="button" @click="importModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.subjects.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Pilih File Excel Mapel (.xlsx / .xls)
                        </label>
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required
                               class="py-2 px-3 block w-full text-xs text-gray-500 file:me-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-lg cursor-pointer">
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-600 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-800">Format Kolom Header Excel:</span>
                            <a href="{{ route('admin.subjects.template') }}" class="text-blue-600 hover:text-blue-700 font-semibold inline-flex items-center gap-1 text-[11px]">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                <span>Unduh Template</span>
                            </a>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-gray-200 font-mono text-[11px] text-gray-700 overflow-x-auto whitespace-nowrap">
                            code, name
                        </div>
                        <ul class="text-[11px] text-gray-500 space-y-1 list-disc list-inside">
                            <li><strong>code</strong>: Kode unik mata pelajaran (misal: <code class="font-mono text-gray-800">IPA-7</code>).</li>
                            <li><strong>name</strong>: Nama mata pelajaran lengkap.</li>
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
