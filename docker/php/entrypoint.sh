#!/usr/bin/env sh
set -e
cd /var/www/html

# vendor/ lives in a named volume that starts empty on first boot -> install.
# Guarded so subsequent boots skip straight to the command (idempotent).
if [ ! -f vendor/autoload.php ]; then
  echo "[entrypoint] Installing Composer dependencies (first boot)..."
  composer install --no-interaction --prefer-dist --no-progress
fi

# First boot: ensure an .env and app key exist.
[ -f .env ] || cp .env.example .env
if ! grep -q '^APP_KEY=base64' .env; then
  echo "[entrypoint] Generating application key..."
  php artisan key:generate --force
fi

# Migrations are intentionally NOT run here (kept explicit so restarts stay
# idempotent): run `docker compose exec app php artisan migrate --seed` once.

exec "$@"
