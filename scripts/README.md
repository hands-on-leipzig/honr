# Deployment Scripts

This directory contains deployment scripts for HOTR.

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
8. Runs database migrations
9. Creates symlink to current release

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

## Prerequisites

1. PHP 8.2+ with required extensions
2. Composer installed
3. MySQL/MariaDB database created
4. Web server (Nginx/Apache) configured
5. Proper file permissions set

## Notes

- Scripts use `set -e` to exit on any error
- Always backup database before running migrations in PRD
- Adjust service restart commands based on your server setup
- Modify paths and permissions as needed for your environment

