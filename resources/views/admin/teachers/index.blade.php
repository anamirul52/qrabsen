<x-layouts.app>
    <x-slot:title>Data Guru</x-slot:title>
    <x-slot:header>Data Guru Pengajar</x-slot:header>

    <div class="space-y-4 sm:space-y-6" x-data="{ 
        createModalOpen: false, 
        importModalOpen: false,
        editModalOpen: false,
        editForm: {
            id: '',
            name: '',
            nip: '',
            email: '',
            username: '',
            phone: '',
            gender: 'L',
            is_active: true,
            homeroom_class_id: '',
            actionUrl: ''
        },
        openEdit(t) {
            this.editForm = {
                id: t.id,
                name: t.user ? t.user.name : '',
                nip: t.nip || '',
                email: t.user ? t.user.email : '',
                username: t.user ? t.user.username : '',
                phone: t.user ? (t.user.phone || '') : '',
                gender: t.gender || 'L',
                is_active: t.user ? Boolean(t.user.is_active) : true,
                homeroom_class_id: t.homeroom_class ? t.homeroom_class.id : '',
                actionUrl: '{{ url('admin/teachers') }}/' + t.id
            };
            this.editModalOpen = true;
        }
    }">

        <!-- Preline Toolbar & Filters Card -->
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs flex flex-col gap-3">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <!-- Filters Form -->
                <form action="{{ route('admin.teachers.index') }}" method="GET" class="flex-1 flex flex-wrap items-center gap-2">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[160px] sm:min-w-[220px]">
                        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3 text-gray-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIP, email, atau username..."
                               class="py-2 ps-9 pe-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Gender Filter -->
                    <select name="gender" onchange="this.form.submit()"
                            class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Jenis Kelamin</option>
                        <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>

                    @if (request()->hasAny(['search', 'gender']))
                        <a href="{{ route('admin.teachers.index') }}" class="py-2 px-2.5 inline-flex items-center text-gray-400 hover:text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 text-xs" title="Reset filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </form>

                <!-- Actions Toolbar -->
                <div class="flex items-center flex-wrap gap-2 shrink-0">
                    <!-- Template Excel -->
                    <a href="{{ route('admin.teachers.template') }}"
                       class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 shadow-2xs hover:bg-gray-50 transition">
                        <i data-lucide="download" class="w-4 h-4 text-gray-500"></i>
                        <span>Template Excel</span>
                    </a>

                    <!-- Export Excel -->
                    <a href="{{ route('admin.teachers.export', request()->all()) }}"
                       class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 shadow-2xs hover:bg-emerald-100 transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                        <span>Export Excel</span>
                    </a>

                    <!-- Import Excel Button -->
                    <button type="button" @click="importModalOpen = true"
                            class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 shadow-2xs hover:bg-gray-50 transition cursor-pointer">
                        <i data-lucide="upload" class="w-4 h-4 text-gray-500"></i>
                        <span>Import Excel</span>
                    </button>

                    <!-- Tambah Guru Button -->
                    <button type="button" @click="createModalOpen = true"
                            class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-2xs transition cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tambah Guru</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Preline Teachers Table Card (360px Native Optimized) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="p-3.5 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase tracking-wider">Daftar Guru &amp; Wali Kelas</h3>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Total {{ $teachers->total() }} tenaga pengajar terdaftar</p>
                </div>
            </div>

            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <div class="min-w-full inline-block align-middle px-3 sm:px-0">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm text-start">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-3 sm:px-4 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Nama Guru</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">NIP</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Email &amp; Kontak</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Jenis Kelamin</th>
                                <th class="py-3 px-2.5 sm:px-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-700">Wali Kelas</th>
                                <th class="py-3 px-3 sm:px-4 text-end text-xs font-semibold uppercase tracking-wider text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($teachers as $t)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-3 sm:px-4">
                                        <div class="font-bold text-gray-900 flex items-center gap-1.5">
                                            <span>{{ $t->user?->name }}</span>
                                            @if ($t->user && !$t->user->is_active)
                                                <span class="py-0.5 px-1.5 rounded text-[10px] bg-rose-50 text-rose-600 font-semibold border border-rose-200">Non-Aktif</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-500">Username: <span class="font-mono text-gray-700 font-medium">{{ $t->user?->username }}</span></div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3 font-mono text-gray-700">
                                        {{ $t->nip ?? '-' }}
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <div class="text-gray-900 truncate max-w-[180px] sm:max-w-none">{{ $t->user?->email }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $t->user?->phone ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        <span class="inline-flex items-center py-0.5 px-2 rounded-md text-xs font-medium {{ $t->gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200/60' : 'bg-pink-50 text-pink-700 border border-pink-200/60' }}">
                                            {{ $t->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2.5 sm:px-3">
                                        @if ($t->homeroomClass)
                                            <span class="inline-flex items-center gap-1 py-0.5 px-2 rounded-md bg-emerald-50 text-emerald-800 text-xs font-semibold border border-emerald-200">
                                                Wali {{ $t->homeroomClass->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-end whitespace-nowrap">
                                        <div class="inline-flex items-center gap-x-1">
                                            <!-- Edit Button -->
                                            <button type="button" 
                                                    @click="openEdit({{ json_encode([
                                                        'id' => $t->id,
                                                        'nip' => $t->nip,
                                                        'gender' => $t->gender,
                                                        'user' => [
                                                            'name' => $t->user?->name,
                                                            'email' => $t->user?->email,
                                                            'username' => $t->user?->username,
                                                            'phone' => $t->user?->phone,
                                                            'is_active' => $t->user?->is_active ?? true,
                                                        ],
                                                        'homeroom_class' => $t->homeroomClass ? ['id' => $t->homeroomClass->id, 'name' => $t->homeroomClass->name] : null
                                                    ]) }})"
                                                    class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg inline-flex cursor-pointer transition"
                                                    title="Edit Data Guru">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>

                                            <!-- Delete Button -->
                                            <form action="{{ route('admin.teachers.destroy', $t) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru {{ addslashes($t->name) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg inline-flex cursor-pointer transition"
                                                        title="Hapus Guru">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-gray-400">
                                        <i data-lucide="user-x" class="w-8 h-8 mx-auto stroke-1 mb-2 text-gray-300"></i>
                                        <p class="text-sm font-medium">Tidak ada data guru pengajar ditemukan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($teachers->hasPages())
                <div class="py-3 px-4 border-t border-gray-200">
                    {{ $teachers->links() }}
                </div>
            @endif
        </div>

        <!-- ================= PRELINE MODAL TAMBAH GURU ================= -->
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
                        <i data-lucide="user-plus" class="w-5 h-5 text-blue-600"></i>
                        <span>Tambah Data Guru Baru</span>
                    </h3>
                    <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Lengkap Guru &amp; Gelar <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="Contoh: Budi Santoso, S.Pd."
                               class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                NIP <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" name="nip" placeholder="18 digit NIP"
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg font-mono text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Jenis Kelamin <span class="text-rose-500">*</span>
                            </label>
                            <select name="gender" required
                                    class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Email Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" required placeholder="guru@sipres.test"
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                No. Telepon / WA
                            </label>
                            <input type="text" name="phone" placeholder="081234567890"
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Username <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="username" required placeholder="budi_santoso"
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg font-mono text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Password Awal
                            </label>
                            <input type="password" name="password" placeholder="Default: password"
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Tugas Wali Kelas
                        </label>
                        <select name="homeroom_class_id"
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="">- Bukan Wali Kelas -</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">
                                    Kelas {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-x-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="createModalOpen = false"
                                class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2 px-3.5 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs cursor-pointer">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Simpan Guru</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= PRELINE MODAL EDIT GURU ================= -->
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
                        <span>Edit Data Guru Pengajar</span>
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
                            Nama Lengkap Guru &amp; Gelar <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="editForm.name" required
                               class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                NIP <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" name="nip" x-model="editForm.nip"
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg font-mono text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Jenis Kelamin <span class="text-rose-500">*</span>
                            </label>
                            <select name="gender" x-model="editForm.gender" required
                                    class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Email Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" x-model="editForm.email" required
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                No. Telepon / WA
                            </label>
                            <input type="text" name="phone" x-model="editForm.phone"
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Username <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="username" x-model="editForm.username" required
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg font-mono text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Ganti Password <span class="text-gray-400 font-normal">(Kosongkan jika tidak diganti)</span>
                            </label>
                            <input type="password" name="password" placeholder="Password baru"
                                   class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Tugas Wali Kelas
                        </label>
                        <select name="homeroom_class_id" x-model="editForm.homeroom_class_id"
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="">- Bukan Wali Kelas -</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">
                                    Kelas {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-x-2 pt-1">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" x-model="editForm.is_active"
                               class="border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                        <label for="edit_is_active" class="text-xs font-semibold text-gray-700 select-none">
                            Akun Aktif (Dapat login dan mengajar)
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-x-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="editModalOpen = false"
                                class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2 px-3.5 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs cursor-pointer">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Perbarui Data Guru</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= PRELINE MODAL IMPORT EXCEL GURU ================= -->
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
                        <span>Import Data Guru (Excel)</span>
                    </h3>
                    <button type="button" @click="importModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Pilih File Excel Guru (.xlsx / .xls)
                        </label>
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required
                               class="py-2 px-3 block w-full text-xs text-gray-500 file:me-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-lg cursor-pointer">
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-600 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-800">Format Kolom Header Excel:</span>
                            <a href="{{ route('admin.teachers.template') }}" class="text-blue-600 hover:text-blue-700 font-semibold inline-flex items-center gap-1 text-[11px]">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                <span>Unduh Template</span>
                            </a>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-gray-200 font-mono text-[11px] text-gray-700 overflow-x-auto whitespace-nowrap">
                            nip,name,gender,email,phone,username,homeroom_class
                        </div>
                        <ul class="text-[11px] text-gray-500 space-y-1 list-disc list-inside">
                            <li><strong>name</strong> &amp; <strong>email</strong> wajib diisi untuk tiap baris.</li>
                            <li><strong>gender</strong>: <code class="text-gray-700 font-mono">L</code> (Laki-laki) atau <code class="text-gray-700 font-mono">P</code> (Perempuan).</li>
                            <li>Password awal seluruh akun guru baru adalah: <code class="font-mono text-gray-800 font-semibold">password</code>.</li>
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
