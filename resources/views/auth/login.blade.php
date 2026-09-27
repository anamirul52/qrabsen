<x-layouts.guest>
    <x-slot:title>Masuk Sistem Presensi</x-slot:title>

    <div class="w-full max-w-md mx-auto">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-blue-600 text-white shadow-sm shadow-blue-500/30 mb-3 sm:mb-4">
                <i data-lucide="qr-code" class="w-8 h-8 sm:w-9 sm:h-9"></i>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">
                SIPRES SMPN 2 MIJEN
            </h1>
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mt-1">
                Sistem Presensi Berbasis QR Code
            </p>
        </div>

        <!-- Preline Login Card -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-2xs p-5 sm:p-7"
             x-data="{ showPassword: false, loginRole(email) { document.getElementById('login').value = email; document.getElementById('password').value = 'password'; } }">
            
            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-red-50 border border-red-200 p-3.5 text-xs sm:text-sm text-red-800 flex items-start gap-x-2.5">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0 mt-0.5"></i>
                    <div class="flex-1 font-medium">
                        {{ $errors->first() }}
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 rounded-xl bg-teal-50 border border-teal-200 p-3.5 text-xs sm:text-sm text-teal-800 flex items-start gap-x-2.5">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-teal-600 shrink-0 mt-0.5"></i>
                    <div class="flex-1 font-medium">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <form class="space-y-4" action="{{ route('login.post') }}" method="POST">
                @csrf

                <div>
                    <label for="login" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                        Email atau Username / NIS
                    </label>
                    <div class="relative">
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                            autocomplete="username" placeholder="admin@sipres.test / guru / 24001"
                            class="py-2.5 px-3.5 block w-full border border-gray-200 rounded-lg text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500 transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Kata Sandi
                        </label>
                    </div>
                    <div class="relative">
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                            autocomplete="current-password" placeholder="••••••••"
                            class="py-2.5 px-3.5 pe-10 block w-full border border-gray-200 rounded-lg text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500 transition">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600 focus:outline-hidden">
                            <span x-show="!showPassword">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </span>
                            <span x-show="showPassword" style="display:none;">
                                <i data-lucide="eye-off" class="w-4 h-4"></i>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-x-2 text-xs sm:text-sm text-gray-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="shrink-0 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                        <span>Ingat saya</span>
                    </label>
                    <span class="text-[11px] text-gray-400 font-medium">SMPN 2 MIJEN</span>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-2.5 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 shadow-2xs transition cursor-pointer">
                        <span>Masuk ke SIPRES</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <!-- Quick Demo Credentials Selector (Preline Badged Buttons) -->
            <div class="mt-6 pt-5 border-t border-gray-100">
                <p class="text-[11px] font-semibold text-gray-400 text-center uppercase tracking-wider mb-2.5">
                    Akun Uji Coba Cepat (Klik untuk isi)
                </p>
                <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                    <button type="button" @click="loginRole('admin@sipres.test')"
                        class="p-2 rounded-lg border border-gray-200 bg-gray-50/80 hover:bg-blue-50 hover:border-blue-300 text-center transition cursor-pointer">
                        <span class="block text-xs font-bold text-blue-700">Admin</span>
                        <span class="block text-[10px] text-gray-500 truncate">admin@sipres.test</span>
                    </button>
                    <button type="button" @click="loginRole('guru@sipres.test')"
                        class="p-2 rounded-lg border border-gray-200 bg-gray-50/80 hover:bg-emerald-50 hover:border-emerald-300 text-center transition cursor-pointer">
                        <span class="block text-xs font-bold text-emerald-700">Guru</span>
                        <span class="block text-[10px] text-gray-500 truncate">guru@sipres.test</span>
                    </button>
                    <button type="button" @click="loginRole('student@sipres.test')"
                        class="p-2 rounded-lg border border-gray-200 bg-gray-50/80 hover:bg-amber-50 hover:border-amber-300 text-center transition cursor-pointer">
                        <span class="block text-xs font-bold text-amber-700">Siswa</span>
                        <span class="block text-[10px] text-gray-500 truncate">student@sipres.test</span>
                    </button>
                </div>
                <p class="text-[11px] text-gray-400 text-center mt-3">
                    Kata sandi bawaan: <code class="font-mono text-gray-700 bg-gray-100 px-1.5 py-0.5 rounded text-[11px]">password</code>
                </p>
            </div>
        </div>
    </div>
</x-layouts.guest>
