<x-layouts.app>
    <x-slot:title>Scanner QR - Kelas {{ $session->schoolClass->name }}</x-slot:title>
    <x-slot:header>Scanner Presensi Kelas {{ $session->schoolClass->name }}</x-slot:header>

    <div x-data="scannerApp()" x-init="initScanner()" class="space-y-4 max-w-6xl mx-auto">
        
        <!-- Header Info Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-lg bg-blue-600 text-white font-black text-xs sm:text-sm">
                        KELAS {{ $session->schoolClass->name }}
                    </span>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900">
                        {{ $session->subject->name }}
                    </h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $session->date?->isoFormat('dddd, D MMMM Y') }} • Toleransi Terlambat: <span class="font-semibold text-slate-700">{{ $session->late_tolerance_minutes }} menit</span>
                </p>
            </div>

            <!-- Real-Time Counter & Action Buttons -->
            <div class="flex items-center flex-wrap gap-2 shrink-0">
                <div class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Kehadiran</div>
                    <div class="text-sm sm:text-base font-black text-blue-700 leading-tight">
                        <span x-text="stats.attended_count">{{ $stats['attended_count'] }}</span> / {{ $stats['total_students'] }}
                    </div>
                </div>

                <button type="button" @click="manualModalOpen = true"
                        class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 shadow-2xs transition cursor-pointer">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Manual</span>
                </button>

                <button type="button" @click="closeModalOpen = true"
                        class="py-2 px-3 inline-flex items-center gap-x-1.5 text-xs font-bold rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-2xs transition cursor-pointer">
                    <i data-lucide="stop-circle" class="w-3.5 h-3.5"></i>
                    <span>Tutup Sesi</span>
                </button>
            </div>
        </div>

        <!-- Scanner View & Real-Time Feed Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            <!-- Left Column: Camera Viewfinder & Instant Feedback (7 Cols) -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Camera Box -->
                <div class="bg-slate-900 rounded-2xl overflow-hidden shadow-lg border-2 border-slate-800 relative">
                    
                    <!-- Scanner Container -->
                    <div id="qr-reader" class="w-full bg-slate-950 aspect-4/3 sm:aspect-16/10 flex items-center justify-center text-white">
                        <div x-show="!scannerStarted" class="text-center p-6 text-slate-400">
                            <i data-lucide="camera" class="w-12 h-12 mx-auto stroke-1 mb-3 animate-pulse text-blue-400"></i>
                            <p class="text-sm font-semibold text-slate-200">Menyiapkan Kamera Guru...</p>
                            <p class="text-xs text-slate-400 mt-1">Pastikan izin kamera browser telah diaktifkan.</p>
                            <button type="button" @click="startCamera()" class="mt-4 px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 cursor-pointer">
                                Aktifkan Kamera Sekarang
                            </button>
                        </div>
                    </div>

                    <!-- Scanning Overlay Overlay Viewfinder -->
                    <div x-show="scannerStarted" class="absolute inset-0 pointer-events-none flex flex-col justify-between p-4">
                        <div class="flex items-center justify-between text-xs text-white/80 bg-slate-900/60 backdrop-blur-xs px-3 py-1.5 rounded-xl w-fit">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping mr-2"></span>
                            <span class="font-medium">Scanner Aktif • Arahkan QR Siswa</span>
                        </div>

                        <!-- Center Targeting Guide -->
                        <div class="self-center w-48 h-48 sm:w-60 sm:h-60 border-2 border-blue-400/60 rounded-2xl relative">
                            <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-blue-500 rounded-tl-lg"></div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-blue-500 rounded-tr-lg"></div>
                            <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-blue-500 rounded-bl-lg"></div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-blue-500 rounded-br-lg"></div>
                        </div>

                        <div class="text-center text-[11px] text-white/70 bg-slate-900/60 backdrop-blur-xs py-1 px-3 rounded-lg mx-auto">
                            Scanner tidak perlu di-restart setelah scan
                        </div>
                    </div>
                </div>

                <!-- Instant Visual Feedback Alert -->
                <div x-show="feedback.show"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="p-4 rounded-2xl border shadow-sm text-sm"
                     :class="{
                         'bg-emerald-50 border-emerald-300 text-emerald-900': feedback.type === 'HADIR',
                         'bg-amber-50 border-amber-300 text-amber-900': feedback.type === 'TERLAMBAT',
                         'bg-blue-50 border-blue-300 text-blue-900': feedback.type === 'DUPLICATE',
                         'bg-rose-50 border-rose-300 text-rose-900': feedback.type === 'ERROR'
                     }"
                     style="display: none;">
                    
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="p-2 rounded-xl shrink-0 mt-0.5"
                             :class="{
                                 'bg-emerald-200/80 text-emerald-800': feedback.type === 'HADIR',
                                 'bg-amber-200/80 text-amber-800': feedback.type === 'TERLAMBAT',
                                 'bg-blue-200/80 text-blue-800': feedback.type === 'DUPLICATE',
                                 'bg-rose-200/80 text-rose-800': feedback.type === 'ERROR'
                             }">
                            <template x-if="feedback.type === 'HADIR' || feedback.type === 'TERLAMBAT'">
                                <i data-lucide="check" class="w-5 h-5 stroke-[2.5]"></i>
                            </template>
                            <template x-if="feedback.type === 'DUPLICATE'">
                                <i data-lucide="info" class="w-5 h-5 stroke-[2.5]"></i>
                            </template>
                            <template x-if="feedback.type === 'ERROR'">
                                <i data-lucide="alert-triangle" class="w-5 h-5 stroke-[2.5]"></i>
                            </template>
                        </div>

                        <!-- Feedback Text -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-black text-sm uppercase tracking-wide" x-text="feedback.title"></span>
                                <span class="text-xs font-mono font-bold" x-text="feedback.time"></span>
                            </div>

                            <div class="mt-1 font-extrabold text-base leading-tight" x-text="feedback.studentName"></div>
                            
                            <div class="text-xs mt-0.5 opacity-90" x-text="feedback.message"></div>
                        </div>
                    </div>
                </div>

                <!-- Camera Controls & Device Selector -->
                <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between text-xs text-slate-600">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="video" class="w-4 h-4 text-slate-400"></i>
                        <span>Kamera:</span>
                        <select id="camera-select" @change="changeCamera($event.target.value)"
                                class="border border-slate-200 rounded-lg px-2 py-1 bg-white text-xs text-slate-700">
                            <template x-for="cam in cameras" :key="cam.id">
                                <option :value="cam.id" x-text="cam.label || 'Kamera ' + cam.id"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" @click="restartScanner()" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-500" title="Muat Ulang Scanner">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Real-Time Attendance Stream & Class List (5 Cols) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col h-[520px] lg:h-auto overflow-hidden">
                
                <!-- Tab Header -->
                <div class="p-4 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-1.5">
                            <i data-lucide="list-checks" class="w-4 h-4 text-blue-600"></i>
                            <span>Daftar Kehadiran Siswa</span>
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Terkini: <span class="font-semibold text-emerald-600" x-text="stats.present_count + stats.late_count">0</span> Hadir • <span class="font-semibold text-rose-500" x-text="stats.unattended_count">0</span> Belum
                        </p>
                    </div>

                    <!-- Search within class -->
                    <input type="text" x-model="studentFilter" placeholder="Filter siswa..."
                           class="text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 w-28 sm:w-36">
                </div>

                <!-- Students Stream List -->
                <div class="flex-1 overflow-y-auto divide-y divide-slate-100 p-2 space-y-1">
                    <template x-for="st in filteredStudents" :key="st.id">
                        <div class="p-2.5 rounded-xl flex items-center justify-between transition"
                             :class="{
                                 'bg-emerald-50/70 border border-emerald-100': st.status === 'HADIR',
                                 'bg-amber-50/70 border border-amber-100': st.status === 'TERLAMBAT',
                                 'bg-blue-50/70 border border-blue-100': st.status === 'IZIN',
                                 'bg-purple-50/70 border border-purple-100': st.status === 'SAKIT',
                                 'bg-slate-50 border border-slate-100 opacity-70': !st.status || st.status === 'ALPA'
                             }">
                            <div class="min-w-0 pr-2">
                                <div class="font-semibold text-slate-900 text-xs truncate" x-text="st.name"></div>
                                <div class="text-[10px] text-slate-500 font-mono" x-text="'NIS: ' + st.nis"></div>
                            </div>

                            <div class="text-right shrink-0 flex items-center space-x-1.5">
                                <template x-if="st.status">
                                    <div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider block"
                                              :class="{
                                                  'bg-emerald-100 text-emerald-800': st.status === 'HADIR',
                                                  'bg-amber-100 text-amber-800': st.status === 'TERLAMBAT',
                                                  'bg-blue-100 text-blue-800': st.status === 'IZIN',
                                                  'bg-purple-100 text-purple-800': st.status === 'SAKIT',
                                                  'bg-rose-100 text-rose-800': st.status === 'ALPA'
                                              }"
                                              x-text="st.status">
                                        </span>
                                        <span class="text-[9px] font-mono text-slate-400 block mt-0.5" x-text="st.time || ''"></span>
                                    </div>
                                </template>
                                <template x-if="!st.status">
                                    <span class="text-[11px] font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">
                                        Belum Scan
                                    </span>
                                </template>

                                <!-- Quick manual edit button -->
                                <button type="button" @click="openQuickManual(st)" class="p-1 text-slate-400 hover:text-slate-600 rounded">
                                    <i data-lucide="more-vertical" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Manual Attendance Modal -->
        <div x-show="manualModalOpen"
             x-transition:enter="transition-opacity ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-100"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             style="display: none;">
            
            <div @click.away="manualModalOpen = false"
                 class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center space-x-2">
                        <i data-lucide="user-check" class="w-5 h-5 text-blue-600"></i>
                        <span>Input Presensi Manual</span>
                    </h3>
                    <button type="button" @click="manualModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form @submit.prevent="submitManualAttendance()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Pilih Siswa
                        </label>
                        <select x-model="manualForm.student_id" required
                                class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white">
                            <option value="">-- Pilih Siswa --</option>
                            <template x-for="st in studentsList" :key="st.id">
                                <option :value="st.id" x-text="st.name + ' (' + st.nis + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Status Kehadiran
                        </label>
                        <div class="grid grid-cols-3 sm:grid-cols-5 gap-1.5">
                            <label class="p-2 border rounded-xl text-center text-xs font-bold cursor-pointer transition"
                                   :class="manualForm.status === 'HADIR' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200'">
                                <input type="radio" x-model="manualForm.status" value="HADIR" class="sr-only">
                                Hadir
                            </label>
                            <label class="p-2 border rounded-xl text-center text-xs font-bold cursor-pointer transition"
                                   :class="manualForm.status === 'TERLAMBAT' ? 'bg-amber-600 text-white border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200'">
                                <input type="radio" x-model="manualForm.status" value="TERLAMBAT" class="sr-only">
                                Terlambat
                            </label>
                            <label class="p-2 border rounded-xl text-center text-xs font-bold cursor-pointer transition"
                                   :class="manualForm.status === 'IZIN' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-700 border-slate-200'">
                                <input type="radio" x-model="manualForm.status" value="IZIN" class="sr-only">
                                Izin
                            </label>
                            <label class="p-2 border rounded-xl text-center text-xs font-bold cursor-pointer transition"
                                   :class="manualForm.status === 'SAKIT' ? 'bg-purple-600 text-white border-purple-600' : 'bg-slate-50 text-slate-700 border-slate-200'">
                                <input type="radio" x-model="manualForm.status" value="SAKIT" class="sr-only">
                                Sakit
                            </label>
                            <label class="p-2 border rounded-xl text-center text-xs font-bold cursor-pointer transition"
                                   :class="manualForm.status === 'ALPA' ? 'bg-rose-600 text-white border-rose-600' : 'bg-slate-50 text-slate-700 border-slate-200'">
                                <input type="radio" x-model="manualForm.status" value="ALPA" class="sr-only">
                                Alpa
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Catatan / Keterangan
                        </label>
                        <input type="text" x-model="manualForm.notes" placeholder="Contoh: Lupa bawa kartu / Sakit demam"
                               class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="manualModalOpen = false" class="py-2 px-3.5 inline-flex items-center text-xs font-medium rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="py-2 px-4 inline-flex items-center text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition cursor-pointer">
                            Simpan Presensi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Close Session Confirmation Modal -->
        <div x-show="closeModalOpen"
             x-transition:enter="transition-opacity ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             style="display: none;">
            
            <div @click.away="closeModalOpen = false" class="bg-white rounded-2xl max-w-sm w-full p-5 sm:p-6 shadow-xl border border-slate-100 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 mx-auto flex items-center justify-center mb-3">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Tutup Sesi Presensi?</h3>
                <p class="text-xs text-slate-500 mt-2">
                    Siswa yang belum tercatat presensi akan <strong>otomatis ditandai ALPA</strong>. Sesi akan selesai dan rekapitulasi akan disimpan.
                </p>

                <form action="{{ route('teacher.sessions.close', $session) }}" method="POST" class="mt-5 flex items-center justify-center gap-2">
                    @csrf
                    <button type="button" @click="closeModalOpen = false" class="py-2 px-3.5 inline-flex items-center text-xs font-semibold rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                        Kembali Scan
                    </button>
                    <button type="submit" class="py-2 px-4 inline-flex items-center text-xs font-bold rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-2xs transition cursor-pointer">
                        Ya, Tutup Sesi
                    </button>
                </form>
            </div>
        </div>

        <!-- Popup Modal Hasil Scan Presensi -->
        <div x-show="popupModal.show"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4"
             @keydown.window.escape="closePopupModal()"
             style="display: none;">
            
            <div @click.away="closePopupModal()"
                 class="relative w-full max-w-sm sm:max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border text-center overflow-hidden transition-all"
                 :class="{
                     'border-emerald-200 ring-4 ring-emerald-100/70': popupModal.type === 'HADIR',
                     'border-amber-200 ring-4 ring-amber-100/70': popupModal.type === 'TERLAMBAT',
                     'border-blue-200 ring-4 ring-blue-100/70': popupModal.type === 'DUPLICATE',
                     'border-rose-200 ring-4 ring-rose-100/70': popupModal.type === 'ERROR'
                 }">
                
                <!-- Status Icon Header -->
                <div class="mb-4">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl mx-auto flex items-center justify-center shadow-lg transition-transform duration-300 transform scale-100"
                         :class="{
                             'bg-emerald-500 text-white shadow-emerald-500/30': popupModal.type === 'HADIR',
                             'bg-amber-500 text-white shadow-amber-500/30': popupModal.type === 'TERLAMBAT',
                             'bg-blue-500 text-white shadow-blue-500/30': popupModal.type === 'DUPLICATE',
                             'bg-rose-500 text-white shadow-rose-500/30': popupModal.type === 'ERROR'
                         }">
                        <template x-if="popupModal.type === 'HADIR'">
                            <i data-lucide="check-circle" class="w-10 h-10 sm:w-12 sm:h-12 stroke-[2.5]"></i>
                        </template>
                        <template x-if="popupModal.type === 'TERLAMBAT'">
                            <i data-lucide="clock" class="w-10 h-10 sm:w-12 sm:h-12 stroke-[2.5]"></i>
                        </template>
                        <template x-if="popupModal.type === 'DUPLICATE'">
                            <i data-lucide="alert-circle" class="w-10 h-10 sm:w-12 sm:h-12 stroke-[2.5]"></i>
                        </template>
                        <template x-if="popupModal.type === 'ERROR'">
                            <i data-lucide="x-circle" class="w-10 h-10 sm:w-12 sm:h-12 stroke-[2.5]"></i>
                        </template>
                    </div>

                    <!-- Badge Pill -->
                    <div class="mt-3">
                        <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5 shadow-2xs"
                              :class="{
                                  'bg-emerald-100 text-emerald-800 border border-emerald-300': popupModal.type === 'HADIR',
                                  'bg-amber-100 text-amber-800 border border-amber-300': popupModal.type === 'TERLAMBAT',
                                  'bg-blue-100 text-blue-800 border border-blue-300': popupModal.type === 'DUPLICATE',
                                  'bg-rose-100 text-rose-800 border border-rose-300': popupModal.type === 'ERROR'
                              }"
                              x-text="popupModal.badgeText">
                        </span>
                    </div>
                </div>

                <!-- Main Text Details -->
                <div class="space-y-1.5">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug" x-text="popupModal.studentName"></h2>
                    
                    <template x-if="popupModal.studentClass && popupModal.studentNis && popupModal.studentNis !== '-'">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-slate-100 text-xs sm:text-sm font-bold text-slate-700">
                            <span x-text="'Kelas ' + popupModal.studentClass"></span>
                            <span>•</span>
                            <span class="font-mono text-blue-600" x-text="'NIS: ' + popupModal.studentNis"></span>
                        </div>
                    </template>

                    <p class="text-xs sm:text-sm text-slate-600 mt-1" x-text="popupModal.message"></p>

                    <template x-if="popupModal.time && popupModal.time !== '-'">
                        <div class="pt-2 text-xs font-medium text-slate-500 flex items-center justify-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Waktu scan:</span>
                            <span class="font-mono font-bold text-slate-800" x-text="popupModal.time + ' WIB'"></span>
                        </div>
                    </template>
                </div>

                <!-- Auto-close countdown progress bar -->
                <div class="mt-5 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    <div class="h-full transition-all duration-75 ease-linear rounded-full"
                         :style="'width: ' + popupModal.timerProgress + '%'"
                         :class="{
                             'bg-emerald-500': popupModal.type === 'HADIR',
                             'bg-amber-500': popupModal.type === 'TERLAMBAT',
                             'bg-blue-500': popupModal.type === 'DUPLICATE',
                             'bg-rose-500': popupModal.type === 'ERROR'
                         }">
                    </div>
                </div>

                <!-- Dismiss Button -->
                <button type="button" @click="closePopupModal()"
                        class="mt-4 w-full py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white shadow-2xs transition cursor-pointer flex items-center justify-center gap-2"
                        :class="{
                            'bg-emerald-600 hover:bg-emerald-700': popupModal.type === 'HADIR',
                            'bg-amber-600 hover:bg-amber-700': popupModal.type === 'TERLAMBAT',
                            'bg-blue-600 hover:bg-blue-700': popupModal.type === 'DUPLICATE',
                            'bg-rose-600 hover:bg-rose-700': popupModal.type === 'ERROR'
                        }">
                    <i data-lucide="scan-line" class="w-4 h-4"></i>
                    <span>Lanjut Scan Berikutnya (Otomatis)</span>
                </button>
            </div>
        </div>

    </div>

    <!-- Scanner Alpine & Html5Qrcode Core Logic -->
    <script>
        function scannerApp() {
            return {
                sessionId: {{ $session->id }},
                scannerStarted: false,
                html5QrCode: null,
                cameras: [],
                selectedCameraId: null,
                isProcessing: false,
                lastScannedCode: null,
                lastScanTimestamp: 0,
                manualModalOpen: false,
                closeModalOpen: false,
                studentFilter: '',
                stats: @json($stats),
                studentsList: @json($studentsList),
                feedback: {
                    show: false,
                    type: '',
                    title: '',
                    message: '',
                    studentName: '',
                    time: ''
                },
                popupModal: {
                    show: false,
                    type: 'HADIR',
                    title: 'BERHASIL ABSEN!',
                    badgeText: 'HADIR TEPAT WAKTU',
                    studentName: '',
                    studentNis: '',
                    studentClass: '',
                    time: '',
                    message: '',
                    timerProgress: 100
                },
                manualForm: {
                    student_id: '',
                    status: 'HADIR',
                    notes: ''
                },

                get filteredStudents() {
                    if (!this.studentFilter) return this.studentsList;
                    const q = this.studentFilter.toLowerCase();
                    return this.studentsList.filter(s => 
                        s.name.toLowerCase().includes(q) || s.nis.toLowerCase().includes(q)
                    );
                },

                initScanner() {
                    this.$nextTick(() => {
                        this.startCamera();
                    });
                },

                async startCamera() {
                    try {
                        const devices = await Html5Qrcode.getCameras();
                        if (devices && devices.length > 0) {
                            this.cameras = devices;
                            // Prefer back camera if available
                            const backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('belakang') || d.label.toLowerCase().includes('environment'));
                            this.selectedCameraId = backCam ? backCam.id : devices[0].id;
                            
                            this.initHtml5Qrcode(this.selectedCameraId);
                        } else {
                            alert('Tidak ada kamera terdeteksi di perangkat Anda.');
                        }
                    } catch (err) {
                        console.error('Error getting cameras:', err);
                    }
                },

                initHtml5Qrcode(cameraId) {
                    if (this.html5QrCode) {
                        this.html5QrCode.stop().then(() => {
                            this.html5QrCode.clear();
                            this.launchCamera(cameraId);
                        }).catch(() => {
                            this.launchCamera(cameraId);
                        });
                    } else {
                        this.launchCamera(cameraId);
                    }
                },

                launchCamera(cameraId) {
                    this.html5QrCode = new Html5Qrcode("qr-reader");
                    const config = {
                        fps: 15,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.333333
                    };

                    this.html5QrCode.start(
                        cameraId,
                        config,
                        (decodedText) => this.onScanSuccess(decodedText),
                        (errorMessage) => {
                            // parse error, silent ignore
                        }
                    ).then(() => {
                        this.scannerStarted = true;
                    }).catch(err => {
                        console.error('Failed to start camera:', err);
                    });
                },

                changeCamera(cameraId) {
                    this.selectedCameraId = cameraId;
                    this.initHtml5Qrcode(cameraId);
                },

                restartScanner() {
                    this.startCamera();
                },

                async onScanSuccess(decodedText) {
                    if (this.popupModal.show) {
                        return;
                    }

                    const now = Date.now();
                    // Debounce same QR within 2.5 seconds to avoid rapid duplicate triggers
                    if (this.lastScannedCode === decodedText && (now - this.lastScanTimestamp) < 2500) {
                        return;
                    }

                    if (this.isProcessing) return;
                    this.isProcessing = true;
                    this.lastScannedCode = decodedText;
                    this.lastScanTimestamp = now;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch(`/teacher/sessions/${this.sessionId}/scan`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                token: decodedText,
                                device_info: navigator.userAgent
                            })
                        });

                        const data = await res.json();
                        this.handleScanResult(data);
                    } catch (err) {
                        console.error('Scan request error:', err);
                        this.showFeedback('ERROR', 'KONEKSI GAGAL', 'Terjadi kesalahan komunikasi dengan server.', '-', '-');
                        this.triggerPopup('ERROR', 'KONEKSI GAGAL', 'ERROR SERVER', 'Gagal Terhubung', '-', '-', '-', 'Terjadi kesalahan komunikasi jaringan dengan server.', 2500);
                        if (window.playBeep) window.playBeep('error');
                    } finally {
                        setTimeout(() => {
                            if (!this.popupModal.show) {
                                this.isProcessing = false;
                            }
                        }, 800);
                    }
                },

                handleScanResult(data) {
                    if (data.success) {
                        const isLate = (data.status === 'TERLAMBAT');
                        const type = isLate ? 'TERLAMBAT' : 'HADIR';
                        const title = isLate ? 'BERHASIL ABSEN (TERLAMBAT)' : 'BERHASIL ABSEN!';
                        const badgeText = isLate ? 'TERLAMBAT' : 'HADIR TEPAT WAKTU';
                        
                        this.showFeedback(
                            type,
                            title,
                            `Kelas ${data.student.class} • Berhasil dicatat.`,
                            data.student.name,
                            data.scanned_at
                        );

                        // Trigger Popup Modal Berhasil Absen
                        this.triggerPopup(
                            type,
                            title,
                            badgeText,
                            data.student.name,
                            data.student.nis,
                            data.student.class,
                            data.scanned_at,
                            isLate ? 'Presensi berhasil dicatat namun melewati batas toleransi keterlambatan.' : 'Presensi berhasil dicatat tepat waktu ke sistem presensi SIPRES.',
                            2500
                        );

                        // Sound feedback
                        if (window.playBeep) window.playBeep(isLate ? 'late' : 'success');

                        // Update local stats
                        if (data.stats) {
                            this.stats = data.stats;
                        }

                        // Update local student status in list
                        const target = this.studentsList.find(s => s.name === data.student.name || s.nis === data.student.nis);
                        if (target) {
                            target.status = data.status;
                            target.time = data.scanned_at;
                        }
                    } else {
                        // Sound feedback for error/duplicate
                        if (window.playBeep) window.playBeep('error');

                        if (data.code === 'ALREADY_ATTENDED') {
                            this.showFeedback(
                                'DUPLICATE',
                                'SUDAH MELAKUKAN PRESENSI',
                                `Telah tercatat pada pukul ${data.scanned_at} (${data.status}).`,
                                data.student?.name || 'Siswa',
                                data.scanned_at || '-'
                            );

                            this.triggerPopup(
                                'DUPLICATE',
                                'SUDAH ABSEN SEBELUMNYA',
                                'SUDAH TERCATAT (' + (data.status || 'HADIR') + ')',
                                data.student?.name || 'Siswa',
                                data.student?.nis || '-',
                                data.student?.class || '-',
                                data.scanned_at || '-',
                                `Siswa ini sudah melakukan presensi pada pukul ${data.scanned_at} WIB.`,
                                2300
                            );
                        } else if (data.code === 'WRONG_CLASS') {
                            this.showFeedback(
                                'ERROR',
                                'BUKAN SISWA KELAS INI!',
                                `Siswa terdaftar di ${data.student?.class}. Sesi ini untuk ${data.session_class}.`,
                                data.student?.name || 'Siswa',
                                '-'
                            );

                            this.triggerPopup(
                                'ERROR',
                                'BUKAN SISWA KELAS INI!',
                                'KELAS BERBEDA',
                                data.student?.name || 'Siswa',
                                data.student?.nis || '-',
                                data.student?.class || '-',
                                '-',
                                `Siswa terdaftar di ${data.student?.class}. Sesi ini khusus untuk kelas ${data.session_class}.`,
                                3000
                            );
                        } else {
                            this.showFeedback(
                                'ERROR',
                                'QR CODE TIDAK VALID',
                                data.message || 'QR siswa tidak dikenali oleh sistem SIPRES.',
                                '-',
                                '-'
                            );

                            this.triggerPopup(
                                'ERROR',
                                'QR CODE TIDAK VALID',
                                'TIDAK DIKENAL',
                                'Data Tidak Valid',
                                '-',
                                '-',
                                '-',
                                data.message || 'QR code siswa tidak terdaftar atau telah dinonaktifkan.',
                                2500
                            );
                        }
                    }
                },

                triggerPopup(type, title, badgeText, studentName, studentNis, studentClass, time, message, duration = 2500) {
                    this.popupModal = {
                        show: true,
                        type: type,
                        title: title,
                        badgeText: badgeText,
                        studentName: studentName,
                        studentNis: studentNis,
                        studentClass: studentClass,
                        time: time,
                        message: message,
                        timerProgress: 100
                    };

                    this.$nextTick(() => {
                        if (window.renderLucide) window.renderLucide();
                    });

                    clearInterval(this._popupInterval);
                    clearTimeout(this._popupTimeout);

                    const startTime = Date.now();
                    this._popupInterval = setInterval(() => {
                        const elapsed = Date.now() - startTime;
                        const remaining = Math.max(0, duration - elapsed);
                        this.popupModal.timerProgress = (remaining / duration) * 100;
                        if (remaining <= 0) {
                            clearInterval(this._popupInterval);
                        }
                    }, 50);

                    this._popupTimeout = setTimeout(() => {
                        this.closePopupModal();
                    }, duration);
                },

                closePopupModal() {
                    clearInterval(this._popupInterval);
                    clearTimeout(this._popupTimeout);
                    this.popupModal.show = false;
                    setTimeout(() => {
                        this.isProcessing = false;
                    }, 350);
                },

                showFeedback(type, title, message, studentName, time) {
                    this.feedback = {
                        show: true,
                        type: type,
                        title: title,
                        message: message,
                        studentName: studentName,
                        time: time
                    };

                    this.$nextTick(() => {
                        if (window.renderLucide) window.renderLucide();
                    });

                    // Keep feedback visible for 4.5 seconds
                    clearTimeout(this._feedbackTimeout);
                    this._feedbackTimeout = setTimeout(() => {
                        this.feedback.show = false;
                    }, 4500);
                },

                openQuickManual(student) {
                    this.manualForm.student_id = student.id;
                    this.manualForm.status = student.status || 'HADIR';
                    this.manualForm.notes = '';
                    this.manualModalOpen = true;
                },

                async submitManualAttendance() {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch(`/teacher/sessions/${this.sessionId}/manual`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify(this.manualForm)
                        });

                        const data = await res.json();
                        if (data.success) {
                            if (data.stats) this.stats = data.stats;
                            const target = this.studentsList.find(s => s.id == this.manualForm.student_id);
                            if (target) {
                                target.status = this.manualForm.status;
                                target.time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                            }
                            this.manualModalOpen = false;
                            this.showFeedback('HADIR', 'PRESENSI MANUAL BERHASIL', 'Data presensi berhasil diperbarui.', target ? target.name : '', '-');
                        }
                    } catch (e) {
                        alert('Gagal menyimpan presensi manual.');
                    }
                }
            }
        }
    </script>
</x-layouts.app>
