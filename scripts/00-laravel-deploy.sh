#!/usr/bin/env bash
set -e

echo "==> Caching Laravel configuration, routes, and views..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "==> Running database migrations..."
if [ -n "$DB_HOST" ] || [ -n "$DATABASE_URL" ]; then
    php artisan migrate --force || echo "Migration skipped or failed, continuing..."
fi

echo "==> Laravel application initialized successfully!"
