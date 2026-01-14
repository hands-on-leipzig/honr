#!/bin/bash

# Server-side deployment script
# This script runs ON THE SERVER and pulls code from GitHub
# Usage: ./deploy-from-git.sh [environment] [deploy_path] [branch]

set -e

ENVIRONMENT=${1:-tst}
DEPLOY_PATH=${2:-/var/www/honr}
BRANCH=${3:-main}
REPO_URL=${4:-https://github.com/hands-on-leipzig/honr.git}

echo "🚀 Starting deployment from Git..."
echo "Environment: ${ENVIRONMENT}"
echo "Deploy path: ${DEPLOY_PATH}"
echo "Branch: ${BRANCH}"

# Create directories if they don't exist
mkdir -p "${DEPLOY_PATH}/releases"
mkdir -p "${DEPLOY_PATH}/shared/storage/{app/public,framework/{cache,sessions,views},logs}"
mkdir -p "${DEPLOY_PATH}/shared/bootstrap/cache"

# Create release directory
RELEASE_DIR="${DEPLOY_PATH}/releases/$(date +%Y%m%d%H%M%S)"
mkdir -p "${RELEASE_DIR}"

echo "📥 Cloning repository..."
git clone --depth 1 --branch "${BRANCH}" "${REPO_URL}" "${RELEASE_DIR}"

# Install backend dependencies
echo "📦 Installing backend dependencies..."
cd "${RELEASE_DIR}/backend"
composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend
echo "🏗️  Building frontend..."
cd "${RELEASE_DIR}/frontend"
npm ci
# Set API URL based on environment
if [ "${ENVIRONMENT}" = "tst" ]; then
  export VITE_API_URL=https://test.honr.hands-on-technology.org/api
elif [ "${ENVIRONMENT}" = "prd" ]; then
  export VITE_API_URL=https://honr.hands-on-technology.org/api
fi
npm run build-only

# Link shared storage
echo "🔗 Linking shared storage..."
ln -sf "${DEPLOY_PATH}/shared/storage" "${RELEASE_DIR}/backend/storage"
ln -sf "${DEPLOY_PATH}/shared/bootstrap/cache" "${RELEASE_DIR}/backend/bootstrap/cache"

# Copy environment file
if [ -f "${DEPLOY_PATH}/shared/.env.${ENVIRONMENT}" ]; then
  cp "${DEPLOY_PATH}/shared/.env.${ENVIRONMENT}" "${RELEASE_DIR}/backend/.env"
else
  echo "⚠️  Warning: .env.${ENVIRONMENT} not found. Using example file."
  cp "${RELEASE_DIR}/backend/env.${ENVIRONMENT}.example" "${RELEASE_DIR}/backend/.env" || true
fi

# Set permissions
chmod -R 775 "${DEPLOY_PATH}/shared/storage" || true
chmod -R 775 "${DEPLOY_PATH}/shared/bootstrap/cache" || true

# Run Laravel setup
echo "⚙️  Running Laravel setup..."
cd "${RELEASE_DIR}/backend"
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan storage:link || true

# Check and run migrations
echo "📊 Checking database migration status..."
MIGRATIONS_EXIST=$(php artisan tinker --execute="try { DB::table('migrations')->count(); echo 'true'; } catch (Exception \$e) { echo 'false'; }" 2>/dev/null | tail -1 || echo "false")

if [ "$MIGRATIONS_EXIST" = "true" ]; then
  echo "📊 Running database migrations..."
  php artisan migrate --force
else
  echo "⚠️  Migrations table not found. Skipping migrations (initial deployment)."
fi

# Clear and cache config
php artisan config:clear || true
php artisan cache:clear || true
php artisan config:cache || true

# Create symlink to current release
ln -sfn "${RELEASE_DIR}" "${DEPLOY_PATH}/current"

# Clean up old releases (keep last 5)
echo "🧹 Cleaning up old releases..."
cd "${DEPLOY_PATH}/releases"
ls -t | tail -n +6 | xargs rm -rf 2>/dev/null || true

echo "✅ Deployment completed!"
echo "Release directory: ${RELEASE_DIR}"
