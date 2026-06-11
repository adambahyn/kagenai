FROM php:8.2-cli

# 1. Install Python 3 dan pip
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    && rm -rf /var/lib/apt/lists/*

# 2. Tentukan directory kerja
WORKDIR /app

# 3. Salin seluruh file proyek
COPY . .

# 4. Install dependensi Python
RUN pip3 install --no-cache-dir --break-system-packages -r requirements.txt

# 5. Jalankan PHP Built-in Server menggunakan port dinamis dari Railway
CMD php -S 0.0.0.0:$PORT
