# Tanggal: 19 Januari 2026
# Panduan Deployment

Jika **PythonAnywhere** terasa berat atau nge-lag (karena compiling dlib memakan CPU kuota), solusi terbaik dan GRATIS untuk aplikasi Machine Learning adalah **Hugging Face Spaces**.

Mereka menyediakan Cloud Docker Container dengan spesifikasi: **2 vCPU dan 16GB RAM** (Gratis), jauh lebih kuat daripada PythonAnywhere.

---

## OPSI ALTERNATIF: DEPLOY FLASK DI HUGGING FACE SPACES (Rekomendasi)

### 1. Buat Space Baru
1. Daftar di [Hugging Face](https://huggingface.co/join).
2. Klik Profile -> **New Space**.
3. Isi detail:
   - **Space Name**: `presensiku-api` (bebas).
   - **License**: MIT (atau kosong).
   - **SDK**: Pilih **Docker** (Penting! Jangan pilih Streamlit/Gradio).
   - **Visibility**: Public.
4. Klik **Create Space**.

### 2. Upload File (Via Browser)
Anda tidak perlu install git, cukup upload lewat browser di halaman "Files" space tersebut.

1. Buka tab **Files** di Space yang baru dibuat.
2. Klik **Add file** -> **Upload files**.
3. Upload 3 file dari folder `presensiku/face-detection-app` di komputer Anda:
   - `Dockerfile` (yang baru dibuatkan)
   - `requirements.txt`
   - `src/app.py` -> **PENTING**: Saat upload `app.py`, karena struktur folder di Dockerfile mengharapkan file ini ada di root `/code` (hasil copy dari src), struktur di Hugging Face sebaiknya Flat (sejajar) atau sesuaikan `COPY`.
   
   **Cara Paling Mudah (Flat Structure):**
   Agar sesuai dengan Dockerfile yang saya buatkan, upload file dengan struktur folder seperti ini di Hugging Face:
   
   ```
   Dockerfile
   requirements.txt
   src/
      app.py
   ```
   *(Tips: Di menu upload Hugging Face, Anda bisa drag & drop satu folder `src` dan file lainnya sekaligus).*

4. Setelah upload, klik **Commit changes**.
5. Space akan mulai **Building**. Proses ini akan memakan waktu 5-10 menit untuk compile `dlib` pertama kali. Pantau tab **Logs**.

### 3. Dapatkan URL
1. Jika statusnya sudah **Running**, lihat toolbar di atas preview app.
2. Klik tombol menu (titik tiga) -> **Embed this Space** -> copy **Direct URL**.
3. URL biasanya formatnya: `https://username-projectname.hf.space`

### 4. Update .env Laravel di cPanel
Ganti URL flask server dengan URL dari Hugging Face:
```env
FLASK_SERVER_URL=https://username-projectname.hf.space
```

---

## BAGIAN 2: DEPLOY LARAVEL (CPANEL)
*(Lanjut ikuti langkah cPanel seperti sebelumnya...)*

---

## BAGIAN 2: DEPLOY LARAVEL (CPANEL)

### 1. Persiapan File
1. Di komputer lokal, zip seluruh folder project `presensiku` (kecuali `node_modules` dan `face-detection-app`).
2. Login ke cPanel -> **File Manager**.
3. Upload zip ke folder root (sejajar dengan public_html, BUKAN di dalamnya).
4. Extract zip tersebut. Rename folder hasil extract menjadi `presensiku-core`.

### 2. Mengatur Folder Public
1. Masuk ke `presensiku-core/public`.
2. Pilih semua file (Select All) -> Move.
3. Pindahkan ke `/public_html` (atau folder subdomain Anda).
4. Edit file `/public_html/index.php`:
   
   Cari baris ini:
   ```php
   if (file_exists(__DIR__.'/../storage/framework/maintenance.php')) {
       require __DIR__.'/../storage/framework/maintenance.php';
   }
   require __DIR__.'/../vendor/autoload.php';
   $app = require_once __DIR__.'/../bootstrap/app.php';
   ```

   Ubah menjadi (sesuaikan path ke folder core):
   ```php
   if (file_exists(__DIR__.'/../presensiku-core/storage/framework/maintenance.php')) {
       require __DIR__.'/../presensiku-core/storage/framework/maintenance.php';
   }
   require __DIR__.'/../presensiku-core/vendor/autoload.php';
   $app = require_once __DIR__.'/../presensiku-core/bootstrap/app.php';
   ```

### 3. Konfigurasi .env
1. Di folder `presensiku-core`, rename `.env.example` (atau copy .env lokal) menjadi `.env`.
2. Edit `.env` dan sesuaikan:
   ```
   APP_NAME="Presensiku"
   APP_URL=https://domain-anda.com
   APP_ENV=production
   APP_DEBUG=false

   # Database cPanel
   DB_DATABASE=usernamecpanel_presensiku
   DB_USERNAME=usernamecpanel_user
   DB_PASSWORD=password_db_anda

   # Tautkan ke PythonAnywhere
   FLASK_SERVER_URL=https://username.pythonanywhere.com
   FACE_DISTANCE_THRESHOLD=0.55
   FACE_MIN_SIMILARITY=40
   ```

### 4. Database & Storage
1. Di cPanel -> **MySQL Databases**, buat database dan user baru. Import file SQL (jika sudah ada data) atau biarkan kosong untuk migrasi.
2. Jika Anda punya akses terminal di cPanel:
   ```bash
   cd presensiku-core
   php artisan migrate --seed
   php artisan storage:link
   ```
3. **Jika TIDAK ada akses terminal**:
   - Gunakan fitur PHPMyAdmin untuk import database lokal.
   - Untuk storage link, buat file `link.php` di `public_html`:
     ```php
     <?php
     $target = '/home/username/presensiku-core/storage/app/public';
     $shortcut = '/home/username/public_html/storage';
     symlink($target, $shortcut);
     echo "Symlink created";
     ?>
     ```
   - Buka `domain-anda.com/link.php` sekali, lalu hapus filenya.

### 5. Permissions
Pastikan folder `presensiku-core/storage` dan `presensiku-core/bootstrap/cache` memiliki permission `775`.

---

## Troubleshooting Umum

1. **Gambar tidak muncul?**
   Cek symlink storage. Pastikan di database, path gambar tersimpan relatif (contoh: `photos/abc.jpg`), bukan path absolut komputer lokal.

2. **Error 500 di Laravel?**
   Cek `presensiku-core/storage/logs/laravel.log`.

3. **Face API Error?**
   Pastikan di PythonAnywhere, file XML Haar Cascade ada di folder yang sama dengan `app.py`.
