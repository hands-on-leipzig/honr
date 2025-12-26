# Deployment Scripts

This directory contains deployment scripts for HONR.

## Scripts

### `deploy-backend.sh`
Deploys the Laravel backend application.

**Usage:**
```bash
./deploy-backend.sh [environment] [deploy_path]
```

**Parameters:**
- `environment`: `tst` or `prd` (default: `tst`)
- `deploy_path`: Base deployment path (default: `/var/www/honr`)

**What it does:**
1. Creates release directory with timestamp
2. Sets up shared storage directories
3. Installs Composer dependencies
4. Links shared storage and cache directories
5. Copies environment file
6. Sets proper permissions
7. Runs Laravel optimization commands
8. **Runs database migrations** (only on subsequent deployments, skips on initial)
9. Creates symlink to current release

**Migration Behavior:**
- **Initial deployment**: Detects if `migrations` table exists. If not, skips migrations (database was copied).
- **Subsequent deployments**: Runs migrations automatically to update schema.

### `deploy-frontend.sh`
Deploys the Vue.js frontend application.

**Usage:**
```bash
./deploy-frontend.sh [environment] [deploy_path]
```

**Parameters:**
- `environment`: `tst` or `prd` (default: `tst`)
- `deploy_path`: Base deployment path (default: `/var/www/honr`)

**What it does:**
1. Creates frontend directory in release
2. Sets proper permissions
3. Creates symlink to current release

### `deploy-full.sh`
Combines backend and frontend deployment.

**Usage:**
```bash
./deploy-full.sh [environment] [deploy_path]
```

**Parameters:**
- `environment`: `tst` or `prd` (default: `tst`)
- `deploy_path`: Base deployment path (default: `/var/www/honr`)

### `initial-deployment.sh`
Complete initial deployment including database copy.

**Usage:**
```bash
./initial-deployment.sh [target_env] [deploy_path]
```

**Parameters:**
- `target_env`: `tst` or `prd` (required)
- `deploy_path`: Base deployment path (default: `/var/www/honr`)

**What it does:**
1. Deploys code (backend + frontend)
2. Copies database from source environment:
   - For TST: Copies from DEV (local)
   - For PRD: Copies from TST
3. Sets up initial configuration

**Use this for:**
- First deployment to TST
- First deployment to PRD

### `copy-database-tst.sh`
Copies database from DEV (local) to TST server.

**Usage:**
```bash
# Set environment variables first
export DEV_DB_NAME=honr
export DEV_DB_USER=root
export DEV_DB_PASS=
export TST_HOST=your-tst-server.com
export TST_SSH_USER=deploy
export TST_DB_NAME=honr_tst
export TST_DB_USER=honr_tst_user
export TST_DB_PASS=your_password

./copy-database-tst.sh
```

**What it does:**
1. Exports local DEV database
2. Uploads to TST server
3. Backs up existing TST database (if exists)
4. Imports DEV database to TST

### `copy-database-prd.sh`
Copies database from TST to PRD server.

**Usage:**
```bash
# Set environment variables first
export TST_HOST=your-tst-server.com
export TST_SSH_USER=deploy
export TST_DB_NAME=honr_tst
export TST_DB_USER=honr_tst_user
export TST_DB_PASS=tst_password
export PRD_HOST=your-prd-server.com
export PRD_SSH_USER=deploy
export PRD_DB_NAME=honr_prd
export PRD_DB_USER=honr_prd_user
export PRD_DB_PASS=prd_password

./copy-database-prd.sh
```

**What it does:**
1. Exports TST database
2. Backs up existing PRD database (if exists)
3. Imports TST database to PRD

**⚠️ Warning**: This replaces ALL data in PRD. Only use for initial deployment!

### `copy-database.sh`
Generic database copy script (supports dev→tst and tst→prd).

**Usage:**
```bash
./copy-database.sh [source_env] [target_env]
```

## Directory Structure

The scripts expect the following directory structure on the server:

```
/var/www/honr/
├── current/          -> symlink to current release
├── releases/         -> all release directories
│   ├── 20250101120000/
│   ├── 20250101130000/
│   └── ...
└── shared/           -> shared files across releases
    ├── .env.tst      -> TST environment file
    ├── .env.prd      -> PRD environment file
    ├── storage/      -> Laravel storage
    └── bootstrap/
        └── cache/    -> Laravel cache
```

## Deployment Workflow

### Initial Deployment

1. **Deploy code**:
   ```bash
   bash scripts/initial-deployment.sh tst /var/www/honr
   # or
   bash scripts/initial-deployment.sh prd /var/www/honr
   ```

   This will:
   - Deploy code
   - Copy database from source environment
   - Set up configuration

### Subsequent Deployments

1. **Deploy code only**:
   ```bash
   bash scripts/deploy-full.sh tst /var/www/honr
   # or
   bash scripts/deploy-full.sh prd /var/www/honr
   ```

   This will:
   - Deploy code
   - Run migrations (if needed)
   - **NOT** copy database

## Prerequisites

1. PHP 8.2+ with required extensions
2. Composer installed
3. MySQL/MariaDB database created
4. Web server (Nginx/Apache) configured
5. Proper file permissions set
6. SSH access to servers (for database copy scripts)

## Notes

- Scripts use `set -e` to exit on any error
- Always backup database before running migrations in PRD
- Database copy scripts require SSH access to target server
- Adjust service restart commands based on your server setup
- Modify paths and permissions as needed for your environment
- Initial deployments skip migrations (database already copied)
- Subsequent deployments run migrations automatically
