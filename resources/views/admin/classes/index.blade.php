<x-layouts.app>
    <x-slot:title>Data Kelas</x-slot:title>
    <x-slot:header>Rombongan Belajar / Kelas</x-slot:header>

    <div class="space-y-4 sm:space-y-6"
         x-data="{
             createModalOpen: false,
             importModalOpen: false,
             editModalOpen: false,
             editForm: {
                 id: '',
                 name: '',
                 homeroom_teacher_id: '',
                 actionUrl: ''
             },
             openEdit(c) {
                 this.editForm.id = c.id;
                 this.editForm.name = c.name;
                 this.editForm.homeroom_teacher_id = c.homeroom_teacher_id || '';
                 this.editForm.actionUrl = '/admin/classes/' + c.id;
                 this.editModalOpen = true;
             }
         }">

        <!-- Toolbar & Action Buttons -->
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-sm sm:text-base font-bold text-gray-900">Daftar Rombel / Kelas</h2>
                <p class="text-xs text-gray-500 mt-0.5">Total {{ $classes->count() }} rombongan belajar terdaftar dalam sistem.</p>
            </div>

            <div class="flex items-center flex-wrap gap-2 shrink-0">
                <!-- Template Excel -->
                <a href="{{ route('admin.classes.template') }}"
                   class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 shadow-2xs hover:bg-gray-50 transition">
                    <i data-lucide="download" class="w-4 h-4 text-gray-500"></i>
                    <span>Template Excel</span>
                </a>

                <!-- Import Excel -->
                <button type="button" @click="importModalOpen = true"
                        class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 shadow-2xs hover:bg-gray-50 transition cursor-pointer">
                    <i data-lucide="upload" class="w-4 h-4 text-gray-500"></i>
                    <span>Import Excel</span>
                </button>

                <!-- Tambah Kelas -->
                <button type="button" @click="createModalOpen = true"
                        class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-2xs transition cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Kelas</span>
                </button>
            </div>
        </div>

        <!-- Grid Cards Kelas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
            @forelse ($classes as $c)
                <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 shadow-2xs flex flex-col justify-between hover:border-gray-300 transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <!-- Badge Kelas -->
                            <span class="inline-flex items-center py-0.5 px-2 rounded-md bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200/60">
                                Kelas {{ $c->level }}
                            </span>
                            <span class="py-0.5 px-2 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                                {{ $c->students->count() }} Siswa
                            </span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900">
                            Kelas {{ $c->name }}
                        </h3>
                        <div class="text-xs text-gray-600 mt-2 flex items-center gap-1.5">
                            <i data-lucide="user-check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span class="truncate">Wali: <strong class="text-gray-900 font-semibold">{{ $c->homeroomTeacher?->name ?? 'Belum ditentukan' }}</strong></span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.students.index', ['class_id' => $c->id]) }}"
                               class="font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1">
                                <span>Lihat Siswa</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                            <a href="{{ route('admin.qr-cards.index', ['class_id' => $c->id]) }}"
                               class="py-1 px-2.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 font-medium text-gray-700 inline-flex items-center gap-1 shadow-2xs transition">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                <span>Cetak QR</span>
                            </a>
                        </div>

                        <!-- Edit & Hapus Buttons -->
                        <div class="flex items-center gap-1">
                            <button type="button" @click="openEdit({{ json_encode($c) }})"
                                    class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition cursor-pointer" title="Edit Kelas">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </button>
                            @if ($c->students->count() === 0)
                                <form action="{{ route('admin.classes.destroy', $c) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus kelas {{ $c->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Hapus Kelas">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white border border-gray-200 rounded-xl p-8 text-center text-gray-500">
                    <i data-lucide="layout-grid" class="w-10 h-10 mx-auto stroke-1 text-gray-300 mb-2"></i>
                    <p class="text-sm font-medium">Belum ada data kelas yang terdaftar.</p>
                    <button type="button" @click="createModalOpen = true" class="mt-3 py-1.5 px-3 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 cursor-pointer">
                        Tambah Kelas Sekarang
                    </button>
                </div>
            @endforelse
        </div>

        <!-- ================= MODAL TAMBAH KELAS ================= -->
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
                        <i data-lucide="plus-circle" class="w-5 h-5 text-blue-600"></i>
                        <span>Tambah Kelas Baru</span>
                    </h3>
                    <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.classes.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Nama Kelas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="Contoh: 7C, 8D, 9B"
                               class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 uppercase focus:border-blue-500 focus:ring-blue-500">
                        <p class="text-[11px] text-gray-400 mt-1">Kelas (7, 8, atau 9) otomatis ditentukan dari angka awal nama kelas.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Wali Kelas (Opsional)
                        </label>
                        <select name="homeroom_teacher_id"
                                class="py-2.5 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="">- Belum Ditentukan -</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->user?->name }} (NIP: {{ $t->nip ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-x-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="createModalOpen = false"
                                class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2 px-4 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Kelas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= MODAL EDIT KELAS ================= -->
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
                        <i data-lucide="pencil" class="w-5 h-5 text-blue-600"></i>
                        <span>Edit Data Kelas</span>
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="editForm.actionUrl" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Nama Kelas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="editForm.name" required
                               class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 uppercase focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Wali Kelas
                        </label>
                        <select name="homeroom_teacher_id" x-model="editForm.homeroom_teacher_id"
                                class="py-2.5 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="">- Belum Ditentukan -</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->user?->name }} (NIP: {{ $t->nip ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-x-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="editModalOpen = false"
                                class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2 px-4 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= MODAL IMPORT EXCEL KELAS ================= -->
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
                        <span>Import Data Kelas (Excel)</span>
                    </h3>
                    <button type="button" @click="importModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.classes.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                            <span class="font-bold text-gray-800">Format Kolom Excel:</span>
                            <a href="{{ route('admin.classes.template') }}" class="text-blue-600 hover:text-blue-700 font-semibold inline-flex items-center gap-1 text-[11px]">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                <span>Unduh Template</span>
                            </a>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-gray-200 font-mono text-[11px] text-gray-700 overflow-x-auto whitespace-nowrap">
                            Nama Kelas
                        </div>
                        <ul class="text-[11px] text-gray-500 space-y-1 list-disc list-inside">
                            <li>Isi dengan nama kelas seperti <code class="font-mono text-gray-800">7A</code>, <code class="font-mono text-gray-800">7B</code>, <code class="font-mono text-gray-800">8C</code>, <code class="font-mono text-gray-800">9D</code>.</li>
                            <li>Kelas (7, 8, atau 9) otomatis terdeteksi dari awalan nama.</li>
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
