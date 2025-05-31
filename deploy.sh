#!/bin/bash
set -e

# 📥 Load environment variables from ../.env
echo "🔧 Loading .env variables..."
if [ -f ../.env ]; then
  export $(grep -v '^#' ../.env | xargs)
else
  echo "❌ ../.env not found. Aborting."
  exit 1
fi

# 🔐 Login to Docker registry
echo "🔐 Logging in to Docker registry..."
docker login ghcr.io -u "${REGISTRY_USERNAME}" -p "${REGISTRY_TOKEN}"

if [ ! -d storage/app ]; then
  echo "📁 Creating storage folder structure..."
  mkdir -p storage/app \
           storage/framework/cache \
           storage/framework/sessions \
           storage/framework/testing \
           storage/framework/views \
           storage/logs

  echo "🔒 Setting permissions..."
  find storage -type d -exec chmod 775 {} \;
  find storage -type d -exec chown $(id -u):$(id -g) {} \;
else
  echo "✅ storage/ already exists, skipping folder creation and chmod."
fi

echo "📦 Pulling latest image..."
docker compose -f docker-compose.prod.yaml pull

echo "♻️ Recreating app container..."
docker compose -f docker-compose.prod.yaml up -d

echo "🔁 Waiting for container to start..."
sleep 10

echo "⚙️ Running Laravel commands..."
docker exec medicine-application php artisan config:cache
docker exec medicine-application php artisan route:cache
docker exec medicine-application php artisan migrate --force
docker exec medicine-application php artisan db:seed --force
#docker exec medicine-application php artisan l5-swagger:generate
docker exec medicine-application php artisan storage:link || true

echo "✅ Deployment complete."

echo "🧹 Cleaning up unused Docker images..."
docker image prune -f
