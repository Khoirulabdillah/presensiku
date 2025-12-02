<x-guest-layout>
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
    <!-- Logo -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/logo-presensi.png') }}" alt="Logo" class="h-12">
        </div>
        <div class="flex flex-col items-center mb-6">
            <h1 class="text-3xl font-bold text-blue-600">PresensikuLogin</h1>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-4">
                {{-- Menghilangkan Label "Email" dan hanya menyisakan placeholder --}}
                {{-- Label Asli: <x-input-label for="email" :value="__('Email')" /> --}}
                
                <input id="username" 
                        class="block mt-1 w-full border-blue-600 rounded-xl shadow-inner placeholder-gray-500 p-3 pr-10"                       type="username" 
                       name="username" 
                       :value="old('username')" 
                       required 
                       autofocus 
                       autocomplete="username" 
                       placeholder="Masukan username" {{-- Placeholder ditambahkan --}}
                />
                <x-input-error :messages="$errors->get('username')" class="mt-2" />
            </div>

            <div class="mt-4">
                {{-- Menghilangkan Label "Password" dan hanya menyisakan placeholder --}}
                {{-- Label Asli: <x-input-label for="password" :value="__('Password')" /> --}}

                <div class="relative">
                    <input id="password"
                           class="block mt-1 w-full border-gray-300 rounded-xl shadow-inner placeholder-gray-500 p-3 pr-10"
                           type="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           placeholder="Masukan password"
                           aria-label="Masukan password"
                    />

                    <button type="button" id="togglePassword"
                            aria-pressed="false"
                            aria-label="Tampilkan password"
                            title="Tampilkan/Sembunyikan password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <!-- eye (default visible) -->
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <!-- eye-slash (hidden by default) -->
                        <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.958 9.958 0 012.223-3.428M6.18 6.18A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-2.003 3.197M3 3l18 18"/>
                        </svg>
                    </button>
                </div>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Bagian Remember Me dihilangkan sesuai gambar --}}
            {{-- <div class="block mt-4">...</div> --}}


            <div class="flex flex-col items-center justify-center mt-8">
                
                <button type="submit" 
                        class="w-full bg-blue-500 hover:bg-blue-800 text-white font-bold py-3 px-4 border-b-4  rounded-full transition duration-300 shadow-lg">
                    Login
                </button>

                {{-- @if (Route::has('password.request'))
                    <a class="mt-4 text-sm text-blue-500 hover:text-blue-700 underline" href="{{ route('password.request') }}">
                        Forgot your password?
                    </a>
                @endif --}}
                
            </div>
        </form>
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