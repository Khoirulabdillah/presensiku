@extends('layouts.app')

@section('title', 'Edit Pegawai')

@section('content')

<div class="flex items-center justify-center min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">

    <div class="w-full max-w-s bg-white p-8 rounded-xl shadow-2xl transition-all duration-300 hover:shadow-3xl">

        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 border-b pb-2">
            <i class="fas fa-user-edit mr-2 text-blue-700"></i> Edit Data Pegawai
        </h2>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <strong class="font-bold">Oops!</strong>
                <span class="block sm:inline">Silakan perbaiki kesalahan input di bawah ini.</span>
            </div>
        @endif
        
        <form action="{{ route('admin.pegawai.update', $pegawai->nip) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">NIP (Nomor Induk Pegawai)</label>
                <input type="number" name="nip" id="nip" value="{{ old('nip', $pegawai->nip) }}" 
                        placeholder="Masukkan Nomor Induk Pegawai"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('nip') border-red-500 @enderror">
                @error('nip')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nama_pegawai" class="block text-sm font-medium text-gray-700 mb-1">Nama Pegawai</label>
                <input type="text" name="nama_pegawai" id="nama_pegawai" value="{{ old('nama_pegawai', $pegawai->nama_pegawai) }}" 
                        placeholder="Masukkan Nama Lengkap"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('nama_pegawai') border-red-500 @enderror">
                @error('nama_pegawai')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan', $pegawai->jabatan) }}" 
                        placeholder="Masukkan Jabatan Pegawai"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('jabatan') border-red-500 @enderror">
                @error('jabatan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="divisi_id" class="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
                <select id="divisi_id" name="divisi_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('divisi_id') border-red-500 @enderror">
                    <option value="">-- Pilih Divisi --</option>
                    @foreach($divisi as $d)
                        <option value="{{ $d->id }}" {{ old('divisi_id', $pegawai->divisi_id) == $d->id ? 'selected' : '' }}>{{ $d->nama_divisi }}</option>
                    @endforeach
                </select>
                @error('divisi_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- FOTO WAJAH REFERENSI -->
            <div>
                <label for="foto_wajah_asli" class="block text-sm font-medium text-gray-700 mb-1">Foto Wajah Referensi (opsional)</label>
                @if(!empty($pegawai->foto_wajah_asli))
                    <div class="mb-2">
                        @php
                            $photoPath = $pegawai->foto_wajah_asli;
                            $showUrl = null;
                        @endphp
                        @php
                            $publicCandidate = public_path('storage/' . $photoPath);
                            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath) && file_exists($publicCandidate)) {
                                $showUrl = asset('storage/' . $photoPath);
                            } else {
                                $showUrl = route('storage.image', ['path' => $photoPath]);
                            }
                        @endphp

                        <img src="{{ $showUrl }}" alt="Foto Wajah" class="h-20 w-20 object-cover rounded-md border">
                    </div>
                @endif
                <input type="file" name="foto_wajah_asli" id="foto_wajah_asli" accept="image/*"
                       class="w-full p-2 border border-gray-300 rounded-lg @error('foto_wajah_asli') border-red-500 @enderror">
                <input type="hidden" name="foto_wajah_encoding" id="foto_wajah_encoding" value='{{ json_encode($pegawai->foto_wajah_encoding) }}' />
                @error('foto_wajah_asli')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
                <div class="relative">
                    <input type="password" name="password" id="password" 
                           placeholder="Kosongkan jika tidak ingin mengubah password"
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out pr-10 @error('password') border-red-500 @enderror">
                    <button type="button" id="togglePassword"
                            aria-pressed="false"
                            aria-label="Tampilkan password"
                            title="Tampilkan/Sembunyikan password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.958 9.958 0 012.223-3.428M6.18 6.18A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-2.003 3.197M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           placeholder="Konfirmasi password baru"
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out pr-10 @error('password_confirmation') border-red-500 @enderror">
                    <button type="button" id="togglePasswordConfirmation"
                            aria-pressed="false"
                            aria-label="Tampilkan konfirmasi password"
                            title="Tampilkan/Sembunyikan konfirmasi password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg id="eyeIconConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <svg id="eyeSlashIconConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.958 9.958 0 012.223-3.428M6.18 6.18A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-2.003 3.197M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- <div>
                <label for="users_id" class="block text-sm font-medium text-gray-700 mb-1">ID User (Akun Login)</label>
                <input type="number" name="users_id" id="users_id" value="{{ old('users_id', $pegawai->users_id) }}" 
                        placeholder="ID User Akun (Angka)"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('users_id') border-red-500 @enderror">
                @error('users_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div> --}}

            <button type="submit" 
                    class="w-full bg-blue-800 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-blue-700 transition duration-200 ease-in-out transform hover:scale-[1.01]">
                <i class="fas fa-save mr-2"></i> Perbarui Data Pegawai
            </button>
            
            <a href="{{ route('admin.pegawai.index') }}" 
               class="w-full block text-center bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg shadow-md hover:bg-gray-300 transition duration-200 ease-in-out">
                Batal / Kembali
            </a>
        </form>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle for password field
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        if (togglePassword && password) {
            togglePassword.addEventListener('click', function () {
                const isPwd = password.getAttribute('type') === 'password';
                if (isPwd) {
                    password.setAttribute('type', 'text');
                    eyeIcon.classList.add('hidden');
                    eyeSlashIcon.classList.remove('hidden');
                    this.setAttribute('aria-pressed', 'true');
                } else {
                    password.setAttribute('type', 'password');
                    eyeIcon.classList.remove('hidden');
                    eyeSlashIcon.classList.add('hidden');
                    this.setAttribute('aria-pressed', 'false');
                }
            });
        }

        // Toggle for password confirmation field
        const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
        const passwordConfirmation = document.getElementById('password_confirmation');
        const eyeIconConfirm = document.getElementById('eyeIconConfirm');
        const eyeSlashIconConfirm = document.getElementById('eyeSlashIconConfirm');

        if (togglePasswordConfirmation && passwordConfirmation) {
            togglePasswordConfirmation.addEventListener('click', function () {
                const isPwd = passwordConfirmation.getAttribute('type') === 'password';
                if (isPwd) {
                    passwordConfirmation.setAttribute('type', 'text');
                    eyeIconConfirm.classList.add('hidden');
                    eyeSlashIconConfirm.classList.remove('hidden');
                    this.setAttribute('aria-pressed', 'true');
                } else {
                    passwordConfirmation.setAttribute('type', 'password');
                    eyeIconConfirm.classList.remove('hidden');
                    eyeSlashIconConfirm.classList.add('hidden');
                    this.setAttribute('aria-pressed', 'false');
                }
            });
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
    const fileInput = document.getElementById('foto_wajah_asli');
    const hiddenEnc = document.getElementById('foto_wajah_encoding');
    if (!fileInput) return;

    await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
    await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
    await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files[0];
        if (!file) return;
        const img = await faceapi.bufferToImage(file);
        const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
        if (!detection) {
            alert('Tidak terdeteksi wajah pada foto. Pastikan wajah jelas dan menghadap kamera.');
            hiddenEnc.value = '';
            return;
        }
        const desc = Array.from(detection.descriptor);
        hiddenEnc.value = JSON.stringify(desc);
    });
});
</script>

@endsection