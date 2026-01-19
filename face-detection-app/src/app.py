from flask import Flask, request, render_template, jsonify
from werkzeug.utils import secure_filename
import os
import cv2
import numpy as np
from PIL import Image
import io
import base64
from flask_cors import CORS

app = Flask(__name__, template_folder='../templates', static_folder='../static')
CORS(app)  # Enable CORS for Laravel integration
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024  # 16MB max file size
app.config['UPLOAD_FOLDER'] = os.path.join(os.path.dirname(__file__), '..', 'uploads')

# Buat folder uploads jika belum ada
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

# Load pre-trained face cascade classifier
face_cascade = cv2.CascadeClassifier(
    cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
)

ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp'}

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def detect_faces_in_image(image_cv2):
    """Deteksi wajah dalam gambar menggunakan OpenCV"""
    gray = cv2.cvtColor(image_cv2, cv2.COLOR_BGR2GRAY)
    faces = face_cascade.detectMultiScale(
        gray,
        scaleFactor=1.3,
        minNeighbors=4,
        minSize=(30, 30)
    )
    return faces

@app.route('/')
def index():
    return render_template('index.html')

# API Endpoint untuk deteksi wajah dari gambar
@app.route('/api/detect-face', methods=['POST'])
def api_detect_face():
    """
    API endpoint untuk deteksi wajah
    Menerima gambar sebagai base64 atau file upload
    Returns: JSON dengan hasil deteksi
    """
    try:
        # Cek apakah image dikirim sebagai file atau base64
        image_cv2 = None
        
        if 'file' in request.files:
            file = request.files['file']
            if file.filename == '':
                return jsonify({'success': False, 'error': 'File tidak dipilih'}), 400
            
            if not allowed_file(file.filename):
                return jsonify({'success': False, 'error': 'Tipe file tidak didukung'}), 400
            
            file_bytes = file.read()
            nparr = np.frombuffer(file_bytes, np.uint8)
            image_cv2 = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        elif 'image' in request.json:
            # Menerima base64 image
            image_data = request.json['image']
            if image_data.startswith('data:image'):
                image_data = image_data.split(',')[1]
            
            image_bytes = base64.b64decode(image_data)
            nparr = np.frombuffer(image_bytes, np.uint8)
            image_cv2 = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        else:
            return jsonify({'success': False, 'error': 'Tidak ada image yang dikirim'}), 400
        
        if image_cv2 is None:
            return jsonify({'success': False, 'error': 'Gagal membaca gambar'}), 400
        
        # Deteksi wajah
        faces = detect_faces_in_image(image_cv2)
        
        if len(faces) == 0:
            return jsonify({
                'success': True,
                'face_count': 0,
                'faces': [],
                'message': 'Tidak ada wajah terdeteksi'
            })
        
        # Format hasil deteksi
        faces_list = []
        for i, (x, y, w, h) in enumerate(faces):
            faces_list.append({
                'id': i + 1,
                'x': int(x),
                'y': int(y),
                'width': int(w),
                'height': int(h),
                'confidence': 0.95  # Placeholder confidence
            })
        
        return jsonify({
            'success': True,
            'face_count': len(faces),
            'faces': faces_list,
            'message': f'{len(faces)} wajah terdeteksi'
        })
    
    except Exception as e:
        return jsonify({'success': False, 'error': f'Error: {str(e)}'}), 500

# API Endpoint untuk deteksi wajah dari video stream (base64)
@app.route('/api/detect-face-frame', methods=['POST'])
def api_detect_face_frame():
    """
    API endpoint untuk deteksi wajah dari frame video
    Menerima frame video sebagai base64
    Returns: JSON dengan hasil deteksi (face_count only untuk performance)
    """
    try:
        data = request.json
        if not data or 'frame' not in data:
            return jsonify({'success': False, 'error': 'Tidak ada frame'}), 400
        
        frame_data = data['frame']
        if frame_data.startswith('data:image'):
            frame_data = frame_data.split(',')[1]
        
        frame_bytes = base64.b64decode(frame_data)
        nparr = np.frombuffer(frame_bytes, np.uint8)
        image_cv2 = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        if image_cv2 is None:
            return jsonify({'success': False, 'face_count': 0})
        
        # Deteksi wajah
        faces = detect_faces_in_image(image_cv2)
        
        return jsonify({
            'success': True,
            'face_detected': len(faces) > 0,
            'face_count': len(faces),
            'message': f'{len(faces)} wajah terdeteksi' if len(faces) > 0 else 'Tidak ada wajah'
        })
    
    except Exception as e:
        return jsonify({'success': False, 'face_count': 0, 'error': str(e)})

@app.route('/detect', methods=['POST'])
def detect():
    """Original endpoint untuk testing dengan browser"""
    try:
        if 'file' not in request.files:
            return jsonify({'error': 'Tidak ada file yang diunggah'}), 400
        
        file = request.files['file']
        
        if file.filename == '':
            return jsonify({'error': 'File tidak dipilih'}), 400
        
        if not allowed_file(file.filename):
            return jsonify({'error': 'Tipe file tidak didukung. Gunakan PNG, JPG, JPEG, GIF, atau BMP'}), 400
        
        # Baca file gambar
        file_bytes = file.read()
        nparr = np.frombuffer(file_bytes, np.uint8)
        image_cv2 = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        if image_cv2 is None:
            return jsonify({'error': 'Gagal membaca gambar'}), 400
        
        # Deteksi wajah
        faces = detect_faces_in_image(image_cv2)
        
        # Gambar kotak di sekitar wajah yang terdeteksi
        image_with_faces = image_cv2.copy()
        face_count = 0
        for (x, y, w, h) in faces:
            cv2.rectangle(image_with_faces, (x, y), (x+w, y+h), (0, 255, 0), 2)
            face_count += 1
        
        # Simpan gambar hasil
        _, buffer = cv2.imencode('.jpg', image_with_faces)
        img_base64_str = base64.b64encode(buffer).decode()
        
        return render_template('results.html', 
                             image=img_base64_str,
                             face_count=face_count,
                             faces=faces.tolist() if len(faces) > 0 else [])
    
    except Exception as e:
        return jsonify({'error': f'Terjadi kesalahan: {str(e)}'}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)