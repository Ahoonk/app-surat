#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

echo "==> Pull latest code"
git pull origin main

echo "==> Fix permissions"
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo touch storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log

if command -v composer >/dev/null 2>&1 && [ -f composer.json ]; then
  echo "==> Install PHP dependencies"
  composer install --no-dev --optimize-autoloader
fi

if command -v npm >/dev/null 2>&1 && [ -f package.json ]; then
  echo "==> Build frontend assets"
  npm install
  npm run build
fi

echo "==> Clear and rebuild caches"
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

echo "==> Done"
