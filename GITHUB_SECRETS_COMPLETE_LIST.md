# Complete GitHub Secrets List

## Required Secrets for TST Deployment

### ✅ Already Configured (You mentioned these are done)
- `TST_HOST` - TST server hostname/IP
- `TST_USER` - SSH username for TST server
- `TST_SSH_KEY` - Private SSH key for TST server access
- `TST_DEPLOY_PATH` - Deployment path on TST server (e.g., `/var/www/honr`)
- `TST_API_URL` - TST API URL for frontend build (e.g., `https://test.honr.hands-on-technology.org/api`)

### ⚠️ NEW - Required for Current Workflow
- `SSH_KNOWN_HOST` - SSH known hosts entry (prevents host key verification issues)
  - **How to get**: `ssh-keyscan -H test.honr.hands-on-technology.org 2>&1 | grep -v "^#"`
  - **What to use**: Copy ALL key lines (RSA, ED25519, etc.)
  - See `SSH_KNOWN_HOST_SETUP.md` for details

## Required Secrets for PRD Deployment

### Currently Required (PRD workflow still uses old approach)
- `PRD_HOST` - PRD server hostname/IP
- `PRD_USER` - SSH username for PRD server
- `PRD_SSH_KEY` - Private SSH key for PRD server access
- `PRD_DEPLOY_PATH` - Deployment path on PRD server (e.g., `/var/www/honr`)
- `PRD_API_URL` - PRD API URL for frontend build (e.g., `https://honr.hands-on-technology.org/api`)

### Note: PRD workflow will need `SSH_KNOWN_HOST` too when updated

## Complete Checklist

### TST Environment (6 secrets)
- [x] `TST_HOST` ✅
- [x] `TST_USER` ✅
- [x] `TST_SSH_KEY` ✅
- [x] `TST_DEPLOY_PATH` ✅
- [x] `TST_API_URL` ✅
- [ ] `SSH_KNOWN_HOST` ⚠️ **ADD THIS ONE**

### PRD Environment (5 secrets - will need 6 when updated)
- [ ] `PRD_HOST`
- [ ] `PRD_USER`
- [ ] `PRD_SSH_KEY`
- [ ] `PRD_DEPLOY_PATH`
- [ ] `PRD_API_URL`
- [ ] `SSH_KNOWN_HOST` (for PRD server, when PRD workflow is updated)

## How to Add Secrets

1. Go to: https://github.com/hands-on-leipzig/honr/settings/secrets/actions
2. Click **"New repository secret"**
3. Enter the secret name and value
4. Click **"Add secret"**

## Secret Values Reference

### TST Environment
```
TST_HOST=test.honr.hands-on-technology.org
TST_USER=deploy
TST_SSH_KEY=[Complete private SSH key with BEGIN/END lines]
TST_DEPLOY_PATH=/var/www/honr
TST_API_URL=https://test.honr.hands-on-technology.org/api
SSH_KNOWN_HOST=[Output from: ssh-keyscan -H test.honr.hands-on-technology.org 2>&1 | grep -v "^#"]
```

### PRD Environment (when ready)
```
PRD_HOST=honr.hands-on-technology.org
PRD_USER=deploy
PRD_SSH_KEY=[Complete private SSH key with BEGIN/END lines]
PRD_DEPLOY_PATH=/var/www/honr
PRD_API_URL=https://honr.hands-on-technology.org/api
SSH_KNOWN_HOST=[Output from: ssh-keyscan -H honr.hands-on-technology.org 2>&1 | grep -v "^#"]
```

## Quick Setup Command

For TST `SSH_KNOWN_HOST`:
```bash
ssh-keyscan -H test.honr.hands-on-technology.org 2>&1 | grep -v "^#"
```

Copy the entire output and paste into GitHub Secret `SSH_KNOWN_HOST`.

## Verification

After adding all secrets, the TST deployment workflow should:
1. ✅ Build successfully
2. ✅ Rsync files to server
3. ✅ Run deployment script on server
4. ✅ Complete without SSH errors
