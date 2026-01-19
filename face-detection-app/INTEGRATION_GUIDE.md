# Integrasi Flask Face Detection dengan Laravel Presensi

Panduan untuk mengintegrasikan Flask Face Detection API dengan sistem presensi Laravel.

## Setup

### 1. Install Flask CORS
```bash
cd face-detection-app
pip install flask-cors
```

### 2. Jalankan Flask Server
```bash
cd face-detection-app/src
python app.py
```

Flask akan berjalan di `http://localhost:5000` atau `http://0.0.0.0:5000`

## API Endpoints

### 1. `/api/detect-face` (POST)
Deteksi wajah dari file upload atau base64 image

**Request:**
```javascript
// Dari file
const formData = new FormData();
formData.append('file', fileInput.files[0]);
fetch('http://localhost:5000/api/detect-face', {
    method: 'POST',
    body: formData
});

// Atau dari base64
fetch('http://localhost:5000/api/detect-face', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ 
        image: 'data:image/jpeg;base64,...' 
    })
});
```

**Response:**
```json
{
    "success": true,
    "face_count": 2,
    "faces": [
        {
            "id": 1,
            "x": 100,
            "y": 50,
            "width": 80,
            "height": 100,
            "confidence": 0.95
        }
    ],
    "message": "2 wajah terdeteksi"
}
```

### 2. `/api/detect-face-frame` (POST)
Deteksi wajah dari video frame (untuk real-time detection)

**Request:**
```javascript
fetch('http://localhost:5000/api/detect-face-frame', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ 
        frame: 'data:image/jpeg;base64,...' 
    })
});
```

**Response:**
```json
{
    "success": true,
    "face_detected": true,
    "face_count": 1,
    "message": "1 wajah terdeteksi"
}
```

## Integrasi dengan Blade Template

### 1. Tambahkan Script ke presensi.blade.php

Di bagian `<script>` atau sebelum closing `</head>`, tambahkan:

```blade
{{-- Face Detection Integration --}}
<script src="{{ asset('js/face-detection-integration.js') }}"></script>
<script>
    // Initialize Face Detection API
    initFaceDetectionAPI('http://localhost:5000');
</script>
```

### 2. Integrasikan dengan Event Kamera

Di dalam script JavaScript pada presensi.blade.php, tambahkan kode berikut ke dalam frame loop:

```javascript
// Setelah mendapatkan frame dari camera
const canvasFrame = document.getElementById('canvas');
const ctx = canvasFrame.getContext('2d');
ctx.drawImage(video, 0, 0);

// Deteksi wajah menggunakan Flask
const frameBase64 = canvasFrame.toDataURL('image/jpeg');
const result = await window.faceDetectionAPI.detectFaceFromFrame(frameBase64);

// Update status
if (result.face_detected) {
    document.getElementById('face-status').textContent = 
        `Status: ${result.face_count} wajah terdeteksi ✓`;
} else {
    document.getElementById('face-status').textContent = 
        'Status: Tidak ada wajah';
}
```

### 3. Gunakan Hasil Deteksi untuk Validasi Presensi

```javascript
// Contoh: Hanya aktifkan tombol presensi jika wajah terdeteksi
const presenceBtn = document.getElementById('presensi-masuk');

async function handlePresenceClick() {
    // Ambil frame saat ini
    const canvas = document.getElementById('canvas');
    const frameBase64 = canvas.toDataURL('image/jpeg');
    
    // Deteksi wajah
    const result = await window.faceDetectionAPI.detectFaceFromFrame(frameBase64);
    
    if (result.face_count === 0) {
        alert('Wajah harus terdeteksi untuk melakukan presensi!');
        return;
    }
    
    // Lanjutkan dengan proses presensi normal
    // ... kode presensi Anda di sini ...
}

presenceBtn.addEventListener('click', handlePresenceClick);
```

## Contoh Integrasi Lengkap

### HTML (di presensi.blade.php)
```html
<div class="mb-6">
    <div class="relative bg-gray-100 rounded-lg overflow-hidden" style="height: 400px;">
        <video id="camera" class="w-full h-full object-cover mirror" 
               autoplay playsinline muted></video>
        <canvas id="overlay" class="absolute inset-0 w-full h-full pointer-events-none mirror"></canvas>
        <canvas id="canvas" class="hidden"></canvas>

        <div class="absolute top-4 left-4 bg-white/90 px-3 py-1 rounded-md text-sm shadow-sm">
            <div id="face-status" class="text-xs font-bold text-gray-600">
                Status: Memuat...
            </div>
        </div>
    </div>
</div>
```

### JavaScript
```javascript
<script src="{{ asset('js/face-detection-integration.js') }}"></script>
<script>
    // Initialize
    initFaceDetectionAPI('http://localhost:5000');
    
    const video = document.getElementById('camera');
    const canvas = document.getElementById('canvas');
    const statusEl = document.getElementById('face-status');
    let detectionRunning = false;

    // Start camera
    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' }
            });
            video.srcObject = stream;
            startFaceDetection();
        } catch (err) {
            console.error('Error mengakses kamera:', err);
            statusEl.textContent = 'Error: Tidak bisa akses kamera';
        }
    }

    // Face detection loop
    async function startFaceDetection() {
        detectionRunning = true;
        
        while (detectionRunning) {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0);
                
                // Deteksi wajah
                const frameBase64 = canvas.toDataURL('image/jpeg', 0.7);
                const result = await window.faceDetectionAPI.detectFaceFromFrame(frameBase64);
                
                // Update status
                if (result.success && result.face_detected) {
                    statusEl.textContent = `✓ ${result.face_count} wajah terdeteksi`;
                    statusEl.classList.add('text-green-600');
                    statusEl.classList.remove('text-red-600');
                } else {
                    statusEl.textContent = '✗ Tidak ada wajah';
                    statusEl.classList.add('text-red-600');
                    statusEl.classList.remove('text-green-600');
                }
            }
            
            // Deteksi setiap 500ms untuk efisiensi
            await new Promise(resolve => setTimeout(resolve, 500));
        }
    }

    // Mulai saat page load
    window.addEventListener('load', startCamera);
</script>
```

## Konfigurasi Environment

### Production (jika Flask di server lain)
```blade
<script>
    const FLASK_URL = '{{ env("FLASK_SERVER_URL", "http://localhost:5000") }}';
    initFaceDetectionAPI(FLASK_URL);
</script>
```

Di `.env` Laravel:
```
FLASK_SERVER_URL=http://your-flask-server:5000
```

## Troubleshooting

### CORS Error
Pastikan Flask sudah install `flask-cors`:
```bash
pip install flask-cors
```

### Flask Server Tidak Terkoneksi
1. Pastikan Flask server berjalan: `python src/app.py`
2. Cek URL: default adalah `http://localhost:5000`
3. Jika menggunakan domain lain, update URL di script

### Performa Lambat
- Kurangi frequency deteksi (default 500ms)
- Kurangi kualitas frame: `canvas.toDataURL('image/jpeg', 0.5)`
- Gunakan resolution kamera yang lebih kecil

## Tips Penggunaan

1. **Validasi Wajah Sebelum Presensi**
   - Hanya izinkan presensi jika wajah terdeteksi
   - Tampilkan warning jika tidak ada wajah

2. **Performance Optimization**
   - Jalankan deteksi setiap 500ms atau 1000ms, bukan setiap frame
   - Gunakan JPEG compression untuk frame (quality 0.5-0.7)

3. **Testing API**
   - Gunakan curl untuk test endpoint:
   ```bash
   curl -F "file=@photo.jpg" http://localhost:5000/api/detect-face
   ```

## File yang Disediakan

- `face-detection-integration.js` - JavaScript library untuk integrasi
- `app.py` - Flask API server dengan CORS enabled
- `requirements.txt` - Dependencies Python
