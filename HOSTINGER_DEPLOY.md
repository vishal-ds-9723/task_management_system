# Hostinger Deployment Guide

This project is prepared for deployment to `tms.thelayout.co.in`.

## 1. Server assumptions

- PHP 8.2 or newer
- MySQL or MariaDB database created in Hostinger hPanel
- SSH access preferred
- SSL enabled for `tms.thelayout.co.in`
- Cron access enabled in Hostinger hPanel

## 2. Recommended directory layout

Keep the full Laravel project outside the public web root when possible, and point the subdomain document root to the `public` directory.

Recommended document root:

```text
/home/YOUR_HOSTINGER_USER/domains/tms.thelayout.co.in/public_html/public
```

If your Hostinger plan does not let you point the document root directly to Laravel's `public` directory, stop and fix that first. Do not expose the project root publicly.

## 3. Environment file

Use `.env.hostinger.example` as the production starting point.

Minimum required changes:

- `APP_KEY`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`

Required production values:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tms.thelayout.co.in
APP_FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
```

## 4. Install dependencies

If SSH is available on the server:

```bash
composer install --no-dev --optimize-autoloader
```

If Node is not available on Hostinger shared hosting, build assets locally and upload the generated `public/build` output.

Local asset build:

```bash
npm install
npm run build
```

## 5. Laravel deployment commands

Run this from the project root on the server:

```bash
composer run deploy:production
```

That command executes:

- `php artisan optimize:clear`
- `php artisan migrate --force`
- `php artisan storage:link`
- `php artisan optimize`

## 6. Cron jobs

Add the scheduler cron:

```text
* * * * * /usr/bin/php /home/YOUR_HOSTINGER_USER/domains/tms.thelayout.co.in/public_html/artisan schedule:run >> /dev/null 2>&1
```

If you use the database queue in production, add a worker cron:

```text
* * * * * /usr/bin/php /home/YOUR_HOSTINGER_USER/domains/tms.thelayout.co.in/public_html/artisan queue:work --stop-when-empty --tries=3 >> /dev/null 2>&1
```

Adjust `/usr/bin/php` and the full project path to your actual Hostinger account values.

## 7. Storage and uploads

This project expects Laravel's public storage symlink to exist.

Verify that:

- `public/storage` is a symlink or Hostinger-supported equivalent
- uploaded files resolve under `/storage/...`

Do not replace the symlink with a normal copied directory.

## 8. Realtime broadcasting note

This codebase contains Laravel Reverb and chat broadcasting support.

For standard Hostinger shared hosting, do not enable Reverb unless you have a server process that can keep the websocket service alive.

Safe shared-hosting default:

```dotenv
BROADCAST_CONNECTION=log
```

If you need realtime chat or websocket broadcasting in production, move to a VPS or use an external broadcaster such as Pusher or Ably.

## 9. Final verification checklist

- `https://tms.thelayout.co.in` loads over HTTPS only
- login works and session cookies are secure
- `php artisan migrate --force` completes without error
- `public/build/manifest.json` exists after asset deployment
- uploaded files render through `/storage/...`
- scheduler cron is active
- queue cron is active if background jobs are required
