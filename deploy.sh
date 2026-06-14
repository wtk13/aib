#!/usr/bin/env bash
# Skrypt wdrożenia na Hetzner
# Uruchom: ./deploy.sh
set -euo pipefail

COMPOSE="docker compose -f docker-compose.prod.yml --env-file .env.production"

echo "==> [1/6] Pull z GitHub"
git pull origin master

echo "==> [2/6] Build obrazów Docker"
$COMPOSE build --pull

echo "==> [3/6] Build assetów (npm)"
docker run --rm \
  -v "$(pwd)":/app \
  -w /app \
  "node:20-alpine" \
  sh -c "npm ci && npm run build"

echo "==> [4/6] Uruchom kontenery"
$COMPOSE up -d --remove-orphans

echo "==> [5/6] Migracje i cache"
$COMPOSE exec -T app php artisan migrate --force
$COMPOSE exec -T app php artisan config:cache
$COMPOSE exec -T app php artisan route:cache
$COMPOSE exec -T app php artisan view:cache
$COMPOSE exec -T app php artisan event:cache

echo "==> [6/6] Restart Horizon"
$COMPOSE restart horizon

echo ""
echo "Wdrożenie zakończone pomyślnie."
