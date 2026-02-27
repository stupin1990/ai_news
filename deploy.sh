#!/usr/bin/env bash

set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.server.yml}"
APP_SERVICE="${APP_SERVICE:-php}"

chown -R 1000:1000 "$PROJECT_DIR"

cd "$PROJECT_DIR"

if [ ! -f "$COMPOSE_FILE" ]; then
  echo "[ERROR] Compose file not found: $COMPOSE_FILE"
  exit 1
fi

if [ ! -f ".env" ]; then
  echo "[ERROR] .env file not found in $PROJECT_DIR"
  exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "[ERROR] docker is not installed"
  exit 1
fi

git config --global --add safe.directory "$PROJECT_DIR" || true

echo "[1/9] Updating git repository..."
git fetch --all --prune
git pull --ff-only

echo "[2/9] Validating Docker Compose config..."
docker compose -f "$COMPOSE_FILE" config >/dev/null

echo "[3/9] Pulling base images..."
docker compose -f "$COMPOSE_FILE" pull || true

echo "[4/9] Building and starting containers..."
docker compose -f "$COMPOSE_FILE" up -d --build --remove-orphans

echo "[5/9] Installing PHP dependencies..."
docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" composer install --no-dev --optimize-autoloader --no-interaction

APP_KEY_VALUE="$(grep -E '^APP_KEY=' .env | cut -d '=' -f2- || true)"
if [ -z "$APP_KEY_VALUE" ]; then
  echo "[6/9] APP_KEY is missing, generating..."
  docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" php artisan key:generate --force --no-interaction
else
  echo "[6/9] APP_KEY already exists, skipping key generation..."
fi

echo "[7/9] Running database migrations..."
docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" php artisan migrate --force --no-interaction
docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" php artisan db:seed --force --no-interaction

echo "[8/9] Building frontend assets and optimizing Laravel..."
docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" npm ci
docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" npm run build
docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" php artisan optimize:clear --no-interaction
docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" php artisan optimize --no-interaction
docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" php artisan queue:restart --no-interaction

echo "[9/9] Final container status:"
docker compose -f "$COMPOSE_FILE" ps

echo ""
echo "[DONE] Deploy completed successfully."