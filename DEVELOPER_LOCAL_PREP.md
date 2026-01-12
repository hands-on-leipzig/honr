# Local Preparation - Quick Guide

## What You Need to Do

1. **Verify local database** is ready
2. **Wait for server admin** to deploy code and create database
3. **Copy database** from local DEV to TST server

## Step 1: Verify Local Database

```bash
cd /Users/thomas/GitHub/honr/backend
php artisan migrate:status  # Should show all migrations ran
php artisan tinker
>>> DB::table('users')->count();  # Should return > 0
>>> exit
```

## Step 2: Wait for Server Admin

Server admin will:
- Deploy code to TST server
- Create database `honr_tst`
- Share credentials with you

## Step 3: Get Credentials from Admin

You need:
- **SSH**: `user@test.honr.hands-on-technology.org`
- **Database**: `honr_tst` / `honr_tst_user` / `[password]`

## Step 4: Copy Database

```bash
cd /Users/thomas/GitHub/honr

# Set your local database credentials
export DEV_DB_NAME=honr
export DEV_DB_USER=root
export DEV_DB_PASS=  # Leave empty if no password

# Set TST credentials (from admin)
export TST_HOST=test.honr.hands-on-technology.org
export TST_SSH_USER=deploy  # From admin
export TST_DB_NAME=honr_tst
export TST_DB_USER=honr_tst_user
export TST_DB_PASS=password_from_admin  # From admin

# Run copy script
bash scripts/copy-database-tst.sh
```

## Step 5: Verify

Visit: https://test.honr.hands-on-technology.org

Should work now! 🎉
