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
```
speakly
├─ .editorconfig
├─ app
│  ├─ Actions
│  │  └─ Fortify
│  │     ├─ CreateNewUser.php
│  │     └─ ResetUserPassword.php
│  ├─ Concerns
│  │  ├─ PasswordValidationRules.php
│  │  └─ ProfileValidationRules.php
│  ├─ Http
│  │  ├─ Controllers
│  │  │  ├─ AdminDashboardController.php
│  │  │  ├─ ApprovalController.php
│  │  │  ├─ Controller.php
│  │  │  ├─ MessageController.php
│  │  │  ├─ ParentDashboardController.php
│  │  │  ├─ SecretaryDashboardController.php
│  │  │  ├─ SecretaryTimetableController.php
│  │  │  ├─ StudentDashboardController.php
│  │  │  ├─ TeacherAttendanceController.php
│  │  │  ├─ TeacherDashboardController.php
│  │  │  ├─ TeacherResourceController.php
│  │  │  ├─ TeacherSettingsController.php
│  │  │  ├─ TeacherTimetableController.php
│  │  │  └─ TimetableController.php
│  │  ├─ Middleware
│  │  │  ├─ EnsureApproved.php
│  │  │  └─ EnsureRouteRoleMatchesUser.php
│  │  └─ Responses
│  │     ├─ LoginResponse.php
│  │     └─ RegisterResponse.php
│  ├─ Livewire
│  │  ├─ Actions
│  │  │  └─ Logout.php
│  │  └─ Settings
│  │     ├─ Appearance.php
│  │     ├─ DeleteUserForm.php
│  │     ├─ Password.php
│  │     ├─ Profile.php
│  │     ├─ TwoFactor
│  │     │  └─ RecoveryCodes.php
│  │     └─ TwoFactor.php
│  ├─ Models
│  │  ├─ AttendanceRecord.php
│  │  ├─ Course.php
│  │  ├─ CourseClass.php
│  │  ├─ Message.php
│  │  ├─ Room.php
│  │  ├─ Schedule.php
│  │  ├─ TeacherResource.php
│  │  └─ User.php
│  ├─ Notifications
│  │  ├─ AccountApprovedNotification.php
│  │  ├─ AccountRejectedNotification.php
│  │  ├─ NewMessageNotification.php
│  │  ├─ TeacherAttendanceSavedNotification.php
│  │  └─ TeacherResourceActionNotification.php
│  ├─ Providers
│  │  ├─ AppServiceProvider.php
│  │  └─ FortifyServiceProvider.php
│  └─ Support
│     ├─ DashboardDataProvider.php
│     └─ DashboardRedirector.php
├─ artisan
├─ BACKEND_INTEGRATION_SUMMARY.md
├─ BACKEND_INTEGRATION_SUMMARY_WITH_ADMIN.md
├─ bootstrap
│  ├─ app.php
│  ├─ cache
│  │  ├─ packages.php
│  │  └─ services.php
│  └─ providers.php
├─ CODE_EXAMPLES.md
├─ composer.json
├─ composer.lock
├─ config
│  ├─ app.php
│  ├─ auth.php
│  ├─ cache.php
│  ├─ database.php
│  ├─ filesystems.php
│  ├─ fortify.php
│  ├─ logging.php
│  ├─ mail.php
│  ├─ permission.php
│  ├─ queue.php
│  ├─ services.php
│  └─ session.php
├─ database
│  ├─ factories
│  │  ├─ CourseClassFactory.php
│  │  ├─ CourseFactory.php
│  │  ├─ MessageFactory.php
│  │  ├─ RoomFactory.php
│  │  ├─ ScheduleFactory.php
│  │  ├─ TeacherResourceFactory.php
│  │  └─ UserFactory.php
│  ├─ migrations
│  │  ├─ 0001_01_01_000000_create_users_table.php
│  │  ├─ 0001_01_01_000001_create_cache_table.php
│  │  ├─ 0001_01_01_000002_create_jobs_table.php
│  │  ├─ 2025_08_14_170933_add_two_factor_columns_to_users_table.php
│  │  ├─ 2026_02_20_152350_create_permission_tables.php
│  │  ├─ 2026_03_28_014220_add_approval_fields_to_users_table.php
│  │  ├─ 2026_03_29_233652_add_rejection_fields_to_users_table.php
│  │  ├─ 2026_03_30_021121_create_notifications_table.php
│  │  ├─ 2026_03_31_031807_create_messages_table.php
│  │  ├─ 2026_04_01_223202_create_courses_table.php
│  │  ├─ 2026_04_01_223212_create_classes_table.php
│  │  ├─ 2026_04_01_223223_create_class_student_table.php
│  │  ├─ 2026_04_01_223229_create_schedules_table.php
│  │  ├─ 2026_04_03_165026_create_rooms_table.php
│  │  ├─ 2026_04_03_165043_modify_schedules_table_for_room_id.php
│  │  ├─ 2026_04_05_195931_add_parent_link_and_birth_date_to_users_table.php
│  │  ├─ 2026_04_07_222349_create_teacher_resources_table.php
│  │  ├─ 2026_04_07_231454_add_teacher_profile_fields_to_users_table.php
│  │  └─ 2026_04_07_234240_create_attendance_records_table.php
│  └─ seeders
│     ├─ DatabaseSeeder.php
│     ├─ PermissionSeeder.php
│     ├─ RoleSeeder.php
│     ├─ RoomSeeder.php
│     ├─ TeacherWorkflowSeeder.php
│     └─ TimetableSeeder.php
├─ DELIVERABLES.md
├─ email)
├─ package-lock.json
├─ package.json
├─ phpstan.neon
├─ phpunit.xml
├─ pint.json
├─ public
│  ├─ .htaccess
│  ├─ apple-touch-icon.png
│  ├─ css
│  │  └─ style.css
│  ├─ favicon.ico
│  ├─ favicon.svg
│  ├─ images
│  │  ├─ flag_gr.png
│  │  ├─ flag_it.png
│  │  ├─ learners.jpg
│  │  └─ student_progress.png
│  ├─ index.php
│  └─ robots.txt
├─ README.md
├─ resources
│  ├─ css
│  │  └─ app.css
│  ├─ flags_of_countries
│  │  └─ flag_gr.png
│  ├─ js
│  │  └─ app.js
│  └─ views
│     ├─ admin
│     │  ├─ messages-new.blade.php
│     │  ├─ messages.blade.php
│     │  └─ notifications.blade.php
│     ├─ approvals
│     │  └─ index.blade.php
│     ├─ auth
│     │  └─ forgot-password.blade.php
│     ├─ components
│     │  ├─ action-message.blade.php
│     │  ├─ app-logo-icon.blade.php
│     │  ├─ app-logo.blade.php
│     │  ├─ auth-header.blade.php
│     │  ├─ auth-session-status.blade.php
│     │  ├─ desktop-user-menu.blade.php
│     │  ├─ layouts
│     │  │  ├─ admin.blade.php
│     │  │  ├─ parent.blade.php
│     │  │  ├─ student.blade.php
│     │  │  └─ teacher.blade.php
│     │  ├─ messages-preview.blade.php
│     │  ├─ notifications-dropdown.blade.php
│     │  ├─ parent
│     │  │  ├─ header.blade.php
│     │  │  └─ sidebar.blade.php
│     │  ├─ placeholder-pattern.blade.php
│     │  ├─ settings
│     │  │  └─ layout.blade.php
│     │  ├─ student
│     │  │  ├─ header.blade.php
│     │  │  └─ sidebar.blade.php
│     │  └─ teacher
│     │     ├─ header.blade.php
│     │     └─ sidebar.blade.php
│     ├─ dashboard.blade.php
│     ├─ dashboards
│     │  ├─ admin.blade.php
│     │  ├─ parent.blade.php
│     │  ├─ secretary.blade.php
│     │  ├─ student.blade.php
│     │  └─ teacher.blade.php
│     ├─ flux
│     │  ├─ icon
│     │  │  ├─ book-open-text.blade.php
│     │  │  ├─ chevrons-up-down.blade.php
│     │  │  ├─ folder-git-2.blade.php
│     │  │  └─ layout-grid.blade.php
│     │  └─ navlist
│     │     └─ group.blade.php
│     ├─ layouts
│     │  ├─ app
│     │  │  ├─ header.blade.php
│     │  │  └─ sidebar.blade.php
│     │  ├─ app.blade.php
│     │  ├─ auth
│     │  │  ├─ card.blade.php
│     │  │  ├─ simple.blade.php
│     │  │  └─ split.blade.php
│     │  └─ auth.blade.php
│     ├─ livewire
│     │  ├─ auth
│     │  │  ├─ confirm-password.blade.php
│     │  │  ├─ forgot-password.blade.php
│     │  │  ├─ login.blade.php
│     │  │  ├─ register.blade.php
│     │  │  ├─ reset-password.blade.php
│     │  │  ├─ two-factor-challenge.blade.php
│     │  │  └─ verify-email.blade.php
│     │  └─ settings
│     │     ├─ appearance.blade.php
│     │     ├─ delete-user-form.blade.php
│     │     ├─ password.blade.php
│     │     ├─ profile.blade.php
│     │     ├─ two-factor
│     │     │  └─ recovery-codes.blade.php
│     │     └─ two-factor.blade.php
│     ├─ messages
│     │  ├─ index.blade.php
│     │  └─ partials
│     │     └─ nav.blade.php
│     ├─ notifications
│     │  └─ index.blade.php
│     ├─ parent
│     │  ├─ calendar.blade.php
│     │  ├─ dashboard.blade.php
│     │  ├─ financial.blade.php
│     │  ├─ messages.blade.php
│     │  ├─ notifications.blade.php
│     │  ├─ password.blade.php
│     │  └─ settings.blade.php
│     ├─ partials
│     │  ├─ head.blade.php
│     │  ├─ navigation.blade.php
│     │  └─ settings-heading.blade.php
│     ├─ pending-approval.blade.php
│     ├─ register-login-page.blade.php
│     ├─ secretary
│     │  └─ timetable
│     │     └─ index.blade.php
│     ├─ student
│     │  ├─ academic.blade.php
│     │  ├─ dashboard.blade.php
│     │  ├─ financial.blade.php
│     │  ├─ materials.blade.php
│     │  ├─ messages.blade.php
│     │  ├─ notifications.blade.php
│     │  ├─ password.blade.php
│     │  └─ settings.blade.php
│     ├─ teacher
│     │  ├─ attendance.blade.php
│     │  ├─ dashboard.blade.php
│     │  ├─ messages.blade.php
│     │  ├─ notifications.blade.php
│     │  ├─ resources.blade.php
│     │  └─ settings.blade.php
│     ├─ timetable
│     │  ├─ index.blade.php
│     │  └─ teacher.blade.php
│     ├─ visitor.blade.php
│     └─ welcome.blade.php
├─ routes
│  ├─ console.php
│  ├─ settings.php
│  └─ web.php
├─ SESSION_SUMMARY_ADMIN_THEMING.md
├─ storage
│  ├─ app
│  │  ├─ private
│  │  └─ public
│  │     └─ teacher-resources
│  │        ├─ 3
│  │        │  ├─ a2-conversation-prompts.docx
│  │        │  └─ b2-grammar-week-1.pdf
│  │        ├─ 4
│  │        │  └─ ielts-writing-band-descriptors.pdf
│  │        ├─ 6
│  │        │  ├─ a2-conversation-prompts.docx
│  │        │  └─ b2-grammar-week-1.pdf
│  │        └─ 7
│  │           └─ ielts-writing-band-descriptors.pdf
│  ├─ framework
│  │  ├─ cache
│  │  │  └─ data
│  │  ├─ sessions
│  │  ├─ testing
│  │  └─ views
│  │     ├─ 03b1ad82dbb4c8296933bd857e6ab223.php
│  │     ├─ 0570598d22bdf26495232e92669881a3.php
│  │     ├─ 08db9ecd9a988ae2074ed08215f5b0b8.php
│  │     ├─ 0a339888244e3040c571405a1c742d4c.php
│  │     ├─ 0e6d497a5586fd8122b264a651b52403.php
│  │     ├─ 134c6aeac98d6fe097552ea93ea75daf.php
│  │     ├─ 18353e59eff6ee307ccd6257f875ab49.php
│  │     ├─ 1ab0acd7a783c1ab71b1fd6982cbcbee.php
│  │     ├─ 1d73bc60e3b9fad6bdf60831477f03d2.php
│  │     ├─ 290c0eac1fc26d6d87a6781390319b8f.php
│  │     ├─ 2b0a7f5ba1a432cce6c7aac18779841d.php
│  │     ├─ 2f3fdab6b5c6f266327bad6c13ea5b88.php
│  │     ├─ 33cd49897abceeb9789566dd4d916949.php
│  │     ├─ 374b11201ee888f77571c099b08b21eb.php
│  │     ├─ 3f28921a8a2cf50e35b8e3f02923756a.php
│  │     ├─ 4719f346fbc70d52e08749c306c177f5.php
│  │     ├─ 4cbf83e23d3b5b9096d0cadb91b36cf1.php
│  │     ├─ 4d78b2105ea9b20f05def4ead578ed2f.php
│  │     ├─ 4dfdd858af2799295c243efc84e2b341.php
│  │     ├─ 510c328e139ee0922b689e9524ccc0f0.php
│  │     ├─ 511ba2a6925980a3509f83b0aca935dd.php
│  │     ├─ 5808e7b845d83c4c5ed0ec5eb71b3f1b.php
│  │     ├─ 588b1ab8c238e83b8ed583866f358b7d.php
│  │     ├─ 5ba7d37dc321732ee26ef20401fc6b8f.php
│  │     ├─ 5de46281ddfc94635710827d6241cf9d.php
│  │     ├─ 5e276dd20497495c6030f7f7fb4ba0c6.php
│  │     ├─ 66acb4af8d6496199654dbabb67a428b.php
│  │     ├─ 6756cbb418a43d7665be18c7752c85bd.php
│  │     ├─ 6a46f35f25899dc99240611a5db7569c.php
│  │     ├─ 6c2dc1e1acc50ebf187116859e93659e.php
│  │     ├─ 720a41e407bd1d41f6dd98b144040cc0.php
│  │     ├─ 7e77a6892e2ed115c9b5adbb33bc43b0.php
│  │     ├─ 836fe3ed172a1387f551afc929dca52b.php
│  │     ├─ 876c95db7c575aa425e39f3ac34cd90c.php
│  │     ├─ 8f634ec4ff9cce28867e27e240fcfb9d.php
│  │     ├─ 981476bc7af7221a7dd11e1b6d18c7b3.php
│  │     ├─ 99dcec09c675ad745772d57b028a65a4.php
│  │     ├─ 9caba416284a9d88625d1bf9fd74e423.php
│  │     ├─ a09d4e6a9e9aae7f95eeefdb154de2fa.php
│  │     ├─ a36a93713e84168dd2bf61d974399018.php
│  │     ├─ a8a8b0c69ef96dd6012fdd406835ecc1.php
│  │     ├─ a9f7d446dfbc978f1663ce87a1033085.php
│  │     ├─ ad0d74673d677ee72e9d5bbd758eb255.php
│  │     ├─ b29c8d2eb788f1ff856de6f69e83e86a.php
│  │     ├─ b351bacbc9185cd060358ca7a2c348f7.php
│  │     ├─ b783476e04715cdfa3d10313ae97a075.php
│  │     ├─ ba09615e73dc6ef2217ace176d2b13eb.php
│  │     ├─ bdee92e538fc5ac0c7ad1d70b6615b76.php
│  │     ├─ c00a00eb5d10cee017077c119f37d141.php
│  │     ├─ c14b46dbc44b0f0fd63b720151850bd5.php
│  │     ├─ d549ab27f092d31cd7a78237ebd9e804.php
│  │     ├─ d7f165a299177dcaa56cd064dfc50c61.php
│  │     ├─ dbb9e2c910bce983ddbbea8a7d4d4437.php
│  │     ├─ e99d0dbf5bc17c2cdaa02b4c8dd5a5f3.php
│  │     ├─ ecc4bcb5488a4f4a6918f3c6d35c648e.php
│  │     ├─ eef08949d901019675de831f3c6d4fbd.php
│  │     ├─ ef3273b70203aa42b8c0ec06f693a9f0.php
│  │     ├─ f57cd1494d007b25af0587182c1926ef.php
│  │     └─ f6741718b9e394b33de5526a070e05ae.php
│  └─ logs
│     ├─ browser.log
│     └─ laravel.log
├─ tatus
├─ tests
│  ├─ Feature
│  │  ├─ ApprovalFlowTest.php
│  │  ├─ ApprovalNotificationsTest.php
│  │  ├─ Auth
│  │  │  ├─ ApprovalAuthorizationTest.php
│  │  │  ├─ AuthenticationTest.php
│  │  │  ├─ EmailVerificationTest.php
│  │  │  ├─ LoginRoleDashboardRedirectTest.php
│  │  │  ├─ PasswordConfirmationTest.php
│  │  │  ├─ PasswordResetTest.php
│  │  │  ├─ RegistrationTest.php
│  │  │  └─ TwoFactorChallengeTest.php
│  │  ├─ DashboardTest.php
│  │  ├─ Database
│  │  │  └─ TeacherWorkflowSeederTest.php
│  │  ├─ ExampleTest.php
│  │  ├─ GuestMiddlewareTest.php
│  │  ├─ Messages
│  │  │  ├─ ConversationTest.php
│  │  │  ├─ MessagingTest.php
│  │  │  └─ TeacherMessageAuthorizationTest.php
│  │  ├─ RoleDashboardAccessTest.php
│  │  ├─ SecretaryTimetableTest.php
│  │  ├─ Settings
│  │  │  ├─ PasswordUpdateTest.php
│  │  │  ├─ ProfileUpdateTest.php
│  │  │  └─ TwoFactorAuthenticationTest.php
│  │  ├─ TeacherAttendanceTest.php
│  │  ├─ TeacherDashboardDataTest.php
│  │  ├─ TeacherNotificationsTest.php
│  │  ├─ TeacherResourcesTest.php
│  │  ├─ TeacherSettingsTest.php
│  │  ├─ TeacherTimetableTest.php
│  │  └─ TimetableTest.php
│  ├─ Pest.php
│  ├─ TestCase.php
│  └─ Unit
│     └─ ExampleTest.php
└─ vite.config.js

```