<x-guest-layout>
                <div class="w-full max-w-md  rounded-3xl p-6 sm:p-8 shadow-lg border border-gray-100">
                    <div class="flex flex-col items-center text-center mb-6">
                        <img src="{{ asset('images/logo-presensi.png') }}" alt="Logo" class="h-12 sm:h-14 mb-2">
                        <h1 class="text-2xl font-bold text-sky-700">Masuk ke Presensiku</h1>
                        <p class="text-sm text-gray-500 mt-1">Masukkan username dan password Anda</p>
                    </div>

                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="username" class="sr-only">Username</label>
                            <input id="username" name="username" type="text" required autofocus autocomplete="username"
                                   value="{{ old('username') }}"
                                   placeholder="Username"
                                   class="block w-full border border-gray-200 rounded-xl p-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-sky-400 transition" />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password" class="sr-only">Password</label>
                            <div class="relative">
                                <input id="password" name="password" type="password" required autocomplete="current-password"
                                       placeholder="Password"
                                       aria-label="Masukan password"
                                       class="block w-full border border-gray-200 rounded-xl p-3 pr-10 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-sky-400 transition" />

                                <button type="button" id="togglePassword" aria-pressed="false" aria-label="Tampilkan password" title="Tampilkan/Sembunyikan password"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 focus:outline-none">
                                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.958 9.958 0 012.223-3.428M6.18 6.18A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-2.003 3.197M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <button type="submit" class="w-full sm:w-auto flex-1 bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3 px-6 rounded-xl transition shadow">
                                Login
                            </button>
                            {{-- <a class="text-sm text-sky-600 hover:underline" href="#">Lupa password?</a> --}}
                        </div>
                    </form>

                    <div class="mt-6 text-center text-xs text-gray-400">Versi aplikasi • Presensiku</div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('togglePassword');
            const pwd = document.getElementById('password');
            if (!toggle || !pwd) return;

            const eye = document.getElementById('eyeIcon');
            const eyeSlash = document.getElementById('eyeSlashIcon');

            toggle.addEventListener('click', function () {
                const isPwd = pwd.getAttribute('type') === 'password';
                if (isPwd) {
                    pwd.setAttribute('type', 'text');
                    eye.classList.add('hidden');
                    eyeSlash.classList.remove('hidden');
                    this.setAttribute('aria-pressed', 'true');
                } else {
                    pwd.setAttribute('type', 'password');
                    eye.classList.remove('hidden');
                    eyeSlash.classList.add('hidden');
                    this.setAttribute('aria-pressed', 'false');
                }
            });
        });
    </script>
</x-guest-layout>