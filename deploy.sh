#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

DEPLOY_USER="$(id -un)"
DEPLOY_GROUP="$(id -gn)"

echo "==> Fix repository ownership"
if command -v sudo >/dev/null 2>&1 && git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  sudo chown -R "$DEPLOY_USER:$DEPLOY_GROUP" .git .
fi

echo "==> Reset storage symlink"
if [ -L public/storage ] || [ -d public/storage ]; then
  rm -rf public/storage
fi

echo "==> Pull latest code"
if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  git pull --rebase --autostash origin main
else
  echo "    Skipping git pull: not inside a git worktree."
fi

echo "==> Fix permissions"
mkdir -p storage/logs bootstrap/cache
touch storage/logs/laravel.log

if grep -q '^DB_CONNECTION=sqlite' .env 2>/dev/null; then
  mkdir -p database
  touch database/database.sqlite
fi

if command -v sudo >/dev/null 2>&1; then
  sudo chown -R "$DEPLOY_USER:www-data" storage bootstrap/cache
  sudo chmod -R 775 storage bootstrap/cache
  sudo chown "$DEPLOY_USER:www-data" storage/logs/laravel.log
  sudo chmod 664 storage/logs/laravel.log
else
  chmod -R ug+rwX storage bootstrap/cache || true
fi

if command -v composer >/dev/null 2>&1 && [ -f composer.json ]; then
  echo "==> Install PHP dependencies"
  composer install --no-dev --optimize-autoloader
fi

if command -v npm >/dev/null 2>&1 && [ -f package.json ]; then
  echo "==> Build frontend assets"
  npm ci
  npm run build
fi

if [ -d frontend ] && [ -f frontend/package.json ]; then
  echo "==> Build separated frontend"
  (
    cd frontend
    npm ci
    npm run build
  )
fi

echo "==> Clear and rebuild caches"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

echo "==> Done"
