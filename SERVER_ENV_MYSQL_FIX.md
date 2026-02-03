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
