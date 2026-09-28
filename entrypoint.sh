#!/bin/sh
set -eu

echo "--- Iniciando IFBANK ---"

if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env criado a partir de .env.example."
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --ignore-platform-reqs
fi

if [ -f package-lock.json ] && [ ! -x node_modules/.bin/vite ]; then
    npm ci --no-audit --no-fund
fi

if [ -f package.json ] && [ ! -f public/build/manifest.json ]; then
    npm run build
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force >/dev/null
fi

php artisan migrate --force --graceful
php artisan optimize:clear >/dev/null

mkdir -p storage bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

exec php -S 0.0.0.0:15000 -t public
