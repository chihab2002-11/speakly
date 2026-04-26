# Railway Deployment

This project is deployed to Railway with a root `Dockerfile` that uses `php:8.4-cli` and serves Laravel with:

```bash
php -S 0.0.0.0:$PORT -t public
```

Do not commit `.env`. Add production variables in the Railway dashboard.

## 1. Add the web service from this repository

1. In Railway, create or open your project.
2. Add a service from this GitHub repository.
3. Keep the Dockerfile at the repository root so Railway auto-detects `Dockerfile`.

## 2. Add a MySQL database

1. In the Railway project, click `+ New`.
2. Add a `MySQL` database service.
3. Wait for the MySQL service to finish provisioning.

## 3. Add service variables in the Railway dashboard

Add these variables to the Laravel web service.

```dotenv
APP_NAME=Laravel
APP_ENV=production
APP_DEBUG=false
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local
```

If your database service is not named `MySQL`, replace `MySQL` in those reference variables with your actual Railway MySQL service name.

## 4. Generate and set `APP_KEY`

Generate a Laravel app key locally:

```bash
php artisan key:generate --show
```

Copy the full `base64:...` output and save it as the `APP_KEY` variable in the Railway web service.

## 5. Run database migrations and seeders

After the variables are saved and deployed, run:

```bash
php artisan migrate --seed --force
```

This app uses database-backed sessions and cache, so migrations must run successfully before the site can work in production.

## 6. Create the storage symlink

Run:

```bash
php artisan storage:link
```

## 7. Clear stale caches after environment changes

Run:

```bash
php artisan optimize:clear
```

## 8. Debugging a 500 error safely

If the public URL still returns `500`, temporarily set:

```dotenv
APP_DEBUG=true
```

Deploy again, inspect the error, then set it back to:

```dotenv
APP_DEBUG=false
```

Keep `APP_DEBUG=false` for normal production use.

## 9. Production checklist

- `APP_KEY` is set in Railway.
- `APP_URL` points to `https://${{RAILWAY_PUBLIC_DOMAIN}}`.
- MySQL variables are linked from the Railway MySQL service.
- `php artisan migrate --seed --force` has been run.
- `php artisan storage:link` has been run.
- `php artisan optimize:clear` has been run after variable changes.
- `APP_DEBUG=false` is set after debugging is complete.
