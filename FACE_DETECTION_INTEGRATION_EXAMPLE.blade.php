{{-- 
    CONTOH INTEGRASI FLASK FACE DETECTION KE PRESENSI.BLADE.PHP
    
    Copy-paste kode ini ke file presensi.blade.php sesuai bagian yang ditunjukkan
--}}

{{-- 1. TAMBAHKAN INI DI DALAM <script> TAG YANG SUDAH ADA (setelah axios) --}}

<script src="{{ asset('js/face-detection-integration.js') }}"></script>

<script>
    // ===== INISIALISASI FACE DETECTION API =====
    // Pastikan Flask server berjalan di http://localhost:5000
    // Jika Flask di server lain, ubah URL di bawah
    const FLASK_URL = '{{ env("FLASK_SERVER_URL", "http://localhost:5000") }}';
    
    // Inisialisasi API
    let faceDetectionAPI = null;
    let faceDetectionActive = false;
    let lastDetectionResult = { face_count: 0, face_detected: false };
    
    window.addEventListener('DOMContentLoaded', async () => {
        faceDetectionAPI = new (window.FaceDetectionAPI || class {})();
        faceDetectionAPI = new FaceDetectionAPI(FLASK_URL);
        
        // Check availability
        setTimeout(() => {
            if (faceDetectionAPI.isAvailable) {
                console.log('✓ Flask Face Detection Server AKTIF');
            } else {
                console.warn('⚠ Flask Face Detection Server tidak tersedia');
            }
        }, 1000);
    });

    // ===== FUNGSI UNTUK DETEKSI WAJAH DARI VIDEO FRAME =====
    async function detectFaceFromCamera(videoElement) {
        if (!videoElement || videoElement.readyState !== videoElement.HAVE_ENOUGH_DATA) {
            return { face_count: 0, face_detected: false };
        }

        try {
            // Create canvas dari video element
            const canvas = document.createElement('canvas');
            canvas.width = videoElement.videoWidth;
            canvas.height = videoElement.videoHeight;

            const ctx = canvas.getContext('2d');
            // Mirror untuk front camera
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(videoElement, 0, 0);
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            // Convert ke base64 dengan compression untuk performa
            const frameBase64 = canvas.toDataURL('image/jpeg', 0.6);
            
            // Send ke Flask API
            if (faceDetectionAPI && faceDetectionAPI.isAvailable) {
                const result = await faceDetectionAPI.detectFaceFromFrame(frameBase64);
                lastDetectionResult = result;
                return result;
            }
        } catch (error) {
            console.error('Error dalam deteksi wajah:', error);
        }
        
        return { face_count: 0, face_detected: false };
    }

    // ===== FUNGSI UPDATE STATUS DETEKSI WAJAH =====
    async function updateFaceDetectionStatus(videoElement) {
        if (!faceDetectionActive || !videoElement) return;

        const result = await detectFaceFromCamera(videoElement);
        const statusEl = document.getElementById('face-status');
        
        if (!statusEl) return;

        if (result.face_detected && result.face_count > 0) {
            statusEl.innerHTML = `
                <span class="text-green-600 font-bold">✓ Status: ${result.face_count} wajah terdeteksi</span>
            `;
            statusEl.classList.remove('text-red-600', 'text-gray-600');
            statusEl.classList.add('text-green-600');
            
            // Enable presensi buttons jika wajah terdeteksi
            enablePresenceButtons(true);
        } else {
            statusEl.innerHTML = `
                <span class="text-red-600 font-bold">✗ Status: Tidak ada wajah</span>
            `;
            statusEl.classList.remove('text-green-600', 'text-gray-600');
            statusEl.classList.add('text-red-600');
            
            // Disable presensi buttons jika tidak ada wajah
            enablePresenceButtons(false);
        }
    }

    // ===== FUNGSI HELPER UNTUK ENABLE/DISABLE TOMBOL =====
    function enablePresenceButtons(enable) {
        const masukBtn = document.getElementById('presensi-masuk');
        const pulangBtn = document.getElementById('presensi-pulang');
        
        if (masukBtn) masukBtn.disabled = !enable;
        if (pulangBtn) pulangBtn.disabled = !enable;
    }

    // ===== MODIFIKASI START CAMERA UNTUK INTEGRASI =====
    // Tambahkan ini ke dalam event listener untuk start-camera button:
    
    const originalStartCamera = async function() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user' }
            });
            
            const video = document.getElementById('camera');
            video.srcObject = stream;
            
            // MULAI FACE DETECTION
            faceDetectionActive = true;
            
            // Loop deteksi setiap 500ms
            const detectionInterval = setInterval(async () => {
                if (!faceDetectionActive) {
                    clearInterval(detectionInterval);
                    return;
                }
                await updateFaceDetectionStatus(video);
            }, 500);
            
            // Update UI
            document.getElementById('start-camera').classList.add('hidden');
            document.getElementById('stop-camera').classList.remove('hidden');
            
        } catch (error) {
            console.error('Error akses kamera:', error);
            const statusEl = document.getElementById('face-status');
            if (statusEl) {
                statusEl.innerHTML = '<span class="text-red-600">✗ Error: Tidak bisa akses kamera</span>';
            }
        }
    };

    // ===== MODIFIKASI STOP CAMERA =====
    const originalStopCamera = function() {
        faceDetectionActive = false;
        
        const video = document.getElementById('camera');
        if (video && video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
        }
        
        document.getElementById('start-camera').classList.remove('hidden');
        document.getElementById('stop-camera').classList.add('hidden');
        
        const statusEl = document.getElementById('face-status');
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-gray-600">Status: Kamera dimatikan</span>';
        }
    };

    // ===== MODIFIKASI HANDLER TOMBOL PRESENSI =====
    async function handlePresenceSubmit(type) {
        // type = 'masuk' atau 'pulang'
        
        // Validasi wajah terdeteksi
        if (!lastDetectionResult.face_detected || lastDetectionResult.face_count === 0) {
            showAlert('Wajah harus terdeteksi untuk melakukan presensi!', 'error');
            return;
        }

        // Get video frame
        const video = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        
        if (!video || video.readyState !== video.HAVE_ENOUGH_DATA) {
            showAlert('Kamera belum siap, coba lagi!', 'error');
            return;
        }

        // Capture frame
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0);
        ctx.setTransform(1, 0, 0, 1, 0, 0);

        const photoBase64 = canvas.toDataURL('image/jpeg');
        
        // Lanjutkan dengan proses presensi normal (submit ke server Laravel)
        submitPresence(type, photoBase64);
    }

    // ===== HELPER FUNCTION UNTUK SUBMIT PRESENSI =====
    async function submitPresence(type, photoBase64) {
        const statusMsg = document.getElementById('status-message');
        const statusText = document.getElementById('status-text');

        try {
            showStatus('Memproses presensi...', 'processing');

            const response = await axios.post(`/pegawai/presensi/${type}`, {
                foto: photoBase64,
                // tambahkan data lain yang diperlukan
            });

            if (response.data.success) {
                showStatus(`✓ Presensi ${type} berhasil!`, 'success');
                
                // Reload atau update UI setelah berhasil
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showStatus(response.data.message || 'Gagal melakukan presensi', 'error');
            }
        } catch (error) {
            const message = error.response?.data?.message || error.message;
            showStatus(`✗ Error: ${message}`, 'error');
        }
    }

    // ===== HELPER FUNCTION UNTUK TAMPIL STATUS =====
    function showStatus(message, type = 'info') {
        const statusMsg = document.getElementById('status-message');
        const statusText = document.getElementById('status-text');

        if (!statusMsg || !statusText) return;

        statusText.textContent = message;
        statusMsg.classList.remove('hidden');
        
        // Styling berdasarkan type
        statusMsg.className = 'mt-4 text-center';
        if (type === 'success') {
            statusMsg.className += ' text-green-600 bg-green-50 border border-green-200 rounded p-4';
        } else if (type === 'error') {
            statusMsg.className += ' text-red-600 bg-red-50 border border-red-200 rounded p-4';
        } else if (type === 'processing') {
            statusMsg.className += ' text-blue-600 bg-blue-50 border border-blue-200 rounded p-4';
        }
    }

    // ===== UPDATE EVENT LISTENERS PADA TOMBOL =====
    // Ganti onclick handler untuk tombol presensi:
    
    // Untuk tombol presensi masuk:
    document.getElementById('presensi-masuk')?.addEventListener('click', () => {
        handlePresenceSubmit('masuk');
    });

    // Untuk tombol presensi pulang:
    document.getElementById('presensi-pulang')?.addEventListener('click', () => {
        handlePresenceSubmit('pulang');
    });

    // Untuk tombol start camera (jika ada):
    document.getElementById('start-camera')?.addEventListener('click', originalStartCamera);
    
    // Untuk tombol stop camera (jika ada):
    document.getElementById('stop-camera')?.addEventListener('click', originalStopCamera);

</script>

{{-- 
    INSTRUKSI INTEGRASI:
    
    1. Copy script di atas (bagian <script>) ke file presensi.blade.php
       Letakkan setelah script yang sudah ada (axios, dll)
    
    2. Pastikan Flask server berjalan:
       cd face-detection-app/src
       python app.py
    
    3. Update element IDs sesuai dengan presensi.blade.php Anda:
       - #camera (video element)
       - #canvas (hidden canvas untuk capture)
       - #face-status (element untuk tampil status)
       - #presensi-masuk (button presensi masuk)
       - #presensi-pulang (button presensi pulang)
       - #status-message (element untuk tampil pesan)
       - #status-text (element untuk isi pesan)
       - #start-camera (button start kamera)
       - #stop-camera (button stop kamera)
    
    4. Update endpoint submit presensi di fungsi submitPresence()
       Sesuaikan dengan route di Laravel Anda
    
    5. Test dengan upload gambar atau live video
       
    TROUBLESHOOTING:
    - Buka browser console (F12) untuk melihat error
    - Pastikan kamera dan microphone sudah di-allow
    - Pastikan HTTPS atau localhost untuk akses kamera
--}}
