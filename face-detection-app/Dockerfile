FROM python:3.10-slim-bookworm

ENV DEBIAN_FRONTEND=noninteractive

# 1. Install sistem dependencies yang lengkap
# Penting: dlib membutuhkan cmake dan library C++ boost/x11
RUN apt-get update && apt-get install -y \
    cmake \
    build-essential \
    libopenblas-dev \
    liblapack-dev \
    libx11-dev \
    libgtk-3-dev \
    libgl1 \
    libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

# 2. Setup User (Hugging Face Best Practice)
# Kita buat user di awal agar permission file selanjutnya benar
RUN useradd -m -u 1000 user
USER user
ENV HOME=/home/user \
    PATH=/home/user/.local/bin:$PATH

WORKDIR $HOME/app

# 3. Copy & Install Requirements
COPY --chown=user requirements.txt $HOME/app/requirements.txt

# Install dengan pip (gunakan cache pip user)
RUN pip install --no-cache-dir --upgrade pip && \
    pip install --no-cache-dir -r requirements.txt && \
    pip install gunicorn

# 4. Copy Source Code
# Perhatikan: ini mengcopy ISI folder src ke dalam root app
COPY --chown=user src/ $HOME/app/

# 5. Expose & Run
EXPOSE 7860
CMD ["gunicorn", "--bind", "0.0.0.0:7860", "app:app", "--timeout", "120"]
