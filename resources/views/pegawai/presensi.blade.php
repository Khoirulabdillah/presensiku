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



    {{-- Camera Interface --}}
    <div class="bg-white shadow-xl rounded-2xl w-full max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-6 text-center">Presensi dengan Kamera</h3>

        {{-- Info Jam Kerja (di atas kamera) --}}
        <div class="mb-4">
            @if(isset($office) && $office)
                @php
                    $jamMasuk = $office->jam_masuk ? \Carbon\Carbon::parse($office->jam_masuk)->format('H:i') : '-';
                    $jamPulang = $office->jam_pulang ? \Carbon\Carbon::parse($office->jam_pulang)->format('H:i') : '-';
                    $batasAwal = ($office->jam_masuk && $office->batas_awal_masuk) ? \Carbon\Carbon::parse($office->jam_masuk)->subMinutes((int)$office->batas_awal_masuk)->format('H:i') : '-';
                    $batasToleransi = ($office->jam_masuk && $office->toleransi_terlambat) ? \Carbon\Carbon::parse($office->jam_masuk)->addMinutes((int)$office->toleransi_terlambat)->format('H:i') : '-';
                @endphp
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-3 sm:p-4 shadow-sm">
                    <div class="grid grid-cols-2 gap-2 sm:gap-3">
                        <div class="bg-white rounded-md p-2 sm:p-3 shadow-xs border border-blue-100">
                            <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Jam Masuk</p>
                            <p class="text-lg sm:text-xl font-bold text-blue-600 mt-1">{{ $jamMasuk }}</p>
                        </div>
                        <div class="bg-white rounded-md p-2 sm:p-3 shadow-xs border border-indigo-100">
                            <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Jam Pulang</p>
                            <p class="text-lg sm:text-xl font-bold text-indigo-600 mt-1">{{ $jamPulang }}</p>
                        </div>
                        <div class="bg-white rounded-md p-2 sm:p-3 shadow-xs border border-amber-100">
                            <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Batas Awal</p>
                            <p class="text-lg sm:text-xl font-bold text-amber-600 mt-1">{{ $batasAwal }}</p>
                        </div>
                        <div class="bg-white rounded-md p-2 sm:p-3 shadow-xs border border-emerald-100">
                            <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Toleransi</p>
                            <p class="text-lg sm:text-xl font-bold text-emerald-600 mt-1">{{ $batasToleransi }}</p>
                        </div>
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

        {{-- Status Presensi Hari Ini --}}
    @if($presensiMasuk || $presensiPulang)
    <div class="bg-white shadow-xl rounded-2xl w-full max-w-4xl mx-auto mt-4 p-4 sm:p-6 lg:p-8 mb-6">
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
</div>

<style>
    /* Mirror preview for camera and overlay so it behaves like a front-facing camera */
    #camera.mirror, #overlay.mirror {
        transform: scaleX(-1);
        transform-origin: center;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
// --- HELPER LOAD LIBRARIES ---
function loadScriptOnce(url, checkGlobal, timeout = 15000) {
    return new Promise((resolve, reject) => {
        try { if (checkGlobal && typeof checkGlobal() !== 'undefined' && checkGlobal() !== null) return resolve(checkGlobal()); } catch(e){}
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
        s.src = url; s.async = true;
        s.onload = () => {
            if (checkGlobal) {
                const start = Date.now();
                (function waitForGlobal2() {
                    try { if (typeof checkGlobal() !== 'undefined' && checkGlobal() !== null) return resolve(checkGlobal()); } catch(e){}
                    if (Date.now() - start > timeout) return reject(new Error('Timeout waiting for global after ' + url));
                    setTimeout(waitForGlobal2, 100);
                })();
            } else { resolve(); }
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
    try { await loadScriptOnce(urls.tf, () => window.tf, 10000); window.__ai_load_report.tf = 'ok'; } 
    catch (e) { console.warn('TFJS Error:', e); }
    try { await loadScriptOnce(urls.blazeface, () => window.blazeface, 10000); window.__ai_load_report.blazeface = 'ok'; } 
    catch (e) { console.warn('BlazeFace Error:', e); }
}

async function initAI() {
    try {
        if (typeof blazeface !== 'undefined' && typeof tf !== 'undefined') {
            faceModel = await blazeface.load();
        } else { faceModel = null; }
    } catch (e) { console.warn('blazeface load failed:', e); faceModel = null; }

    const faceStatusEl = document.getElementById('face-status');
    if (faceModel) {
        if (faceStatusEl) { faceStatusEl.textContent = 'Status: AI Siap (BlazeFace)'; faceStatusEl.className = 'text-xs font-bold text-green-600'; }
    } else if ('FaceDetector' in window) {
        if (faceStatusEl) { faceStatusEl.textContent = 'Status: AI Siap (Native)'; faceStatusEl.className = 'text-xs font-bold text-green-600'; }
    } else {
        if (faceStatusEl) { faceStatusEl.textContent = 'Status: Fallback Mode (Skin Tone)'; faceStatusEl.className = 'text-xs font-bold text-yellow-600'; }
    }
}

// --- GLOBAL VARIABLES ---
let stream = null;
let faceModel = null;
const FACE_BOX_SCALE = 0.45;
const SMOOTHING = 0.2;
const HOLD_MS = 1000;

let smoothedBox = null;
let lastSeenAt = 0;
let remoteHoldUntil = 0;
let faceDetected = false;
let isDetecting = false;
let lastFaceBox = null;
let isSubmitting = false; // <--- VARIABLE BARU: Kunci tombol saat loading

// Flask Integration vars
const FLASK_URL = "{{ env('FLASK_SERVER_URL', 'http://127.0.0.1:5000') }}";
let flaskAvailable = false;
let lastFlaskCheck = 0;
let lastFlaskDetectAt = 0;
const FLASK_DETECT_COOLDOWN = 800;

// Server Data
const serverHasMasuk = @json($presensiMasuk ? true : false);
const serverHasPulang = @json($presensiPulang ? true : false);
const office = @json($office ?? null);

// --- MAIN LOGIC ---
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

    function showToast(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position:fixed; z-index:99999; top:24px; right:24px; display:flex; flex-direction:column; gap:8px;';
            document.body.appendChild(container);
        }
        const el = document.createElement('div');
        el.textContent = message;
        el.style.cssText = 'padding:10px 14px; border-radius:8px; color:#fff; box-shadow:0 6px 18px rgba(0,0,0,0.12); font-size:13px; max-width:320px; word-break:break-word; opacity:0; transform:translateY(-10px); transition:all 0.3s ease;';
        
        if (type === 'success') el.style.background = '#16a34a';
        else if (type === 'warning') el.style.background = '#f59e0b';
        else if (type === 'danger') el.style.background = '#dc2626';
        else el.style.background = '#1f2937';
        
        container.appendChild(el);
        requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
        setTimeout(() => {
            el.style.opacity = '0'; el.style.transform = 'translateY(-10px)';
            setTimeout(() => el.remove(), 300);
        }, 3500);
    }

    setInterval(() => {
        document.getElementById('current-time').textContent = new Date().toLocaleTimeString('id-ID');
    }, 1000);

    async function checkFlaskAvailability() {
        const now = Date.now();
        if (now - lastFlaskCheck < 5000) return flaskAvailable;
        lastFlaskCheck = now;
        try {
            const pingFrame = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HwAF/gL+u1rRygAAAABJRU5ErkJggg==';
            const res = await fetch(`${FLASK_URL}/api/detect-face-frame`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ frame: pingFrame })
            });
            flaskAvailable = res.ok;
        } catch (e) { flaskAvailable = false; }
        return flaskAvailable;
    }

    function parseTimeStringToDate(timeStr) {
        if (!timeStr) return null;
        const parts = timeStr.split(':').map(Number);
        const now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), now.getDate(), parts[0], parts[1] || 0, parts[2] || 0);
    }

    function updateWorkStatus() {
        const statusContainer = document.getElementById('status-message');
        const statusText = document.getElementById('status-text');
        
        if (!office || !office.jam_masuk) {
            if (statusContainer) statusContainer.classList.add('hidden');
            if (!isSubmitting) {
                presensiMasukBtn.disabled = true;
                presensiPulangBtn.disabled = true;
            }
            return;
        }

        const now = new Date();
        const jamMasukDate = parseTimeStringToDate(office.jam_masuk);
        const batasAwalMinutes = parseInt(office.batas_awal_masuk || 0, 10);
        const toleransiMinutes = parseInt(office.toleransi_terlambat || 0, 10);
        const awalAbsen = new Date(jamMasukDate.getTime() - batasAwalMinutes * 60000);
        const batasToleransi = new Date(jamMasukDate.getTime() + toleransiMinutes * 60000);

        if (statusContainer) statusContainer.classList.remove('hidden');

        if (now < awalAbsen) {
            const mins = Math.ceil((awalAbsen - now) / 60000);
            if (statusText) {
                statusText.textContent = `Absen belum dibuka. Tunggu ${mins} menit lagi.`;
                statusText.className = 'text-sm font-medium text-red-600';
            }
        } else if (now <= batasToleransi) {
            if (statusText) {
                statusText.textContent = 'Silakan absen sekarang. Status: Tepat Waktu';
                statusText.className = 'text-sm font-medium text-green-600';
            }
        } else {
            if (statusText) {
                statusText.textContent = 'Status: Terlambat';
                statusText.className = 'text-sm font-medium text-red-600';
            }
        }
    }
    updateWorkStatus();
    setInterval(updateWorkStatus, 1000);

    function resizeOverlay() {
        overlay.width = video.videoWidth || video.clientWidth || 640;
        overlay.height = video.videoHeight || video.clientHeight || 480;
    }

    // --- DETEKSI WAJAH ---
    async function detectWithFlask() {
        try {
            const w = 320;
            const h = Math.max(240, Math.floor((video.videoHeight || 480) / (video.videoWidth || 640) * 320));
            const tmp = document.createElement('canvas'); tmp.width = w; tmp.height = h;
            const tctx = tmp.getContext('2d');
            tctx.drawImage(video, 0, 0, w, h);
            const frameBase64 = tmp.toDataURL('image/jpeg', 0.6);

            const res = await fetch(`${FLASK_URL}/api/detect-face-frame`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ frame: frameBase64 })
            });
            if (!res.ok) { flaskAvailable = false; return { detected: false }; }
            const data = await res.json();
            
            if (data.success && data.face_detected && data.face_count > 0) {
                if (!lastFaceBox) {
                   const ow = overlay.width; const oh = overlay.height;
                   lastFaceBox = { x: (ow*0.3), y: (oh*0.2), width: (ow*0.4), height: (oh*0.6) };
                }
                return { detected: true, count: data.face_count };
            }
        } catch (e) {}
        return { detected: false };
    }

    async function detectWithFaceDetector() {
        if (!('FaceDetector' in window)) return false;
        try {
            const detector = new window.FaceDetector({ fastMode: true, maxDetectedFaces: 1 });
            const results = await detector.detect(video);
            if (results && results.length > 0) {
                const r = results[0].boundingBox;
                lastFaceBox = { x: r.x, y: r.y, width: r.width, height: r.height };
                return true;
            }
        } catch (e) {}
        return false;
    }

    async function detectWithBlazeFace() {
        if (!faceModel) return false;
        try {
            const predictions = await faceModel.estimateFaces(video, false);
            if (predictions.length > 0) {
                const p = predictions[0];
                const start = p.topLeft; const end = p.bottomRight;
                const x1 = start.x ?? start[0]; const y1 = start.y ?? start[1];
                const x2 = end.x ?? end[0]; const y2 = end.y ?? end[1];
                lastFaceBox = { x: x1, y: y1, width: x2 - x1, height: y2 - y1 };
                return true;
            }
        } catch (e) {}
        return false;
    }

    function detectWithSkinTone() {
        const w = 160; const h = 120;
        const tmp = document.createElement('canvas'); tmp.width = w; tmp.height = h;
        const tctx = tmp.getContext('2d');
        try { tctx.drawImage(video, 0, 0, w, h); } catch (e) { return null; }
        const data = tctx.getImageData(0, 0, w, h).data;
        let count = 0, minX = w, maxX = 0, minY = h, maxY = 0;
        for (let y = 0; y < h; y++) {
            for (let x = 0; x < w; x++) {
                const i = (y * w + x) * 4;
                const r = data[i], g = data[i+1], b = data[i+2];
                if (r > 95 && g > 40 && b > 20 && r > g && r > b && (Math.max(r,g,b)-Math.min(r,g,b))>15) {
                    count++;
                    if(x<minX) minX=x; if(x>maxX) maxX=x;
                    if(y<minY) minY=y; if(y>maxY) maxY=y;
                }
            }
        }
        if (count < 300) return null;
        const vW = overlay.width; const vH = overlay.height;
        const sX = vW/w; const sY = vH/h;
        const bw = (maxX - minX)*sX; const bh = (maxY - minY)*sY;
        if (bw < vW*0.1 || bh < vH*0.1) return null; 
        return { x: minX*sX, y: minY*sY, width: bw, height: bh };
    }

    function lerpBox(a, b, t) {
        if (!a) return b;
        return {
            x: a.x + (b.x - a.x) * t,
            y: a.y + (b.y - a.y) * t,
            width: a.width + (b.width - a.width) * t,
            height: a.height + (b.height - a.height) * t
        };
    }

    function drawBox(box) {
        if (!box) return;
        const cx = box.x + (box.width/2);
        const cy = box.y + (box.height/2);
        const nw = box.width * FACE_BOX_SCALE;
        const nh = box.height * FACE_BOX_SCALE;
        overlayCtx.strokeStyle = '#10B981';
        overlayCtx.lineWidth = 4;
        overlayCtx.lineJoin = 'round';
        overlayCtx.strokeRect(cx - (nw/2), cy - (nh/2), nw, nh);
        overlayCtx.lineWidth = 2;
        overlayCtx.strokeStyle = '#ffffff';
        overlayCtx.strokeRect(cx - (nw/2) - 2, cy - (nh/2) - 2, nw + 4, nh + 4);
    }

    async function simpleDetectLoop() {
        if (!isDetecting) return;
        
        let detectedInThisFrame = false;

        if ('FaceDetector' in window) {
            try { if (await detectWithFaceDetector()) detectedInThisFrame = true; } catch(e){}
        }
        if (!detectedInThisFrame && faceModel) {
            try { if (await detectWithBlazeFace()) detectedInThisFrame = true; } catch(e){}
        }
        if (!detectedInThisFrame) {
            const skin = detectWithSkinTone();
            if (skin) { lastFaceBox = skin; detectedInThisFrame = true; }
        }

        if (!detectedInThisFrame && enableDetectCheckbox.checked) {
            const now = Date.now();
            if (now - lastFlaskDetectAt > FLASK_DETECT_COOLDOWN) {
                lastFlaskDetectAt = now;
                detectWithFlask().then(res => {
                    if (res.detected) {
                        lastSeenAt = performance.now();
                        remoteHoldUntil = performance.now() + 1000;
                        faceStatus.textContent = `Status: Wajah Terdeteksi (Remote)`;
                        faceStatus.className = 'text-xs font-bold text-green-600';
                    }
                });
            }
        }

        const now = performance.now();
        if (detectedInThisFrame) lastSeenAt = now;

        overlayCtx.clearRect(0, 0, overlay.width, overlay.height);

        const isGracePeriod = (now - lastSeenAt) <= HOLD_MS;
        const isRemoteHold = now < remoteHoldUntil;

        if (lastFaceBox && (isGracePeriod || isRemoteHold)) {
            if (!smoothedBox) smoothedBox = lastFaceBox;
            else smoothedBox = lerpBox(smoothedBox, lastFaceBox, SMOOTHING);
            drawBox(smoothedBox);
            
            faceDetected = true;
            if (!isRemoteHold) {
                faceStatus.textContent = 'Status: Wajah Terdeteksi';
                faceStatus.className = 'text-xs font-bold text-green-600';
            }

            // --- JANGAN UBAH TOMBOL JIKA SEDANG LOADING ---
            if (!isSubmitting) {
                let allowMasuk = true;
                try {
                    if (office && office.jam_masuk) {
                        const jd = parseTimeStringToDate(office.jam_masuk);
                        const bm = parseInt(office.batas_awal_masuk || 0, 10);
                        if (new Date() < new Date(jd.getTime() - bm*60000)) allowMasuk = false;
                    }
                } catch(e) { allowMasuk = true; }

                presensiMasukBtn.disabled = serverHasMasuk ? true : !allowMasuk;
                presensiPulangBtn.disabled = (serverHasMasuk && !serverHasPulang) ? false : true;
            }

        } else {
            smoothedBox = null;
            faceDetected = false;
            faceStatus.textContent = 'Status: Wajah Tidak Terlihat';
            faceStatus.className = 'text-xs font-bold text-red-600';
            
            // --- JANGAN UBAH TOMBOL JIKA SEDANG LOADING ---
            if (!isSubmitting) {
                presensiMasukBtn.disabled = true;
                presensiPulangBtn.disabled = true;
            }
        }

        requestAnimationFrame(simpleDetectLoop);
    }

    startBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: 'user' } });
            video.srcObject = stream;
            video.onloadedmetadata = async () => {
                try { await video.play(); } catch(e){}
                resizeOverlay();
                isDetecting = true;
                try {
                    await ensureAIlibs();
                    await initAI();
                    checkFlaskAvailability();
                } catch(e) { console.warn(e); }
                simpleDetectLoop();
            };
            startBtn.classList.add('hidden');
            stopBtn.classList.remove('hidden');
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal akses kamera', text: e?.message });
        }
    });

    window.addEventListener('resize', () => { if (video.srcObject) resizeOverlay(); });

    stopBtn.addEventListener('click', () => {
        if (stream) stream.getTracks().forEach(t => t.stop());
        isDetecting = false; video.srcObject = null;
        overlayCtx.clearRect(0,0,overlay.width, overlay.height);
        startBtn.classList.remove('hidden'); stopBtn.classList.add('hidden');
        presensiMasukBtn.disabled = true; presensiPulangBtn.disabled = true;
    });

    // --- FUNGSI KIRIM PRESENSI DENGAN LOADING ANIMATION ---
    async function captureAndSend(type) {
        // Validasi 1: Wajah harus terdeteksi
        if (!faceDetected) {
            Swal.fire({ icon: 'warning', title: 'Wajah tidak terdeteksi', text: 'Pastikan wajah terlihat jelas di dalam kotak hijau.' });
            return;
        }

        // Validasi 2: Kunci tombol agar tidak double-submit
        if (isSubmitting) {
            Swal.fire({ icon: 'warning', title: 'Sedang Diproses', text: 'Silakan tunggu...' });
            return;
        }

        // 1. Kunci UI dan Tampilkan Loading
        isSubmitting = true;
        const btn = type === 'masuk' ? presensiMasukBtn : presensiPulangBtn;
        const originalText = btn.innerHTML; // Simpan teks asli tombol
        
        // Ubah jadi spinner
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Memproses...</span>';
        presensiMasukBtn.disabled = true;
        presensiPulangBtn.disabled = true;
        faceStatus.textContent = 'Status: Mengirim Data...';

        // Helper untuk reset jika gagal
        const resetUI = () => {
            isSubmitting = false;
            btn.innerHTML = originalText;
            faceStatus.textContent = 'Status: Wajah Terdeteksi';
            presensiMasukBtn.disabled = false;
            presensiPulangBtn.disabled = false;
        };

        // 2. Ambil Foto
        canvas.width = 480;
        canvas.height = (video.videoHeight / video.videoWidth) * 480;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const photoBase64 = canvas.toDataURL('image/jpeg', 0.7);

        // Validasi 3: Foto harus berhasil dicapture
        if (!photoBase64 || photoBase64.length < 100) {
            Swal.fire({ icon: 'error', title: 'Gagal Capture', text: 'Tidak dapat mengambil foto. Silakan coba lagi.' });
            resetUI();
            return;
        }

        // 3. Ambil Lokasi & Kirim
        const geoOptions = { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 };
        navigator.geolocation.getCurrentPosition(async (pos) => {
            try {
                // Validasi 4: Lokasi harus valid
                if (!pos.coords.latitude || !pos.coords.longitude) {
                    throw new Error('Koordinat lokasi tidak valid');
                }

                const payload = {
                    photo: photoBase64,
                    type: type,
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude
                };
                const res = await axios.post('/pegawai/presensi', payload, {
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                if (res.data.success) {
                    showToast('Berhasil: ' + res.data.status, 'success');
                    btn.innerHTML = '<i class="fas fa-check"></i> <span>Berhasil!</span>';
                    // Tunggu sebentar sebelum reload, biarkan tombol mati
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.data.message });
                    resetUI(); // Kembalikan tombol agar bisa coba lagi
                }
            } catch (err) {
                const errMsg = err.response?.data?.message || err.message || 'Gagal menghubungi server';
                Swal.fire({ icon: 'error', title: 'Error Sistem', text: errMsg });
                resetUI();
            }
        }, (err) => {
            Swal.fire({ icon: 'warning', title: 'Gagal Lokasi', text: 'Mohon aktifkan GPS & Refresh halaman.' });
            resetUI();
        }, geoOptions);
    }

    presensiMasukBtn.addEventListener('click', () => captureAndSend('masuk'));
    presensiPulangBtn.addEventListener('click', () => captureAndSend('pulang'));
});

function goBack() { window.history.back(); }
</script>
@endsection