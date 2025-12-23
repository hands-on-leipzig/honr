#!/bin/bash

# Full Deployment Script
# Combines backend and frontend deployment

set -e

ENVIRONMENT=${1:-tst}
DEPLOY_PATH=${2:-/var/www/honr}

echo "🚀 Starting full deployment to ${ENVIRONMENT}..."

# Run backend deployment
bash "$(dirname "$0")/deploy-backend.sh" "${ENVIRONMENT}" "${DEPLOY_PATH}"

# Run frontend deployment
bash "$(dirname "$0")/deploy-frontend.sh" "${ENVIRONMENT}" "${DEPLOY_PATH}"

echo "✅ Full deployment completed!"

