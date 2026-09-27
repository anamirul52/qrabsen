<x-layouts.app>
    <x-slot:title>Data Kelas</x-slot:title>
    <x-slot:header>Rombongan Belajar / Kelas</x-slot:header>

    <div class="space-y-4 sm:space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
            @foreach ($classes as $c)
                <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 shadow-2xs flex flex-col justify-between hover:border-gray-300 transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center py-0.5 px-2 rounded-md bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200/60">
                                Tingkat {{ $c->level }}
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
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
