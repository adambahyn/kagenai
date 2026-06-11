FROM php:8.2-apache

# 1. Install Python 3, pip, dan dependensi sistem lainnya
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    && rm -rf /var/lib/apt/lists/*

# 2. Tentukan directory kerja Apache
WORKDIR /var/www/html

# 3. Salin seluruh file proyek ke dalam container
COPY . .

# 4. Install dependensi Python menggunakan pip
# Menggunakan --break-system-packages karena berada di dalam container terisolasi (Debian 12+)
RUN pip3 install --no-cache-dir --break-system-packages -r requirements.txt

# 5. Berikan izin eksekusi jika diperlukan dan ubah port apache jika Railway membutuhkan (Railway otomatis mendeteksi port 80/8080)
EXPOSE 80
