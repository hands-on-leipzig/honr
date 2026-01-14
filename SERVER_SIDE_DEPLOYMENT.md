# Server-Side Deployment Guide

## Overview

Instead of GitHub Actions deploying via SSH (which has authentication issues), the **server pulls code from GitHub** and deploys itself.

## Benefits

- ✅ No SSH key management in GitHub Actions
- ✅ Server controls its own deployment
- ✅ Simpler and more reliable
- ✅ Can be triggered manually or via webhook

## Setup on Server

### Step 1: Install Prerequisites

On the TST server, ensure you have:
- Git
- PHP 8.4+
- Composer
- Node.js 20+
- npm

### Step 2: Set Up Deployment Script

```bash
# On TST server
cd /var/www/honr
mkdir -p scripts
# Copy deploy-from-git.sh to server (or clone repo to get it)
chmod +x scripts/deploy-from-git.sh
```

### Step 3: Create Environment File

```bash
# On TST server
mkdir -p /var/www/honr/shared
cp /path/to/honr/backend/env.tst.example /var/www/honr/shared/.env.tst
nano /var/www/honr/shared/.env.tst
# Edit with your database credentials and settings
```

### Step 4: Run Deployment

```bash
# On TST server
cd /var/www/honr
bash scripts/deploy-from-git.sh tst /var/www/honr main
```

## Option 1: Manual Deployment (Recommended for now)

Server admin runs the script manually when needed:

```bash
ssh user@test.honr.hands-on-technology.org
cd /var/www/honr
bash scripts/deploy-from-git.sh tst /var/www/honr main
```

## Option 2: Automated via Webhook (Future)

1. Create a webhook endpoint on the server
2. GitHub Actions triggers webhook on push
3. Server pulls and deploys

## Option 3: Cron Job (Future)

```bash
# Check for updates every hour
0 * * * * cd /var/www/honr && bash scripts/deploy-from-git.sh tst /var/www/honr main
```

## GitHub Actions Workflow (Simplified)

The GitHub Actions workflow can now just:
1. Build and test code
2. Create artifact (for backup/reference)
3. Optionally trigger webhook

Or we can remove automatic deployment entirely and just do manual server-side deployment.

## For Initial Setup

1. Server admin runs deployment script manually
2. Database is copied separately (as before)
3. Application is ready

## For Subsequent Deployments

1. Developer pushes code to GitHub
2. Server admin runs: `bash scripts/deploy-from-git.sh tst /var/www/honr main`
3. Done!
