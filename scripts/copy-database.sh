#!/bin/bash

# Database Copy Script
# Copies database from source environment to target environment
# Usage: ./copy-database.sh [source_env] [target_env]

set -e

SOURCE_ENV=${1:-dev}
TARGET_ENV=${2:-tst}

if [ "$SOURCE_ENV" = "$TARGET_ENV" ]; then
    echo "❌ Error: Source and target environments cannot be the same"
    exit 1
fi

echo "📊 Copying database from ${SOURCE_ENV} to ${TARGET_ENV}..."

# Load database credentials from environment or config
# This assumes you have .env files or can pass credentials
# Adjust based on your setup

# For DEV → TST
if [ "$SOURCE_ENV" = "dev" ] && [ "$TARGET_ENV" = "tst" ]; then
    echo "⚠️  This will copy your local DEV database to TST server"
    echo "⚠️  Make sure you have SSH access to TST server configured"
    read -p "Continue? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        echo "Cancelled."
        exit 0
    fi
    
    # Export from local DEV
    echo "📤 Exporting database from DEV..."
    DEV_DB_NAME=${DEV_DB_NAME:-honr}
    DEV_DB_USER=${DEV_DB_USER:-root}
    DEV_DB_PASS=${DEV_DB_PASS:-}
    
    if [ -z "$DEV_DB_PASS" ]; then
        mysqldump -u "$DEV_DB_USER" "$DEV_DB_NAME" > /tmp/honr_dev_export.sql
    else
        mysqldump -u "$DEV_DB_USER" -p"$DEV_DB_PASS" "$DEV_DB_NAME" > /tmp/honr_dev_export.sql
    fi
    
    # Import to TST (requires TST credentials)
    echo "📥 Importing database to TST..."
    echo "⚠️  You need to provide TST server details and credentials"
    echo "Run this on TST server or via SSH:"
    echo "  mysql -u honr_tst_user -p honr_tst < /tmp/honr_dev_export.sql"
    
# For TST → PRD
elif [ "$SOURCE_ENV" = "tst" ] && [ "$TARGET_ENV" = "prd" ]; then
    echo "⚠️  This will copy TST database to PRD server"
    echo "⚠️  This should only be done during initial deployment!"
    read -p "Continue? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        echo "Cancelled."
        exit 0
    fi
    
    # Export from TST
    echo "📤 Exporting database from TST..."
    TST_DB_NAME=${TST_DB_NAME:-honr_tst}
    TST_DB_USER=${TST_DB_USER:-honr_tst_user}
    TST_DB_PASS=${TST_DB_PASS:-}
    TST_HOST=${TST_HOST:-localhost}
    
    if [ -z "$TST_DB_PASS" ]; then
        mysqldump -h "$TST_HOST" -u "$TST_DB_USER" "$TST_DB_NAME" > /tmp/honr_tst_export.sql
    else
        mysqldump -h "$TST_HOST" -u "$TST_DB_USER" -p"$TST_DB_PASS" "$TST_DB_NAME" > /tmp/honr_tst_export.sql
    fi
    
    # Import to PRD
    echo "📥 Importing database to PRD..."
    PRD_DB_NAME=${PRD_DB_NAME:-honr_prd}
    PRD_DB_USER=${PRD_DB_USER:-honr_prd_user}
    PRD_DB_PASS=${PRD_DB_PASS:-}
    PRD_HOST=${PRD_HOST:-localhost}
    
    if [ -z "$PRD_DB_PASS" ]; then
        mysql -h "$PRD_HOST" -u "$PRD_DB_USER" "$PRD_DB_NAME" < /tmp/honr_tst_export.sql
    else
        mysql -h "$PRD_HOST" -u "$PRD_DB_USER" -p"$PRD_DB_PASS" "$PRD_DB_NAME" < /tmp/honr_tst_export.sql
    fi
    
    echo "✅ Database copied from TST to PRD"
else
    echo "❌ Unsupported copy operation: ${SOURCE_ENV} → ${TARGET_ENV}"
    echo "Supported: dev → tst, tst → prd"
    exit 1
fi

echo "✅ Database copy completed!"

