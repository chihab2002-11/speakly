# Speakly

Speakly is a Laravel 12 web application for managing academic workflows with role-based access, approvals, messaging, and timetable views for students and teachers.

## Tech Stack

- PHP 8.2+
- Laravel 12
- Livewire 4 + Flux UI
- Tailwind CSS 4 + Vite
- Fortify authentication
- Spatie Laravel Permission
- Pest testing

## Core Features

- Authentication with login, registration, email verification, and optional two-factor flow
- Account approval flow (`pending-approval`) for newly registered users
- Role-based authorization (`student`, `teacher`, `admin`, `secretary`)
- Timetable pages:
  - Student timetable: `timetable.index`
  - Teacher timetable: `timetable.teacher`
  - Secretary timetable: `secretary.timetable.index`
- Internal messaging and notifications

## Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- A supported database (MySQL, MariaDB, PostgreSQL, or SQLite)

## Quick Start

1. Install dependencies and bootstrap the app:

```bash
composer run setup
```

2. Start local development services:

```bash
composer run dev
```

This runs the Laravel app server, queue listener, and Vite dev server concurrently.

## Manual Setup (Alternative)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
```

## Build Frontend Assets

```bash
npm run build
```

## Testing

Run full test suite:

```bash
php artisan test
```

Run compact output:

```bash
php artisan test --compact
```

## Code Style

Format code with Laravel Pint:

```bash
vendor/bin/pint --format agent
```

Check style only:

```bash
vendor/bin/pint --test --format agent
```

## Useful Routes

- `/` -> visitor/home entry
- `/register-login` -> combined registration/login page
- `/dashboard` -> authenticated dashboard
- `/timetable` -> student timetable
- `/teacher/timetable` -> teacher timetable
- `/secretary/timetable` -> secretary timetable

## Project Structure (High Level)

- `app/` application logic (controllers, models, middleware, livewire)
- `routes/` web and settings routes
- `resources/views/` Blade templates
- `database/` migrations, factories, seeders
- `tests/` Pest feature and unit tests

## Notes

- If UI changes are not reflected, run `npm run dev` for local development or `npm run build` for production assets.
- Keep `.env` out of version control.