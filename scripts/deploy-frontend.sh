#!/bin/bash

# Frontend Deployment Script
# This script should be run on the server after uploading the deployment package

set -e  # Exit on error

ENVIRONMENT=${1:-tst}  # tst or prd
DEPLOY_PATH=${2:-/var/www/honr}
RELEASE_DIR="${DEPLOY_PATH}/releases/$(date +%Y%m%d%H%M%S)"
CURRENT_LINK="${DEPLOY_PATH}/current"

echo "🚀 Starting frontend deployment to ${ENVIRONMENT}..."
echo "Release directory: ${RELEASE_DIR}"

# Create directory
mkdir -p "${RELEASE_DIR}/frontend"

# Extract or move frontend files
# If using tar: tar -xzf deploy-${ENVIRONMENT}.tar.gz -C "${RELEASE_DIR}"
# If files are already in place: mv frontend-dist/* "${RELEASE_DIR}/frontend/"

# Set proper permissions
chmod -R 755 "${RELEASE_DIR}/frontend"
chown -R www-data:www-data "${RELEASE_DIR}/frontend" || true

# Create symlink to current release (if not already done by backend script)
if [ ! -L "${CURRENT_LINK}" ]; then
    ln -sfn "${RELEASE_DIR}" "${CURRENT_LINK}"
fi

echo "✅ Frontend deployment completed!"
echo "Frontend files: ${RELEASE_DIR}/frontend"

