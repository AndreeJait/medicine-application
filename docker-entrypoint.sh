#!/bin/sh
set -e

echo "🔁 Ensuring APP_KEY is set..."
if [ -z "$APP_KEY" ] || grep -q "APP_KEY=" .env && grep -q "APP_KEY=$" .env; then
  echo "🔐 Generating APP_KEY..."
  php artisan key:generate
else
  echo "✅ APP_KEY already set."
fi

echo "🔁 Checking for Passport keys..."
if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
  echo "🔐 Generating Passport keys..."
  php artisan passport:keys
else
  echo "✅ Passport keys already exist."
fi

exec "$@"
