# Deployment Quick Reference

## GitHub Secrets Setup

Configure in: **Settings → Secrets and variables → Actions**

### TST Secrets
```
TST_HOST=your-tst-server.com
TST_USER=deploy
TST_SSH_KEY=<private-ssh-key>
TST_DEPLOY_PATH=/var/www/honr
TST_API_URL=https://tst.honr.example.com/api
```

### PRD Secrets
```
PRD_HOST=your-prd-server.com
PRD_USER=deploy
PRD_SSH_KEY=<private-ssh-key>
PRD_DEPLOY_PATH=/var/www/honr
PRD_API_URL=https://honr.example.com/api
```

## Deployment Workflows

### DEV → TST
1. Push to `main` or `master` branch
2. GitHub Actions automatically triggers
3. Download artifact from Actions page
4. Upload to TST server and run deployment script

### TST → PRD
1. Go to **Actions** → **Deploy to PRD**
2. Click **Run workflow**
3. Type `deploy` to confirm
4. Download artifact from Actions page
5. Upload to PRD server and run deployment script

## Server Commands

### Initial Setup
```bash
# Create directory structure
mkdir -p /var/www/honr/{releases,shared,current}
mkdir -p /var/www/honr/shared/storage/{app/public,framework/{cache,sessions,views},logs}
mkdir -p /var/www/honr/shared/bootstrap/cache

# Set permissions
chown -R www-data:www-data /var/www/honr
chmod -R 775 /var/www/honr/shared/storage
```

### Deploy
```bash
# Extract deployment package
tar -xzf deploy-tst.tar.gz
cd deploy-tst

# Run deployment
bash scripts/deploy-full.sh tst /var/www/honr
```

### Database Backup (Before PRD)
```bash
mysqldump -u honr_prd_user -p honr_prd | gzip > backup_prd_$(date +%Y%m%d_%H%M%S).sql.gz
```

### Rollback
```bash
cd /var/www/honr
ln -sfn releases/YYYYMMDDHHMMSS current
systemctl restart php8.2-fpm nginx
```

## Environment Files

Copy example files and configure:
- `backend/env.tst.example` → `/var/www/honr/shared/.env.tst`
- `backend/env.prd.example` → `/var/www/honr/shared/.env.prd`

## Quick Checklist

### Before TST Deployment
- [ ] Code tested locally
- [ ] Committed and pushed to `main`/`master`
- [ ] GitHub Actions workflow runs successfully

### Before PRD Deployment
- [ ] TST tested and verified
- [ ] PRD database backed up
- [ ] Deployment window scheduled
- [ ] Rollback plan ready

### After Deployment
- [ ] Frontend loads correctly
- [ ] API responds
- [ ] Database migrations successful
- [ ] File uploads work
- [ ] Email sending works

