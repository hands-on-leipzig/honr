# Server: Use MySQL Instead of SQLite

## Problem

Error on server:
```
Database file at path [.../database/database.sqlite] does not exist.
(Connection: sqlite, ...)
```

Laravel is using **SQLite** because the server `.env` does not set `DB_CONNECTION=mysql` (Laravel defaults to sqlite when not set).

## Fix on Server

Edit the `.env` file on the server (path may be something like `/usr/www/users/handsb/honr-test/.env` or your shared env path).

Ensure these lines exist and are correct:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=honr_tst
DB_USERNAME=honr_tst_user
DB_PASSWORD=your_actual_password
```

- **DB_CONNECTION=mysql** is required so Laravel uses MySQL instead of SQLite.
- Use the real TST database name, user, and password from your server/MySQL setup.

Then clear config cache on the server:

```bash
cd /usr/www/users/handsb/honr-test
php artisan config:clear
php artisan config:cache
```

After that, the app should use MySQL and the error should stop.

---

## "Access denied for user 'honr_tst_user'@'%' to database 'honr_test'" (1044)

The app is connecting as `honr_tst_user` but that user does **not** have permission to use the database named in `.env`.

### 1. Check the database name in `.env`

The example uses **`honr_tst`** (with **tst**), not `honr_test`:

```env
DB_DATABASE=honr_tst
```

If you have `DB_DATABASE=honr_test`, either:

- **Option A:** Change to the database that actually exists (often `honr_tst`):
  ```env
  DB_DATABASE=honr_tst
  ```
- **Option B:** Keep `honr_test` and create that database + grant access (see below).

### 2. Ensure the database exists and the user has access

On the server (as MySQL root or admin):

```sql
-- List databases (check exact name)
SHOW DATABASES;

-- If using honr_tst:
CREATE DATABASE IF NOT EXISTS honr_tst CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON honr_tst.* TO 'honr_tst_user'@'%';
FLUSH PRIVILEGES;

-- Or if you want to use honr_test:
CREATE DATABASE IF NOT EXISTS honr_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON honr_test.* TO 'honr_tst_user'@'%';
FLUSH PRIVILEGES;
```

Set `DB_DATABASE` in `.env` to the **exact** name you used (`honr_tst` or `honr_test`).

### 3. Reload Laravel config

```bash
cd ~/public_html/honr-test
php artisan config:clear
php artisan config:cache
```

**Summary:** 1044 = wrong database name in `.env` or the user has no GRANT on that database. Use the correct name and/or run the GRANT above.
