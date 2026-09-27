<x-layouts.app>
    <x-slot:title>Buka Sesi Presensi Baru</x-slot:title>
    <x-slot:header>Buka Sesi Presensi Baru</x-slot:header>

    <div class="max-w-xl mx-auto space-y-4 sm:space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-2xs">
            <div class="pb-3 sm:pb-4 border-b border-gray-100 mb-4 sm:mb-5">
                <h2 class="text-sm sm:text-base font-bold text-gray-900">Konfigurasi Sesi Pembelajaran</h2>
                <p class="text-xs text-gray-500 mt-0.5">Setelah sesi dibuka, Anda akan langsung diarahkan ke halaman kamera scanner QR.</p>
            </div>

            <form action="{{ route('teacher.sessions.store') }}" method="POST" class="space-y-3.5 sm:space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                        Pilih Kelas Rombel <span class="text-red-500">*</span>
                    </label>
                    <select name="class_id" required
                            class="py-2.5 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" {{ (old('class_id') ?? $prefillSchedule?->class_id) == $c->id ? 'selected' : '' }}>
                                Kelas {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                        Mata Pelajaran <span class="text-red-500">*</span>
                    </label>
                    <select name="subject_id" required
                            class="py-2.5 px-3 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}" {{ (old('subject_id') ?? $prefillSchedule?->subject_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                        Batas Toleransi Keterlambatan (Menit) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" min="0" max="120" name="late_tolerance_minutes"
                           value="{{ old('late_tolerance_minutes') ?? $prefillSchedule?->late_tolerance_minutes ?? 15 }}" required
                           class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-[11px] text-gray-400 mt-1">Siswa yang scan lebih dari waktu ini sejak sesi dimulai otomatis berstatus TERLAMBAT.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                        Catatan Sesi (Opsional)
                    </label>
                    <textarea name="notes" rows="2" placeholder="Contoh: Materi pengenalan algoritma pemrograman..."
                              class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-x-2 pt-3 sm:pt-4 border-t border-gray-100">
                    <a href="{{ route('teacher.dashboard') }}"
                       class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                        Batal
                    </a>
                    <button type="submit"
                            class="py-2 px-4 inline-flex items-center gap-x-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        <span>Buka Sesi &amp; Scanner</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
