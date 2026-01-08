from flask import Flask, request, jsonify
import face_recognition
import numpy as np
import logging

app = Flask(__name__)
logging.basicConfig(level=logging.INFO)


def _first_face_encoding(image, model='hog', num_jitters=0):
    # detect face locations and choose the largest face (by area)
    locations = face_recognition.face_locations(image, model=model)
    if not locations:
        return None, None

    # choose largest face
    areas = [ (abs((top-bottom)*(right-left)), idx) for idx, (top, right, bottom, left) in enumerate(locations) ]
    areas.sort(reverse=True)
    best_idx = areas[0][1]
    best_location = locations[best_idx]

    # Try to align face using landmarks if Pillow is available
    try:
        from PIL import Image, ImageOps
        pil_available = True
    except Exception:
        pil_available = False

    if pil_available:
        try:
            # attempt to get landmarks for the chosen face
            landmarks_list = face_recognition.face_landmarks(image, [best_location])
            if landmarks_list:
                # convert to PIL for processing
                pil = Image.fromarray(image)
                lm = landmarks_list[0]
                if 'left_eye' in lm and 'right_eye' in lm:
                    left = np.mean(lm['left_eye'], axis=0)
                    right = np.mean(lm['right_eye'], axis=0)
                    dy = right[1] - left[1]
                    dx = right[0] - left[0]
                    angle = np.degrees(np.arctan2(dy, dx))
                    # rotate to align eyes horizontally
                    pil = pil.rotate(-angle, resample=Image.BICUBIC, expand=True)
                    # equalize to reduce lighting differences
                    try:
                        pil = ImageOps.equalize(pil)
                    except Exception:
                        pass
                    rotated = np.array(pil)
                    encodings = face_recognition.face_encodings(rotated, num_jitters=num_jitters)
                    if encodings:
                        return encodings[0], best_location
        except Exception:
            # fallback to original encoding path
            pass

    encodings = face_recognition.face_encodings(image, known_face_locations=[best_location], num_jitters=num_jitters)
    if not encodings:
        return None, best_location

    return encodings[0], best_location


@app.route('/compare', methods=['POST'])
def compare_faces():
    if 'source_image' not in request.files or 'target_image' not in request.files:
        return jsonify({'error': 'Both source_image and target_image are required.'}), 400

    source_file = request.files['source_image']
    target_file = request.files['target_image']

    # parameters
    tolerance = float(request.form.get('tolerance', 0.5))
    model = request.form.get('model', 'hog')  # 'hog' or 'cnn'
    num_jitters = int(request.form.get('num_jitters', 0))

    try:
        source_img = face_recognition.load_image_file(source_file)
        target_img = face_recognition.load_image_file(target_file)

        src_enc, src_loc = _first_face_encoding(source_img, model=model, num_jitters=num_jitters)
        if src_enc is None:
            return jsonify({'error': 'No face detected in source_image.'}), 400

        # target original
        tgt_enc, tgt_loc = _first_face_encoding(target_img, model=model, num_jitters=num_jitters)

        # target flipped (mirror)
        flipped_img = np.fliplr(target_img)
        flip_enc, flip_loc = _first_face_encoding(flipped_img, model=model, num_jitters=num_jitters)

        if tgt_enc is None and flip_enc is None:
            return jsonify({'error': 'No face detected in target_image (original or flipped).'}), 400

        results = []

        if tgt_enc is not None:
            dist = float(face_recognition.face_distance([src_enc], tgt_enc)[0])
            results.append({'which': 'original', 'distance': dist, 'matched': dist <= tolerance, 'location': tgt_loc})

        if flip_enc is not None:
            distf = float(face_recognition.face_distance([src_enc], flip_enc)[0])
            results.append({'which': 'flipped', 'distance': distf, 'matched': distf <= tolerance, 'location': flip_loc})

        # pick best (smallest distance)
        best = min(results, key=lambda r: r['distance'])

        resp = {
            'match': bool(best['matched']),
            'which': best['which'],
            'distance': best['distance'],
            'tolerance': tolerance,
            'detected_mirror': (best['which'] == 'flipped'),
            'debug': {
                'source_location': src_loc,
                'results': results,
                'model': model,
                'num_jitters': num_jitters,
            }
        }

        logging.info('Compare result: %s', resp)

        return jsonify(resp)
    except Exception as e:
        logging.exception('Compare error')
        return jsonify({'error': str(e)}), 400


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
