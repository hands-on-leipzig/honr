#!/bin/bash

# Copy Database from DEV to TST
# This script should be run from your local DEV machine
# It exports the local database and imports it to TST server

set -e

echo "📊 Copying database from DEV to TST..."

# Configuration - adjust these values
DEV_DB_NAME=${DEV_DB_NAME:-honr}
DEV_DB_USER=${DEV_DB_USER:-root}
DEV_DB_PASS=${DEV_DB_PASS:-}

TST_HOST=${TST_HOST:-your-tst-server.com}
TST_SSH_USER=${TST_SSH_USER:-deploy}
TST_DB_NAME=${TST_DB_NAME:-honr_tst}
TST_DB_USER=${TST_DB_USER:-honr_tst_user}
TST_DB_PASS=${TST_DB_PASS:-}

BACKUP_FILE="/tmp/honr_dev_to_tst_$(date +%Y%m%d_%H%M%S).sql"

echo "⚠️  This will:"
echo "   1. Export database from local DEV (${DEV_DB_NAME})"
echo "   2. Import to TST server (${TST_HOST})"
echo "   3. Replace all data in TST database"
read -p "Continue? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Cancelled."
    exit 0
fi

# Step 1: Export from local DEV
echo "📤 Exporting database from DEV..."
if [ -z "$DEV_DB_PASS" ]; then
    mysqldump -u "$DEV_DB_USER" "$DEV_DB_NAME" > "$BACKUP_FILE"
else
    mysqldump -u "$DEV_DB_USER" -p"$DEV_DB_PASS" "$DEV_DB_NAME" > "$BACKUP_FILE"
fi

echo "✅ Exported to ${BACKUP_FILE}"

# Step 2: Copy to TST server
echo "📤 Uploading to TST server..."
scp "$BACKUP_FILE" "${TST_SSH_USER}@${TST_HOST}:/tmp/"

# Step 3: Import on TST server
echo "📥 Importing to TST database..."
ssh "${TST_SSH_USER}@${TST_HOST}" << EOF
    # Backup existing TST database first
    echo "Creating backup of existing TST database..."
    mysqldump -u "$TST_DB_USER" -p"$TST_DB_PASS" "$TST_DB_NAME" > /tmp/honr_tst_backup_\$(date +%Y%m%d_%H%M%S).sql || true
    
    # Drop and recreate database (or just import)
    echo "Importing DEV database..."
    mysql -u "$TST_DB_USER" -p"$TST_DB_PASS" "$TST_DB_NAME" < /tmp/$(basename "$BACKUP_FILE")
    
    # Clean up
    rm -f /tmp/$(basename "$BACKUP_FILE")
    
    echo "✅ Database imported successfully"
EOF

# Clean up local file
rm -f "$BACKUP_FILE"

echo "✅ Database copy from DEV to TST completed!"

