# Concrete Next Steps

## Order of operations

1. **You: Deploy first** (so the latest code is on the server, including .env preservation and DB_CONNECTION=mysql fix).
2. **Admin: Create `.env` once** in the app directory and **import the database** you sent.
3. After that, the site works. Future deploys will keep .env and force MySQL automatically.

---

## 1. You: Deploy

- Push to `main` (or run the "Deploy Vue + Laravel App" workflow).
- Wait until the workflow finishes (health check may still fail until .env exists and DB is imported).

---

## 2. What to tell the admin

Send something like this (you can copy-paste and adjust):

---

**Subject: HONR test server – create .env and import database**

Please do these two things on the **test** server so the app can use MySQL and the data we sent.

### A) Create `.env` in the application directory

On the server, the app runs from a directory like:
`~/public_html/honr-test` or `/usr/www/users/handsb/honr-test` (or wherever `honr-test` is deployed).

In **that** directory (same folder as `artisan`), create a file named `.env` with at least these lines (fill in the real values):

```env
APP_NAME="HONR"
APP_ENV=testing
APP_KEY=
APP_DEBUG=false
APP_URL=https://test.honr.hands-on-technology.org

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=honr_tst
DB_USERNAME=honr_tst_user
DB_PASSWORD=<actual MySQL password for honr_tst>
```

**Important:**

- Set `DB_PASSWORD` to the real MySQL password for the TST database.
- Generate `APP_KEY`: in that directory run  
  `php artisan key:generate`  
  so Laravel writes a key into `.env`.

If you prefer a full template, you can copy from the repo file `backend/env.tst.example` and then set at least `APP_URL`, `DB_*`, and run `php artisan key:generate`.

### B) Import the database dump

Use the database file I sent you and the instructions in **DB_DATABASE_DEPLOYMENT_FOR_ADMIN.md**:

1. Copy the `.sql.gz` file to the server (e.g. `/tmp/`).
2. Decompress: `gunzip honr-database-for-tst-*.sql.gz`
3. Import: `mysql -u honr_tst_user -p honr_tst < honr-database-for-tst-*.sql`

After both steps, the site https://test.honr.hands-on-technology.org should load with data. Future deployments will keep this `.env` and force `DB_CONNECTION=mysql` automatically.

---

You can attach or link **DB_DATABASE_DEPLOYMENT_FOR_ADMIN.md** and **SERVER_ENV_MYSQL_FIX.md** so the admin has the full context.
