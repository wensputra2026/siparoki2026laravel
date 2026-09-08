#!/usr/bin/env bash
set -e

echo "========================================"
echo "   MEMULAI SETUP SIPAROKI OTOMATIS      "
echo "========================================"

# 1. Pastikan file .env ada
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "[+] File .env berhasil disalin dari .env.example"
    fi
fi

# 2. Install dependency Composer
echo "[+] Menjalankan composer install..."
composer install --no-dev --optimize-autoloader

# 3. Generate APP_KEY jika belum ada
echo "[+] Memastikan APP_KEY terpasang..."
php artisan key:generate --force

# 4. Migrasi & Seeder
echo "[+] Menjalankan migrasi & seeder database bersih..."
php artisan migrate --force
php artisan db:seed --force

# 5. Storage Symlink
echo "[+] Membuat tautan storage..."
php artisan storage:link || true

# 6. Set Permission
echo "[+] Mengatur permission folder storage & cache..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www:www storage bootstrap/cache 2>/dev/null || true

# 7. Bersihkan & Optimalkan Cache
echo "[+] Membersihkan cache konfigurasi..."
php artisan optimize:clear

echo "========================================"
echo "   SETUP SELESAI! APLIKASI SIAP PAKAI   "
echo "   CAPTCHA OTOMATIS AKTIF DI LOGIN      "
echo "========================================"
