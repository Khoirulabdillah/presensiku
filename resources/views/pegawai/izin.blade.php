@extends('layouts.pegawai')

@section('title', 'Kelola Izin')

@section('content')
<div class="fixed top-4 left-4 z-50">
    <button onclick="goBack()" class="bg-white hover:bg-gray-50 shadow-lg rounded-full w-12 h-12 flex items-center justify-center transition duration-200 hover:shadow-xl">
        <i class="fas fa-arrow-left text-gray-700 text-lg"></i>
    </button>
</div>

<script>
    function goBack() {
        window.history.back();
    }
</script>
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt text-white text-2xl mr-3"></i>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Kelola Permohonan Izin</h1>
                        <p class="text-blue-100 text-sm mt-1">
                            <i class="fas fa-user mr-1"></i>{{ $pegawai->nama_pegawai }} ({{ $pegawai->nip }})
                            @if($pegawai->jabatan)
                                - {{ $pegawai->jabatan }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mx-6 mt-4" />
        @endif

        @if ($errors->any())
            <x-alert type="error" title="Oops!" message="Ada kesalahan input. Silakan perbaiki kesalahan di bawah ini." class="mx-6 mt-4" />
        @endif

        <!-- Navigation Tabs -->
        <div class="bg-gray-50 px-6 py-4 border-b">
            <div class="flex space-x-1">
                <a href="#create" onclick="showTab('create')"
                    class="tab-link bg-white text-blue-600 shadow-sm px-6 py-2 rounded-lg font-medium transition-all duration-200 ease-in-out">
                    <i class="fas fa-plus mr-2"></i>Ajukan Izin
                </a>
                <a href="#history" onclick="showTab('history')"
                    class="tab-link bg-gray-100 text-gray-600 hover:bg-gray-200 px-6 py-2 rounded-lg font-medium transition-all duration-200 ease-in-out">
                    <i class="fas fa-history mr-2"></i>Riwayat Izin
                </a>
            </div>
        </div>

        <!-- Content Sections -->
        <div class="p-6">

            <!-- Create Section -->
            <div id="section-create" class="tab-content">
                <!-- Pegawai Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-user-circle text-blue-600 text-2xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-900">{{ $pegawai->nama_pegawai }}</h3>
                            <p class="text-blue-700">
                                NIP: {{ $pegawai->nip }}
                                @if($pegawai->jabatan)
                                    | Jabatan: {{ $pegawai->jabatan }}
                                @endif
                                @if($pegawai->divisi)
                                    | Divisi: {{ $pegawai->divisi->nama_divisi ?? 'N/A' }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="max-w-3xl mx-auto">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Form Permohonan Izin Baru</h2>

                    <form action="{{ route('pegawai.izin.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label for="jenis_izin" class="block text-sm font-medium text-gray-700 mb-1">Jenis Izin <span class="text-red-500">*</span></label>
                            <select id="jenis_izin" name="jenis_izin" required
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-600 focus:border-blue-600">
                                <option value="">-- Pilih Jenis Izin --</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Cuti Tahunan">Cuti Tahunan</option>
                                <option value="Cuti Melahirkan">Cuti Melahirkan</option>
                                <option value="Izin Tidak Masuk">Izin Tidak Masuk</option>
                                <option value="Izin Terlambat">Izin Terlambat</option>
                                <option value="Izin Pulang Cepat">Izin Pulang Cepat</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            @error('jenis_izin') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
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
                            <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Alasan <span class="text-red-500">*</span></label>
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

                            <div id="preview" class="mt-4 max-h-48 rounded-md border border-gray-200"></div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
                                Kirim Permohonan
                            </button>
                            <button type="button" onclick="resetForm()" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
                                Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- History Section -->
            <div id="section-history" class="tab-content hidden">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Riwayat Permohonan Izin</h2>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-yellow-100 rounded-full p-3">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-yellow-600">Pending</p>
                                <p class="text-2xl font-bold text-yellow-800">{{ $stats['pending'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-green-100 rounded-full p-3">
                                <i class="fas fa-check text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-green-600">Disetujui</p>
                                <p class="text-2xl font-bold text-green-800">{{ $stats['approved'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-red-100 rounded-full p-3">
                                <i class="fas fa-times text-red-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-red-600">Ditolak</p>
                                <p class="text-2xl font-bold text-red-800">{{ $stats['rejected'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-blue-100 rounded-full p-3">
                                <i class="fas fa-list text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-blue-600">Total</p>
                                <p class="text-2xl font-bold text-blue-800">{{ $stats['total'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Table -->
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pegawai</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Izin</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($izin as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $pegawai->nama_pegawai }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->jenis_izin }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}
                                            @if($item->tanggal_selesai)
                                                - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($item->status_izin === 'pending')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                                            @elseif($item->status_izin === 'approved')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                                            @elseif($item->status_izin === 'rejected')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($item->status_izin === 'pending')
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('pegawai.izin.edit', $item->id) }}"
                                                       class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 px-2 py-1 rounded-md text-xs">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('pegawai.izin.destroy', $item->id) }}" method="POST" class="inline"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus permohonan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded-md text-xs">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                                            <p class="text-gray-500 mb-4">Belum ada riwayat permohonan izin</p>
                                            <a href="#create" onclick="showTab('create')"
                                               class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                                <i class="fas fa-plus mr-2"></i>Ajukan Izin Pertama
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Simple tab switching without complex JavaScript class
    function showTab(tabName) {
        // Hide all sections
        document.querySelectorAll('.tab-content').forEach(section => {
            section.classList.add('hidden');
        });

        // Remove active class from all tabs
        document.querySelectorAll('.tab-link').forEach(tab => {
            tab.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            tab.classList.add('bg-gray-100', 'text-gray-600');
        });

        // Show selected section
        document.getElementById('section-' + tabName).classList.remove('hidden');

        // Activate selected tab
        event.target.classList.remove('bg-gray-100', 'text-gray-600');
        event.target.classList.add('bg-white', 'text-blue-600', 'shadow-sm');

        // Update URL hash
        window.location.hash = tabName;
    }

    // Handle URL hash on page load
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash.substring(1);
        if (hash === 'history') {
            showTab('history');
        } else {
            showTab('create');
        }

        // File preview functionality
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

    function resetForm() {
        document.getElementById('jenis_izin').value = '';
        document.getElementById('tanggal_mulai').value = '';
        document.getElementById('tanggal_selesai').value = '';
        document.getElementById('keterangan').value = '';
        document.getElementById('bukti').value = '';
        document.getElementById('preview').innerHTML = '';
    }
</script>
@endsection
