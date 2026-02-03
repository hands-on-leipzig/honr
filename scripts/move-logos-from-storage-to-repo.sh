#!/usr/bin/env bash
# One-time: copy logo files from backend/public/storage/logos (gitignored)
# to backend/public/images/logos (in repo) so they deploy with the app.
# Run from repo root.

set -e
BACKEND="backend"
STORAGE_LOGS="${BACKEND}/storage/app/public/logos"
PUBLIC_IMAGES="${BACKEND}/public/images/logos"

if [ ! -d "$STORAGE_LOGS" ]; then
  echo "No storage logos dir at $STORAGE_LOGS (nothing to move)."
  exit 0
fi

mkdir -p "${PUBLIC_IMAGES}/roles"
if [ -d "${STORAGE_LOGS}/roles" ]; then
  cp -n "${STORAGE_LOGS}/roles/"* "${PUBLIC_IMAGES}/roles/" 2>/dev/null || true
  echo "Copied role logos to ${PUBLIC_IMAGES}/roles/"
fi

mkdir -p "${PUBLIC_IMAGES}/programs"
if [ -d "${STORAGE_LOGS}/first_programs" ]; then
  cp -n "${STORAGE_LOGS}/first_programs/"* "${PUBLIC_IMAGES}/programs/" 2>/dev/null || true
  echo "Copied program logos to ${PUBLIC_IMAGES}/programs/"
fi

mkdir -p "${PUBLIC_IMAGES}/seasons"
if [ -d "${STORAGE_LOGS}/seasons" ]; then
  cp -n "${STORAGE_LOGS}/seasons/"* "${PUBLIC_IMAGES}/seasons/" 2>/dev/null || true
  echo "Copied season logos to ${PUBLIC_IMAGES}/seasons/"
fi

echo "Done. Run migrations so logo_path in DB points to images/logos/... then commit the new files."
