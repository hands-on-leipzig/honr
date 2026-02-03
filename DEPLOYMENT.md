# Deployment Guide

This document describes the deployment process for HONR across three environments: DEV, TST, and PRD.

## Environments

### DEV (Development)
- **Location**: Local development on MacBook (and other developer machines)
- **Purpose**: Active development and testing
- **Database**: Local MySQL/SQLite
- **Deployment**: Manual (local setup)

### TST (Test/Staging)
- **Location**: Hosted test environment
- **Purpose**: Pre-production testing and QA
- **Database**: Hosted MySQL/MariaDB
- **Deployment**: Automated via GitHub Actions (from `main`/`master` branch)

### PRD (Production)
- **Location**: Hosted production environment
- **Purpose**: Live application for end users
- **Database**: Hosted MySQL/MariaDB
- **Deployment**: Manual trigger via GitHub Actions (from TST)

## Deployment Workflow

### Initial Deployment vs Subsequent Deployments

**Initial Deployment:**
- Copies entire database from source environment (DEV → TST, TST → PRD)
- Sets up complete environment from scratch
- Skips migrations (database already has schema and data)

**Subsequent Deployments:**
- Only deploys code changes
- Runs database migrations to update schema
- Does NOT copy database (preserves existing data)

### Initial Deployment: DEV → TST

1. **Prepare local DEV database** with initial data (seeders, test data, etc.)
2. **Commit and push** code to `main` or `master` branch
3. **GitHub Actions** creates deployment package
4. **Deploy code** to TST server
5. **Copy database** from DEV to TST using `scripts/copy-database-tst.sh`
6. **Verify** TST environment is working

### Initial Deployment: TST → PRD

1. **Test thoroughly** in TST environment
2. **GitHub Actions** creates deployment package (manual trigger)
3. **Deploy code** to PRD server
4. **Copy database** from TST to PRD using `scripts/copy-database-prd.sh`
5. **Verify** PRD environment is working

### Subsequent Deployments: DEV → TST

1. **Development** happens on local DEV environment
2. **Commit and push** changes to `main` or `master` branch
3. **GitHub Actions** automatically triggers TST deployment workflow
4. **Deployment package** is created and uploaded as artifact
5. **Deploy code** to TST server (migrations run automatically)
6. **No database copy** - only code and migrations

### Subsequent Deployments: TST → PRD

1. **Testing** completed in TST environment
2. **Manual trigger**: Go to GitHub Actions → "Deploy to PRD" → Run workflow
3. **Confirmation**: Type "deploy" to confirm production deployment
4. **Deployment package** is created and uploaded as artifact
5. **Deploy code** to PRD server (migrations run automatically)
6. **No database copy** - only code and migrations

## GitHub Actions Setup

### Required Secrets

Configure the following secrets in GitHub repository settings (Settings → Secrets and variables → Actions):

#### TST Environment Secrets
- `TST_HOST` - TST server hostname/IP
- `TST_USER` - SSH username for TST server
- `TST_SSH_KEY` - Private SSH key for TST server access
- `TST_DEPLOY_PATH` - Deployment path on TST server (e.g., `/var/www/honr`)
- `TST_API_URL` - TST API URL for frontend build (e.g., `https://tst.honr.example.com/api`)

#### PRD Environment Secrets
- `PRD_HOST` - PRD server hostname/IP
- `PRD_USER` - SSH username for PRD server
- `PRD_SSH_KEY` - Private SSH key for PRD server access
- `PRD_DEPLOY_PATH` - Deployment path on PRD server (e.g., `/var/www/honr`)
- `PRD_API_URL` - PRD API URL for frontend build (e.g., `https://honr.example.com/api`)

### Workflow Files

- `.github/workflows/deploy-tst.yml` - TST deployment (auto-triggered on push to main/master)
- `.github/workflows/deploy-prd.yml` - PRD deployment (manual trigger only)

## Server Setup

### Initial Server Configuration

1. **Create deployment directory structure**:
```bash
mkdir -p /var/www/honr/{releases,shared,current}
mkdir -p /var/www/honr/shared/storage/{app/public,framework/{cache,sessions,views},logs}
mkdir -p /var/www/honr/shared/bootstrap/cache
```

2. **Set permissions**:
```bash
chown -R www-data:www-data /var/www/honr
chmod -R 775 /var/www/honr/shared/storage
chmod -R 775 /var/www/honr/shared/bootstrap/cache
```

3. **Create environment files**:
```bash
# Copy and configure .env files
cp backend/.env.tst.example /var/www/honr/shared/.env.tst
cp backend/.env.prd.example /var/www/honr/shared/.env.prd
# Edit with actual values
nano /var/www/honr/shared/.env.tst
nano /var/www/honr/shared/.env.prd
```

4. **Generate application key**:
```bash
cd /var/www/honr/current/backend
php artisan key:generate
```

## Initial Deployment Process

### Initial Deployment: DEV → TST

For the **first time** deploying to TST:

1. **Prepare your local DEV database**:
   ```bash
   # Make sure your local database has the data you want to copy
   cd backend
   php artisan migrate
   php artisan db:seed  # Optional: seed initial data
   ```

2. **Deploy code**:
   ```bash
   # Download deployment package from GitHub Actions
   # Upload to TST server and extract
   scp deploy-tst.tar.gz user@tst-server:/tmp/
   ssh user@tst-server
   cd /tmp
   tar -xzf deploy-tst.tar.gz
   cd deploy-tst
   bash scripts/deploy-full.sh tst /var/www/honr
   ```

3. **Copy database from DEV to TST**:
   ```bash
   # From your local DEV machine
   cd /path/to/honr
   # Configure database credentials in the script or via environment variables
   export DEV_DB_NAME=honr
   export DEV_DB_USER=root
   export TST_HOST=your-tst-server.com
   export TST_SSH_USER=deploy
   export TST_DB_NAME=honr_tst
   export TST_DB_USER=honr_tst_user
   export TST_DB_PASS=your_password
   
   bash scripts/copy-database-tst.sh
   ```

   Or use the combined initial deployment script:
   ```bash
   bash scripts/initial-deployment.sh tst /var/www/honr
   ```

### Initial Deployment: TST → PRD

For the **first time** deploying to PRD:

1. **Deploy code**:
   ```bash
   # Download deployment package from GitHub Actions
   # Upload to PRD server and extract
   scp deploy-prd.tar.gz user@prd-server:/tmp/
   ssh user@prd-server
   cd /tmp
   tar -xzf deploy-prd.tar.gz
   cd deploy-prd
   bash scripts/deploy-full.sh prd /var/www/honr
   ```

2. **Copy database from TST to PRD**:
   ```bash
   # From a machine with access to both TST and PRD
   # Configure credentials
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
   
   bash scripts/copy-database-prd.sh
   ```

   Or use the combined initial deployment script:
   ```bash
   bash scripts/initial-deployment.sh prd /var/www/honr
   ```

## Deployment Process

### Option 1: Manual Deployment (Subsequent Deployments)

1. **GitHub Actions creates deployment package**
   - Workflow runs and creates a `.tar.gz` artifact
   - Download artifact from GitHub Actions run page

2. **Upload to server**:
```bash
# Upload artifact to server
scp deploy-tst.tar.gz user@tst-server:/tmp/

# SSH into server
ssh user@tst-server

# Extract and deploy
cd /tmp
tar -xzf deploy-tst.tar.gz
cd deploy-tst

# Run deployment script
bash scripts/deploy-full.sh tst /var/www/honr
```

### Option 2: Automated Deployment (Recommended)

Modify GitHub Actions workflows to automatically:
1. Transfer artifact to server via SCP
2. Execute deployment scripts remotely
3. Run database migrations
4. Restart services

See workflow files for SSH-based deployment examples.

## Database Deployment

### Initial Database Copy

**Important**: Initial deployments copy the entire database. Subsequent deployments only run migrations.

#### Copying Database: DEV → TST

Use the `copy-database-tst.sh` script from your local DEV machine:

```bash
# Set environment variables
export DEV_DB_NAME=honr
export DEV_DB_USER=root
export DEV_DB_PASS=  # Leave empty if no password
export TST_HOST=your-tst-server.com
export TST_SSH_USER=deploy
export TST_DB_NAME=honr_tst
export TST_DB_USER=honr_tst_user
export TST_DB_PASS=your_password

# Run script
bash scripts/copy-database-tst.sh
```

This will:
1. Export your local DEV database
2. Upload to TST server
3. Backup existing TST database (if exists)
4. Import DEV database to TST

#### Copying Database: TST → PRD

Use the `copy-database-prd.sh` script:

```bash
# Set environment variables
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

# Run script
bash scripts/copy-database-prd.sh
```

This will:
1. Export TST database
2. Backup existing PRD database (if exists)
3. Import TST database to PRD

### Initial Database Setup

1. **Create databases**:
```sql
CREATE DATABASE honr_tst CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE honr_prd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Create database users**:
```sql
CREATE USER 'honr_tst_user'@'localhost' IDENTIFIED BY 'secure_password';
CREATE USER 'honr_prd_user'@'localhost' IDENTIFIED BY 'secure_password';

GRANT ALL PRIVILEGES ON honr_tst.* TO 'honr_tst_user'@'localhost';
GRANT ALL PRIVILEGES ON honr_prd.* TO 'honr_prd_user'@'localhost';

FLUSH PRIVILEGES;
```

### Running Migrations

Migrations run automatically during deployment via `deploy-backend.sh`:
```bash
php artisan migrate --force
```

**Important**: 
- Always backup database before running migrations in PRD
- Test migrations in TST first
- Use `--force` flag only in non-interactive environments

### Database Seeding

Seed initial data (only for TST, not PRD):
```bash
cd /var/www/honr/current/backend
php artisan db:seed
```

### Database Backups

**Before PRD deployment**, always backup:
```bash
# Backup TST database
mysqldump -u honr_tst_user -p honr_tst > backup_tst_$(date +%Y%m%d_%H%M%S).sql

# Backup PRD database
mysqldump -u honr_prd_user -p honr_prd > backup_prd_$(date +%Y%m%d_%H%M%S).sql
```

## Frontend Deployment

The frontend is built during GitHub Actions workflow with environment-specific API URL:

- **TST**: Uses `VITE_API_URL` from `TST_API_URL` secret
- **PRD**: Uses `VITE_API_URL` from `PRD_API_URL` secret

Built files are in `frontend/dist/` and should be served as static files by your web server.

### Web Server Configuration

#### Nginx Example

```nginx
server {
    listen 80;
    server_name tst.honr.example.com;
    root /var/www/honr/current/frontend;
    index index.html;

    # Frontend SPA
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend API
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Laravel backend
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Storage files
    location /storage {
        alias /var/www/honr/shared/storage/app/public;
    }
}
```

## Rollback Procedure

If deployment fails, rollback to previous release:

```bash
cd /var/www/honr
# List releases
ls -la releases/

# Point current to previous release
ln -sfn releases/YYYYMMDDHHMMSS current

# Restart services
systemctl restart php8.2-fpm
systemctl restart nginx
```

## Monitoring and Verification

After deployment, verify:

1. **Frontend loads**: Visit the application URL
2. **API responds**: Check `/api/health` or similar endpoint
3. **Database connection**: Check Laravel logs
4. **File uploads**: Test logo/icon uploads
5. **Email sending**: Test registration/verification emails

## Icons and storage (badges, program/season logos)

- **Icons are not in the repo.** They live in `storage/app/public/logos/` (gitignored).
- **Role badges** (badges per role on /awards): Generated from code. Deployment runs `php artisan roles:generate-icons --generate-svgs` so role icons exist on the server. To regenerate manually: `php artisan roles:generate-icons --generate-svgs`.
- **Program and season icons**: No generator; they are uploaded via Admin (First Programs, Seasons). In TST/PRD, upload logos in Admin so program and season icons show on /awards.
- **Storage link**: Deployment runs `php artisan storage:link` so `public/storage` points to `storage/app/public`. The web server must serve `/storage` from that path (see WEB_SERVER_CONFIGURATION.md).

## Troubleshooting

### Common Issues

1. **Permission errors**: Ensure `www-data` user owns storage directories
2. **Database connection**: Verify `.env` database credentials
3. **Cache issues**: Run `php artisan config:clear && php artisan cache:clear`
4. **Storage link**: Ensure `php artisan storage:link` has been run
5. **Frontend API URL**: Verify `VITE_API_URL` matches backend URL
6. **Empty screens after login**: If you can log in but all screens show no data (or "Daten konnten nicht geladen werden"), the API may be returning HTML instead of JSON (e.g. wrong rewrite). Ensure all `/api` (and, if app is in a subpath, `/subpath/api`) requests go to Laravel, not to the SPA. In the browser DevTools → Network tab, check that requests to `/api/engagements`, `/api/users`, etc. return JSON (Content-Type: application/json), not HTML.

### Logs

Check Laravel logs:
```bash
tail -f /var/www/honr/shared/storage/logs/laravel.log
```

Check web server logs:
```bash
tail -f /var/log/nginx/error.log
```

## Security Considerations

1. **Environment files**: Never commit `.env` files to Git
2. **SSH keys**: Store securely in GitHub Secrets
3. **Database passwords**: Use strong, unique passwords
4. **File permissions**: Restrict access to sensitive files
5. **HTTPS**: Always use HTTPS in TST and PRD
6. **Backups**: Regular database and file backups

## Next Steps

1. Configure GitHub Secrets with actual server details
2. Set up initial server infrastructure
3. Configure web server (Nginx/Apache)
4. Test TST deployment workflow
5. Establish backup procedures
6. Document server-specific configurations

