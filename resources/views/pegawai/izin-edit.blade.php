@extends('layouts.pegawai')

@section('title', 'Edit Izin')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-600 px-6 py-4">
            <div class="flex items-center">
                <a href="{{ route('pegawai.izin.index') }}" class="text-white mr-4 hover:text-blue-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">Edit Permohonan Izin</h1>
                    <p class="text-blue-100 text-sm mt-1">
                        <i class="fas fa-user mr-1"></i>{{ $pegawai->nama_pegawai }} ({{ $pegawai->nip }})
                        @if($pegawai->jabatan)
                            - {{ $pegawai->jabatan }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Warning Message -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mx-6 mt-4">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                <span class="text-sm text-yellow-800">
                    Anda hanya dapat mengedit izin yang masih berstatus "Menunggu Persetujuan".
                </span>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mx-6 mt-4" />
        @endif

        @if ($errors->any())
            <x-alert type="error" title="Oops!" message="Ada kesalahan input. Silakan perbaiki kesalahan di bawah ini." class="mx-6 mt-4" />
        @endif

        <!-- Edit Form -->
        <div class="p-6">
            <form action="{{ route('pegawai.izin.update', $izin->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="jenis_izin" class="block text-sm font-medium text-gray-700 mb-1">Jenis Izin <span class="text-red-500">*</span></label>
                    <select id="jenis_izin" name="jenis_izin" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-600 focus:border-blue-600">
                        <option value="">-- Pilih Jenis Izin --</option>
                        <option value="Sakit" {{ $izin->jenis_izin === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="Cuti Tahunan" {{ $izin->jenis_izin === 'Cuti Tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                        <option value="Cuti Melahirkan" {{ $izin->jenis_izin === 'Cuti Melahirkan' ? 'selected' : '' }}>Cuti Melahirkan</option>
                        <option value="Izin Tidak Masuk" {{ $izin->jenis_izin === 'Izin Tidak Masuk' ? 'selected' : '' }}>Izin Tidak Masuk</option>
                        <option value="Izin Terlambat" {{ $izin->jenis_izin === 'Izin Terlambat' ? 'selected' : '' }}>Izin Terlambat</option>
                        <option value="Izin Pulang Cepat" {{ $izin->jenis_izin === 'Izin Pulang Cepat' ? 'selected' : '' }}>Izin Pulang Cepat</option>
                        <option value="Lainnya" {{ $izin->jenis_izin === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('jenis_izin') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" required
                               value="{{ $izin->tanggal_mulai }}" class="w-full p-3 border border-gray-300 rounded-lg">
                        @error('tanggal_mulai') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai"
                               value="{{ $izin->tanggal_selesai ?? '' }}" class="w-full p-3 border border-gray-300 rounded-lg">
                        @error('tanggal_selesai') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Alasan <span class="text-red-500">*</span></label>
                    <textarea id="keterangan" name="keterangan" rows="4" required
                              class="w-full p-3 border border-gray-300 rounded-lg" placeholder="Jelaskan alasan izin...">{{ $izin->keterangan }}</textarea>
                    @error('keterangan') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="bukti" class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti Baru (gambar atau PDF)</label>
                    <div class="flex items-center gap-3">
                        <input id="bukti" name="bukti" type="file" accept="image/*,application/pdf"
                               class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700"
                               />
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Maks: 5MB. Format: JPG, PNG, PDF. Kosongkan jika tidak ingin mengubah file.</p>
                    @if($izin->bukti_path)
                        <p class="text-sm text-gray-600 mt-1">
                            File saat ini:
                            <a href="{{ Storage::url($izin->bukti_path) }}" target="_blank" class="text-blue-600 underline">
                                Lihat file
                            </a>
                        </p>
                    @endif
                    @error('bukti') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror

                    <div id="preview" class="mt-4 max-h-48 rounded-md border border-gray-200"></div>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('pegawai.izin.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // File preview functionality
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('bukti');
        const preview = document.getElementById('preview');

        fileInput.addEventListener('change', function () {
            preview.innerHTML = '';
            const file = this.files[0];
            if (!file) return;

            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                preview.innerHTML = '<p class="text-red-500">File terlalu besar (maks 5MB).</p>';
                this.value = '';
                return;
            }

            const type = file.type;
            if (type === 'application/pdf') {
                const el = document.createElement('div');
                el.className = 'flex items-center gap-3 p-3 border border-gray-200 rounded-md';
                el.innerHTML = '<svg class="h-8 w-8 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l4 4H6l6-6z M12 14v4m0 0h.01"/></svg>' +
                    '<div><p class="font-medium">' + file.name + '</p><p class="text-xs text-gray-500">PDF</p></div>';
                preview.appendChild(el);
            } else if (type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'max-h-48 rounded-md border border-gray-200';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<p class="text-red-500">Format file tidak didukung.</p>';
                this.value = '';
            }
        });

        // Date validation
        const tanggalMulai = document.getElementById('tanggal_mulai');
        const tanggalSelesai = document.getElementById('tanggal_selesai');

        tanggalMulai.addEventListener('change', function() {
            const startDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (startDate < today) {
                alert('Tanggal mulai tidak boleh kurang dari hari ini.');
                this.value = '';
            }
        });

        tanggalSelesai.addEventListener('change', function() {
            const startDate = new Date(tanggalMulai.value);
            const endDate = new Date(this.value);

            if (endDate < startDate) {
                alert('Tanggal selesai tidak boleh kurang dari tanggal mulai.');
                this.value = '';
            }
        });
    });
</script>
@endsection