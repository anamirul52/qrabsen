<x-layouts.app>
    <x-slot:title>Tambah Siswa Baru</x-slot:title>
    <x-slot:header>Pendaftaran Siswa Baru</x-slot:header>

    <div class="max-w-xl mx-auto space-y-4 sm:space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-2xs">
            <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-gray-100 mb-4 sm:mb-5">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-gray-900">Formulir Data Siswa</h2>
                    <p class="text-xs text-gray-500 mt-0.5">QR Code otomatis dibuat setelah data tersimpan.</p>
                </div>
                <a href="{{ route('admin.students.index') }}" class="py-1.5 px-3 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition inline-flex items-center gap-1.5 shadow-2xs">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <form action="{{ route('admin.students.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- 1. Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Lengkap Siswa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Ahmad Fauzi"
                           class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- 2. Kelas Rombel -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                        Kelas <span class="text-red-500">*</span>
                    </label>
                    <select name="class_id" required
                            class="py-2.5 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Kelas</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>
                                Kelas {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- 3. NISN & 4. Jenis Kelamin -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            NISN <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nisn" value="{{ old('nisn') }}" required placeholder="Contoh: 0081234567"
                               class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg font-mono text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        @error('nisn') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select name="gender" required
                                class="py-2.5 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                            <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                        </select>
                        @error('gender') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-x-2 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.students.index') }}"
                       class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Batal
                    </a>
                    <button type="submit"
                            class="py-2 px-4 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Data Siswa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
