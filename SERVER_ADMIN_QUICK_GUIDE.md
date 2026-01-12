# TST Server Deployment - Quick Guide for Server Admin

## What You Need to Do

1. **Download deployment package** from GitHub Actions
2. **Deploy code** to server
3. **Wait for developer** to copy database

## Step 1: Download Deployment Package

1. Go to: https://github.com/hands-on-leipzig/honr/actions
2. Find latest successful "Deploy to TST" run
3. Download `tst-deployment` artifact
4. Extract to get `deploy-tst.tar.gz`

## Step 2: Deploy Code to Server

```bash
# Upload to server
scp deploy-tst.tar.gz user@test.honr.hands-on-technology.org:/tmp/

# SSH to server
ssh user@test.honr.hands-on-technology.org

# Create directories
sudo mkdir -p /var/www/honr/{releases,shared,current}
sudo mkdir -p /var/www/honr/shared/storage/{app/public,framework/{cache,sessions,views},logs}
sudo mkdir -p /var/www/honr/shared/bootstrap/cache
sudo chown -R www-data:www-data /var/www/honr
sudo chmod -R 775 /var/www/honr/shared/storage

# Extract and deploy
cd /tmp
tar -xzf deploy-tst.tar.gz
cd deploy-tst
bash scripts/deploy-full.sh tst /var/www/honr

# Generate app key
cd /var/www/honr/current/backend
php artisan key:generate
```

## Step 3: Create Database

```bash
mysql -u root -p

CREATE DATABASE honr_tst CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'honr_tst_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON honr_tst.* TO 'honr_tst_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Step 4: Configure Environment

```bash
# Copy and edit environment file
sudo cp /tmp/deploy-tst/backend/env.tst.example /var/www/honr/shared/.env.tst
sudo nano /var/www/honr/shared/.env.tst
```

**Update these values:**
- `APP_URL=https://test.honr.hands-on-technology.org`
- `DB_DATABASE=honr_tst`
- `DB_USERNAME=honr_tst_user`
- `DB_PASSWORD=secure_password` (the one you created)
- `MAIL_*` settings (your email server)

## Step 5: Configure Web Server

See detailed instructions in `DEPLOYMENT_INSTRUCTIONS_FOR_SERVER_ADMIN.md` for Nginx/Apache configuration.

## Step 6: Share Information with Developer

Send developer:
- **SSH access**: `user@test.honr.hands-on-technology.org`
- **Database**: `honr_tst` / `honr_tst_user` / `[password]`
- **Status**: Code deployed ✅, Database ready (empty) ✅

**Then wait** - developer will copy database from their local machine.

## That's It!

Once developer copies database, test: https://test.honr.hands-on-technology.org
