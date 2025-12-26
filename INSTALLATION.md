# Installation Guide

This guide will help you set up the HONR project locally for development.

## Prerequisites

Before starting, ensure you have the following installed:

- **PHP 8.2+** with extensions: `pdo`, `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`
- **Composer** (PHP package manager)
- **Node.js 18+** and **npm**
- **MySQL/MariaDB 9.5+**
- **Git**

### Verify Prerequisites

```bash
php --version        # Should be 8.2 or higher
composer --version
node --version       # Should be 18 or higher
npm --version
mysql --version      # Should be 9.5 or higher
```

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/hands-on-leipzig/honr.git
cd honr
```

### 2. Backend Setup (Laravel)

```bash
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Setup

#### Create MySQL Database

```bash
# Connect to MySQL
mysql -u root

# Create database
CREATE DATABASE honr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### Configure Database Connection

Edit `backend/.env` and update the database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=honr
DB_USERNAME=root
DB_PASSWORD=
```

#### Run Migrations

```bash
cd backend
php artisan migrate
```

### 4. Frontend Setup (Vue)

```bash
cd frontend

# Install Node dependencies
npm install

# Create environment file
echo "VITE_API_URL=http://localhost:8000/api" > .env.local
```

### 5. Backend Environment Configuration

Edit `backend/.env` and add/update:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1:5173
SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
```

## Running the Application

### Start Backend (Laravel)

```bash
cd backend
php artisan serve
```

The API will be available at: `http://localhost:8000`

### Start Frontend (Vue)

In a new terminal:

```bash
cd frontend
npm run dev
```

The frontend will be available at: `http://localhost:5173`

## Quick Start Script

You can also use these commands to start both servers:

**Terminal 1 (Backend):**
```bash
cd backend && php artisan serve
```

**Terminal 2 (Frontend):**
```bash
cd frontend && npm run dev
```

## Troubleshooting

### MySQL Connection Issues

- Ensure MySQL is running: `brew services start mysql` (macOS) or `sudo systemctl start mysql` (Linux)
- Verify database credentials in `backend/.env`
- Check MySQL socket path if using non-standard installation

### Port Already in Use

- Backend (8000): Change port with `php artisan serve --port=8001`
- Frontend (5173): Vite will automatically use the next available port

### Permission Issues

- Ensure `storage/` and `bootstrap/cache/` directories are writable:
  ```bash
  cd backend
  chmod -R 775 storage bootstrap/cache
  ```

### Frontend Build Errors

- Clear node modules and reinstall:
  ```bash
  cd frontend
  rm -rf node_modules package-lock.json
  npm install
  ```

## Next Steps

After installation:

1. Review `DATABASE_SCHEMA.md` for database structure
2. Check `ARCHITECTURE_NOTES.md` for project architecture
3. See `APP_UI_STRUCTURE.md` for UI/UX documentation
4. Review `DECISIONS_CHECKLIST.md` for technical decisions

## Development

- **Backend API**: `http://localhost:8000/api`
- **Frontend**: `http://localhost:5173`
- **API Documentation**: See `routes/api.php` for available endpoints

## Support

For help, feedback, or ideas, see the Contact/Support link in the Settings screen of the application.





