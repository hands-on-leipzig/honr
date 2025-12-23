# Database Deployment Guide

This guide covers database deployment strategies for HOTR across DEV, TST, and PRD environments.

## Overview

HOTR uses MySQL/MariaDB 9.5+ with Laravel migrations for schema management.

## Database Strategy

### DEV (Development)
- **Database**: Local MySQL or SQLite
- **Purpose**: Development and testing
- **Migrations**: Run manually as needed
- **Seeders**: Use seeders for test data

### TST (Test/Staging)
- **Database**: Hosted MySQL/MariaDB (`honr_tst`)
- **Purpose**: Pre-production testing
- **Migrations**: Run automatically during deployment
- **Seeders**: Optional (for consistent test data)

### PRD (Production)
- **Database**: Hosted MySQL/MariaDB (`honr_prd`)
- **Purpose**: Live production data
- **Migrations**: Run automatically during deployment (with backups)
- **Seeders**: **NEVER** run seeders in production

## Initial Database Setup

### 1. Create Databases

```sql
-- TST Database
CREATE DATABASE honr_tst CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- PRD Database
CREATE DATABASE honr_prd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Create Database Users

```sql
-- TST User
CREATE USER 'honr_tst_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON honr_tst.* TO 'honr_tst_user'@'localhost';

-- PRD User
CREATE USER 'honr_prd_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON honr_prd.* TO 'honr_prd_user'@'localhost';

FLUSH PRIVILEGES;
```

### 3. Configure Environment Files

Update `.env` files on servers with database credentials:

**TST** (`/var/www/honr/shared/.env.tst`):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=honr_tst
DB_USERNAME=honr_tst_user
DB_PASSWORD=secure_password_here
```

**PRD** (`/var/www/honr/shared/.env.prd`):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=honr_prd
DB_USERNAME=honr_prd_user
DB_PASSWORD=secure_password_here
```

## Running Migrations

### During Deployment

Migrations run automatically during backend deployment via `deploy-backend.sh`:

```bash
php artisan migrate --force
```

The `--force` flag is required in non-interactive environments.

### Manual Migration

If you need to run migrations manually:

```bash
# TST
cd /var/www/honr/current/backend
cp ../shared/.env.tst .env
php artisan migrate --force

# PRD (with backup first!)
cd /var/www/honr/current/backend
cp ../shared/.env.prd .env
php artisan migrate --force
```

## Database Backups

### Before PRD Deployment

**Always backup PRD database before running migrations:**

```bash
# Create backup directory
mkdir -p /var/backups/honr

# Backup PRD database
mysqldump -u honr_prd_user -p honr_prd > /var/backups/honr/prd_$(date +%Y%m%d_%H%M%S).sql

# Compress backup
gzip /var/backups/honr/prd_*.sql
```

### Automated Backups

Set up automated daily backups:

```bash
# Add to crontab
0 2 * * * mysqldump -u honr_prd_user -p'password' honr_prd | gzip > /var/backups/honr/prd_$(date +\%Y\%m\%d).sql.gz
```

### Backup Retention

- **Daily backups**: Keep 7 days
- **Weekly backups**: Keep 4 weeks
- **Monthly backups**: Keep 12 months

## Restoring from Backup

### Full Database Restore

```bash
# Stop application (optional, recommended)
# systemctl stop php8.2-fpm

# Restore database
gunzip < /var/backups/honr/prd_20250101_020000.sql.gz | mysql -u honr_prd_user -p honr_prd

# Restart application
# systemctl start php8.2-fpm
```

### Partial Restore (Single Table)

```bash
# Extract specific table
gunzip < /var/backups/honr/prd_20250101_020000.sql.gz | \
  sed -n '/^CREATE TABLE.*users/,/^\/\*!40000 ALTER TABLE.*users/p' | \
  mysql -u honr_prd_user -p honr_prd
```

## Migration Best Practices

### 1. Test Migrations in TST First

Always test migrations in TST before deploying to PRD:
- Run migrations in TST
- Verify application functionality
- Check for data integrity issues
- Test rollback procedures

### 2. Review Migration Files

Before deploying, review migration files for:
- Data loss risks
- Long-running operations
- Index creation (may lock tables)
- Foreign key constraints

### 3. Use Transactions When Possible

Laravel migrations run in transactions by default (MySQL 5.7.8+). For older MySQL versions, wrap critical operations:

```php
DB::transaction(function () {
    // Migration operations
});
```

### 4. Handle Large Tables

For large tables, consider:
- Running migrations during low-traffic periods
- Using `--pretend` flag to preview changes
- Breaking large migrations into smaller steps
- Using `DB::statement()` for raw SQL when needed

## Seeding Data

### TST Environment

Seed initial data in TST for consistent testing:

```bash
cd /var/www/honr/current/backend
cp ../shared/.env.tst .env
php artisan db:seed
```

Or seed specific seeders:

```bash
php artisan db:seed --class=CountrySeeder
php artisan db:seed --class=RoleSeeder
```

### PRD Environment

**NEVER run seeders in production!** Production data should only come from:
- User registrations
- Admin CRUD operations
- Data imports (if needed)

## Data Migration Between Environments

### TST → PRD (Structure Only)

To sync database structure from TST to PRD:

```bash
# On TST server
mysqldump -u honr_tst_user -p --no-data honr_tst > structure.sql

# On PRD server (after backup!)
mysql -u honr_prd_user -p honr_prd < structure.sql
```

### Export/Import Specific Data

Export specific table data:

```bash
# Export users table (example)
mysqldump -u honr_tst_user -p honr_tst users > users.sql

# Import to PRD (careful!)
mysql -u honr_prd_user -p honr_prd < users.sql
```

## Monitoring

### Check Migration Status

```bash
cd /var/www/honr/current/backend
php artisan migrate:status
```

### View Migration History

```sql
SELECT * FROM migrations ORDER BY id DESC LIMIT 10;
```

### Check Database Size

```sql
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'honr_prd'
GROUP BY table_schema;
```

## Troubleshooting

### Migration Fails

1. **Check Laravel logs**:
```bash
tail -f /var/www/honr/shared/storage/logs/laravel.log
```

2. **Check database connection**:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

3. **Rollback last migration**:
```bash
php artisan migrate:rollback --step=1
```

4. **Reset and re-run** (TST only, never PRD):
```bash
php artisan migrate:fresh --seed
```

### Foreign Key Constraint Errors

If migrations fail due to foreign key constraints:
1. Check if referenced data exists
2. Temporarily disable foreign key checks (use with caution)
3. Fix data issues
4. Re-enable foreign key checks

### Lock Timeout Errors

For large tables, increase lock timeout:

```sql
SET innodb_lock_wait_timeout = 120;
```

## Security

1. **Strong passwords**: Use complex, unique passwords for each environment
2. **Limited privileges**: Database users should only have necessary privileges
3. **Backup encryption**: Encrypt database backups
4. **Access control**: Restrict database access to necessary IPs only
5. **Audit logs**: Enable MySQL audit logging for PRD

## Checklist

Before PRD deployment:
- [ ] Backup PRD database
- [ ] Test migrations in TST
- [ ] Review migration files
- [ ] Verify backup restoration works
- [ ] Schedule deployment during low-traffic period
- [ ] Have rollback plan ready
- [ ] Monitor logs during migration
- [ ] Verify application after migration

