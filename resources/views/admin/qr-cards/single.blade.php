<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Presensi - {{ $student->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col items-center justify-center p-3 sm:p-4">

    <!-- Top Action Bar -->
    <div class="no-print mb-6 flex flex-wrap items-center justify-center gap-2">
        <button onclick="window.print()" class="py-2.5 px-4 inline-flex items-center gap-x-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-xs cursor-pointer transition">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Cetak Kartu</span>
        </button>
        <button onclick="window.close()" class="py-2.5 px-4 inline-flex items-center gap-x-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs sm:text-sm font-medium hover:bg-slate-50 cursor-pointer transition">
            Tutup
        </button>
    </div>

    <!-- Student QR Card (Credit Card Size Layout 85.6mm x 54mm equivalent) -->
    <div class="w-full max-w-sm bg-white border-2 border-slate-900 rounded-3xl p-6 shadow-xl print:shadow-none print:m-0 print:border-2 print:border-black">
        <!-- Header -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3 mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                    S2M
                </div>
                <div>
                    <div class="text-[10px] font-bold text-slate-600 uppercase tracking-widest leading-none">SMP NEGERI 2 MIJEN</div>
                    <div class="text-sm font-black text-slate-900 mt-0.5">KARTU PRESENSI QR</div>
                </div>
            </div>
            <div class="px-2.5 py-1 rounded-lg bg-blue-600 text-white text-xs font-bold">
                {{ $student->currentClass?->name ?? '-' }}
            </div>
        </div>

        <!-- Body -->
        <div class="flex flex-col items-center text-center">
            <div class="p-2 border-2 border-slate-200 rounded-2xl bg-white mb-3 shadow-inner">
                {!! $qrSvg !!}
            </div>

            <h2 class="text-lg font-black text-slate-900 leading-tight">
                {{ $student->name }}
            </h2>
            <div class="text-xs font-mono font-bold text-blue-600 mt-0.5">
                NISN: {{ $student->nisn ?: $student->nis }}
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">
                {{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }} • Kelas {{ $student->currentClass?->name ?? '-' }}
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between text-[10px] text-slate-400">
            <span>SIPRES • Presensi Siswa Berbasis QR</span>
            <span class="font-bold text-slate-700">STATUS: AKTIF</span>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
