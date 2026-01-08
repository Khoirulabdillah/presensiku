from flask import Flask, request, jsonify
import face_recognition

app = Flask(__name__)

@app.route('/compare', methods=['POST'])
def compare_faces():
    if 'source_image' not in request.files or 'target_image' not in request.files:
        return jsonify({'error': 'Both source_image and target_image are required.'}), 400

    source_file = request.files['source_image']
    target_file = request.files['target_image']

    try:
        source_img = face_recognition.load_image_file(source_file)
        target_img = face_recognition.load_image_file(target_file)

        source_encodings = face_recognition.face_encodings(source_img)
        target_encodings = face_recognition.face_encodings(target_img)

        if not source_encodings:
            return jsonify({'error': 'No face detected in source_image.'}), 400
        if not target_encodings:
            return jsonify({'error': 'No face detected in target_image.'}), 400

        match = face_recognition.compare_faces(
            [source_encodings[0]], target_encodings[0], tolerance=0.5
        )[0]
        return jsonify({'match': match})
    except Exception as e:
        return jsonify({'error': str(e)}), 400

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
