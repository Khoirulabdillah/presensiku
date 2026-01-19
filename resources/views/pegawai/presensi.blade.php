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

        {{-- Info Jam Kerja (di atas kamera) --}}
        <div class="mb-4">
            @if(isset($office) && $office)
                @php
                    $jamMasuk = $office->jam_masuk ? \Carbon\Carbon::parse($office->jam_masuk)->format('H:i') : '-';
                    $jamPulang = $office->jam_pulang ? \Carbon\Carbon::parse($office->jam_pulang)->format('H:i') : '-';
                    $batasAwal = ($office->jam_masuk && $office->batas_awal_masuk) ? \Carbon\Carbon::parse($office->jam_masuk)->subMinutes($office->batas_awal_masuk)->format('H:i') : '-';
                    $batasToleransi = ($office->jam_masuk && $office->toleransi_terlambat) ? \Carbon\Carbon::parse($office->jam_masuk)->addMinutes($office->toleransi_terlambat)->format('H:i') : '-';
                @endphp
                <div class="bg-white/90 border border-gray-100 rounded-lg p-3 shadow-sm max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <div class="px-3 py-2 bg-green-50 border border-green-100 rounded-md">
                            <p class="text-xs text-gray-500">Jam Masuk</p>
                            <p class="text-lg font-semibold text-green-700">{{ $jamMasuk }}</p>
                        </div>
                        <div class="px-3 py-2 bg-blue-50 border border-blue-100 rounded-md">
                            <p class="text-xs text-gray-500">Jam Pulang</p>
                            <p class="text-lg font-semibold text-blue-700">{{ $jamPulang }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 text-center sm:text-right">
                        <div>Batas Awal Absen: <span class="font-medium">{{ $batasAwal }}</span> <span class="text-xs text-gray-400">({{ $office->batas_awal_masuk ?? 0 }} menit sebelum)</span></div>
                        <div class="mt-1">Batas Toleransi: <span class="font-medium">{{ $batasToleransi }}</span> <span class="text-xs text-gray-400">({{ $office->toleransi_terlambat ?? 0 }} menit setelah)</span></div>
                    </div>
                </div>
            @else
                <div class="text-sm text-gray-500">Informasi jam kerja belum diatur.</div>
            @endif
        </div>

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

// Global flag shared between initAI() and DOM handlers
var faceApiAvailable = false;

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

const faceStatusEl = document.getElementById('face-status');
if (faceApiAvailable) {
    if (faceStatusEl) {
        faceStatusEl.textContent = 'Status: AI Siap';
        faceStatusEl.className = 'text-xs font-bold text-green-600';
    }
} else if (faceModel) {
    if (faceStatusEl) {
        faceStatusEl.textContent = 'Status: AI terbatas (deteksi saja)';
        faceStatusEl.className = 'text-xs font-bold text-yellow-600';
    }
} else if ('FaceDetector' in window) {
    if (faceStatusEl) {
        faceStatusEl.textContent = 'Status: AI terbatas (native detector)';
        faceStatusEl.className = 'text-xs font-bold text-yellow-600';
    }
    window.__ai_load_report = window.__ai_load_report || {};
    window.__ai_load_report.native = 'ok';
} else {
    if (faceStatusEl) {
        faceStatusEl.textContent = 'Status: Gagal memuat AI';
        faceStatusEl.className = 'text-xs font-bold text-red-600';
    }
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
    
    // Simple toast helper
    function showToast(message, type = 'info', timeout = 3500) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.position = 'fixed';
            container.style.zIndex = 99999;
            container.style.top = '24px';
            container.style.right = '24px';
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '8px';
            document.body.appendChild(container);
        }
        const el = document.createElement('div');
        el.textContent = message;
        el.style.padding = '10px 14px';
        el.style.borderRadius = '8px';
        el.style.color = '#fff';
        el.style.boxShadow = '0 6px 18px rgba(0,0,0,0.12)';
        el.style.fontSize = '13px';
        el.style.maxWidth = '320px';
        el.style.wordBreak = 'break-word';
        if (type === 'success') el.style.background = '#16a34a';
        else if (type === 'warning') el.style.background = '#f59e0b';
        else if (type === 'danger') el.style.background = '#dc2626';
        else el.style.background = '#1f2937';
        container.appendChild(el);
        setTimeout(() => {
            el.style.transition = 'opacity 220ms ease, transform 220ms ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-6px)';
            setTimeout(() => el.remove(), 240);
        }, timeout);
    }
    
    let stream = null;
    let faceModel = null;
    // scale of detection box relative to detected face bbox (0 < scale <= 1).
    // Lower values produce a smaller green box around the face.
    const FACE_BOX_SCALE = 0.4;
    // smoothing factor for box movement (0 = no smoothing, 1 = instant)
    const SMOOTHING = 0.25;
    let smoothedBox = null;
    // keep last seen bbox for a short grace period to avoid blinking
    let lastSeenAt = 0;
    const HOLD_MS = 300;
    // keep remote (Flask) detection alive for a short period to avoid immediate red status
    let remoteHoldUntil = 0;
    let faceDetected = false;
    let isDetecting = false;
    let lastFaceBox = null; // store last fast-detection box for faster descriptor crop

    // Flask Face Detection API integration
    const FLASK_URL = "{{ env('FLASK_SERVER_URL', 'http://127.0.0.1:5000') }}";
    // Expose to window for quick debugging via DevTools console
    window.FLASK_URL = FLASK_URL;
    let flaskAvailable = false;
    let lastFlaskCheck = 0;
    let lastFlaskDetectAt = 0;
    // Interval antara pengiriman frame ke Flask (ms)
    const FLASK_DETECT_COOLDOWN = 800;

        // server-side flags: has already presensi masuk/pulang
        const serverHasMasuk = @json($presensiMasuk ? true : false);
        const serverHasPulang = @json($presensiPulang ? true : false);
        // office settings from server (may be null)
        const office = @json($office ?? null);

    // Timer
    setInterval(() => {
        document.getElementById('current-time').textContent = new Date().toLocaleTimeString('id-ID');
    }, 1000);

    // Cek ketersediaan Flask Face Detection API
    async function checkFlaskAvailability() {
        const now = Date.now();
        if (now - lastFlaskCheck < 5000) return flaskAvailable;
        lastFlaskCheck = now;
        try {
            // kirim frame dummy kecil untuk ping API
            const pingFrame = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HwAF/gL+u1rRygAAAABJRU5ErkJggg==';
            const res = await fetch(`${FLASK_URL}/api/detect-face-frame`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ frame: pingFrame })
            });
            flaskAvailable = res.ok;
        } catch (e) {
            flaskAvailable = false;
        }
        return flaskAvailable;
    }

    // Dynamic work-time status checker (runs every second)
    function parseTimeStringToDate(timeStr) {
        if (!timeStr) return null;
        // accept HH:MM:SS or HH:MM
        const parts = timeStr.split(':').map(Number);
        if (parts.length < 2) return null;
        const now = new Date();
        const hh = parts[0];
        const mm = parts[1] || 0;
        const ss = parts[2] || 0;
        return new Date(now.getFullYear(), now.getMonth(), now.getDate(), hh, mm, ss);
    }

    function updateWorkStatus() {
        const statusContainer = document.getElementById('status-message');
        const statusText = document.getElementById('status-text');
        const masukBtn = document.getElementById('presensi-masuk');
        const pulangBtn = document.getElementById('presensi-pulang');
        if (!office || !office.jam_masuk) {
            // hide
            if (statusContainer) statusContainer.classList.add('hidden');
            if (masukBtn) masukBtn.disabled = true;
            if (pulangBtn) pulangBtn.disabled = true;
            return;
        }

        const now = new Date();
        const jamMasukDate = parseTimeStringToDate(office.jam_masuk);
        const batasAwalMinutes = parseInt(office.batas_awal_masuk || 0, 10);
        const toleransiMinutes = parseInt(office.toleransi_terlambat || 0, 10);
        const awalAbsen = new Date(jamMasukDate.getTime() - batasAwalMinutes * 60000);
        const batasToleransi = new Date(jamMasukDate.getTime() + toleransiMinutes * 60000);

        // ensure visible
        if (statusContainer) statusContainer.classList.remove('hidden');

        if (now < awalAbsen) {
            const mins = Math.ceil((awalAbsen - now) / 60000);
            if (statusText) {
                statusText.textContent = 'Absen belum dibuka. Kamera akan aktif dalam ' + mins + ' menit lagi.';
                statusText.className = 'text-sm font-medium text-red-600';
            }
            if (masukBtn) masukBtn.disabled = true;
            if (pulangBtn) pulangBtn.disabled = true;
        } else if (now >= awalAbsen && now <= batasToleransi) {
            if (statusText) {
                statusText.textContent = 'Silakan absen sekarang. Status: Tepat Waktu';
                statusText.className = 'text-sm font-medium text-green-600';
            }
            if (masukBtn) masukBtn.disabled = !!serverHasMasuk; // allow masuk only if not already
            if (pulangBtn) pulangBtn.disabled = !serverHasMasuk || !!serverHasPulang; // pulang only after masuk
        } else {
            if (statusText) {
                statusText.textContent = 'Status: Terlambat';
                statusText.className = 'text-sm font-medium text-red-600';
            }
            if (masukBtn) masukBtn.disabled = !!serverHasMasuk;
            if (pulangBtn) pulangBtn.disabled = !serverHasMasuk || !!serverHasPulang;
        }
    }

    // run immediately and every second
    updateWorkStatus();
    setInterval(updateWorkStatus, 1000);

    // Load face-api models for descriptor computation and fallback blazeface for fast detection
    function resizeOverlay() {
        overlay.width = video.videoWidth || video.clientWidth || 640;
        overlay.height = video.videoHeight || video.clientHeight || 480;
    }

    // Deteksi melalui Flask API (remote) sebagai fallback/validasi
    async function detectWithFlask() {
        if (!enableDetectCheckbox.checked) return { detected: false };
        if (!await checkFlaskAvailability()) return { detected: false };
        const now = Date.now();
        if (now - lastFlaskDetectAt < FLASK_DETECT_COOLDOWN) return { detected: false, throttled: true };
        lastFlaskDetectAt = now;

        try {
            const w = 320;
            const h = Math.max(240, Math.floor((video.videoHeight || 480) / (video.videoWidth || 640) * 320));
            const tmp = document.createElement('canvas');
            tmp.width = w; tmp.height = h;
            const tctx = tmp.getContext('2d');
            tctx.drawImage(video, 0, 0, w, h);
            const frameBase64 = tmp.toDataURL('image/jpeg', 0.6);

            const res = await fetch(`${FLASK_URL}/api/detect-face-frame`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ frame: frameBase64 })
            });
            if (!res.ok) {
                flaskAvailable = false;
                return { detected: false };
            }
            const data = await res.json();
            if (data.success && data.face_detected && data.face_count > 0) {
                // hold green status briefly even if next local frame misses
                remoteHoldUntil = performance.now() + 800;
                // synthesize a simple bbox if none exists so overlay still shows a box
                if (!lastFaceBox) {
                    const ow = overlay.width || video.videoWidth || 640;
                    const oh = overlay.height || video.videoHeight || 480;
                    const bw = ow * 0.35;
                    const bh = oh * 0.45;
                    lastFaceBox = {
                        x: (ow - bw) / 2,
                        y: (oh - bh) / 2,
                        width: bw,
                        height: bh,
                    };
                }
                return { detected: true, count: data.face_count };
            }
        } catch (e) {
            console.warn('Flask detection error:', e);
        }
        return { detected: false };
    }

    // Simple detection loop WITHOUT TensorFlow (native FaceDetector or skin-tone)
    async function detectWithFaceDetector() {
        if (!('FaceDetector' in window)) return false;
        try {
            const detector = new window.FaceDetector({ fastMode: true, maxDetectedFaces: 1 });
            const results = await detector.detect(video);
            if (results && results.length > 0) {
                const r = results[0].boundingBox;
                const x = r.x, y = r.y, w = r.width, h = r.height;
                // store raw bbox; drawing will be handled centrally in drawBox()
                lastFaceBox = { x: x, y: y, width: w, height: h };
                return true;
            }
            return false;
        } catch (e) { console.warn('FaceDetector failed:', e); return false; }
    }

    async function detectWithBlazeFace() {
        if (!faceModel) return false;
        try {
            const predictions = await faceModel.estimateFaces(video, false);
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
                // store raw bbox; drawing will be handled centrally in drawBox()
                lastFaceBox = { x: x, y: y, width: w, height: h };
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
        // require enough skin pixels to reduce false positives
        if (count < 300) return null;
        // return bbox in video pixel coordinates (not already scaled to overlay)
        const vW = video.videoWidth || video.clientWidth || overlay.width;
        const vH = video.videoHeight || video.clientHeight || overlay.height;
        const scaleX = vW / w, scaleY = vH / h;
        const bx = minX * scaleX, by = minY * scaleY, bwidth = (maxX - minX) * scaleX, bheight = (maxY - minY) * scaleY;
        // require reasonable size and centrality to be considered a face
        const minDim = Math.min(vW, vH) * 0.08; // at least 8% of smaller dimension
        const maxDim = Math.max(vW, vH) * 0.9;  // at most 90% of larger dimension
        const cx = bx + bwidth / 2, cy = by + bheight / 2;
        if (bwidth < minDim || bheight < minDim) return null;
        if (bwidth > maxDim || bheight > maxDim) return null;
        // central area check (within 90% central box)
        if (cx < vW * 0.05 || cx > vW * 0.95 || cy < vH * 0.05 || cy > vH * 0.95) return null;
        return { x: bx, y: by, width: bwidth, height: bheight };
    }

    function lerpBox(a, b, t) {
        if (!a) return { x: b.x, y: b.y, width: b.width, height: b.height };
        return {
            x: a.x + (b.x - a.x) * t,
            y: a.y + (b.y - a.y) * t,
            width: a.width + (b.width - a.width) * t,
            height: a.height + (b.height - a.height) * t
        };
    }

    function drawBox(box) {
        if (!box) return;
        // shrink the provided box slightly for visual niceness
        const cx = box.x + (box.width || 0) / 2;
        const cy = box.y + (box.height || 0) / 2;
        const nw = (box.width || 0) * FACE_BOX_SCALE;
        const nh = (box.height || 0) * FACE_BOX_SCALE;
        const nx = cx - nw / 2;
        const ny = cy - nh / 2;
        overlayCtx.strokeStyle='#10B981';
        overlayCtx.lineWidth=4;
        overlayCtx.strokeRect(nx, ny, nw, nh);
    }

    async function simpleDetectLoop() {
        if (!isDetecting) return;
        // try native detector first
        if ('FaceDetector' in window) {
            try {
                const ok = await detectWithFaceDetector();
                if (ok) {
                    // mark last seen moment to hold box for a short period
                    lastSeenAt = performance.now();
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
                    // mark last seen moment to hold box for a short period
                    lastSeenAt = performance.now();
                    requestAnimationFrame(simpleDetectLoop);
                    return;
                }
            } catch (e) { console.warn(e); }
        }

        // skin-tone heuristic fallback
        const skinBox = detectWithSkinTone();
        if (skinBox) {
            // store raw box (video coords) for centralized drawing
            lastFaceBox = skinBox;
            // mark last seen moment to hold box for a short period
            lastSeenAt = performance.now();
        }

        // Jika semua deteksi lokal gagal, coba deteksi via Flask API (remote)
        if (!lastFaceBox && enableDetectCheckbox.checked) {
            const remote = await detectWithFlask();
            if (remote.detected) {
                faceDetected = true;
                faceStatus.textContent = `Status: ${remote.count || 1} wajah terdeteksi (Flask)`;
                faceStatus.className = 'text-xs font-bold text-green-600';
                let allowMasukByTime = true;
                try {
                    if (office && office.jam_masuk) {
                        const jamMasukDate = parseTimeStringToDate(office.jam_masuk);
                        const batasAwalMinutes = parseInt(office.batas_awal_masuk || 0, 10);
                        const awalAbsen = new Date(jamMasukDate.getTime() - batasAwalMinutes * 60000);
                        if (new Date() < awalAbsen) allowMasukByTime = false;
                    }
                } catch (e) { allowMasukByTime = true; }
                presensiMasukBtn.disabled = serverHasMasuk ? true : !allowMasukByTime;
                presensiPulangBtn.disabled = (serverHasMasuk && !serverHasPulang) ? false : true;
                overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
                requestAnimationFrame(simpleDetectLoop);
                return;
            }
        }

        // central drawing logic: clear overlay once per frame
        overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
        const now = performance.now();
        const seenRecently = (now - lastSeenAt) <= HOLD_MS && lastFaceBox;
        const remoteHeld = now < remoteHoldUntil;

        if (seenRecently || remoteHeld) {
            // smooth and draw
            if (lastFaceBox) {
                smoothedBox = lerpBox(smoothedBox, lastFaceBox, SMOOTHING);
                drawBox(smoothedBox);
            }
            faceDetected = true;
            faceStatus.textContent = 'Status: Wajah Terdeteksi';
            faceStatus.className = 'text-xs font-bold text-green-600';
            // Respect time-window: do not enable masuk button if now < awalAbsen
            let allowMasukByTime = true;
            try {
                if (office && office.jam_masuk) {
                    const jamMasukDate = parseTimeStringToDate(office.jam_masuk);
                    const batasAwalMinutes = parseInt(office.batas_awal_masuk || 0, 10);
                    const awalAbsen = new Date(jamMasukDate.getTime() - batasAwalMinutes * 60000);
                    if (new Date() < awalAbsen) allowMasukByTime = false;
                }
            } catch (e) { allowMasukByTime = true; }
            presensiMasukBtn.disabled = serverHasMasuk ? true : !allowMasukByTime;
            presensiPulangBtn.disabled = (serverHasMasuk && !serverHasPulang) ? false : true;
        } else {
            // no face: clear overlay and reset smoothedBox
            smoothedBox = null;
            lastFaceBox = null;
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
                    // Cek ketersediaan Flask API di background
                    checkFlaskAvailability();
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
            Swal.fire({
                icon: 'error',
                title: 'Gagal akses kamera',
                text: e?.message || 'Periksa izin kamera di browser.',
            });
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
            Swal.fire({
                icon: 'warning',
                title: 'Wajah tidak terdeteksi',
                text: 'Pastikan wajah terlihat jelas di kamera.',
            });
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
                    if (response.data.status === 'terlambat_masuk' && response.data.late_minutes) {
                        msg += ' — Terlambat ' + response.data.late_minutes + ' menit';
                    }

                    // Show pulang-specific toast and append details to message when needed
                    if (response.data.status === 'pulang_tepat') {
                        showToast('Pulang tepat waktu', 'success');
                    } else if (response.data.status === 'pulang_cepat') {
                        const early = response.data.early_minutes || 0;
                        showToast('Pulang cepat — ' + early + ' menit lebih awal', 'warning');
                        msg += ' — Pulang cepat ' + early + ' menit';
                    } else if (response.data.status === 'pulang_terlambat') {
                        const late = response.data.late_minutes || 0;
                        showToast('Pulang terlambat — ' + late + ' menit', 'danger');
                        msg += ' — Pulang terlambat ' + late + ' menit';
                    }

                    statusText.textContent = msg;
                    document.getElementById('status-message').classList.remove('hidden');
                    setTimeout(() => location.reload(), 1100);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.data.message || 'Presensi gagal.',
                    });
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
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.response?.data?.message || 'Terjadi kesalahan sistem',
                });
            } finally {
                faceStatus.textContent = 'Status: Selesai';
            }
        }, (err) => {
            // Improve error messages for common geolocation failures
            let geoMsg = 'Gagal mendapatkan lokasi. Harap aktifkan GPS Anda.';
            if (err.code === 1) {
                geoMsg = 'Izin lokasi ditolak. Aktifkan izin lokasi di browser dan akses situs melalui HTTPS.';
            } else if (err.code === 2) {
                geoMsg = 'Lokasi tidak dapat ditentukan. Pastikan perangkat Anda memiliki sinyal GPS atau koneksi internet.';
            } else if (err.code === 3) {
                geoMsg = 'Timeout mendapatkan lokasi. Coba lagi atau periksa pengaturan lokasi.';
            }
            Swal.fire({
                icon: 'warning',
                title: 'Lokasi diperlukan',
                text: geoMsg,
            });
        });
    }

    presensiMasukBtn.addEventListener('click', () => captureAndSend('masuk'));
    presensiPulangBtn.addEventListener('click', () => captureAndSend('pulang'));
});

function goBack() { window.history.back(); }
</script>
@endsection