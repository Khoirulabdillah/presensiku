Deploying Flask face verification service (recommended: Render / VPS)

This repository includes a Dockerfile and requirements.txt to run the Flask `/compare` service with `face_recognition` and `dlib`.

Recommended: Deploy as a separate service (Render, Railway, or a VPS) because building `dlib` on shared cPanel is typically not possible.

Quick Render/Docker steps:

1. Push this repo to GitHub (the branch that contains the Dockerfile).
2. Create a new Web Service on Render (https://render.com) and connect your GitHub repository.
   - Select "Docker" as the environment (Render will build the Dockerfile).
   - Set the start command: `gunicorn --bind 0.0.0.0:5000 app:app` (already in Dockerfile).
3. Add environment variables on Render:
   - `FLASK_ENV=production`
   - (optional) `SECRET_KEY=...`
4. Deploy and wait until the service is live. Copy the service URL (e.g. `https://my-flask-service.onrender.com/compare`).
5. In your Laravel app `.env` on cPanel, set:
   FLASK_COMPARE_URL=https://my-flask-service.onrender.com/compare

6. Restart/clear config cache if necessary:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

If you prefer a VPS (DigitalOcean / Hetzner / AWS):
- Build Docker image locally and push to Docker Hub, then run on server with `docker run -p 5000:5000 your/image:tag`.

Notes about dependencies:
- `face_recognition` depends on `dlib` which needs C++ toolchain and may take several minutes to compile during Docker build.
- For faster builds, use a prebuilt `dlib` wheel matching your Python version and OS, or use an image that already has `dlib` installed.

If you want, I can:
- Provide a `docker-compose.yml` for local testing.
- Prepare a minimal systemd service file for a VPS.
- Deploy the Flask service to Render for you if you provide GitHub/Render access.
