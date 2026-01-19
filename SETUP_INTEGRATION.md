# Panduan Integrasi Flask Face Detection dengan Sistem Presensi Laravel

## Overview

Sistem ini mengintegrasikan Flask Face Detection API dengan halaman presensi Laravel Anda. Flask berjalan sebagai service terpisah yang menyediakan API untuk deteksi wajah real-time.

## Arsitektur

```
┌─────────────────────────────────────────────────────────────┐
│                   Browser (Laravel Frontend)                │
│  presensi.blade.php dengan Video Kamera                     │
└────────────────────────┬────────────────────────────────────┘
                         │ HTTP POST (frame base64)
                         ▼
┌─────────────────────────────────────────────────────────────┐
│          Flask Face Detection API (Python)                  │
│  /api/detect-face-frame, /api/detect-face                  │
│  OpenCV + Haar Cascade untuk deteksi wajah                 │
└────────────────────────┬────────────────────────────────────┘
                         │ JSON Response (face_count, faces)
                         ▼
┌─────────────────────────────────────────────────────────────┐
│           Browser (menampilkan hasil deteksi)              │
└─────────────────────────────────────────────────────────────┘
```

## Langkah-Langkah Setup

### 1. Persiapan Flask Server

```bash
# Masuk folder flask
cd D:\Presensiku\presensiku\face-detection-app

# Install dependencies (jika belum)
pip install -r requirements.txt

# Jalankan server
cd src
python app.py
```

Server akan berjalan di: `http://localhost:5000`

### 2. Copy File JavaScript

Copy file `face-detection-integration.js` ke folder public Laravel:

```bash
cp D:\Presensiku\presensiku\face-detection-app\static\face-detection-integration.js \
   D:\Presensiku\presensiku\public\js\
```

### 3. Integrasikan ke presensi.blade.php

Buka file `resources/views/pegawai/presensi.blade.php` dan lakukan langkah berikut:

#### A. Tambahkan Script Import (di atas closing </head> atau sebelum </body>)

```blade
{{-- Face Detection Integration --}}
<script src="{{ asset('js/face-detection-integration.js') }}"></script>
```

#### B. Tambahkan Initialization Script

Tambahkan di dalam tag `<script>` yang sudah ada:

```javascript
// Initialize Face Detection API
let faceDetectionAPI = null;
let faceDetectionActive = false;

document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi dengan URL Flask
    faceDetectionAPI = new FaceDetectionAPI('http://localhost:5000');
    
    // Tunggu API ready
    setTimeout(() => {
        if (faceDetectionAPI.isAvailable) {
            console.log('✓ Face Detection API AKTIF');
        } else {
            console.warn('⚠ Flask server tidak tersedia');
        }
    }, 1000);
});
```

#### C. Tambahkan Fungsi Deteksi Wajah

```javascript
// Fungsi untuk deteksi wajah dari video
async function detectFaceFromCamera(videoElement) {
    if (!videoElement || videoElement.readyState !== videoElement.HAVE_ENOUGH_DATA) {
        return { face_count: 0, face_detected: false };
    }

    try {
        const canvas = document.createElement('canvas');
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(videoElement, 0, 0);

        const frameBase64 = canvas.toDataURL('image/jpeg', 0.6);
        
        if (faceDetectionAPI && faceDetectionAPI.isAvailable) {
            return await faceDetectionAPI.detectFaceFromFrame(frameBase64);
        }
    } catch (error) {
        console.error('Error:', error);
    }
    
    return { face_count: 0, face_detected: false };
}

// Fungsi update status UI
async function updateFaceStatus(videoElement) {
    if (!faceDetectionActive) return;

    const result = await detectFaceFromCamera(videoElement);
    const statusEl = document.getElementById('face-status');
    
    if (statusEl) {
        if (result.face_detected && result.face_count > 0) {
            statusEl.innerHTML = `Status: <span class="text-green-600">✓ ${result.face_count} wajah</span>`;
        } else {
            statusEl.innerHTML = `Status: <span class="text-red-600">✗ Tidak ada wajah</span>`;
        }
    }
}
```

#### D. Update Event Listener untuk Kamera

Cari code yang handle tombol `start-camera` dan `stop-camera`, kemudian update:

```javascript
// Start Camera dengan Face Detection
document.getElementById('start-camera')?.addEventListener('click', async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'user' }
        });
        
        const video = document.getElementById('camera');
        video.srcObject = stream;
        
        // Mulai face detection loop
        faceDetectionActive = true;
        
        const detectionInterval = setInterval(async () => {
            if (!faceDetectionActive) {
                clearInterval(detectionInterval);
                return;
            }
            await updateFaceStatus(video);
        }, 500); // Update setiap 500ms
        
        // Update UI
        document.getElementById('start-camera').classList.add('hidden');
        document.getElementById('stop-camera').classList.remove('hidden');
        
    } catch (error) {
        console.error('Error:', error);
        alert('Tidak bisa mengakses kamera');
    }
});

// Stop Camera
document.getElementById('stop-camera')?.addEventListener('click', () => {
    faceDetectionActive = false;
    
    const video = document.getElementById('camera');
    if (video?.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
    }
    
    document.getElementById('start-camera').classList.remove('hidden');
    document.getElementById('stop-camera').classList.add('hidden');
});
```

#### E. Update Handler untuk Presensi Button

Tambahkan validasi wajah sebelum submit presensi:

```javascript
// Override atau update handler presensi masuk
async function handlePresenceSubmit(type) {
    const video = document.getElementById('camera');
    const canvas = document.getElementById('canvas');
    
    // Validasi wajah
    if (!faceDetectionAPI.isAvailable) {
        alert('Face Detection tidak tersedia!');
        return;
    }

    // Check wajah terdeteksi
    const result = await detectFaceFromCamera(video);
    if (!result.face_detected || result.face_count === 0) {
        alert('⚠ Wajah harus terdeteksi untuk melakukan presensi!');
        return;
    }

    // Capture frame
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);

    const photoBase64 = canvas.toDataURL('image/jpeg');

    // Submit ke Laravel (sesuaikan dengan endpoint Anda)
    try {
        const response = await axios.post(`/pegawai/presensi/${type}`, {
            foto: photoBase64
        });

        if (response.data.success) {
            alert('✓ Presensi berhasil!');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('✗ ' + (response.data.message || 'Gagal presensi'));
        }
    } catch (error) {
        alert('Error: ' + error.response?.data?.message || error.message);
    }
}

// Attach ke button
document.getElementById('presensi-masuk')?.addEventListener('click', () => {
    handlePresenceSubmit('masuk');
});

document.getElementById('presensi-pulang')?.addEventListener('click', () => {
    handlePresenceSubmit('pulang');
});
```

## Testing

### 1. Test Flask API Langsung

```bash
# Test endpoint dengan curl
curl -F "file=@photo.jpg" http://localhost:5000/api/detect-face

# Atau dengan JSON (base64)
curl -X POST http://localhost:5000/api/detect-face-frame \
  -H "Content-Type: application/json" \
  -d '{"frame":"data:image/jpeg;base64,..."}'
```

### 2. Test di Browser

1. Buka halaman presensi: `http://your-site/pegawai/presensi`
2. Buka browser console (F12)
3. Klik tombol "Start Camera"
4. Lihat di console apakah face detection berjalan
5. Cek status "✓ wajah terdeteksi"

## Troubleshooting

### Flask Server Tidak Terkoneksi

**Error:** `Flask server tidak tersedia`

**Solusi:**
```bash
# Pastikan Flask running
cd face-detection-app/src
python app.py

# Cek apakah accessible dari browser
# Kunjungi: http://localhost:5000
```

### CORS Error

**Error:** `Access to XMLHttpRequest blocked by CORS`

**Solusi:**
- Flask sudah menggunakan `flask-cors`
- Pastikan file app.py sudah updated dengan `CORS(app)`
- Jika masih error, restart Flask server

### Kamera Tidak Terdeteksi

**Error:** `Tidak bisa mengakses kamera`

**Solusi:**
- Izinkan browser akses kamera (check browser permissions)
- Gunakan HTTPS atau localhost
- Jangan multiple tabs mengakses kamera bersamaan
- Restart browser

### Wajah Tidak Terdeteksi

**Sebab:**
- Pencahayaan kurang
- Wajah terlalu jauh atau terlalu dekat
- Wajah terhalang (sunglasses, mask, dll)
- Resolusi video terlalu rendah

**Solusi:**
- Pastikan pencahayaan cukup terang
- Posisi wajah 30-60cm dari kamera
- Singkirkan halangan di wajah
- Tujukan wajah langsung ke kamera

### Performance Lambat

**Solusi:**
- Reduce detection frequency: ubah `500` menjadi `1000` di setInterval
- Reduce frame quality: ubah `0.6` menjadi `0.4` di toDataURL
- Pastikan hardware CPU cukup

## Customization

### Ubah Port Flask

Edit `face-detection-app/src/app.py`:
```python
if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=8000)  # Ganti port
```

Kemudian update URL di presensi.blade.php:
```javascript
faceDetectionAPI = new FaceDetectionAPI('http://localhost:8000');
```

### Ubah Sensitivity Deteksi

Edit `face-detection-app/src/app.py`:
```python
faces = face_cascade.detectMultiScale(
    gray,
    scaleFactor=1.3,    # Smaller = lebih sensitif (1.1-1.5)
    minNeighbors=4,     # Smaller = lebih sensitif (3-7)
    minSize=(30, 30)    # Smaller = deteksi wajah kecil
)
```

### Ubah Frequency Deteksi

Edit presensi.blade.php:
```javascript
const detectionInterval = setInterval(async () => {
    // ...
}, 1000); // 1000ms = deteksi setiap 1 detik (default 500ms)
```

## Environment Configuration (Production)

Jika Flask di server lain, gunakan environment variable:

**`.env` Laravel:**
```
FLASK_SERVER_URL=http://your-flask-server.com:5000
```

**`presensi.blade.php`:**
```blade
<script>
    const FLASK_URL = '{{ env("FLASK_SERVER_URL", "http://localhost:5000") }}';
    faceDetectionAPI = new FaceDetectionAPI(FLASK_URL);
</script>
```

## Tips & Best Practices

1. **Always Validate Face Before Submit**
   - Jangan izinkan presensi tanpa wajah terdeteksi

2. **Optimize for Performance**
   - Gunakan JPEG compression (0.5-0.7)
   - Deteksi setiap 500-1000ms bukan setiap frame

3. **Provide User Feedback**
   - Tampilkan status "wajah terdeteksi" realtime
   - Disable/enable button berdasarkan deteksi

4. **Handle Errors Gracefully**
   - Tangkap error network/camera
   - Berikan pesan error yang jelas ke user

5. **Monitor Performance**
   - Check browser console untuk warning
   - Monitor CPU usage saat detection active

## Support & Documentation

- Flask Documentation: https://flask.palletsprojects.com/
- OpenCV Documentation: https://docs.opencv.org/
- Face Detection Models: Haar Cascade (built-in OpenCV)

## File Structure

```
face-detection-app/
├── src/
│   ├── app.py (Main Flask API)
│   ├── models/detector.py
│   └── utils/helpers.py
├── static/
│   ├── face-detection-integration.js (Copy ke public/js/)
│   └── style.css
├── templates/
│   ├── index.html
│   └── results.html
├── requirements.txt
└── INTEGRATION_GUIDE.md
```

Selamat! 🎉 Sistem face detection sudah terintegrasi dengan halaman presensi Anda.
