# HOTR - Hands-on Recognition

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
└── docs/            # Documentation files
```

## Development

- **Backend API**: http://localhost:8000/api
- **Frontend**: http://localhost:5173

## License

[To be determined]

