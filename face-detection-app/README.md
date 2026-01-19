# Face Detection API untuk Sistem Presensi

Flask API untuk deteksi dan validasi wajah pada sistem presensi online kantor desa.

## Fitur

1. **Deteksi Wajah Real-time** - Mendeteksi wajah dari stream kamera untuk preview
2. **Validasi Wajah** - Membandingkan wajah yang di-capture dengan foto referensi pegawai
3. **Integrasi dengan Laravel** - API endpoint yang siap digunakan dari aplikasi Laravel

## Instalasi

### 1. Install Dependencies

```bash
cd face-detection-app
pip install -r requirements.txt
```

**Catatan:** Instalasi `dlib` memerlukan CMake. Jika belum terinstall:
- **macOS**: `brew install cmake`
- **Ubuntu/Debian**: `sudo apt-get install cmake`
- **Windows**: Download dari https://cmake.org/download/

### 2. Jalankan Server Flask

```bash
cd src
python app.py
```

Server akan berjalan di `http://127.0.0.1:5000`

Atau jalankan dengan port custom:
```bash
FLASK_SERVER_PORT=5001 python app.py
```

## API Endpoints

### 1. Health Check
```
GET /api/health
```

Response:
```json
{
  "success": true,
  "status": "running",
  "message": "Flask Face Recognition API is running"
}
```

### 2. Deteksi Wajah dari Frame (Real-time)
```
POST /api/detect-face-frame
```

Request Body:
```json
{
  "frame": "data:image/jpeg;base64,/9j/4AAQ..."
}
```

Response:
```json
{
  "success": true,
  "face_detected": true,
  "face_count": 1,
  "message": "1 wajah terdeteksi"
}
```

### 3. Validasi Wajah dengan Foto Referensi
```
POST /api/validate-face
```

Request Body:
```json
{
  "photo": "data:image/jpeg;base64,/9j/4AAQ...",
  "reference_photo": "data:image/jpeg;base64,/9j/4AAQ..."
}
```

Response (Match):
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

Response (Tidak Match):
```json
{
  "success": true,
  "match": false,
  "similarity": 42.15,
  "distance": 0.5785,
  "threshold": 0.5,
  "message": "Wajah tidak cocok",
  "face_count": 1,
  "reference_face_count": 1
}
```

Response (Error - Wajah tidak terdeteksi):
```json
{
  "success": false,
  "match": false,
  "error": "Tidak ada wajah terdeteksi di foto",
  "face_count": 0
}
```

## Integrasi dengan Laravel

### 1. Set Environment Variable di Laravel

Tambahkan di `.env`:
```
FLASK_SERVER_URL=http://127.0.0.1:5000
```

### 2. Contoh Penggunaan di Blade Template

Lihat file `resources/views/pegawai/presensi.blade.php` untuk contoh lengkap implementasi.

### 3. Alur Presensi

1. **Buka Kamera** - Pegawai membuka kamera di halaman presensi
2. **Deteksi Wajah** - Sistem mendeteksi wajah secara real-time menggunakan `/api/detect-face-frame`
3. **Validasi Lokasi & Waktu** - Sistem mengecek lokasi GPS dan waktu presensi
4. **Capture & Validasi Wajah** - Saat tombol presensi diklik:
   - Foto di-capture dari kamera
   - Sistem mengambil foto referensi pegawai dari database
   - Kedua foto dikirim ke `/api/validate-face` untuk validasi
   - Jika wajah cocok (similarity > threshold), presensi berhasil

## Teknologi

- **Flask** - Web framework
- **OpenCV** - Deteksi wajah cepat untuk preview
- **face_recognition** - Face recognition library berbasis dlib untuk validasi akurat
- **dlib** - Machine learning library
- **NumPy** - Array processing

## Pengaturan Threshold

Threshold untuk validasi wajah saat ini di-set ke `0.5` (50% similarity).

Untuk mengubah threshold, edit di `src/app.py`:
```python
threshold = 0.5  # Ubah nilai ini (0.4-0.6 direkomendasikan)
```

- **Threshold rendah (0.4)**: Lebih permisif, lebih mudah match
- **Threshold tinggi (0.6)**: Lebih ketat, butuh kemiripan tinggi

## Troubleshooting

### Port sudah digunakan
```bash
# Cek proses yang menggunakan port 5000
lsof -i :5000

# Matikan proses atau gunakan port lain
FLASK_SERVER_PORT=5001 python app.py
```

### Error instalasi dlib
Pastikan CMake terinstall. Jika masih error, coba install dlib dari source:
```bash
pip install cmake
pip install dlib --verbose
```

### CORS Error dari Laravel
Pastikan `flask-cors` sudah terinstall dan `CORS(app)` sudah ada di `app.py`.

## Lisensi

Bagian dari sistem presensi online kantor desa.
