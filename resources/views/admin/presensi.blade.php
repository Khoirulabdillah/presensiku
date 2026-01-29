@extends('layouts.app')

@section('title', 'Kelola Presensi')

@section('content')

<div class="bg-white shadow-xl rounded-2xl w-full max-w-6xl mx-auto p-6">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Data Presensi</h3>

    <!-- Filter Section -->
    <div class="mb-6">
        <form method="GET" action="{{ route('admin.presensi.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Filter Status</label>
                    <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Semua Status --</option>
                        <option value="Tepat Waktu" {{ request('status') == 'Tepat Waktu' ? 'selected' : '' }}>Tepat Waktu</option>
                        <option value="Terlambat" {{ request('status') == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="Tidak Hadir" {{ request('status') == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                        <option value="Pulang" {{ request('status') == 'Pulang' ? 'selected' : '' }}>Pulang</option>
                        <option value="Pulang Lebih Awal" {{ request('status') == 'Pulang Lebih Awal' ? 'selected' : '' }}>Pulang Lebih Awal</option>
                    </select>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Filter Type</label>
                    <select id="type" name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Semua Type --</option>
                        <option value="masuk" {{ request('type') == 'masuk' ? 'selected' : '' }}>Masuk</option>
                        <option value="pulang" {{ request('type') == 'pulang' ? 'selected' : '' }}>Pulang</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                        <i class="fas fa-search"></i>
                        Cari
                    </button>
                    <a href="{{ route('admin.presensi.index') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition font-medium">
                        <i class="fas fa-redo"></i>
                        Reset
                    </a>                   
                </div>
                <div class="flex items-end gap-2">
                    
                    <!-- Export buttons -->
                    @php $qs = request()->getQueryString(); $qs = $qs ? ('?'.$qs) : ''; @endphp
                    <!-- Excel export removed; keep PDF and CSV -->
                    <a href="{{ route('admin.presensi.exportPdf') }}{{ $qs }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-medium">
                        <i class="fas fa-file-pdf"></i>
                        PDF
                    </a>
                    <a href="{{ route('admin.presensi.exportCsv') }}{{ $qs }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition font-medium">
                        <i class="fas fa-file-csv"></i>
                        CSV
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Pulang</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if($presensis->count() > 0)
                    @foreach($presensis as $presensi)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->nip }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->pegawai->nama_pegawai ?? 'N/A' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->tanggal_presensi?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold {{ $presensi->type === 'masuk' ? ' text-blue-800' : ($presensi->type === 'pulang' ? ' text-orange-800' : 'bg-gray-100 text-gray-800') }}">
                                <i class="fas {{ $presensi->type === 'masuk' ? 'fa-sign-in-alt' : ($presensi->type === 'pulang' ? 'fa-sign-out-alt' : 'fa-question-circle') }}"></i>
                                {{ $presensi->type ? ucfirst($presensi->type) : '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            @if($presensi->status)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold 
                                    {{ in_array($presensi->status, ['Tepat Waktu']) ?  'text-green-800' : '' }}
                                    {{ in_array($presensi->status, ['Terlambat']) ? ' text-yellow-800' : '' }}
                                    {{ in_array($presensi->status, ['Tidak Hadir']) ?  'text-red-800' : '' }}
                                    {{ in_array($presensi->status, ['Pulang', 'Pulang Lebih Awal']) ? ' text-blue-800' : '' }}
                                ">
                                    <i class="fas {{ in_array($presensi->status, ['Tepat Waktu']) ? 'fa-check-circle' : '' }} {{ in_array($presensi->status, ['Terlambat']) ? 'fa-hourglass-end' : '' }} {{ in_array($presensi->status, ['Tidak Hadir']) ? 'fa-times-circle' : '' }} {{ in_array($presensi->status, ['Pulang', 'Pulang Lebih Awal']) ? 'fa-sign-out-alt' : '' }}"></i>
                                    {{ $presensi->status }}
                                </span>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->jam_masuk ?? '-' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->jam_pulang ?? '-' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                            <button type="button" onclick="openPreviewModal({{ $presensi->id }})" 
                                    class="inline-flex items-center gap-1 px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition font-medium text-xs">
                                <i class="fas fa-eye"></i>
                                Preview
                            </button>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 block opacity-50"></i>
                                <p class="text-sm font-medium">Tidak ada data presensi</p>
                                <p class="text-xs mt-1">Coba ubah filter atau reset untuk melihat semua data</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        {{ $presensis->appends(request()->query())->links() }}
    </div>
</div>


@endsection

<!-- Modal Preview -->
<div id="previewModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="sticky top-0 bg-gradient-to-r from-blue-900 to-blue-700 text-white p-6 rounded-t-2xl flex items-center justify-between">
            <h2 class="text-2xl font-bold">Detail Presensi</h2>
            <button type="button" onclick="closePreviewModal()" class="hover:bg-white/20 p-2 rounded-lg transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- Loading State -->
            <div id="modalLoading" class="flex justify-center items-center py-8">
                <div class="animate-spin">
                    <i class="fas fa-spinner text-3xl text-blue-500"></i>
                </div>
            </div>

            <!-- Data Content (Hidden initially) -->
            <div id="modalContent" class="hidden space-y-6">
                <!-- Foto Section -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Foto Presensi</h3>
                    <div id="fotoContainer" class="bg-gray-100 rounded-xl overflow-hidden">
                        <img id="fotoPresensi" src="" alt="Foto Presensi" class="w-full h-auto object-cover max-h-96">
                    </div>
                    <p id="fotoNotAvailable" class="text-gray-500 text-center py-8">
                        <i class="fas fa-image text-4xl block mb-2 opacity-50"></i>
                        Tidak ada foto tersedia
                    </p>
                </div>

                <!-- Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Pegawai Info -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b">Data Pegawai</h4>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-gray-600 font-medium">NIP:</span>
                                <span id="detailNip" class="text-gray-900 ml-2">-</span>
                            </div>
                            <div>
                                <span class="text-gray-600 font-medium">Nama:</span>
                                <span id="detailNama" class="text-gray-900 ml-2">-</span>
                            </div>
                            <div>
                                <span class="text-gray-600 font-medium">Jabatan:</span>
                                <span id="detailJabatan" class="text-gray-900 ml-2">-</span>
                            </div>
                            <div>
                                <span class="text-gray-600 font-medium">Divisi:</span>
                                <span id="detailDivisi" class="text-gray-900 ml-2">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Presensi Info -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b">Data Presensi</h4>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-gray-600 font-medium">Tanggal:</span>
                                <span id="detailTanggal" class="text-gray-900 ml-2">-</span>
                            </div>
                            <div>
                                <span class="text-gray-600 font-medium">Type:</span>
                                <span id="detailType" class="text-gray-900 ml-2">-</span>
                            </div>
                            <div>
                                <span class="text-gray-600 font-medium">Status:</span>
                                <span id="detailStatus" class="text-gray-900 ml-2">-</span>
                            </div>
                            <div>
                                <span class="text-gray-600 font-medium">Jam Masuk:</span>
                                <span id="detailJamMasuk" class="text-gray-900 ml-2">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Info -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b">Lokasi Presensi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600 font-medium">Latitude:</span>
                            <span id="detailLatitude" class="text-gray-900 ml-2 block">-</span>
                        </div>
                        <div>
                            <span class="text-gray-600 font-medium">Longitude:</span>
                            <span id="detailLongitude" class="text-gray-900 ml-2 block">-</span>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-blue-50 rounded-lg text-xs text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="detailMapInfo">Koordinat lokasi presensi</span>
                    </div>
                </div>

                <!-- Jam Pulang -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b">Jam Pulang</h4>
                    <div class="text-sm">
                        <span class="text-gray-600 font-medium">Jam:</span>
                        <span id="detailJamPulang" class="text-gray-900 ml-2">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openPreviewModal(presensiId) {
        const modal = document.getElementById('previewModal');
        const loading = document.getElementById('modalLoading');
        const content = document.getElementById('modalContent');

        // Show modal and loading state
        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');

        // Fetch data
        fetch(`/admin/presensi/${presensiId}/preview`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                // Populate data
                document.getElementById('detailNip').textContent = data.pegawai?.nip || '-';
                document.getElementById('detailNama').textContent = data.pegawai?.nama_pegawai || '-';
                document.getElementById('detailJabatan').textContent = data.pegawai?.jabatan || '-';
                document.getElementById('detailDivisi').textContent = data.pegawai?.divisi?.nama_divisi || '-';
                
                document.getElementById('detailTanggal').textContent = data.tanggal_presensi || '-';
                document.getElementById('detailType').textContent = data.type ? data.type.charAt(0).toUpperCase() + data.type.slice(1) : '-';
                document.getElementById('detailStatus').textContent = data.status || '-';
                document.getElementById('detailJamMasuk').textContent = data.jam_masuk || '-';
                document.getElementById('detailJamPulang').textContent = data.jam_pulang || '-';
                document.getElementById('detailLatitude').textContent = data.latitude || '-';
                document.getElementById('detailLongitude').textContent = data.longitude || '-';

                // Handle foto
                const fotoContainer = document.getElementById('fotoContainer');
                const fotoImage = document.getElementById('fotoPresensi');
                const fotoNotAvailable = document.getElementById('fotoNotAvailable');

                if (data.foto_presensi) {
                    fotoImage.src = `/storage/presensi/${data.foto_presensi}`;
                    fotoContainer.classList.remove('hidden');
                    fotoNotAvailable.classList.add('hidden');
                } else {
                    fotoContainer.classList.add('hidden');
                    fotoNotAvailable.classList.remove('hidden');
                }

                // Show content and hide loading
                loading.classList.add('hidden');
                content.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                loading.innerHTML = '<div class="text-red-500 text-center"><i class="fas fa-exclamation-circle text-3xl mb-2 block"></i><p>Gagal memuat data</p></div>';
            });
    }

    function closePreviewModal() {
        document.getElementById('previewModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('previewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePreviewModal();
        }
    });
</script>
