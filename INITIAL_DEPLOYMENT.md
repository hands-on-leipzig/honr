# Initial Deployment Guide

This guide covers the **first-time deployment** process for HOTR, which includes copying the entire database from the source environment.

## Overview

**Initial deployment** differs from subsequent deployments:

- ✅ **Copies entire database** (schema + data) from source environment
- ✅ **Deploys code** (backend + frontend)
- ❌ **Skips migrations** (database already has complete schema)

**Subsequent deployments** only:
- ✅ Deploy code
- ✅ Run migrations (to update schema)
- ❌ Do NOT copy database (preserves existing data)

## Initial Deployment: DEV → TST

### Prerequisites

1. **Local DEV environment** set up and working
2. **Local database** has the data you want to copy to TST
3. **TST server** accessible via SSH
4. **TST database** created (empty or will be replaced)

### Step 1: Prepare Local DEV Database

Make sure your local DEV database has the data you want:

```bash
cd backend

# Run migrations (if not already done)
php artisan migrate

# Optionally seed initial data
php artisan db:seed

# Verify your database
mysql -u root honr -e "SELECT COUNT(*) as user_count FROM users;"
```

### Step 2: Deploy Code to TST

1. **Push code to GitHub**:
   ```bash
   git add .
   git commit -m "Initial deployment to TST"
   git push origin main
   ```

2. **Download deployment package** from GitHub Actions

3. **Upload and deploy**:
   ```bash
   # Upload to TST server
   scp deploy-tst.tar.gz deploy@tst-server:/tmp/
   
   # SSH to TST server
   ssh deploy@tst-server
   
   # Extract and deploy
   cd /tmp
   tar -xzf deploy-tst.tar.gz
   cd deploy-tst
   bash scripts/deploy-full.sh tst /var/www/honr
   ```

### Step 3: Copy Database from DEV to TST

From your **local DEV machine**:

```bash
cd /path/to/honr

# Set environment variables
export DEV_DB_NAME=honr
export DEV_DB_USER=root
export DEV_DB_PASS=  # Leave empty if no password

export TST_HOST=your-tst-server.com
export TST_SSH_USER=deploy
export TST_DB_NAME=honr_tst
export TST_DB_USER=honr_tst_user
export TST_DB_PASS=your_tst_password

# Run database copy script
bash scripts/copy-database-tst.sh
```

**Or use the combined script**:
```bash
bash scripts/initial-deployment.sh tst /var/www/honr
```

### Step 4: Verify TST Deployment

1. **Check application**: Visit `https://tst.honr.example.com`
2. **Verify database**: Check that data from DEV is present
3. **Test functionality**: Login, create engagement, etc.

## Initial Deployment: TST → PRD

### Prerequisites

1. **TST environment** tested and verified
2. **TST database** has the data you want in production
3. **PRD server** accessible via SSH
4. **PRD database** created (empty or will be replaced)

### Step 1: Final Testing in TST

Before deploying to PRD, ensure TST is fully tested:

- [ ] All features working
- [ ] Data integrity verified
- [ ] Performance acceptable
- [ ] No critical bugs

### Step 2: Deploy Code to PRD

1. **Trigger PRD deployment** in GitHub Actions:
   - Go to **Actions** → **Deploy to PRD**
   - Click **Run workflow**
   - Type `deploy` to confirm

2. **Download deployment package** from GitHub Actions

3. **Upload and deploy**:
   ```bash
   # Upload to PRD server
   scp deploy-prd.tar.gz deploy@prd-server:/tmp/
   
   # SSH to PRD server
   ssh deploy@prd-server
   
   # Extract and deploy
   cd /tmp
   tar -xzf deploy-prd.tar.gz
   cd deploy-prd
   bash scripts/deploy-full.sh prd /var/www/honr
   ```

### Step 3: Copy Database from TST to PRD

From a **machine with access to both TST and PRD**:

```bash
cd /path/to/honr

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

# Run database copy script
bash scripts/copy-database-prd.sh
```

**⚠️ WARNING**: This replaces ALL data in PRD database!

**Or use the combined script**:
```bash
bash scripts/initial-deployment.sh prd /var/www/honr
```

### Step 4: Verify PRD Deployment

1. **Check application**: Visit `https://honr.example.com`
2. **Verify database**: Check that data from TST is present
3. **Test critical functionality**: Login, core features
4. **Monitor logs**: Check for errors

## Troubleshooting

### Database Copy Fails

**Issue**: SSH connection fails
- **Solution**: Verify SSH keys and credentials
- Check `TST_SSH_USER` and `PRD_SSH_USER` are correct

**Issue**: Database connection fails
- **Solution**: Verify database credentials in environment variables
- Check database user has proper permissions

**Issue**: Import fails due to foreign key constraints
- **Solution**: Temporarily disable foreign key checks:
  ```sql
  SET FOREIGN_KEY_CHECKS=0;
  -- Import database
  SET FOREIGN_KEY_CHECKS=1;
  ```

### Migrations Run on Initial Deployment

**Issue**: Migrations run even though database was copied
- **Solution**: This is expected if the `migrations` table exists in the copied database
- The script checks for the `migrations` table - if it exists, migrations run
- This is safe - migrations will only add new tables/columns, not modify existing data

### Application Not Working After Deployment

1. **Check Laravel logs**:
   ```bash
   tail -f /var/www/honr/shared/storage/logs/laravel.log
   ```

2. **Verify environment file**:
   ```bash
   cat /var/www/honr/shared/.env.tst
   # or
   cat /var/www/honr/shared/.env.prd
   ```

3. **Check database connection**:
   ```bash
   cd /var/www/honr/current/backend
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

4. **Clear caches**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   ```

## Next Steps

After initial deployment:

1. **Set up monitoring** for PRD
2. **Configure backups** (automated daily backups)
3. **Document server-specific configurations**
4. **Train team** on deployment process
5. **Plan subsequent deployments** (code-only, with migrations)

## Checklist

### Initial TST Deployment
- [ ] Local DEV database prepared
- [ ] Code pushed to GitHub
- [ ] Deployment package downloaded
- [ ] Code deployed to TST server
- [ ] Database copied from DEV to TST
- [ ] Application verified in TST
- [ ] Functionality tested

### Initial PRD Deployment
- [ ] TST fully tested
- [ ] PRD deployment package ready
- [ ] Code deployed to PRD server
- [ ] PRD database backed up (if exists)
- [ ] Database copied from TST to PRD
- [ ] Application verified in PRD
- [ ] Critical functionality tested
- [ ] Monitoring configured

