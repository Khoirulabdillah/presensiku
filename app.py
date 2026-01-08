from flask import Flask, request, jsonify
import face_recognition
import numpy as np

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
        if not source_encodings:
            return jsonify({'error': 'No face detected in source_image.'}), 400

        # try to get encoding from target image (original)
        target_encodings = face_recognition.face_encodings(target_img)

        # also compute encoding from horizontally flipped target (mirror)
        flipped_img = np.fliplr(target_img)
        flipped_encodings = face_recognition.face_encodings(flipped_img)

        if not target_encodings and not flipped_encodings:
            return jsonify({'error': 'No face detected in target_image (original or flipped).'}), 400

        source_enc = source_encodings[0]

        # pick first encoding if multiple faces present
        target_enc = target_encodings[0] if target_encodings else None
        flip_enc = flipped_encodings[0] if flipped_encodings else None

        # compute distances (euclidean); smaller is better
        best = {
            'which': None,
            'distance': None,
            'matched': False,
        }

        tolerance = float(request.form.get('tolerance', 0.5))

        if target_enc is not None:
            dist = face_recognition.face_distance([source_enc], target_enc)[0]
            best.update({'which': 'original', 'distance': float(dist), 'matched': dist <= tolerance})

        if flip_enc is not None:
            distf = face_recognition.face_distance([source_enc], flip_enc)[0]
            # if we don't have a best yet, or flipped is better (smaller distance), prefer it
            if best['distance'] is None or float(distf) < float(best['distance']):
                best.update({'which': 'flipped', 'distance': float(distf), 'matched': float(distf) <= tolerance})

        return jsonify({
            'match': bool(best['matched']),
            'which': best['which'],
            'distance': best['distance'],
            'detected_mirror': (best['which'] == 'flipped')
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 400

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
