#!/bin/bash

# Backend Deployment Script
# This script should be run on the server after uploading the deployment package

set -e  # Exit on error

ENVIRONMENT=${1:-tst}  # tst or prd
DEPLOY_PATH=${2:-/var/www/honr}
RELEASE_DIR="${DEPLOY_PATH}/releases/$(date +%Y%m%d%H%M%S)"
CURRENT_LINK="${DEPLOY_PATH}/current"
SHARED_DIR="${DEPLOY_PATH}/shared"

echo "🚀 Starting backend deployment to ${ENVIRONMENT}..."
echo "Release directory: ${RELEASE_DIR}"

# Create directories
mkdir -p "${RELEASE_DIR}"
mkdir -p "${SHARED_DIR}/storage"
mkdir -p "${SHARED_DIR}/storage/app/public"
mkdir -p "${SHARED_DIR}/storage/framework/cache"
mkdir -p "${SHARED_DIR}/storage/framework/sessions"
mkdir -p "${SHARED_DIR}/storage/framework/views"
mkdir -p "${SHARED_DIR}/storage/logs"
mkdir -p "${SHARED_DIR}/bootstrap/cache"

# Extract deployment package (if using tar)
# tar -xzf deploy-${ENVIRONMENT}.tar.gz -C "${RELEASE_DIR}"

# Or if files are already in place, move them
# mv backend/* "${RELEASE_DIR}/"

# Install Composer dependencies
cd "${RELEASE_DIR}/backend"
composer install --no-dev --optimize-autoloader --no-interaction

# Link shared storage
ln -sf "${SHARED_DIR}/storage" "${RELEASE_DIR}/backend/storage"
ln -sf "${SHARED_DIR}/bootstrap/cache" "${RELEASE_DIR}/backend/bootstrap/cache"

# Copy environment file (if not exists)
if [ ! -f "${RELEASE_DIR}/backend/.env" ]; then
    if [ -f "${SHARED_DIR}/.env.${ENVIRONMENT}" ]; then
        cp "${SHARED_DIR}/.env.${ENVIRONMENT}" "${RELEASE_DIR}/backend/.env"
    else
        echo "⚠️  Warning: .env file not found. Please create it manually."
    fi
fi

# Set proper permissions
chmod -R 775 "${SHARED_DIR}/storage"
chmod -R 775 "${SHARED_DIR}/bootstrap/cache"
chown -R www-data:www-data "${SHARED_DIR}/storage" || true
chown -R www-data:www-data "${SHARED_DIR}/bootstrap/cache" || true

# Run Laravel setup commands
cd "${RELEASE_DIR}/backend"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# Create symlink to current release
ln -sfn "${RELEASE_DIR}" "${CURRENT_LINK}"

# Restart services (adjust based on your setup)
# systemctl restart php8.2-fpm || true
# systemctl restart nginx || true

echo "✅ Backend deployment completed!"
echo "Current release: ${RELEASE_DIR}"

