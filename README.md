# HONR - Hands-on Recognition

A mobile-first web application for FIRST LEGO League (FLL) volunteers in the D-A-CH region to record their engagement, earn badges, and view leaderboards.

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue 3 + TypeScript + Tailwind CSS
- **Database**: MySQL/MariaDB 9.5+
- **Authentication**: Laravel Sanctum
- **State Management**: Pinia

## Quick Start

See [INSTALLATION.md](INSTALLATION.md) for detailed setup instructions.

### Prerequisites

- PHP 8.2+, Composer, Node.js 18+, MySQL 9.5+

### Installation

```bash
# Clone repository
git clone https://github.com/hands-on-leipzig/honr.git
cd honr

# Backend setup
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Frontend setup
cd ../frontend
npm install
echo "VITE_API_URL=http://localhost:8000/api" > .env.local

# Start servers
# Terminal 1: Backend
cd backend && php artisan serve

# Terminal 2: Frontend
cd frontend && npm run dev
```

## Documentation

- [INSTALLATION.md](INSTALLATION.md) - Complete installation guide
- [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment guide for DEV → TST → PRD
- [INITIAL_DEPLOYMENT.md](INITIAL_DEPLOYMENT.md) - **First-time deployment guide** (includes database copy)
- [DATABASE_DEPLOYMENT.md](DATABASE_DEPLOYMENT.md) - Database deployment and migration guide
- [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) - Database structure
- [ARCHITECTURE_NOTES.md](ARCHITECTURE_NOTES.md) - Architecture decisions
- [APP_UI_STRUCTURE.md](APP_UI_STRUCTURE.md) - UI/UX documentation
- [USER_FLOWS.md](USER_FLOWS.md) - User flow documentation
- [DECISIONS_CHECKLIST.md](DECISIONS_CHECKLIST.md) - Technical decisions

## Project Structure

```
honr/
├── backend/          # Laravel 11 API
├── frontend/         # Vue 3 SPA
├── scripts/          # Deployment scripts
├── .github/          # GitHub Actions workflows
└── docs/            # Documentation files
```

## Deployment

The project uses a three-environment deployment strategy:

- **DEV**: Local development on MacBook/developer machines
- **TST**: Hosted test/staging environment (auto-deploy from `main`/`master`)
- **PRD**: Hosted production environment (manual deploy from TST)

### Deployment Types

- **Initial Deployment**: Copies entire database from source (DEV → TST, TST → PRD)
- **Subsequent Deployments**: Only deploys code and runs migrations

See [INITIAL_DEPLOYMENT.md](INITIAL_DEPLOYMENT.md) for first-time deployment instructions.  
See [DEPLOYMENT.md](DEPLOYMENT.md) for ongoing deployment process.

## Development

- **Backend API**: http://localhost:8000/api
- **Frontend**: http://localhost:5173

## License

[To be determined]

