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
                        @if(!empty($presensiMasuk->foto_masuk))
                            @php
                                $pPath = $presensiMasuk->foto_masuk;
                                $publicCandidate = public_path('storage/' . $pPath);
                                if (Storage::disk('public')->exists($pPath) && file_exists($publicCandidate)) {
                                    $pUrl = asset('storage/' . $pPath);
                                } else {
                                    $pUrl = route('storage.image', ['path' => $pPath]);
                                }
                            @endphp
                            <div class="mt-3">
                                <img src="{{ $pUrl }}" alt="Foto Masuk" class="h-24 w-24 object-cover rounded-md border">
                            </div>
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
                        @if(!empty($presensiPulang->foto_pulang))
                            @php
                                $ppPath = $presensiPulang->foto_pulang;
                                $publicCandidate = public_path('storage/' . $ppPath);
                                if (Storage::disk('public')->exists($ppPath) && file_exists($publicCandidate)) {
                                    $ppUrl = asset('storage/' . $ppPath);
                                } else {
                                    $ppUrl = route('storage.image', ['path' => $ppPath]);
                                }
                            @endphp
                            <div class="mt-3">
                                <img src="{{ $ppUrl }}" alt="Foto Pulang" class="h-24 w-24 object-cover rounded-md border">
                            </div>
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

        <div class="mb-6">
            <div class="relative bg-gray-100 rounded-lg overflow-hidden" style="height: 400px;">
            <video id="camera" class="w-full h-full object-cover mirror" autoplay playsinline muted></video>
            <canvas id="overlay" class="absolute inset-0 w-full h-full pointer-events-none mirror"></canvas>
                <canvas id="canvas" class="hidden"></canvas>

                {{-- Camera Controls --}}
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-4">
                    <button id="start-camera" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-play"></i> <span>Mulai Kamera</span>
                    </button>
                    <button id="stop-camera" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 hidden">
                        <i class="fas fa-stop"></i> <span>Matikan Kamera</span>
                    </button>
                </div>

                <div class="absolute top-4 left-4 bg-white/90 px-3 py-1 rounded-md text-sm shadow-sm flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="enable-detect" checked />
                        <span class="font-medium">Deteksi Wajah</span>
                    </div>
                    <div id="face-status" class="text-xs font-bold text-gray-600">Status: Memuat AI...</div>
                </div>
            </div>
        </div>

        {{-- Presensi Actions --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button id="presensi-masuk" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition" disabled>
                <i class="fas fa-sign-in-alt"></i> <span>Presensi Masuk</span>
            </button>

            <button id="presensi-pulang" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition" disabled>
                <i class="fas fa-sign-out-alt"></i> <span>Presensi Pulang</span>
            </button>
        </div>

        <div id="status-message" class="mt-4 text-center hidden">
            <p id="status-text" class="text-sm font-medium"></p>
        </div>
    </div>
</div>

<style>
    /* Mirror preview for camera and overlay so it behaves like a front-facing camera */
    #camera.mirror, #overlay.mirror {
        transform: scaleX(-1);
        transform-origin: center;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.20.0/dist/tf.min.js"></script>
<!-- blazeface kept for quick detection fallback, but primary verification uses face-api -->
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/blazeface@0.0.7/dist/blazeface.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let video = document.getElementById('camera');
    let canvas = document.getElementById('canvas');
    let overlay = document.getElementById('overlay');
    let overlayCtx = overlay.getContext('2d');
    let startBtn = document.getElementById('start-camera');
    let stopBtn = document.getElementById('stop-camera');
    let presensiMasukBtn = document.getElementById('presensi-masuk');
    let presensiPulangBtn = document.getElementById('presensi-pulang');
    let faceStatus = document.getElementById('face-status');
    let enableDetectCheckbox = document.getElementById('enable-detect');
    
    let stream = null;
    let faceModel = null;
    let faceDetected = false;
    let isDetecting = false;

    // Timer
    setInterval(() => {
        document.getElementById('current-time').textContent = new Date().toLocaleTimeString('id-ID');
    }, 1000);

    // Load face-api models for descriptor computation and fallback blazeface for fast detection
    let faceApiAvailable = false;
    async function initAI() {
        try {
            // try load face-api models; if any fail, we'll fallback
            await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
            faceApiAvailable = true;
        } catch (e) {
            console.warn('face-api models not available:', e);
            faceApiAvailable = false;
        }

        try {
            faceModel = await blazeface.load();
        } catch (e) {
            console.warn('blazeface failed to load:', e);
            faceModel = null;
        }

        if (faceApiAvailable) {
            faceStatus.textContent = 'Status: AI Siap';
            faceStatus.className = 'text-xs font-bold text-green-600';
        } else if (faceModel) {
            faceStatus.textContent = 'Status: AI terbatas (deteksi saja)';
            faceStatus.className = 'text-xs font-bold text-yellow-600';
        } else {
            faceStatus.textContent = 'Status: Gagal memuat AI';
            faceStatus.className = 'text-xs font-bold text-red-600';
        }
    }
    initAI();

    function resizeOverlay() {
        overlay.width = video.videoWidth || video.clientWidth || 640;
        overlay.height = video.videoHeight || video.clientHeight || 480;
    }

    async function detectFrame() {
        if (!isDetecting || !faceModel) return;

        const predictions = await faceModel.estimateFaces(video, false);
        overlayCtx.clearRect(0, 0, overlay.width, overlay.height);

        if (predictions.length > 0) {
            faceDetected = true;
            faceStatus.textContent = 'Status: Wajah Terdeteksi';
            faceStatus.className = 'text-xs font-bold text-green-600';
            
            // Draw Box
            predictions.forEach(pred => {
                const start = pred.topLeft;
                const end = pred.bottomRight;
                const size = [end[0] - start[0], end[1] - start[1]];
                overlayCtx.strokeStyle = "#10B981";
                overlayCtx.lineWidth = 4;
                overlayCtx.strokeRect(start[0], start[1], size[0], size[1]);
            });

            // Aktifkan tombol jika belum absen
            @if(!$presensiMasuk) presensiMasukBtn.disabled = false; @endif
            @if(!$presensiPulang) presensiPulangBtn.disabled = false; @endif
        } else {
            faceDetected = false;
            faceStatus.textContent = 'Status: Wajah Tidak Terlihat';
            faceStatus.className = 'text-xs font-bold text-red-600';
            presensiMasukBtn.disabled = true;
            presensiPulangBtn.disabled = true;
        }

        if (isDetecting) requestAnimationFrame(detectFrame);
    }

    startBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 640, height: 480, facingMode: 'user' }
            });
            video.srcObject = stream;
            video.onloadedmetadata = async () => {
                try { await video.play(); } catch(e) { console.warn('video.play failed', e); }
                resizeOverlay();
                isDetecting = true;
                if (enableDetectCheckbox.checked) detectFrame();
            };
            startBtn.classList.add('hidden');
            stopBtn.classList.remove('hidden');
        } catch (e) {
            alert('Gagal akses kamera: ' + e.message);
        }
    });

    // Ensure overlay resizes on window resize
    window.addEventListener('resize', () => {
        if (video.srcObject) resizeOverlay();
    });

    stopBtn.addEventListener('click', () => {
        if (stream) stream.getTracks().forEach(t => t.stop());
        isDetecting = false;
        video.srcObject = null;
        overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
        startBtn.classList.remove('hidden');
        stopBtn.classList.add('hidden');
        presensiMasukBtn.disabled = true;
        presensiPulangBtn.disabled = true;
    });

    async function captureAndSend(type) {
        if (!faceDetected) {
            alert('Wajah harus terdeteksi di dalam kotak!');
            return;
        }

        // Tampilkan Loading
        faceStatus.textContent = 'Status: Memproses Presensi...';
        presensiMasukBtn.disabled = true;
        presensiPulangBtn.disabled = true;

        // Resize & Capture (Kecilkan ke 480px agar Flask tidak berat)
        canvas.width = 480;
        canvas.height = (video.videoHeight / video.videoWidth) * 480;
        let ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        let photoBase64 = canvas.toDataURL('image/jpeg', 0.7);

        // Compute descriptor from captured image (face-api) if available
        let descriptor = null;
        if (faceApiAvailable) {
            try {
                const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.7));
                const img = await faceapi.bufferToImage(blob);
                const detect = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
                if (detect && detect.descriptor) descriptor = Array.from(detect.descriptor);
            } catch (err) {
                console.warn('descriptor error', err);
                descriptor = null;
            }
        } else {
            // face-api not available; skip descriptor
            descriptor = null;
        }

        // Get Location
        navigator.geolocation.getCurrentPosition(async (pos) => {
            try {
                const payload = {
                    photo: photoBase64,
                    type: type,
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude
                };
                if (descriptor) payload.photo_descriptor = descriptor;

                const response = await axios.post('/pegawai/presensi', payload, {
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                if (response.data.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + response.data.message);
                }
            } catch (err) {
                alert('Error: ' + (err.response?.data?.message || 'Terjadi kesalahan sistem'));
            } finally {
                faceStatus.textContent = 'Status: Selesai';
            }
        }, (err) => {
            alert('Gagal mendapatkan lokasi. Harap aktifkan GPS Anda.');
        });
    }

    presensiMasukBtn.addEventListener('click', () => captureAndSend('masuk'));
    presensiPulangBtn.addEventListener('click', () => captureAndSend('pulang'));
});

function goBack() { window.history.back(); }
</script>
@endsection