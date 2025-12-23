# Deployment Guide

This document describes the deployment process for HOTR across three environments: DEV, TST, and PRD.

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

### DEV → TST

1. **Development** happens on local DEV environment
2. **Commit and push** changes to `main` or `master` branch
3. **GitHub Actions** automatically triggers TST deployment workflow
4. **Deployment package** is created and uploaded as artifact
5. **Manual step**: Download artifact and deploy to TST server (or configure automatic deployment)

### TST → PRD

1. **Testing** completed in TST environment
2. **Manual trigger**: Go to GitHub Actions → "Deploy to PRD" → Run workflow
3. **Confirmation**: Type "deploy" to confirm production deployment
4. **Deployment package** is created and uploaded as artifact
5. **Manual step**: Download artifact and deploy to PRD server (or configure automatic deployment)

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

## Deployment Process

### Option 1: Manual Deployment (Current Setup)

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

## Troubleshooting

### Common Issues

1. **Permission errors**: Ensure `www-data` user owns storage directories
2. **Database connection**: Verify `.env` database credentials
3. **Cache issues**: Run `php artisan config:clear && php artisan cache:clear`
4. **Storage link**: Ensure `php artisan storage:link` has been run
5. **Frontend API URL**: Verify `VITE_API_URL` matches backend URL

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

