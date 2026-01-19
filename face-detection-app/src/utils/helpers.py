from PIL import Image
import cv2
import numpy as np
import io
import base64

def load_image(file):
    """
    Load gambar dari file upload Flask
    
    Args:
        file: FileStorage object dari Flask
        
    Returns:
        PIL Image object
    """
    image = Image.open(io.BytesIO(file.read()))
    return image

def load_image_as_cv2(file):
    """
    Load gambar dari file upload Flask ke format OpenCV (numpy array)
    
    Args:
        file: FileStorage object dari Flask
        
    Returns:
        numpy array (OpenCV format BGR)
    """
    file_bytes = file.read()
    nparr = np.frombuffer(file_bytes, np.uint8)
    image_cv2 = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    return image_cv2

def preprocess_image(image):
    """
    Preproses gambar untuk deteksi wajah
    
    Args:
        image: PIL Image atau OpenCV image
        
    Returns:
        Gambar yang sudah diproses (OpenCV format)
    """
    if isinstance(image, Image.Image):
        image = cv2.cvtColor(np.array(image), cv2.COLOR_RGB2BGR)
    
    # Resize ke ukuran standar
    image = cv2.resize(image, (640, 480))
    
    # Normalisasi contrast
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    enhanced = clahe.apply(gray)
    
    return image

def draw_faces_on_image(image, faces, color=(0, 255, 0), thickness=2):
    """
    Gambar kotak di sekitar wajah yang terdeteksi
    
    Args:
        image: Gambar dalam format OpenCV
        faces: List dari (x, y, width, height)
        color: Warna BGR tuple
        thickness: Ketebalan garis
        
    Returns:
        Gambar dengan kotak wajah
    """
    result = image.copy()
    for (x, y, w, h) in faces:
        cv2.rectangle(result, (x, y), (x+w, y+h), color, thickness)
    return result

def image_to_base64(image_cv2):
    """
    Konversi gambar OpenCV ke base64 string
    
    Args:
        image_cv2: Gambar dalam format OpenCV
        
    Returns:
        Base64 encoded string
    """
    _, buffer = cv2.imencode('.jpg', image_cv2)
    img_base64 = base64.b64encode(buffer).decode()
    return img_base64