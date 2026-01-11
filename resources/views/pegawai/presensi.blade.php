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
                        <i class="fas fa-play"></i>
                    </button>
                    <button id="stop-camera" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 hidden">
                        <i class="fas fa-stop"></i>
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
            <button id="presensi-masuk" class="bg-green-600 hover:bg-green-700 justify-center text-white px-6 py-3 rounded-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition" disabled>
                <i class="fas fa-sign-in-alt"></i> <span>Presensi Masuk</span>
            </button>

            <button id="presensi-pulang" class="bg-blue-600 hover:bg-blue-700 justify-center text-white px-6 py-3 rounded-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition" disabled>
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
<!-- Dynamic loader for TFJS, face-api and BlazeFace to avoid duplicate loads and version conflicts -->
<script>
// Minimal robust client-side AI loader + detection fallback
function loadScriptOnce(url, checkGlobal, timeout = 15000) {
    return new Promise((resolve, reject) => {
        try {
            if (checkGlobal && typeof checkGlobal() !== 'undefined' && checkGlobal() !== null) return resolve(checkGlobal());
        } catch(e){}
        const existing = Array.from(document.getElementsByTagName('script')).find(s => s.src && s.src.indexOf(url) !== -1);
        if (existing) {
            const start = Date.now();
            (function waitForGlobal() {
                try { if (checkGlobal && typeof checkGlobal() !== 'undefined' && checkGlobal() !== null) return resolve(checkGlobal()); } catch(e){}
                if (Date.now() - start > timeout) return reject(new Error('Timeout loading ' + url));
                setTimeout(waitForGlobal, 100);
            })();
            return;
        }
        const s = document.createElement('script');
        s.src = url;
        s.async = true;
        s.onload = () => {
            if (checkGlobal) {
                const start = Date.now();
                (function waitForGlobal2() {
                    try { if (typeof checkGlobal() !== 'undefined' && checkGlobal() !== null) return resolve(checkGlobal()); } catch(e){}
                    if (Date.now() - start > timeout) return reject(new Error('Timeout waiting for global after ' + url));
                    setTimeout(waitForGlobal2, 100);
                })();
            } else {
                resolve();
            }
        };
        s.onerror = (e) => reject(e || new Error('Failed to load ' + url));
        document.head.appendChild(s);
    });
}

async function ensureAIlibs() {
    const urls = {
        tf: 'https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.21.0/dist/tf.min.js',
        blazeface: 'https://cdn.jsdelivr.net/npm/@tensorflow-models/blazeface@0.0.7/dist/blazeface.min.js'
    };
    window.__ai_load_report = window.__ai_load_report || { tf: null, faceapi: null, blazeface: null };
    let loadedAny = false;
    try {
        await loadScriptOnce(urls.tf, () => window.tf, 10000);
        window.__ai_load_report.tf = 'ok';
        loadedAny = true;
    } catch (e) {
        console.warn('Could not load TFJS:', e);
        window.__ai_load_report.tf = String(e?.message || e);
    }
    try {
        await loadScriptOnce(urls.blazeface, () => window.blazeface, 10000);
        window.__ai_load_report.blazeface = 'ok';
        loadedAny = true;
    } catch (e) {
        console.warn('Could not load BlazeFace:', e);
        window.__ai_load_report.blazeface = String(e?.message || e);
    }
    return loadedAny;
}

async function initAI() {
    // Initialize BlazeFace model if available (we skip face-api.js entirely)
    try {
        if (typeof blazeface !== 'undefined' && typeof tf !== 'undefined') {
            faceModel = await blazeface.load();
        } else {
            faceModel = null;
        }
    } catch (e) {
        console.warn('blazeface failed to load:', e);
        faceModel = null;
    }

    if ('FaceDetector' in window) {
        faceStatus.textContent = 'Status: Native detector tersedia';
        faceStatus.className = 'text-xs font-bold text-green-600';
    } else if (faceModel) {
        faceStatus.textContent = 'Status: AI siap (BlazeFace)';
        faceStatus.className = 'text-xs font-bold text-green-600';
    } else {
        faceStatus.textContent = 'Status: Fallback deteksi (skin-tone)';
        faceStatus.className = 'text-xs font-bold text-yellow-600';
    }
}

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
    let lastFaceBox = null; // store last fast-detection box for faster descriptor crop

        // server-side flags: has already presensi masuk/pulang
        const serverHasMasuk = @json($presensiMasuk ? true : false);
        const serverHasPulang = @json($presensiPulang ? true : false);

    // Timer
    setInterval(() => {
        document.getElementById('current-time').textContent = new Date().toLocaleTimeString('id-ID');
    }, 1000);

    // Load face-api models for descriptor computation and fallback blazeface for fast detection
    let faceApiAvailable = false;
    function resizeOverlay() {
        overlay.width = video.videoWidth || video.clientWidth || 640;
        overlay.height = video.videoHeight || video.clientHeight || 480;
    }

    // Simple detection loop WITHOUT TensorFlow (native FaceDetector or skin-tone)
    async function detectWithFaceDetector() {
        if (!('FaceDetector' in window)) return false;
        try {
            const detector = new window.FaceDetector({ fastMode: true, maxDetectedFaces: 1 });
            const results = await detector.detect(video);
            overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
            if (results && results.length > 0) {
                const r = results[0].boundingBox;
                const x = r.x, y = r.y, w = r.width, h = r.height;
                overlayCtx.strokeStyle = '#10B981';
                overlayCtx.lineWidth = 4;
                overlayCtx.strokeRect(x, y, w, h);
                lastFaceBox = { x, y, width: w, height: h };
                return true;
            }
            return false;
        } catch (e) { console.warn('FaceDetector failed:', e); return false; }
    }

    async function detectWithBlazeFace() {
        if (!faceModel) return false;
        try {
            const predictions = await faceModel.estimateFaces(video, false);
            overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
            if (predictions && predictions.length > 0) {
                const p = predictions[0];
                let topLeft = p.topLeft;
                let bottomRight = p.bottomRight;
                // Some builds return objects
                if (topLeft && topLeft.x !== undefined) {
                    topLeft = [topLeft.x, topLeft.y];
                    bottomRight = [bottomRight.x, bottomRight.y];
                }
                const x = topLeft[0], y = topLeft[1], w = bottomRight[0] - topLeft[0], h = bottomRight[1] - topLeft[1];
                overlayCtx.strokeStyle = '#10B981';
                overlayCtx.lineWidth = 4;
                overlayCtx.strokeRect(x, y, w, h);
                lastFaceBox = { x, y, width: w, height: h };
                return true;
            }
            return false;
        } catch (e) { console.warn('BlazeFace detect failed:', e); return false; }
    }

    function detectWithSkinTone() {
        const w = 160, h = 120;
        const tmp = document.createElement('canvas'); tmp.width = w; tmp.height = h;
        const tctx = tmp.getContext('2d');
        try { tctx.drawImage(video, 0, 0, w, h); } catch (e) { return null; }
        const data = tctx.getImageData(0, 0, w, h).data;
        let minX = w, minY = h, maxX = 0, maxY = 0, count = 0;
        for (let y = 0; y < h; y++) {
            for (let x = 0; x < w; x++) {
                const i = (y * w + x) * 4;
                const r = data[i], g = data[i+1], b = data[i+2];
                const max = Math.max(r,g,b), min = Math.min(r,g,b);
                if (r > 95 && g > 40 && b > 20 && (max - min) > 15 && r > g && r > b) {
                    count++;
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
            }
        }
        if (count < 50) return null;
        const scaleX = overlay.width / w, scaleY = overlay.height / h;
        return { x: minX * scaleX, y: minY * scaleY, width: (maxX - minX) * scaleX, height: (maxY - minY) * scaleY };
    }

    function drawBox(box) { overlayCtx.clearRect(0,0,overlay.width,overlay.height); if (!box) return; overlayCtx.strokeStyle='#10B981'; overlayCtx.lineWidth=4; overlayCtx.strokeRect(box.x, box.y, box.width, box.height); }

    async function simpleDetectLoop() {
        if (!isDetecting) return;
        // try native detector first
        if ('FaceDetector' in window) {
            try {
                const ok = await detectWithFaceDetector();
                if (ok) {
                    faceDetected = true;
                    faceStatus.textContent = 'Status: Wajah Terdeteksi';
                    faceStatus.className = 'text-xs font-bold text-green-600';
                    // enable buttons depending on server state
                    presensiMasukBtn.disabled = serverHasMasuk ? true : false;
                    presensiPulangBtn.disabled = (serverHasMasuk && !serverHasPulang) ? false : true;
                    requestAnimationFrame(simpleDetectLoop);
                    return;
                }
            } catch (e) { console.warn(e); }
        }

        // try BlazeFace (tfjs) if available
        if (faceModel) {
            try {
                const ok = await detectWithBlazeFace();
                if (ok) {
                    faceDetected = true;
                    faceStatus.textContent = 'Status: Wajah Terdeteksi';
                    faceStatus.className = 'text-xs font-bold text-green-600';
                    presensiMasukBtn.disabled = serverHasMasuk ? true : false;
                    presensiPulangBtn.disabled = (serverHasMasuk && !serverHasPulang) ? false : true;
                    requestAnimationFrame(simpleDetectLoop);
                    return;
                }
            } catch (e) { console.warn(e); }
        }

        // skin-tone heuristic fallback
        const skinBox = detectWithSkinTone();
            if (skinBox) {
            	drawBox(skinBox);
            	faceDetected = true;
            	faceStatus.textContent = 'Status: Wajah Terdeteksi';
            	faceStatus.className = 'text-xs font-bold text-green-600';
            	presensiMasukBtn.disabled = serverHasMasuk ? true : false;
            	presensiPulangBtn.disabled = (serverHasMasuk && !serverHasPulang) ? false : true;
        } else {
            overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
            faceDetected = false;
            faceStatus.textContent = 'Status: Wajah Tidak Terlihat';
            faceStatus.className = 'text-xs font-bold text-red-600';
            presensiMasukBtn.disabled = true;
            presensiPulangBtn.disabled = true;
        }
        requestAnimationFrame(simpleDetectLoop);
    }

    startBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: 'user' } });
            video.srcObject = stream;
            video.onloadedmetadata = async () => {
                try { await video.play(); } catch(e) { console.warn('video.play failed', e); }
                resizeOverlay();
                isDetecting = true;
                try {
                    const libsOk = await ensureAIlibs();
                    await initAI();
                    if (enableDetectCheckbox.checked) simpleDetectLoop();
                } catch (e) {
                    console.warn('ensureAIlibs/initAI failed', e);
                    // keep fallback detection (skin-tone) active; do not auto-enable buttons
                    faceStatus.textContent = 'Status: Tidak dapat memuat model AI, gunakan fallback deteksi';
                    faceStatus.className = 'text-xs font-bold text-yellow-600';
                    if (enableDetectCheckbox.checked) simpleDetectLoop();
                }
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
        // Pastikan wajah terdeteksi sebelum mengirim
        if (!faceDetected) {
            alert('Wajah tidak terdeteksi. Pastikan kamera menangkap wajah dengan jelas.');
            return;
        }

        // Tampilkan Loading
        faceStatus.textContent = 'Status: Memproses Presensi...';
        presensiMasukBtn.disabled = true;
        presensiPulangBtn.disabled = true;

        // Resize & Capture (kecilkan ke 480px agar upload ringan)
        canvas.width = 480;
        canvas.height = (video.videoHeight / video.videoWidth) * 480;
        let ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        let photoBase64 = canvas.toDataURL('image/jpeg', 0.7);

        // Dapatkan lokasi lalu kirim ke server
        const geoOptions = { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 };
        navigator.geolocation.getCurrentPosition(async (pos) => {
            try {
                const payload = {
                    photo: photoBase64,
                    type: type,
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude
                };

                const response = await axios.post('/pegawai/presensi', payload, {
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                if (response.data.success) {
                    const statusText = document.getElementById('status-text');
                    let msg = response.data.message;
                    if (response.data.distance !== undefined && response.data.distance !== null) {
                        msg += ' (jarak: ' + Math.round(response.data.distance) + ' m)';
                    }
                    // show lateness info
                    if (response.data.status === 'terlambat_masuk' && response.data.late_minutes) {
                        msg += ' — Terlambat ' + response.data.late_minutes + ' menit';
                    }
                    if (response.data.status === 'pulang_terlambat' && response.data.late_minutes) {
                        msg += ' — Pulang terlambat ' + response.data.late_minutes + ' menit';
                    }
                    statusText.textContent = msg;
                    document.getElementById('status-message').classList.remove('hidden');
                    setTimeout(() => location.reload(), 1100);
                } else {
                    alert('Gagal: ' + response.data.message);
                    const statusText = document.getElementById('status-text');
                    let msg = response.data.message || 'Gagal';
                    if (response.data.distance !== undefined && response.data.distance !== null) {
                        msg += ' (jarak: ' + Math.round(response.data.distance) + ' m)';
                    }
                    if (response.data.status === 'terlambat_masuk' && response.data.late_minutes) {
                        msg += ' — Terlambat ' + response.data.late_minutes + ' menit';
                    }
                    if (response.data.status === 'pulang_terlambat' && response.data.late_minutes) {
                        msg += ' — Pulang terlambat ' + response.data.late_minutes + ' menit';
                    }
                    statusText.textContent = msg;
                    document.getElementById('status-message').classList.remove('hidden');
                }
            } catch (err) {
                alert('Error: ' + (err.response?.data?.message || 'Terjadi kesalahan sistem'));
            } finally {
                faceStatus.textContent = 'Status: Selesai';
            }
        }, (err) => {
            // Improve error messages for common geolocation failures
            if (err.code === 1) {
                alert('Izin lokasi ditolak. Aktifkan izin lokasi di browser dan akses situs melalui HTTPS.');
            } else if (err.code === 2) {
                alert('Lokasi tidak dapat ditentukan. Pastikan perangkat Anda memiliki sinyal GPS atau koneksi internet.');
            } else if (err.code === 3) {
                alert('Timeout mendapatkan lokasi. Coba lagi atau periksa pengaturan lokasi.');
            } else {
                alert('Gagal mendapatkan lokasi. Harap aktifkan GPS Anda.');
            }
        });
    }

    presensiMasukBtn.addEventListener('click', () => captureAndSend('masuk'));
    presensiPulangBtn.addEventListener('click', () => captureAndSend('pulang'));
});

function goBack() { window.history.back(); }
</script>
@endsection