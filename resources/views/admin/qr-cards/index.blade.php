<x-layouts.app>
    <x-slot:title>Cetak Kartu QR Siswa</x-slot:title>
    <x-slot:header>Cetak Kartu QR Presensi</x-slot:header>

    <div class="space-y-4 sm:space-y-6">
        <!-- Control Bar (hidden on print) -->
        <div class="no-print bg-white border border-gray-200 rounded-xl p-3.5 sm:p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <form action="{{ route('admin.qr-cards.index') }}" method="GET" class="flex items-center gap-2.5">
                <label for="class_select" class="text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap">
                    Pilih Kelas:
                </label>
                <select id="class_select" name="class_id" onchange="this.form.submit()"
                        class="py-2 px-3 block border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Kelas untuk Dicetak --</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>
                            Kelas {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            @if ($students->isNotEmpty())
                <button type="button" onclick="window.print()"
                        class="py-2 px-3.5 inline-flex items-center gap-x-2 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Lembar A4 ({{ $students->count() }} Kartu)</span>
                </button>
            @endif
        </div>

        @if (!$classId)
            <div class="no-print bg-white border border-gray-200 rounded-xl p-8 sm:p-12 text-center text-gray-400 shadow-2xs">
                <i data-lucide="layout-grid" class="w-10 h-10 sm:w-12 sm:h-12 mx-auto stroke-1 mb-3 text-gray-300"></i>
                <h4 class="text-sm sm:text-base font-bold text-gray-800 mb-1">Pilih Rombongan Belajar / Kelas</h4>
                <p class="text-xs max-w-sm mx-auto text-gray-500">Silakan pilih kelas di atas untuk menampilkan dan mencetak seluruh kartu QR siswa sekaligus dalam format cetak A4.</p>
            </div>
        @elseif ($students->isEmpty())
            <div class="no-print bg-white border border-gray-200 rounded-xl p-8 sm:p-12 text-center text-gray-400 shadow-2xs">
                <i data-lucide="user-x" class="w-10 h-10 sm:w-12 sm:h-12 mx-auto stroke-1 mb-3 text-gray-300"></i>
                <p class="text-xs text-gray-500">Tidak ada siswa aktif di kelas yang dipilih.</p>
            </div>
        @else
            <!-- Printable Cards Grid -->
            <div class="print:block">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4 print:grid-cols-2 print:gap-3">
                    @foreach ($students as $st)
                        <div class="border-2 border-gray-900 rounded-xl p-3.5 sm:p-4 bg-white flex flex-col justify-between shadow-2xs print:shadow-none print:break-inside-avoid print:border-gray-900">
                            <!-- Card Header -->
                            <div class="flex items-center justify-between border-b-2 border-gray-900 pb-2 mb-2.5">
                                <div>
                                    <div class="text-[9px] font-bold uppercase tracking-wider text-gray-700">SMP NEGERI 2 MIJEN</div>
                                    <div class="text-xs font-bold text-blue-700 tracking-tight">KARTU PRESENSI SISWA</div>
                                </div>
                                <div class="px-2 py-0.5 rounded-md bg-gray-900 text-white font-bold text-[10px]">
                                    {{ $st->currentClass?->name }}
                                </div>
                            </div>

                            <!-- Card Body: QR & Details -->
                            <div class="flex items-center gap-x-3 my-1">
                                <!-- QR Image Container -->
                                <div class="w-24 h-24 sm:w-28 sm:h-28 shrink-0 flex items-center justify-center p-1 bg-white border border-gray-300 rounded-lg overflow-hidden">
                                    {!! $st->qrSvg !!}
                                </div>

                                <!-- Student Details -->
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div>
                                        <span class="text-[9px] text-gray-500 uppercase block font-semibold">Nama Siswa</span>
                                        <div class="font-bold text-gray-900 text-xs sm:text-sm leading-tight line-clamp-2">
                                            {{ $st->name }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-[9px] text-gray-500 uppercase block font-semibold">NIS</span>
                                        <div class="font-mono text-xs font-bold text-gray-900">
                                            {{ $st->nis }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-[9px] text-gray-500 uppercase block font-semibold">Tahun Pelajaran</span>
                                        <div class="text-[10px] sm:text-[11px] font-medium text-gray-700">
                                            2025/2026 Ganjil
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="mt-2.5 pt-2 border-t border-dashed border-gray-300 flex items-center justify-between text-[9px] text-gray-500">
                                <span>Tunjukkan saat presensi kelas</span>
                                <span class="font-bold text-gray-800">SIPRES</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
