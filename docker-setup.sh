#!/bin/bash

set -e

echo "========================================="
echo "  AI News - Docker Environment Setup"
echo "========================================="

# Copy environment file
if [ ! -f .env ]; then
    cp .env.docker .env
    echo "[OK] .env file created"
else
    echo "[SKIP] .env file already exists"
fi

# Build containers
echo ""
echo "Building Docker containers..."
docker compose build --no-cache

# Start containers
echo ""
echo "Starting containers..."
docker compose up -d

# Wait for database
echo ""
echo "Waiting for PostgreSQL to be ready..."
sleep 5

# Install PHP dependencies
echo ""
echo "Installing Composer dependencies..."
docker compose exec php composer install

# Generate app key
echo ""
echo "Generating application key..."
docker compose exec php php artisan key:generate

# Run migrations
echo ""
echo "Running database migrations..."
docker compose exec php php artisan migrate

# Install Node dependencies
echo ""
echo "Installing NPM dependencies..."
docker compose exec php npm install

# Build frontend assets
echo ""
echo "Building frontend assets..."
docker compose exec php npm run build

# Set permissions
echo ""
echo "Setting permissions..."
docker compose exec php chmod -R 775 storage bootstrap/cache

echo ""
echo "========================================="
echo "  Setup Complete!"
echo "========================================="
echo ""
echo "  App:      http://localhost:${APP_PORT:-80}"
echo "  Mailpit:  http://localhost:${MAIL_DASHBOARD_PORT:-8025}  (profile: dev)"
echo "  pgAdmin:  http://localhost:${PGADMIN_PORT:-5050}  (profile: dev)"
echo "  Vite:     http://localhost:${VITE_PORT:-5173}  (profile: dev)"
echo ""
echo "  Start dev profile services:"
echo "    docker compose --profile dev up -d"
echo ""
echo "  Useful commands:"
echo "    docker compose exec php php artisan tinker"
echo "    docker compose exec php php artisan migrate:fresh --seed"
echo "    docker compose exec php composer test"
echo ""
