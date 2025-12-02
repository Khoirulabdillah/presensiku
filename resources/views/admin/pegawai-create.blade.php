<x-headeradmin>

<!-- CONTAINER UTAMA - PUSAT DI TENGAH HALAMAN -->
<div class="flex items-center justify-center min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">

    <!-- CARD FORMULIR -->
    <div class="w-full max-w-s bg-white p-8 rounded-xl shadow-2xl transition-all duration-300 hover:shadow-3xl">

        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 border-b pb-2">
            <i class="fas fa-user-plus mr-2 text-blue-700"></i> Tambah Data Pegawai Baru
        </h2>

        <!-- Notifikasi Error Validasi -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <strong class="font-bold">Oops!</strong>
                <span class="block sm:inline">Silakan perbaiki kesalahan input di bawah ini.</span>
            </div>
        @endif
        
        <!-- FORMULIR STORE -->
        <form action="{{ route('admin.pegawai.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- NIP -->
            <div>
                <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">NIP (Nomor Induk Pegawai)</label>
                <input type="number" name="nip" id="nip" value="{{ old('nip') }}" 
                       placeholder="Masukkan NIP (harus unik)"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('nip') border-red-500 @enderror">
                @error('nip')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- NAMA PEGAWAI -->
            <div>
                <label for="nama_pegawai" class="block text-sm font-medium text-gray-700 mb-1">Nama Pegawai</label>
                <input type="text" name="nama_pegawai" id="nama_pegawai" value="{{ old('nama_pegawai') }}" 
                       placeholder="Masukkan Nama Lengkap"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('nama_pegawai') border-red-500 @enderror">
                @error('nama_pegawai')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- JABATAN -->
            <div>
                <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}" 
                       placeholder="Masukkan Jabatan Pegawai"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('jabatan') border-red-500 @enderror">
                @error('jabatan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- DIVISI SELECT -->
            <div>
                <label for="divisi_id" class="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
                <select id="divisi_id" name="divisi_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('divisi_id') border-red-500 @enderror">
                    <option value="">-- Pilih Divisi --</option>
                    @foreach($divisi as $d)
                        <option value="{{ $d->id }}" {{ old('divisi_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_divisi }}</option>
                    @endforeach
                </select>
                @error('divisi_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- USER ACCOUNT FIELDS -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Akun</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                       placeholder="Nama pada akun login"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" 
                       placeholder="Username untuk login"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('username') border-red-500 @enderror">
                @error('username')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" 
                       placeholder="Password akun"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                       placeholder="Konfirmasi Password"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-700 focus:border-blue-700 shadow-sm transition duration-150 ease-in-out @error('password_confirmation') border-red-500 @enderror">
                @error('password_confirmation')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- TOMBOL SIMPAN -->
            <button type="submit" 
                    class="w-full bg-blue-800 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-blue-700 transition duration-200 ease-in-out transform hover:scale-[1.01]">
                <i class="fas fa-plus-circle mr-2"></i> Simpan Data Pegawai
            </button>
            
            <!-- TOMBOL KEMBALI -->
            <a href="{{ route('admin.pegawai.index') }}" 
               class="w-full block text-center bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg shadow-md hover:bg-gray-300 transition duration-200 ease-in-out">
                Batal / Kembali
            </a>
        </form>

    </div>
</div>

</x-headeradmin>