# Flask Compare Service (ready-to-deploy)

This folder contains a minimal Flask `/compare` service (uses `face_recognition`) and a Dockerfile. It's ready to be pushed to a GitHub repo and connected to Render (or any Docker host).

Quick steps to deploy on Render (recommended):

1. Create a new GitHub repository and push this `flask-compare-service` folder (or push entire repo but use this folder as Render root).
2. On Render, create a new **Web Service** and connect your GitHub repo.
   - Set **Environment**: `Docker`.
   - Set **Build Command**: (leave empty)
   - Set **Start Command**: `gunicorn --bind 0.0.0.0:5000 app:app`
   - Add env var: `FLASK_ENV=production`
3. Deploy and wait until the service is live. Copy the service URL and append `/compare`.
4. In your Laravel app production `.env`, set:

```
FLASK_COMPARE_URL=https://<your-service>.onrender.com/compare
```

5. Run on Laravel server:

```bash
php artisan config:clear
php artisan cache:clear
```

Notes:
- Building `dlib` can take several minutes. Use a VPS or Render to avoid cPanel limitations.
- If you want automatic creation on Render, include `.render.yaml` (example provided).
