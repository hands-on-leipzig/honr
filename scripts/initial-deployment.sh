#!/bin/bash

# Initial Deployment Script
# This script handles the complete initial deployment including database copy
# Usage: ./initial-deployment.sh [target_env] [deploy_path]

set -e

TARGET_ENV=${1:-tst}
DEPLOY_PATH=${2:-/var/www/honr}

if [ "$TARGET_ENV" != "tst" ] && [ "$TARGET_ENV" != "prd" ]; then
    echo "❌ Error: Target environment must be 'tst' or 'prd'"
    exit 1
fi

echo "🚀 Starting INITIAL deployment to ${TARGET_ENV}..."
echo ""
echo "This will:"
echo "  1. Deploy code (backend + frontend)"
echo "  2. Copy database from source environment"
echo "  3. Set up initial configuration"
echo ""

if [ "$TARGET_ENV" = "tst" ]; then
    echo "Source: DEV (local)"
    echo "Target: TST"
    read -p "Continue? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        echo "Cancelled."
        exit 0
    fi
    
    # Step 1: Deploy code
    echo ""
    echo "📦 Step 1: Deploying code..."
    bash "$(dirname "$0")/deploy-full.sh" "${TARGET_ENV}" "${DEPLOY_PATH}"
    
    # Step 2: Copy database
    echo ""
    echo "📊 Step 2: Copying database from DEV to TST..."
    bash "$(dirname "$0")/copy-database-tst.sh"
    
elif [ "$TARGET_ENV" = "prd" ]; then
    echo "Source: TST"
    echo "Target: PRD"
    echo "⚠️  WARNING: This will replace all data in PRD!"
    read -p "Type 'yes' to confirm: " confirm
    if [ "$confirm" != "yes" ]; then
        echo "Cancelled."
        exit 0
    fi
    
    # Step 1: Deploy code
    echo ""
    echo "📦 Step 1: Deploying code..."
    bash "$(dirname "$0")/deploy-full.sh" "${TARGET_ENV}" "${DEPLOY_PATH}"
    
    # Step 2: Copy database
    echo ""
    echo "📊 Step 2: Copying database from TST to PRD..."
    bash "$(dirname "$0")/copy-database-prd.sh"
fi

echo ""
echo "✅ Initial deployment to ${TARGET_ENV} completed!"
echo ""
echo "Next steps:"
echo "  1. Verify application is accessible"
echo "  2. Test key functionality"
echo "  3. Check database integrity"
echo "  4. Verify file uploads work"

