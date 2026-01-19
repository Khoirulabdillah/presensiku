/**
 * Face Detection Integration Script
 * Integrasi Flask Face Detection dengan Halaman Presensi Laravel
 */

class FaceDetectionAPI {
    constructor(flaskUrl = 'http://localhost:5000') {
        this.flaskUrl = flaskUrl;
        this.isAvailable = false;
        this.checkAvailability();
    }

    /**
     * Check if Flask server is available
     */
    async checkAvailability() {
        try {
            const response = await fetch(this.flaskUrl + '/api/detect-face-frame', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ frame: 'data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==' })
            });
            this.isAvailable = response.ok;
        } catch (error) {
            console.warn('Flask server tidak tersedia:', error);
            this.isAvailable = false;
        }
    }

    /**
     * Deteksi wajah dari base64 frame
     * @param {string} frameBase64 - Canvas frame sebagai base64
     * @returns {Promise} - Promise dengan hasil deteksi
     */
    async detectFaceFromFrame(frameBase64) {
        if (!this.isAvailable) {
            return { success: false, face_count: 0, error: 'Flask server tidak tersedia' };
        }

        try {
            const response = await fetch(this.flaskUrl + '/api/detect-face-frame', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ frame: frameBase64 })
            });

            if (!response.ok) {
                throw new Error('Response tidak ok: ' + response.status);
            }

            return await response.json();
        } catch (error) {
            console.error('Error deteksi wajah:', error);
            return { success: false, face_count: 0, error: error.message };
        }
    }

    /**
     * Deteksi wajah dari file
     * @param {File} file - File gambar
     * @returns {Promise} - Promise dengan hasil deteksi
     */
    async detectFaceFromFile(file) {
        if (!this.isAvailable) {
            return { success: false, face_count: 0, error: 'Flask server tidak tersedia' };
        }

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch(this.flaskUrl + '/api/detect-face', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Response tidak ok: ' + response.status);
            }

            return await response.json();
        } catch (error) {
            console.error('Error deteksi wajah:', error);
            return { success: false, face_count: 0, error: error.message };
        }
    }

    /**
     * Deteksi wajah dari base64 image
     * @param {string} imageBase64 - Image sebagai base64 (dengan atau tanpa prefix)
     * @returns {Promise} - Promise dengan hasil deteksi
     */
    async detectFaceFromBase64(imageBase64) {
        if (!this.isAvailable) {
            return { success: false, face_count: 0, error: 'Flask server tidak tersedia' };
        }

        try {
            const response = await fetch(this.flaskUrl + '/api/detect-face', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ image: imageBase64 })
            });

            if (!response.ok) {
                throw new Error('Response tidak ok: ' + response.status);
            }

            return await response.json();
        } catch (error) {
            console.error('Error deteksi wajah:', error);
            return { success: false, face_count: 0, error: error.message };
        }
    }
}

// Global instance
window.faceDetectionAPI = null;

/**
 * Initialize Face Detection API
 * @param {string} flaskUrl - URL Flask server
 */
function initFaceDetectionAPI(flaskUrl = 'http://localhost:5000') {
    window.faceDetectionAPI = new FaceDetectionAPI(flaskUrl);
    console.log('Face Detection API initialized');
    return window.faceDetectionAPI;
}

/**
 * Helper function untuk meningkatkan UI dengan informasi deteksi wajah
 * Dapat diintegrasikan dengan kode existing di presensi.blade.php
 */
async function updateFaceDetectionStatus(videoElement) {
    if (!window.faceDetectionAPI) {
        console.warn('Face Detection API belum diinisialisasi');
        return;
    }

    const canvas = document.createElement('canvas');
    canvas.width = videoElement.videoWidth;
    canvas.height = videoElement.videoHeight;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(videoElement, 0, 0);

    const frameBase64 = canvas.toDataURL('image/jpeg');
    const result = await window.faceDetectionAPI.detectFaceFromFrame(frameBase64);

    return result;
}

// Export untuk use di module environment
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { FaceDetectionAPI, initFaceDetectionAPI, updateFaceDetectionStatus };
}
