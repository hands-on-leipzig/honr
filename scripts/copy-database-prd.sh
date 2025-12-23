#!/bin/bash

# Copy Database from TST to PRD
# This script should be run from a machine with access to both TST and PRD servers
# It exports the TST database and imports it to PRD server

set -e

echo "📊 Copying database from TST to PRD..."

# Configuration - adjust these values
TST_HOST=${TST_HOST:-your-tst-server.com}
TST_SSH_USER=${TST_SSH_USER:-deploy}
TST_DB_NAME=${TST_DB_NAME:-honr_tst}
TST_DB_USER=${TST_DB_USER:-honr_tst_user}
TST_DB_PASS=${TST_DB_PASS:-}

PRD_HOST=${PRD_HOST:-your-prd-server.com}
PRD_SSH_USER=${PRD_SSH_USER:-deploy}
PRD_DB_NAME=${PRD_DB_NAME:-honr_prd}
PRD_DB_USER=${PRD_DB_USER:-honr_prd_user}
PRD_DB_PASS=${PRD_DB_PASS:-}

BACKUP_FILE="/tmp/honr_tst_to_prd_$(date +%Y%m%d_%H%M%S).sql"

echo "⚠️  WARNING: This will replace ALL data in PRD database!"
echo "⚠️  This should ONLY be done during initial deployment!"
echo ""
echo "This will:"
echo "   1. Export database from TST (${TST_HOST})"
echo "   2. Import to PRD server (${PRD_HOST})"
echo "   3. Replace all data in PRD database"
read -p "Type 'yes' to confirm: " confirm
if [ "$confirm" != "yes" ]; then
    echo "Cancelled."
    exit 0
fi

# Step 1: Export from TST server
echo "📤 Exporting database from TST..."
ssh "${TST_SSH_USER}@${TST_HOST}" << EOF
    mysqldump -u "$TST_DB_USER" -p"$TST_DB_PASS" "$TST_DB_NAME" > /tmp/$(basename "$BACKUP_FILE")
EOF

# Step 2: Copy from TST to local, then to PRD
echo "📤 Downloading from TST server..."
scp "${TST_SSH_USER}@${TST_HOST}:/tmp/$(basename "$BACKUP_FILE")" "$BACKUP_FILE"

# Step 3: Upload to PRD server
echo "📤 Uploading to PRD server..."
scp "$BACKUP_FILE" "${PRD_SSH_USER}@${PRD_HOST}:/tmp/"

# Step 4: Import on PRD server
echo "📥 Importing to PRD database..."
ssh "${PRD_SSH_USER}@${PRD_HOST}" << EOF
    # Backup existing PRD database first (if it exists)
    echo "Creating backup of existing PRD database..."
    mysqldump -u "$PRD_DB_USER" -p"$PRD_DB_PASS" "$PRD_DB_NAME" > /tmp/honr_prd_backup_\$(date +%Y%m%d_%H%M%S).sql 2>/dev/null || echo "No existing database to backup"
    
    # Import TST database
    echo "Importing TST database..."
    mysql -u "$PRD_DB_USER" -p"$PRD_DB_PASS" "$PRD_DB_NAME" < /tmp/$(basename "$BACKUP_FILE")
    
    # Clean up
    rm -f /tmp/$(basename "$BACKUP_FILE")
    
    echo "✅ Database imported successfully"
EOF

# Clean up local and TST files
rm -f "$BACKUP_FILE"
ssh "${TST_SSH_USER}@${TST_HOST}" "rm -f /tmp/$(basename "$BACKUP_FILE")"

echo "✅ Database copy from TST to PRD completed!"

