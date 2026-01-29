# Tanggal: 19 Januari 2026
# Opsi Deployment Alternatif (Tanpa Docker Rumit)

Jika **Hugging Face Spaces** (Docker) terlalu rumit dan **PythonAnywhere** terlalu lemah, berikut adalah opsi terbaik:

## 1. Render.com (Paling Mudah)
Render terkenal dengan kemudahan "Auto Deploy" dari GitHub. Mereka punya opsi environment yang cukup fleksibel.

**Langkah-langkah:**
1. Push folder `face-detection-app` ke **GitHub** (bisa repo terpisah atau monorepo).
2. Daftar di [Render.com](https://render.com).
3. Klik **New** -> **Web Service**.
4. Hubungkan repo GitHub Anda.
5. Isi konfigurasi:
   - **Runtime**: Python 3
   - **Build Command**: `pip install cmake && pip install -r requirements.txt` (Render support binary wheels, jadi kompilasi lebih lancar).
   - **Start Command**: `gunicorn app:app` (pastikan struktur folder Anda `src/app.py` atau sesuaikan `cd src && gunicorn app:app`).
6. Pilih **Free Tier**.

*Kekurangan*: Free tier akan "tidur" jika tidak diakses dalam 15 menit (cold start lambat).

## 2. Railway.app (Developer Experience Terbaik)
Railway sangat pintar mendeteksi `requirements.txt` dan mengurus instalasi sistem otomatis (termasuk library C++ untuk dlib).

**Langkah-langkah:**
1. Daftar di [Railway.app](https://railway.app).
2. Klik **New Project** -> **Deploy from GitHub repo**.
3. Pilih repo Anda.
4. Railway akan otomatis mendeteksi Python.
5. Masuk ke **Settings** -> **Variables**, tambahkan environment variable jika perlu.
6. Masuk ke **Settings** -> **Build Command**, pastikan: `pip install cmake && pip install -r requirements.txt`.
7. Railway memberikan trial $5 gratis (tanpa kartu kredit). Sangat cukup untuk deployment awal.

*Kelebihan*: Tidak perlu Dockerfile, dia build otomatis menggunakan Nixpacks (sangat canggih).

## 3. Google Colab (Untuk Demo / Sementara)
Jika hanya butuh API nyala sebentar (misal untuk sidang skripsi/presentasi) dan GRATIS total dengan GPU kencang.

1. Buka [Google Colab](https://colab.research.google.com).
2. Upload file `app.py`.
3. Tulis script ini di cell pertama:
   ```python
   !pip install flask flask-cors pyngrok face-recognition
   
   # Jalankan app di background
   get_ipython().system_raw('python app.py &')

   # Expose ke internet pakai Ngrok
   from pyngrok import ngrok
   public_url = ngrok.connect(5000).public_url
   print("API URL Anda:", public_url)
   ```
4. Copy URL ngrok tersebut ke `.env` Laravel Anda.

*Kekurangan*: URL berubah setiap kali restart, tab Colab harus tetap terbuka di laptop.

---

## Rekomendasi Saya: Railway.app
Jika Anda ingin yang "pasti jalan" tanpa pusing konfigurasi sistem/docker:
1. Gunakan **Railway.app**.
2. Upload hanya folder `face-detection-app` ke repo GitHub baru (agar bersih).
3. Deploy di Railway. Railway menggunakan **Nixpacks** yang otomatis bisa menginstall `dlib` biner tanpa error compile yang aneh-aneh.

**Tips untuk Railway / Render:**
Gunakan `requirements.txt` yang versi ringan (tanpa kunci versi ketat) agar server bisa mencari binary yang cocok:

```txt
Flask
flask-cors
opencv-python-headless
numpy
Pillow
face-recognition
dlib
gunicorn
cmake
```
