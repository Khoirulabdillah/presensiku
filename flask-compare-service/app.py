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

    # Try to do robust alignment + preprocessing to improve encoding stability.
    try:
        from PIL import Image, ImageOps
        pil_available = True
    except Exception:
        pil_available = False

    # optional OpenCV for CLAHE equalization
    try:
        import cv2
        cv2_available = True
    except Exception:
        cv2_available = False

    def preprocess_and_encode(img, face_location):
        # face_location: (top, right, bottom, left)
        top, right, bottom, left = face_location
        h, w = img.shape[:2]

        # expand box by 30% margin
        mw = int((right - left) * 0.3)
        mh = int((bottom - top) * 0.3)
        l = max(0, left - mw)
        t = max(0, top - mh)
        r = min(w, right + mw)
        b = min(h, bottom + mh)

        # convert to PIL for rotation/align
        try:
            pil = Image.fromarray(img)
        except Exception:
            pil = None

        # try landmarks-based alignment
        try:
            landmarks_list = face_recognition.face_landmarks(img, [face_location])
        except Exception:
            landmarks_list = None

        rotated_img = None
        if pil and landmarks_list:
            lm = landmarks_list[0]
            if 'left_eye' in lm and 'right_eye' in lm:
                left_eye = np.mean(lm['left_eye'], axis=0)
                right_eye = np.mean(lm['right_eye'], axis=0)
                dy = right_eye[1] - left_eye[1]
                dx = right_eye[0] - left_eye[0]
                angle = np.degrees(np.arctan2(dy, dx))
                # rotate to align eyes horizontally
                try:
                    pil_rot = pil.rotate(-angle, resample=Image.BICUBIC, expand=True)
                    rotated_img = np.array(pil_rot)
                except Exception:
                    rotated_img = None

        candidates = []
        # prefer rotated+cropped if available
        if rotated_img is not None:
            rt_h, rt_w = rotated_img.shape[:2]
            # map original bbox center to rotated image roughly by center crop
            cx = int((left + right) / 2)
            cy = int((top + bottom) / 2)
            # crop center region from rotated image
            cw = int((right - left) * 1.6)
            ch = int((bottom - top) * 1.6)
            rcx = rt_w // 2
            rcy = rt_h // 2
            rl = max(0, rcx - cw//2)
            rt = max(0, rcy - ch//2)
            rr = min(rt_w, rl + cw)
            rb = min(rt_h, rt + ch)
            crop = rotated_img[rt:rb, rl:rr]
            if crop.size > 0:
                candidates.append(crop)

        # original cropped candidate
        orig_crop = img[t:b, l:r]
        if orig_crop.size > 0:
            candidates.append(orig_crop)

        # small rotation jitter attempts to handle tilt
        angles = [-12, -6, 6, 12]
        if pil:
            for a in angles:
                try:
                    pr = pil.rotate(a, resample=Image.BICUBIC, expand=True)
                    pr_arr = np.array(pr)
                    # center crop
                    ph, pw = pr_arr.shape[:2]
                    cw = min(pw, int((right-left)*1.6))
                    ch = min(ph, int((bottom-top)*1.6))
                    px = pw//2 - cw//2
                    py = ph//2 - ch//2
                    pr_crop = pr_arr[py:py+ch, px:px+cw]
                    if pr_crop.size > 0:
                        candidates.append(pr_crop)
                except Exception:
                    continue

        # process candidates: equalize and resize
        for cand in candidates:
            proc = cand
            try:
                if cv2_available:
                    # convert to YCrCb and apply CLAHE on Y channel
                    ycrcb = cv2.cvtColor(proc, cv2.COLOR_RGB2YCrCb)
                    y, cr, cb = cv2.split(ycrcb)
                    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8,8))
                    y = clahe.apply(y)
                    ycrcb = cv2.merge((y, cr, cb))
                    proc = cv2.cvtColor(ycrcb, cv2.COLOR_YCrCb2RGB)
                else:
                    # fallback: PIL equalize per-channel
                    try:
                        p = Image.fromarray(proc)
                        p = ImageOps.equalize(p)
                        proc = np.array(p)
                    except Exception:
                        pass

                # resize to reasonable size for encoding stability
                ph, pw = proc.shape[:2]
                target_w = 256
                if pw != target_w:
                    scale = target_w / pw
                    new_h = max(80, int(ph * scale))
                    proc = np.array(Image.fromarray(proc).resize((target_w, new_h), resample=Image.BICUBIC))

                encs = face_recognition.face_encodings(proc, num_jitters=num_jitters)
                if encs:
                    return encs[0]
            except Exception:
                continue

        return None

    # Try to preprocess + encode
    try:
        enc = preprocess_and_encode(image, best_location)
        if enc is not None:
            return enc, best_location
    except Exception:
        pass

    # Fallback: original encoding of detected face box
    try:
        encodings = face_recognition.face_encodings(image, known_face_locations=[best_location], num_jitters=num_jitters)
        if encodings:
            return encodings[0], best_location
    except Exception:
        return None, best_location

    return None, best_location


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
