<x-layouts.app>
    <x-slot:title>Jadwal Pelajaran</x-slot:title>
    <x-slot:header>Jadwal Pelajaran Sekolah</x-slot:header>

    <div class="space-y-4 sm:space-y-6" x-data="{
        createModalOpen: false,
        editModalOpen: false,
        importModalOpen: false,
        editForm: {
            id: '',
            class_id: '',
            teacher_id: '',
            subject_id: '',
            day_of_week: 'Senin',
            start_time: '07:30',
            end_time: '09:00',
            late_tolerance_minutes: 15,
            actionUrl: ''
        },
        openEdit(sc) {
            this.editForm = {
                id: sc.id,
                class_id: sc.class_id,
                teacher_id: sc.teacher_id,
                subject_id: sc.subject_id,
                day_of_week: sc.day_of_week,
                start_time: sc.start_time.substring(0, 5),
                end_time: sc.end_time.substring(0, 5),
                late_tolerance_minutes: sc.late_tolerance_minutes,
                actionUrl: '{{ url('admin/schedules') }}/' + sc.id
            };
            this.editModalOpen = true;
        }
    }">

        <!-- Preline Toolbar & Filter Card -->
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs flex flex-col gap-3">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <!-- Filters Form -->
                <form action="{{ route('admin.schedules.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
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

                    <!-- Day Filter -->
                    <select name="day" onchange="this.form.submit()"
                            class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Hari</option>
                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                            <option value="{{ $d }}" {{ request('day') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>

                    @if (request()->hasAny(['class_id', 'day']))
                        <a href="{{ route('admin.schedules.index') }}" class="py-2 px-2.5 inline-flex items-center text-gray-400 hover:text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs" title="Reset filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </form>

                <!-- Actions Toolbar -->
                <div class="flex items-center flex-wrap gap-2 shrink-0">
                    <!-- Template Excel -->
                    <a href="{{ route('admin.schedules.template') }}"
                       class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 shadow-2xs hover:bg-gray-50 transition">
                        <i data-lucide="download" class="w-4 h-4 text-gray-500"></i>
                        <span>Template Excel</span>
                    </a>

                    <!-- Export Excel -->
                    <a href="{{ route('admin.schedules.export', request()->all()) }}"
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

                    <!-- Tambah Jadwal -->
                    <button type="button" @click="createModalOpen = true"
                            class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-2xs transition cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tambah Jadwal</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Preline Schedules Table Card (360px Native Optimized) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="p-3.5 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider">Jadwal Pelajaran Sekolah</h3>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Total {{ $schedules->total() }} jadwal mengajar terdaftar</p>
                </div>
            </div>

            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <div class="min-w-full inline-block align-middle px-3 sm:px-0">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm text-start">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Hari &amp; Jam</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Kelas</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Mata Pelajaran</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Guru Pengajar</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Toleransi</th>
                                <th class="py-3 px-3 sm:px-4 text-end text-xs font-semibold uppercase tracking-wider text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($schedules as $sc)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-3 sm:px-4">
                                        <div class="font-bold text-gray-900">{{ $sc->day_of_week }}</div>
                                        <div class="text-[11px] font-mono text-gray-500">{{ substr($sc->start_time, 0, 5) }} - {{ substr($sc->end_time, 0, 5) }} WIB</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center py-0.5 px-2 rounded-md bg-blue-50 text-blue-700 font-bold text-xs border border-blue-200/60">
                                            Kelas {{ $sc->schoolClass->name }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <div class="font-semibold text-gray-900">{{ $sc->subject->name }}</div>
                                        <div class="text-[11px] font-mono text-gray-400">{{ $sc->subject->code }}</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <div class="font-medium text-gray-900">{{ $sc->teacher->name }}</div>
                                        <div class="text-[11px] text-gray-400 font-mono">{{ $sc->teacher->nip ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center gap-1 py-0.5 px-2 rounded-md bg-amber-50 text-amber-700 text-xs font-medium border border-amber-200/60">
                                            {{ $sc->late_tolerance_minutes }} mnt
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-end whitespace-nowrap">
                                        <div class="inline-flex items-center gap-x-1">
                                            <!-- Edit Button -->
                                            <button type="button"
                                                    @click="openEdit({{ json_encode([
                                                        'id' => $sc->id,
                                                        'class_id' => $sc->class_id,
                                                        'teacher_id' => $sc->teacher_id,
                                                        'subject_id' => $sc->subject_id,
                                                        'day_of_week' => $sc->day_of_week,
                                                        'start_time' => $sc->start_time,
                                                        'end_time' => $sc->end_time,
                                                        'late_tolerance_minutes' => $sc->late_tolerance_minutes
                                                    ]) }})"
                                                    class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg inline-flex cursor-pointer transition"
                                                    title="Edit Jadwal">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>

                                            <!-- Delete Button -->
                                            <form action="{{ route('admin.schedules.destroy', $sc) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg inline-flex cursor-pointer transition"
                                                        title="Hapus Jadwal">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-gray-400">
                                        <i data-lucide="calendar-x" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                                        <p class="text-sm font-medium">Tidak ada jadwal pelajaran ditemukan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($schedules->hasPages())
                <div class="py-3 px-4 border-t border-gray-200">
                    {{ $schedules->links() }}
                </div>
            @endif
        </div>

        <!-- ================= PRELINE MODAL TAMBAH JADWAL ================= -->
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
                 class="bg-white border border-gray-200 rounded-2xl max-w-lg w-full p-4 sm:p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-x-2">
                        <i data-lucide="calendar-plus" class="w-5 h-5 text-blue-600"></i>
                        <span>Tambah Jadwal Pelajaran Baru</span>
                    </h3>
                    <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.schedules.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    
                    <!-- Kelas & Hari -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Kelas <span class="text-rose-500">*</span>
                            </label>
                            <select name="class_id" required
                                    class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Kelas</option>
                                @foreach ($classes as $c)
                                    <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Hari <span class="text-rose-500">*</span>
                            </label>
                            <select name="day_of_week" required
                                    class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Mata Pelajaran & Guru -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Mata Pelajaran <span class="text-rose-500">*</span>
                            </label>
                            <select name="subject_id" required
                                    class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Mapel</option>
                                @foreach ($subjects as $sb)
                                    <option value="{{ $sb->id }}">{{ $sb->name }} ({{ $sb->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Guru Pengajar <span class="text-rose-500">*</span>
                            </label>
                            <select name="teacher_id" required
                                    class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Guru</option>
                                @foreach ($teachers as $tc)
                                    <option value="{{ $tc->id }}">{{ $tc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Jam Mulai & Jam Selesai -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Jam Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="start_time" required value="07:30"
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Jam Selesai <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="end_time" required value="09:00"
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Toleransi Terlambat -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Batas Toleransi Keterlambatan (Menit) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="late_tolerance_minutes" required min="0" max="120" value="15"
                               class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        <p class="text-[11px] text-gray-500 mt-1">Siswa yang scan setelah batas toleransi akan berstatus TERLAMBAT.</p>
                    </div>

                    <div class="flex items-center justify-end gap-x-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="createModalOpen = false"
                                class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2 px-3.5 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs cursor-pointer">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Simpan Jadwal</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= PRELINE MODAL EDIT JADWAL ================= -->
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
                 class="bg-white border border-gray-200 rounded-2xl max-w-lg w-full p-4 sm:p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-x-2">
                        <i data-lucide="pencil" class="w-5 h-5 text-blue-600"></i>
                        <span>Edit Jadwal Pelajaran</span>
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="editForm.actionUrl" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    
                    <!-- Kelas & Hari -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Kelas <span class="text-rose-500">*</span>
                            </label>
                            <select name="class_id" x-model="editForm.class_id" required
                                    class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                                @foreach ($classes as $c)
                                    <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Hari <span class="text-rose-500">*</span>
                            </label>
                            <select name="day_of_week" x-model="editForm.day_of_week" required
                                    class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Mata Pelajaran & Guru -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Mata Pelajaran <span class="text-rose-500">*</span>
                            </label>
                            <select name="subject_id" x-model="editForm.subject_id" required
                                    class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                                @foreach ($subjects as $sb)
                                    <option value="{{ $sb->id }}">{{ $sb->name }} ({{ $sb->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Guru Pengajar <span class="text-rose-500">*</span>
                            </label>
                            <select name="teacher_id" x-model="editForm.teacher_id" required
                                    class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                                @foreach ($teachers as $tc)
                                    <option value="{{ $tc->id }}">{{ $tc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Jam Mulai & Jam Selesai -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Jam Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="start_time" x-model="editForm.start_time" required
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Jam Selesai <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="end_time" x-model="editForm.end_time" required
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Toleransi Terlambat -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Batas Toleransi Keterlambatan (Menit) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="late_tolerance_minutes" x-model="editForm.late_tolerance_minutes" required min="0" max="120"
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
                            <span>Perbarui Jadwal</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= PRELINE MODAL IMPORT EXCEL JADWAL ================= -->
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
                        <span>Import Jadwal Pelajaran (Excel)</span>
                    </h3>
                    <button type="button" @click="importModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.schedules.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Pilih File Excel Jadwal (.xlsx / .xls)
                        </label>
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required
                               class="py-2 px-3 block w-full text-xs text-gray-500 file:me-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-lg cursor-pointer">
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-600 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-800">Format Kolom Header Excel:</span>
                            <a href="{{ route('admin.schedules.template') }}" class="text-blue-600 hover:text-blue-700 font-semibold inline-flex items-center gap-1 text-[11px]">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                <span>Unduh Template</span>
                            </a>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-gray-200 font-mono text-[11px] text-gray-700 overflow-x-auto whitespace-nowrap">
                            class_name, subject_code, teacher_nip, day_of_week, start_time, end_time
                        </div>
                        <ul class="text-[11px] text-gray-500 space-y-1 list-disc list-inside">
                            <li><strong>class_name</strong>: Nama kelas (misal: <code class="font-mono text-gray-800">7A</code>, <code class="font-mono text-gray-800">8A</code>).</li>
                            <li><strong>subject_code</strong>: Kode mapel yang sudah terdaftar (misal: <code class="font-mono text-gray-800">IPA-7</code>).</li>
                            <li><strong>teacher_nip</strong>: NIP guru pengajar (18 digit).</li>
                            <li><strong>start_time</strong> &amp; <strong>end_time</strong>: Format HH:MM (misal: <code class="font-mono text-gray-800">07:30</code>).</li>
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
