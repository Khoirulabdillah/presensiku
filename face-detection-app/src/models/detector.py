import cv2
import numpy as np

class FaceDetector:
    def __init__(self):
        """Inisialisasi Face Detector dengan Haar Cascade"""
        self.face_cascade = cv2.CascadeClassifier(
            cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
        )
        self.eye_cascade = cv2.CascadeClassifier(
            cv2.data.haarcascades + 'haarcascade_eye.xml'
        )

    def detect_faces(self, image):
        """
        Deteksi wajah dalam gambar
        
        Args:
            image: Gambar dalam format OpenCV (BGR)
            
        Returns:
            List dari tuple (x, y, width, height) untuk setiap wajah yang terdeteksi
        """
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        faces = self.face_cascade.detectMultiScale(
            gray,
            scaleFactor=1.3,
            minNeighbors=4,
            minSize=(30, 30)
        )
        return faces
    
    def detect_faces_and_eyes(self, image):
        """
        Deteksi wajah dan mata dalam gambar
        
        Args:
            image: Gambar dalam format OpenCV (BGR)
            
        Returns:
            Dictionary dengan 'faces' dan 'eyes'
        """
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        faces = self.face_cascade.detectMultiScale(
            gray,
            scaleFactor=1.3,
            minNeighbors=4,
            minSize=(30, 30)
        )
        
        eyes_list = []
        for (x, y, w, h) in faces:
            roi_gray = gray[y:y+h, x:x+w]
            eyes = self.eye_cascade.detectMultiScale(roi_gray)
            eyes_list.append({
                'face': (x, y, w, h),
                'eyes': eyes.tolist()
            })
        
        return {'faces': faces.tolist(), 'eyes': eyes_list}