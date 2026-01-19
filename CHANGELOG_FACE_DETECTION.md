# Dokumentasi Perubahan Sistem Face Detection

## Perubahan yang Dilakukan

### 1. ✅ Pembersihan File Tidak Diperlukan

**File/Folder yang Dihapus:**
- `face-detection-app/templates/` - Template HTML tidak diperlukan karena API only
- `face-detection-app/static/` - Static files tidak diperlukan
- `face-detection-app/uploads/` - Upload folder tidak diperlukan
- `face-detection-app/src/models/` - Model folder tidak diperlukan
- `face-detection-app/src/utils/` - Utils folder tidak diperlukan
- `INTEGRATION_GUIDE.md`, `README_FACE_DETECTION.md`, dll - Dokumentasi berlebihan
- `FACE_DETECTION_INTEGRATION_EXAMPLE.blade.php` - File contoh tidak diperlukan
- `INTEGRATION_CHECKLIST.md`, `IZIN_FEATURE_README.md`, dll - File dokumentasi lama

### 2. ✅ Update Flask App untuk Validasi Wajah

**File:** `face-detection-app/src/app.py`

**Perubahan:**
- Menghapus dependensi ke template folder dan static folder
- Menambahkan import `face_recognition` library
- Membuat fungsi `decode_base64_image()` untuk decode gambar base64
- **Endpoint baru:** `/api/validate-face` untuk validasi wajah dengan foto referensi
- **Endpoint baru:** `/api/health` untuk health check

**Fitur Endpoint `/api/validate-face`:**
- Menerima 2 foto (base64): foto dari kamera dan foto referensi pegawai
- Mendeteksi wajah di kedua foto
- Mengekstrak fitur wajah (face encoding)
- Membandingkan kedua wajah
- Return hasil: match/tidak match + similarity score

**Parameter Validasi:**
- **Threshold:** 0.5 (50% similarity)
- Wajah dianggap cocok jika distance < 0.5
- Mendeteksi jika ada lebih dari 1 wajah atau tidak ada wajah

### 3. ✅ Update Dependencies

**File:** `face-detection-app/requirements.txt`

**Library Tambahan:**
- `face-recognition==1.3.0` - Face recognition library berbasis dlib
- `cmake` - Diperlukan untuk compile dlib
- `dlib` - Machine learning library untuk face recognition

### 4. ✅ Struktur Folder Baru

```
face-detection-app/
├── src/
│   └── app.py          # Flask app dengan 3 endpoint
├── requirements.txt    # Dependencies
└── README.md          # Dokumentasi lengkap
```

## Cara Menggunakan

### 1. Instalasi

```bash
cd face-detection-app
pip install -r requirements.txt
```

### 2. Jalankan Server

```bash
cd src
python app.py
```

Server akan berjalan di `http://127.0.0.1:5000`

### 3. Integrasi dengan Laravel

Di Laravel, tambahkan di `.env`:
```
FLASK_SERVER_URL=http://127.0.0.1:5000
```

### 4. Alur Presensi

1. **Pegawai membuka halaman presensi** (`resources/views/pegawai/presensi.blade.php`)
2. **Kamera dibuka** - Menampilkan live video
3. **Deteksi wajah real-time** - Menggunakan endpoint `/api/detect-face-frame`
4. **Validasi lokasi & waktu** - Sistem mengecek GPS dan jam kerja
5. **Capture & validasi wajah** - Saat tombol presensi diklik:
   - Foto di-capture dari kamera
   - Laravel mengambil `foto_wajah_asli` pegawai dari database
   - Kedua foto dikirim ke `/api/validate-face`
   - Jika wajah cocok (similarity > threshold), presensi berhasil
   - Jika tidak cocok, presensi ditolak

## API Endpoints

### 1. GET /api/health
Health check server

**Response:**
```json
{
  "success": true,
  "status": "running",
  "message": "Flask Face Recognition API is running"
}
```

### 2. POST /api/detect-face-frame
Deteksi wajah dari frame video (untuk preview real-time)

**Request:**
```json
{
  "frame": "data:image/jpeg;base64,..."
}
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

### 3. POST /api/validate-face ⭐ NEW
Validasi wajah dengan foto referensi

**Request:**
```json
{
  "photo": "data:image/jpeg;base64,...",
  "reference_photo": "data:image/jpeg;base64,..."
}
```

**Response (Match):**
```json
{
  "success": true,
  "match": true,
  "similarity": 85.43,
  "distance": 0.1457,
  "threshold": 0.5,
  "message": "Wajah cocok",
  "face_count": 1,
  "reference_face_count": 1
}
```

**Response (Tidak Match):**
```json
{
  "success": true,
  "match": false,
  "similarity": 42.15,
  "distance": 0.5785,
  "threshold": 0.5,
  "message": "Wajah tidak cocok"
}
```

## Implementasi di Laravel Controller

Contoh penggunaan di Controller untuk validasi wajah:

```php
public function presensi(Request $request)
{
    // ... validasi lokasi dan waktu ...
    
    // Ambil foto referensi pegawai
    $pegawai = Pegawai::where('nip', $nip)->first();
    $fotoReferensi = Storage::disk('public')->get($pegawai->foto_wajah_asli);
    $fotoReferensiBase64 = 'data:image/jpeg;base64,' . base64_encode($fotoReferensi);
    
    // Foto dari kamera
    $photoBase64 = $request->photo; // sudah dalam format base64
    
    // Validasi wajah ke Flask API
    $response = Http::post(env('FLASK_SERVER_URL') . '/api/validate-face', [
        'photo' => $photoBase64,
        'reference_photo' => $fotoReferensiBase64
    ]);
    
    $result = $response->json();
    
    if (!$result['success'] || !$result['match']) {
        return response()->json([
            'success' => false,
            'message' => 'Wajah tidak cocok dengan foto referensi. Similarity: ' . $result['similarity'] . '%'
        ]);
    }
    
    // ... simpan presensi ...
}
```

## Troubleshooting

### Port 5000 sudah digunakan
```bash
FLASK_SERVER_PORT=5001 python app.py
```

### Error CMake
```bash
brew install cmake
```

### Error dlib
```bash
pip install cmake
pip install dlib --verbose
```

## Next Steps

1. **Implementasi di Laravel Controller** - Tambahkan validasi wajah di endpoint presensi
2. **Testing** - Test dengan berbagai kondisi cahaya dan angle
3. **Tuning Threshold** - Sesuaikan threshold (0.4-0.6) berdasarkan akurasi yang diinginkan
4. **Error Handling** - Tambahkan handling untuk berbagai error case
5. **Logging** - Tambahkan logging untuk monitoring

## Catatan Penting

- ✅ Face detection menggunakan OpenCV (cepat untuk preview)
- ✅ Face recognition menggunakan face_recognition/dlib (akurat untuk validasi)
- ✅ Threshold di-set ke 0.5 (dapat disesuaikan)
- ✅ System hanya menerima 1 wajah per foto
- ✅ Foto referensi disimpan di database field `foto_wajah_asli`
- ✅ CORS sudah diaktifkan untuk integrasi dengan Laravel
