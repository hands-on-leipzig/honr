# Import Database on TST Server

**For:** Server admin  
**Purpose:** Import the HONR database dump on the TST server so the application has data.

## What You Need

- The database file from the developer: `honr-database-for-tst-YYYYMMDD.sql.gz`
- SSH access to the TST server
- TST database credentials (database name, user, password)

## Import Steps

### 1. Copy the file to the server

From your machine (after you received the `.sql.gz` file):

```bash
scp honr-database-for-tst-*.sql.gz user@test.honr.hands-on-technology.org:/tmp/
```

Or use SFTP / any secure file transfer. Place the file in `/tmp/` on the server.

### 2. On the TST server: import the database

```bash
# SSH to server
ssh user@test.honr.hands-on-technology.org

# Go to temp
cd /tmp

# Decompress
gunzip honr-database-for-tst-*.sql.gz
# This produces honr-database-for-tst-YYYYMMDD.sql

# Import (replace USER, PASSWORD, and DATABASE with your TST DB credentials)
mysql -u honr_tst_user -p honr_tst < honr-database-for-tst-*.sql

# Optional: remove the dump from /tmp after success
rm -f /tmp/honr-database-for-tst-*.sql
```

### 3. Verify

```bash
# On server
mysql -u honr_tst_user -p honr_tst -e "SELECT COUNT(*) AS users FROM users; SELECT COUNT(*) AS engagements FROM engagements;"
```

You should see non-zero counts. Then reload https://test.honr.hands-on-technology.org — the app should show data.

## If you don’t have the DB credentials

They should be in the Laravel env on the server:

```bash
grep DB_ /var/www/honr/shared/.env.tst
# Or wherever the TST .env is stored
```

Use `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` for the `mysql` command above.

## Troubleshooting

- **Access denied:** Check user/password and that the user has rights on `honr_tst`.
- **Database doesn’t exist:** Create it first:
  ```sql
  CREATE DATABASE honr_tst CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  ```
- **Large file:** Import may take a minute. Wait for the `mysql` command to finish.
