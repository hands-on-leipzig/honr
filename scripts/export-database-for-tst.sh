#!/bin/bash

# Export local DEV database for TST import
# Run on your MacBook. Send the generated .sql file to the server admin.

set -e

# Configuration - adjust if your local DB differs
DEV_DB_NAME=${DEV_DB_NAME:-honr}
DEV_DB_USER=${DEV_DB_USER:-root}
DEV_DB_PASS=${DEV_DB_PASS:-}

# Output file (current directory so you can find it easily)
OUTPUT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
OUTPUT_FILE="${OUTPUT_DIR}/honr-database-for-tst-$(date +%Y%m%d).sql"

echo "📊 Exporting local database for TST..."
echo "   Database: ${DEV_DB_NAME}"
echo "   Output:   ${OUTPUT_FILE}"
echo ""

# Export
if [ -z "$DEV_DB_PASS" ]; then
    mysqldump -u "$DEV_DB_USER" "$DEV_DB_NAME" > "$OUTPUT_FILE"
else
    mysqldump -u "$DEV_DB_USER" -p"$DEV_DB_PASS" "$DEV_DB_NAME" > "$OUTPUT_FILE"
fi

# Compress to save space when sending
gzip -f "$OUTPUT_FILE"
OUTPUT_FILE="${OUTPUT_FILE}.gz"

echo "✅ Export complete!"
echo ""
echo "File created: ${OUTPUT_FILE}"
echo ""
echo "Next steps:"
echo "  1. Send this file to the server admin (email, WeTransfer, shared drive, etc.)"
echo "  2. Share DB_DATABASE_DEPLOYMENT_FOR_ADMIN.md with the admin"
echo "  3. Admin will import it on the TST server"
echo ""
