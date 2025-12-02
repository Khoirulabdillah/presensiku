@extends('layouts.pegawai')

@section('title', 'Ajukan Izin')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Form Permohonan Izin</h2>

        <form action="{{ route('pegawai.izin.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label for="jenis_izin" class="block text-sm font-medium text-gray-700 mb-1">Jenis Izin</label>
                <select id="jenis_izin" name="jenis_izin" required
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-600 focus:border-blue-600">
                    <option value="">-- Pilih Jenis Izin --</option>
                    <option value="sakit">Sakit</option>
                    <option value="izin_keperluan_pribadi">Keperluan Pribadi</option>
                    <option value="cuti">Cuti</option>
                    <option value="lainnya">Lainnya</option>
                </select>
                @error('jenis_izin') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" required
                           class="w-full p-3 border border-gray-300 rounded-lg">
                    @error('tanggal_mulai') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai"
                           class="w-full p-3 border border-gray-300 rounded-lg">
                    @error('tanggal_selesai') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Alasan</label>
                <textarea id="keterangan" name="keterangan" rows="4" required
                          class="w-full p-3 border border-gray-300 rounded-lg" placeholder="Jelaskan alasan izin..."></textarea>
                @error('keterangan') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="bukti" class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti (gambar atau PDF)</label>
                <div class="flex items-center gap-3">
                    <input id="bukti" name="bukti" type="file" accept="image/*,application/pdf"
                           class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700"
                           />
                </div>
                <p class="text-xs text-gray-500 mt-2">Maks: 5MB. Format: JPG, PNG, atau PDF.</p>
                @error('bukti') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror

                <div id="preview" class="mt-4"></div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg">Kirim Permohonan</button>
                <a href="{{ route('pegawai.home') }}" class="text-gray-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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
    });
</script>

@endsection
