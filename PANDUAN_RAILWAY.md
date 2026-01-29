# Panduan Deployment ke Railway.app (Metode Repo Terpisah)

Memisahkan aplikasi Python ke repository GitHub tersendiri adalah cara **paling bersih dan mudah**. Railway akan lebih mudah mendeteksi bahasa pemrograman tanpa konfigurasi manual.

## Langkah 1: Persiapan Repository Baru
Kita akan memisahkan folder `face-detection-app` menjadi repository git yang benar-benar baru.

1.  **Buat Repository Kosong** di GitHub Anda, beri nama misalnya: `presensiku-face-api`.
    *   Jangan centang "Add README", biarkan benar-benar kosong.

2.  **Jalankan perintah ini di Terminal (Local Computer)**:
    Kita akan menyalin folder tersebut ke luar, lalu upload.

    ```bash
    # 1. Kembali ke folder utama (jika belum)
    cd ~/code/website/presensiku

    # 2. Copy folder aplikasi wajah ke lokasi baru (misal ke folder code)
    cp -r face-detection-app ../presensiku-face-api

    # 3. Masuk ke folder baru tersebut
    cd ../presensiku-face-api

    # 4. Inisialisasi Git baru
    git init
    git branch -M main
    git add .
    git commit -m "Initial commit for Face API"

    # 5. Hubungkan ke GitHub baru Anda (Ganti USERNAME dengan username GitHub Anda)
    git remote add origin https://github.com/USERNAME/presensiku-face-api.git
    
    # 6. Push
    git push -u origin main
    ```

## Langkah 2: Deploy di Railway
1. Buka [Railway.app](https://railway.app).
2. Klik **+ New Project** -> **Deploy from GitHub repo**.
3. Pilih repository baru Anda: `presensiku-face-api`.
4. Klik **Deploy Now**.
   *Railway akan otomatis mendeteksi ini sebagai Python Project dan menggunakan Nixpacks untuk menginstall semuanya (termasuk dlib).*
   *Anda TIDAK PERLU lagi mengatur "Root Directory" di settings.*

## Langkah 3: Domain & Environment
1. Tunggu build selesai (Status: Active).
2. Buka tab **Settings** -> **Networking** -> **Generate Domain**.
3. Copy URL yang muncul.

## Langkah 4: Sambungkan ke Laravel
1. Kembali ke cPanel hosting Laravel Anda.
2. Edit `.env`.
3. Update `FLASK_SERVER_URL` dengan domain dari Railway tadi.

Selesai! Cara ini jauh lebih rapi karena kode Python dan PHP tidak tercampur.
