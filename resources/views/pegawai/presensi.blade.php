@extends('layouts.pegawai')

@section('title', 'Presensi')

@section('content')
{{-- Tombol Kembali --}}
<div class="fixed top-4 left-4 z-50">
    <button onclick="goBack()" class="bg-white hover:bg-gray-50 shadow-lg rounded-full w-12 h-12 flex items-center justify-center transition duration-200 hover:shadow-xl">
        <i class="fas fa-arrow-left text-gray-700 text-lg"></i>
    </button>
</div>

<div class="container mx-auto px-4 md:px-8 lg:px-12 pb-10">
    {{-- Header dengan Info Pegawai --}}
    <div class="bg-white shadow-xl rounded-2xl w-full max-w-4xl mx-auto -mt-14 p-4 sm:p-6 lg:p-8 transform transition duration-300 hover:shadow-2xl mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $pegawai->nama }}</h2>
                <p class="text-sm text-gray-500">NIP: {{ $pegawai->nip }}</p>
                <p class="text-sm text-gray-600">{{ $pegawai->divisi->nama_divisi ?? 'Divisi tidak ditemukan' }}</p>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-sm text-gray-500">{{ now()->format('l, d F Y') }}</p>
                <p class="text-sm text-gray-600" id="current-time">{{ now()->format('H:i:s') }}</p>
            </div>
        </div>
    </div>

    {{-- Status Presensi Hari Ini --}}
    @if($presensiMasuk || $presensiPulang)
    <div class="bg-white shadow-xl rounded-2xl w-full max-w-4xl mx-auto p-4 sm:p-6 lg:p-8 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Presensi Hari Ini</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($presensiMasuk)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-sign-in-alt text-green-600 text-xl mr-3"></i>
                    <div>
                        <p class="font-semibold text-green-800">Presensi Masuk</p>
                        <p class="text-sm text-green-600">{{ $presensiMasuk->jam_masuk }}</p>
                        @if($presensiMasuk->foto_masuk)
                        <p class="text-xs text-gray-500 mt-1">Foto tersimpan</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($presensiPulang)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-sign-out-alt text-blue-600 text-xl mr-3"></i>
                    <div>
                        <p class="font-semibold text-blue-800">Presensi Pulang</p>
                        <p class="text-sm text-blue-600">{{ $presensiPulang->jam_pulang }}</p>
                        @if($presensiPulang->foto_pulang)
                        <p class="text-xs text-gray-500 mt-1">Foto tersimpan</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Camera Interface --}}
    <div class="bg-white shadow-xl rounded-2xl w-full max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-6 text-center">Presensi dengan Kamera</h3>

        {{-- Camera Container --}}
        <div class="mb-6">
            <div class="relative bg-gray-100 rounded-lg overflow-hidden" style="height: 400px;">
                <video id="camera" class="w-full h-full object-cover" autoplay playsinline muted></video>
                <canvas id="canvas" class="hidden"></canvas>

                {{-- Camera Controls --}}
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-4">
                    <button id="start-camera" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-play"></i>
                        <span>Start Camera</span>
                    </button>
                    <button id="stop-camera" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 hidden">
                        <i class="fas fa-stop"></i>
                        <span>Stop Camera</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Presensi Actions --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button id="presensi-masuk" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-sign-in-alt"></i>
                <span>Presensi Masuk</span>
            </button>

            <button id="presensi-pulang" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-sign-out-alt"></i>
                <span>Presensi Pulang</span>
            </button>
        </div>

        {{-- Status Messages --}}
        <div id="status-message" class="mt-4 text-center hidden">
            <p id="status-text" class="text-sm"></p>
        </div>
    </div>
</div>

{{-- JavaScript for Camera Access --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let video = document.getElementById('camera');
    let canvas = document.getElementById('canvas');
    let startBtn = document.getElementById('start-camera');
    let stopBtn = document.getElementById('stop-camera');
    let presensiMasukBtn = document.getElementById('presensi-masuk');
    let presensiPulangBtn = document.getElementById('presensi-pulang');
    let statusMessage = document.getElementById('status-message');
    let statusText = document.getElementById('status-text');
    let stream = null;

    // Update current time
    function updateTime() {
        document.getElementById('current-time').textContent = new Date().toLocaleTimeString('id-ID');
    }
    setInterval(updateTime, 1000);

    // Start camera
    startBtn.addEventListener('click', async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                },
                audio: false
            });

            video.srcObject = stream;
            startBtn.classList.add('hidden');
            stopBtn.classList.remove('hidden');

            // Enable buttons only if not already presensi
            @if(!$presensiMasuk)
            presensiMasukBtn.disabled = false;
            @endif

            @if(!$presensiPulang)
            presensiPulangBtn.disabled = false;
            @endif

            showStatus('Kamera berhasil diaktifkan', 'success');
        } catch (error) {
            console.error('Error accessing camera:', error);
            showStatus('Gagal mengakses kamera. Pastikan Anda memberikan izin akses kamera.', 'error');
        }
    });

    // Stop camera
    stopBtn.addEventListener('click', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
            stream = null;
        }

        startBtn.classList.remove('hidden');
        stopBtn.classList.add('hidden');

        // Disable buttons, but keep presensi buttons disabled if already done
        presensiMasukBtn.disabled = @if($presensiMasuk) true @else false @endif;
        presensiPulangBtn.disabled = @if($presensiPulang) true @else false @endif;

        showStatus('Kamera dimatikan', 'info');
    });

    // Capture and send presensi
    async function capturePresensi(type) {
        if (!stream) {
            showStatus('Kamera belum diaktifkan', 'error');
            return;
        }

        try {
            // Set canvas size to video size
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw current video frame to canvas
            let ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert to base64
            let imageData = canvas.toDataURL('image/jpeg', 0.8);

            // Get location
            let latitude = null;
            let longitude = null;

            if (navigator.geolocation) {
                try {
                    let position = await getCurrentPosition();
                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;
                } catch (error) {
                    console.log('Location not available:', error);
                    showStatus('Gagal mendapatkan lokasi. Pastikan Anda memberikan izin akses lokasi.', 'error');
                    return;
                }
            } else {
                showStatus('Geolokasi tidak didukung oleh browser ini.', 'error');
                return;
            }

            // Send presensi
            await kirimAbsen(type, imageData, latitude, longitude);

        } catch (error) {
            console.error('Error capturing presensi:', error);
            showStatus('Terjadi kesalahan saat mengambil foto', 'error');
        }
    }

    // Function to send presensi
    async function kirimAbsen(type, photo, latitude, longitude) {
        try {
            const response = await axios.post('/pegawai/presensi', {
                photo: photo,
                type: type,
                latitude: latitude,
                longitude: longitude
            }, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.data.success) {
                alert('Presensi berhasil: ' + response.data.message);
                // Reload page after 2 seconds to show updated status
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                alert('Gagal: ' + response.data.message);
            }
        } catch (error) {
            console.error('Error sending presensi:', error);
            if (error.response && error.response.data && error.response.data.message) {
                alert('Error: ' + error.response.data.message);
            } else {
                alert('Terjadi kesalahan saat mengirim presensi');
            }
        }
    }

    // Get current position promise
    function getCurrentPosition() {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        });
    }

    // Event listeners for presensi buttons
    presensiMasukBtn.addEventListener('click', () => capturePresensi('masuk'));
    presensiPulangBtn.addEventListener('click', () => capturePresensi('pulang'));

    // Show status message
    function showStatus(message, type) {
        statusText.textContent = message;
        statusMessage.classList.remove('hidden');

        // Remove existing classes
        statusMessage.classList.remove('text-green-600', 'text-red-600', 'text-blue-600');

        // Add appropriate class
        if (type === 'success') {
            statusMessage.classList.add('text-green-600');
        } else if (type === 'error') {
            statusMessage.classList.add('text-red-600');
        } else {
            statusMessage.classList.add('text-blue-600');
        }

        // Hide after 5 seconds
        setTimeout(() => {
            statusMessage.classList.add('hidden');
        }, 5000);
    }

    // Initialize - disable buttons initially
    presensiMasukBtn.disabled = true;
    presensiPulangBtn.disabled = true;

    // Check if presensi already done today
    @if($presensiMasuk)
    presensiMasukBtn.disabled = true;
    presensiMasukBtn.textContent = 'Sudah Presensi Masuk';
    presensiMasukBtn.classList.add('opacity-50', 'cursor-not-allowed');
    @endif

    @if($presensiPulang)
    presensiPulangBtn.disabled = true;
    presensiPulangBtn.textContent = 'Sudah Presensi Pulang';
    presensiPulangBtn.classList.add('opacity-50', 'cursor-not-allowed');
    @endif
});

// Function to go back to previous page
function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Fallback to home page if no history
        window.location.href = '{{ route("pegawai.home") }}';
    }
}
</script>

@endsection