# Aplikasi Deteksi Wajah Sederhana dengan Flask

Aplikasi web sederhana untuk mendeteksi wajah dalam gambar menggunakan Flask dan OpenCV.

## 🎯 Fitur

- ✅ Upload gambar (PNG, JPG, JPEG, GIF, BMP)
- ✅ Deteksi wajah otomatis menggunakan Haar Cascade
- ✅ Tampilkan kotak di sekitar wajah yang terdeteksi
- ✅ Informasi koordinat wajah (posisi X, Y, lebar, tinggi)
- ✅ Interface yang user-friendly dengan drag & drop
- ✅ Responsive design untuk desktop dan mobile

## 📁 Struktur Proyek

```
face-detection-app/
├── src/
│   ├── app.py                 # Main Flask application
│   ├── models/
│   │   └── detector.py        # Face detection logic
│   └── utils/
│       └── helpers.py         # Helper functions
├── templates/
│   ├── index.html             # Upload form page
│   └── results.html           # Results display page
├── static/
│   └── style.css              # Styling
├── uploads/                   # Folder untuk file upload
├── requirements.txt           # Python dependencies
└── README.md                  # Documentation
```

## 🚀 Instalasi

### 1. Clone Repository
```bash
cd face-detection-app
```

### 2. Buat Virtual Environment (Opsional tapi Recommended)
```bash
# Windows
python -m venv venv
venv\Scripts\activate

# Linux/Mac
python3 -m venv venv
source venv/bin/activate
```

### 3. Install Dependencies
```bash
pip install -r requirements.txt
```

## 💻 Cara Menggunakan

### 1. Jalankan Aplikasi
```bash
python src/app.py
```

Aplikasi akan berjalan di `http://localhost:5000`

### 2. Buka di Browser
- Buka browser dan kunjungi `http://localhost:5000`
- Atau buka `http://127.0.0.1:5000`

### 3. Upload Gambar
- Klik area upload atau drag & drop gambar
- Klik tombol "Deteksi Wajah"
- Tunggu hasil deteksi

### 4. Lihat Hasil
- Gambar dengan kotak di sekitar wajah akan ditampilkan
- Informasi koordinat setiap wajah terdeteksi
- Klik "Coba Gambar Lain" untuk upload gambar baru

## 📦 Dependencies

- **Flask**: Web framework untuk Python
- **OpenCV (cv2)**: Computer vision library untuk deteksi wajah
- **NumPy**: Numerical computing library
- **Pillow**: Image processing library
- **Werkzeug**: WSGI utility library

Lihat `requirements.txt` untuk versi spesifik.

## 🔧 Konfigurasi

Beberapa parameter yang dapat dikustomisasi di `src/app.py`:

```python
# Ukuran maksimal file upload (default: 16MB)
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024

# Parameter deteksi wajah
scaleFactor=1.3         # Seberapa banyak ukuran gambar dikecilkan
minNeighbors=4          # Berapa tetangga yang diperlukan untuk deteksi
minSize=(30, 30)        # Ukuran minimum wajah yang terdeteksi
```

## 🎨 Customization

### Mengubah Warna Deteksi
Edit di `src/app.py` line 85:
```python
cv2.rectangle(image_with_faces, (x, y), (x+w, y+h), (0, 255, 0), 2)
# (0, 255, 0) = Hijau dalam format BGR
# Ubah untuk warna lain, misal: (0, 0, 255) = Merah
```

### Mengubah Styling
Edit file `static/style.css` untuk mengubah tampilan interface.

## 🐛 Troubleshooting

### Port 5000 sudah digunakan
```bash
# Ubah port di src/app.py line 115
app.run(debug=True, host='0.0.0.0', port=8000)  # Ganti 5000 dengan port lain
```

### OpenCV tidak terinstall
```bash
pip install opencv-python
```

### ModuleNotFoundError
Pastikan virtual environment sudah aktif:
```bash
# Windows
venv\Scripts\activate

# Linux/Mac
source venv/bin/activate
```

## 📝 Notes

- Deteksi menggunakan Haar Cascade Classifier (pre-trained model dari OpenCV)
- Semakin bagus kualitas gambar, semakin akurat hasilnya
- Gambar dengan pencahayaan baik akan memberikan hasil lebih baik
- Wajah harus cukup jelas dan proporsional untuk terdeteksi

## 👨‍💻 Author

Dibuat dengan Flask, OpenCV, dan Python 🐍

## 📄 License

Bebas digunakan untuk keperluan pembelajaran dan pengembangan.

- Flask
- OpenCV (for face detection)
- NumPy (for image processing)

## License

This project is licensed under the MIT License.