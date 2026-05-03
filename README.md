# Speakly / School Management System

Speakly is a Laravel-based school management system designed for a language school or educational institution. It centralizes academic, administrative, financial, and communication workflows into role-based dashboards for admins, secretaries, teachers, students, and parents.

The project was built as a final-year computer science project and demonstrates MVC architecture, authentication, role-based access control, database design, Blade/Tailwind interfaces, testing, and deployment preparation.

## Project Overview

Many small schools manage students, schedules, tuition payments, attendance, learning materials, and communication manually or across disconnected tools. This system solves that by providing one web platform where each user role only sees the tools relevant to them.

The application supports:

- Centralized registration and account approval.
- Role-based access for admin, secretary, teacher, student, and parent users.
- Language program, course, group, room, and schedule management.
- Student enrollment, attendance, homework/resources, messages, and notifications.
- Parent-child access so parents can follow linked children.
- Student and parent financial views, tuition payments, scholarship/discount workflows, and employee payment tracking.

## Main Features

### Admin

- Admin dashboard and role-scoped navigation.
- Account approval/rejection workflow for new users.
- Employee management for teachers and secretaries.
- Employee payment management with teacher/secretary payment records.
- Language program and course management.
- Classroom and schedule management.
- Timetable hub for administration.
- Notification center and messaging.
- Spatie permission-backed authorization for protected operations.

### Secretary

- Secretary dashboard.
- Registration management and account operations.
- Student payment recording and financial tracking.
- Group/class management.
- Teacher assignment to groups.
- Student enrollment and removal from groups.
- Student and teacher search endpoints for group operations.
- Timetable browsing.
- Publish notifications to selected audiences.
- Messaging, notifications, settings, and secretary payment page.

### Teacher

- Teacher dashboard with class/resource/message information.
- Teacher timetable.
- Attendance and evaluation recording for assigned classes.
- Upload and manage homework and course resources.
- Resource download tracking.
- Messaging and notifications.
- Teacher "My Payments" page and PDF receipt view.

### Student

- Student dashboard.
- Academic page with attendance/evaluation information.
- Timetable and enrolled class context.
- Learning materials page for teacher-uploaded resources/homework.
- Financial page for eligible students.
- Tuition payment receipt PDF route.
- Scholarship/discount activation where eligible.
- Messaging, notifications, profile settings, and password management.

### Parent

- Parent dashboard for linked children.
- Parent financial page with child invoices, payment history, and receipts.
- Child portal for dashboard, academic information, materials, messages, settings, password, and notifications.
- Scholarship/discount activation for eligible children.
- Parent-specific messaging and notification center.

### Public Visitor

- Public landing page showing active language programs.
- Course/program display with registration entry point.
- Review/testimonial display and visitor review voting.

## Technology Stack

### Backend

- PHP `^8.2` from `composer.json`; Docker deployment uses PHP `8.4`.
- Laravel `12`.
- MySQL database.
- Eloquent ORM models and relationships.
- Blade templates and Laravel controllers.
- Laravel validation, middleware, notifications, sessions, cache, and migrations.
- Database-backed notifications, cache, sessions, and queue configuration.

### Authentication and Authorization

- Laravel Fortify for authentication, registration, password reset, email verification, and two-factor authentication.
- Laravel Sanctum for API authentication.
- Spatie Laravel Permission for roles and permissions.
- Custom approval middleware that blocks unapproved accounts from protected dashboards.

### Frontend

- Blade views grouped by role.
- Tailwind CSS `4`.
- Flux UI and Livewire components for starter-kit/auth/settings UI.
- Vite for asset bundling.
- JavaScript entry point in `resources/js/app.js`.

### Testing

- Pest `4` and PHPUnit `12`.
- Laravel feature tests and model factories.
- Tests cover authentication, authorization, approvals, dashboards, financial pages, notifications, employee payments, secretary operations, teacher attendance/resources, timetables, messaging, deployment checks, and seeders.

### Deployment

- Root `Dockerfile` with multi-stage Composer and Node/Vite build.
- `docker-entrypoint.sh` waits for the database, runs migrations, clears caches, and starts Laravel through PHP's built-in server.
- `RAILWAY_DEPLOYMENT.md` documents Railway deployment with MySQL environment variables.

## Important Packages

### Composer

| Package | Purpose |
| --- | --- |
| `laravel/framework` | Main Laravel application framework. |
| `laravel/fortify` | Authentication backend features. |
| `laravel/reverb` | First-party WebSocket server for live notifications. |
| `laravel/sanctum` | API token authentication for API routes. |
| `spatie/laravel-permission` | Role and permission management. |
| `livewire/livewire` | Livewire support used by the starter kit/settings UI. |
| `livewire/flux` | Flux UI Blade components. |
| `laravel/tinker` | Local application debugging. |
| `pestphp/pest` | Testing framework. |
| `pestphp/pest-plugin-laravel` | Pest integration for Laravel tests. |
| `laravel/pint` | PHP code formatting. |
| `laravel/sail` | Local Docker development option. |
| `laravel/pail` | Log tailing during development. |
| `laravel/boost` | Laravel development tooling. |

### npm

| Package | Purpose |
| --- | --- |
| `vite` | Frontend asset bundling. |
| `laravel-vite-plugin` | Laravel/Vite integration. |
| `tailwindcss` | Utility-first CSS framework. |
| `@tailwindcss/vite` | Tailwind CSS Vite integration. |
| `axios` | HTTP client library available to frontend scripts. |
| `laravel-echo` | Frontend listener for Laravel broadcast notifications. |
| `pusher-js` | Pusher protocol client used by Laravel Echo with Reverb. |
| `concurrently` | Runs local development processes together through Composer scripts. |
| `autoprefixer` | CSS post-processing support. |

## System Requirements

- PHP `8.2` or newer.
- Composer.
- Node.js and npm.
- MySQL.
- Git.
- PHP extensions required by the application/deployment include PDO MySQL, mbstring, XML, and Zip.

## Installation

The commands below are Windows/PowerShell friendly.

```powershell
git clone <repository-url>
cd final-project
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Configure the database connection in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=speakly
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seeders:

```powershell
php artisan migrate
php artisan db:seed
```

The default database seeder already calls the presentation demo seeder. You can also rerun it directly:

```powershell
php artisan db:seed --class=PresentationDemoSeeder
```

Build or serve frontend assets:

```powershell
npm run build
```

For active development:

```powershell
npm run dev
php artisan serve
```

The `.env.example` uses `APP_URL=http://final-project.test`, which is suitable for a local Laravel Herd-style domain if configured. `php artisan serve` also works for standard local development.

## Live Notifications

The application stores notifications in the database as before and also broadcasts them live through Laravel Reverb and Laravel Echo. If Reverb is not running, users can still refresh the page and see database notifications normally.

For local real-time testing, run these processes in separate terminals:

```powershell
php artisan serve
php artisan reverb:start
npm run dev
php artisan queue:work
```

`php artisan queue:work` is needed when queued jobs are used. The default `.env.example` uses `QUEUE_CONNECTION=database` and `BROADCAST_CONNECTION=reverb`.

Required Reverb environment variables:

```dotenv
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Generate real app credentials for each environment and do not commit secrets. Production deployments, including Railway, need a separate WebSocket/Reverb process or service configuration alongside the Laravel web process.

## Environment Variables

Important `.env` values:

| Variable | Purpose |
| --- | --- |
| `APP_NAME` | Application name displayed by Laravel. |
| `APP_ENV` | Environment, for example `local` or `production`. |
| `APP_KEY` | Required Laravel encryption key. Generate with `php artisan key:generate`. |
| `APP_DEBUG` | Enables detailed errors in local development. Keep false in production. |
| `APP_URL` | Base URL used by route and asset generation. |
| `DB_CONNECTION` | Database driver, currently MySQL in `.env.example`. |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | MySQL connection settings. |
| `SESSION_DRIVER` | Session storage driver; `.env.example` uses `database`. |
| `CACHE_STORE` | Cache storage driver; `.env.example` uses `database`. |
| `QUEUE_CONNECTION` | Queue driver; `.env.example` uses `database`. |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_FROM_ADDRESS` | Mail configuration for notification/email features. Local default is log mail. |
| `FILESYSTEM_DISK` | Storage disk used by uploaded files. |
| `VITE_APP_NAME` | App name exposed to Vite-built assets. |

Do not commit real secrets or production `.env` files.

## Demo Data and Login Accounts

`PresentationDemoSeeder` creates a presentation-ready scenario with users, roles, programs, courses, groups, rooms, schedules, enrollments, attendance, tuition payments, employee payments, notifications, messages, reviews, teacher resources, and a pending approval account.

The seeder is designed to be safe to rerun on an existing database. It uses stable demo identifiers and refreshes only known demo notification data for known demo users.

Password for all demo accounts:

```text
password
```

| Role | Name | Email | Status |
| --- | --- | --- | --- |
| Admin | Admin Demo | `admin@lumina.test` | Approved |
| Secretary | Sarah Secretary | `secretary@lumina.test` | Approved |
| Teacher | Sofia Rossi | `teacher.sofia@lumina.test` | Approved |
| Teacher | Karim Haddad | `teacher.karim@lumina.test` | Approved |
| Teacher | Nadia Klein | `teacher.nadia@lumina.test` | Approved |
| Parent | Maya Benali | `parent.maya@lumina.test` | Approved |
| Parent | Amine Haddad | `parent.amine@lumina.test` | Approved |
| Student | Alex Benali | `student.alex@lumina.test` | Approved |
| Student | Lina Benali | `student.lina@lumina.test` | Approved |
| Student | Yacine Benali | `student.yacine@lumina.test` | Approved |
| Student | Omar Haddad | `student.omar@lumina.test` | Approved |
| Student | Sara Haddad | `student.sara@lumina.test` | Approved |
| Student | Nour Bensaid | `student.nour@lumina.test` | Approved |
| Student | Amina Pending | `pending.student@lumina.test` | Pending approval |

Demo highlights include:

- English, French, Spanish, German, and IELTS course data.
- Teacher assignments to groups.
- Student enrollments across multiple groups.
- Parent-child links only for underage linked students.
- Tuition payments and scholarship/discount examples.
- Employee payment records for Sofia, Karim, and Sarah.
- Attendance and resource/homework data.
- Realistic notifications for payments, group assignments, homework/resources, and welcome messages.

## Testing

Run the full test suite:

```powershell
php artisan test
```

Compact output:

```powershell
php artisan test --compact
```

Useful focused test filters:

```powershell
php artisan test --filter=Notification --compact
php artisan test --filter=EmployeePayment --compact
php artisan test --filter=TeacherResource --compact
php artisan test --filter=Financial --compact
php artisan test --filter=PresentationDemoSeeder --compact
```

The project includes feature tests for:

- Authentication, registration, email verification, password reset, and two-factor authentication.
- Approval and unapproval flows.
- Role dashboard access.
- Admin language programs, courses, classrooms, schedules, employees, and employee payments.
- Secretary registrations, payments, groups, accounts, and timetables.
- Teacher attendance, resources, notifications, dashboard data, settings, and timetables.
- Student/parent financial pages and scholarships.
- Messaging and conversation authorization.
- API authentication and timetable endpoints.
- Presentation and workflow seeders.
- Docker startup/deployment behavior.

## Useful Commands

```powershell
php artisan optimize:clear
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed --class=PresentationDemoSeeder
php artisan route:list
php artisan test --compact
npm run dev
npm run build
vendor\bin\pint --dirty
```

Composer also includes:

```powershell
composer run dev
composer run test
```

## Deployment

### Docker

The root `Dockerfile` builds the application in stages:

1. Installs Composer dependencies.
2. Builds frontend assets with Node and Vite.
3. Produces a PHP `8.4-cli` runtime image.
4. Copies built assets into `public/build`.
5. Exposes port `8080`.

The entrypoint script:

- Waits for database connectivity.
- Runs `php artisan migrate --force`.
- Clears optimization caches.
- Starts Laravel with:

```bash
php -S 0.0.0.0:${PORT:-8080} -t public
```

### Railway

`RAILWAY_DEPLOYMENT.md` documents Railway deployment. Required practical steps include:

- Add a Railway web service from this repository.
- Add a Railway MySQL database.
- Set production environment variables in Railway.
- Generate and set `APP_KEY`.
- Run migrations and seeders with `php artisan migrate --seed --force`.
- Run `php artisan storage:link`.
- Run `php artisan optimize:clear` after environment changes.
- Keep `APP_DEBUG=false` in production.

## Project Structure

```text
app/Http/Controllers      Role dashboards, admin, secretary, teacher, student, parent, approval, messaging, and API controllers
app/Models                Eloquent models for users, courses, classes, schedules, payments, resources, notifications, and reviews
app/Notifications         Database notification classes
app/Support               Financial, dashboard, payment receipt, notification, and helper services
database/migrations       Database schema
database/seeders          Roles, permissions, presentation data, and demo workflows
database/factories        Test data factories
resources/views           Blade templates grouped by role and feature
resources/css             Tailwind CSS and project theme variables
resources/js              JavaScript entry point
routes/web.php            Web routes and role-protected pages
routes/api.php            Sanctum-protected API routes
tests/Feature             Laravel/Pest feature tests
tests/Unit                Unit and deployment tests
public/build              Vite build output after `npm run build`
```

## Screenshots

Screenshots can be added here for dashboards, timetables, payments, resources, and notifications.

## Academic Context

This project was developed as a final-year/final project to demonstrate full-stack web development using Laravel. It highlights MVC design, relational database modeling, role-based access control, authentication, server-rendered UI, file uploads, notifications, financial workflows, automated tests, and deployment preparation.

## Author

- Hamdane Chihab

## License

No standalone license file is included in this repository. This project is intended for academic use.
