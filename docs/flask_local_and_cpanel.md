Panduan: Menjalankan layanan perbandingan wajah lokal & deploy ke cPanel

Ringkasan
- Kode Laravel sekarang menggunakan variabel .env: `FLASK_COMPARE_URL`, `FLASK_TOLERANCE`, `FLASK_NUM_JITTERS`, `FLASK_MODEL`.

A. Setup dan tes lokal (Windows)
1. Pastikan Python 3.8+ terpasang.
2. Buat virtualenv dan aktifkan (PowerShell):

```powershell
python -m venv .venv
.venv\Scripts\Activate.ps1
```

3. Install dependency untuk `flask-compare-service`:

```powershell
pip install -r flask-compare-service/requirements.txt
# jika tidak ada requirements.txt
pip install flask face_recognition numpy Pillow
pip install opencv-python-headless
```

Catatan: `face_recognition` membutuhkan build tools dan `dlib`. Di Windows biasanya perlu Visual Studio Build Tools atau gunakan WSL / Docker jika instalasi sulit.

4. Jalankan service:

```powershell
python flask-compare-service/app.py
```

Service default: `http://0.0.0.0:5000/compare`.

5. Tes dengan `curl` (atau Postman):

```powershell
curl -F "source_image=@C:\path\to\reference.jpg" -F "target_image=@C:\path\to\scan.jpg" -F "tolerance=0.5" http://localhost:5000/compare
```

6. Di Laravel project, set `.env` variabel:

```
FLASK_COMPARE_URL=http://localhost:5000/compare
FLASK_TOLERANCE=0.5
FLASK_NUM_JITTERS=2
FLASK_MODEL=hog
```

7. Jalankan proses presensi dari browser, cek `storage/logs/laravel.log` untuk entri `Presensi: face verification failed` kalau gagal — log sekarang menyertakan `temp` path file upload untuk debugging.

B. Jika `face_recognition` sulit di Windows
- Jalankan service di Linux (WSL2), Docker atau VPS. Alternatif: gunakan Docker image yang sudah berisi `dlib` dan `face_recognition`.

C. Deploy di cPanel (langkah lengkap)
Catatan: banyak shared cPanel hosting tidak mendukung compiling `dlib`/`face_recognition`. Jika hosting Anda tidak support, gunakan VPS/Droplet atau Docker.

1. Pastikan hosting mendukung Python Application (cPanel "Setup Python App") atau memiliki akses SSH.
2. Upload folder `flask-compare-service` via FTP/File Manager ke direktori proyek (misal `~/flask-compare-service`).
3. Jika cPanel "Setup Python App" tersedia:
   - Buka "Setup Python App" → buat aplikasi baru (pilih Python versi yang tersedia).
   - Pilih path virtualenv (cPanel buat otomatis) dan working directory pointing ke folder yang berisi `app.py`.
   - Install dependencies via terminal yang disediakan atau SSH:

```bash
source /home/username/virtualenv/yourapp/3.8/bin/activate
pip install -r /home/username/flask-compare-service/requirements.txt
```

4. Jika `face_recognition` gagal install karena `dlib`:
   - Opsi A: Pindah ke VPS/docker yang bisa compile (lebih direkomendasikan).
   - Opsi B: Gunakan layanan pihak ketiga (paid) untuk face matching.

5. Setup WSGI/Passenger entrypoint (cPanel Passenger) atau Gunicorn + reverse proxy:
   - WSGI: buat file `passenger_wsgi.py` di folder aplikasi yang memuat:

```python
from app import app as application
```

   - Gunakan setting yang disediakan cPanel untuk memulai aplikasi.

6. Buat subdomain (misal `compare.example.com`) di cPanel dan arahkan Document Root ke aplikasi Python yang dibuat.
7. Amankan dengan HTTPS (Let's Encrypt) melalui cPanel "SSL/TLS" atau AutoSSL.
8. Update Laravel `.env` pada hosting:

```
FLASK_COMPARE_URL=https://compare.example.com/compare
FLASK_TOLERANCE=0.5
FLASK_NUM_JITTERS=2
FLASK_MODEL=hog
```

9. Restart aplikasi Python via cPanel dan lakukan tes `curl` dari server (atau browser):

```bash
curl -F "source_image=@/path/ref.jpg" -F "target_image=@/path/scan.jpg" https://compare.example.com/compare
```

D. Alternatif praktis jika cPanel tidak memungkinkan
- Deploy `flask-compare-service` di VPS/Cloud (DigitalOcean, AWS EC2) atau Docker container, kemudian set `FLASK_COMPARE_URL` ke URL VPS.
- Atau gunakan external face matching API.

E. Catatan keamanan & performa
- Batasi max upload size di Flask & Laravel.
- Pertimbangkan menyimpan precomputed encodings (pickle) untuk tiap pegawai agar tidak menghitung encoding sumber pada tiap request.
- Pastikan HTTPS dan rate-limiting.

---
Jika mau, saya bisa:
- Menjalankan tes `curl` lokal dari repo (butuh sample gambar), atau
- Membuat commit perubahan ini (`PresensiController` + dokumen) untuk Anda push.

Langkah selanjutnya: apakah saya harus commit perubahan sekarang (saya buat branch atau langsung commit ke current)?

**Pembaruan penting — Browser-side verification**

- Project ini sekarang mendukung verifikasi wajah di browser menggunakan `face-api.js`. Dengan ini Anda dapat menonaktifkan kebutuhan `dlib`/`face_recognition` pada server.
- Flow singkat:
   1. Saat admin upload foto referensi, browser akan menghitung descriptor (encoding) dan menyimpannya di kolom `foto_wajah_encoding`.
   2. Saat presensi, browser menangkap foto, menghitung descriptor, dan mengirim `photo_descriptor` ke server.
   3. Server membandingkan jarak Euclidean antara descriptor client dan descriptor yang tersimpan, menggunakan `BROWSER_TOLERANCE` dari `.env` (default 0.6).
   4. Jika tidak ada encoding di DB, server masih dapat menggunakan Flask fallback jika `FLASK_COMPARE_URL` diset.

Tambahkan ke `.env` contoh berikut jika ingin men-tune threshold:

```
BROWSER_TOLERANCE=0.6
```

Catatan: Anda harus meletakkan model face-api (`ssd_mobilenetv1`, `face_landmark_68`, `face_recognition`) di `public/models`.

Saya siap untuk membuat branch dan commit sekarang sehingga Anda tinggal `git push` dan `git pull` di server. Jika setuju, saya akan membuat branch `feat/browser-face` dan commit semua perubahan.
