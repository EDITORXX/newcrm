# Deployment Handoff

This repository can be published to `https://github.com/EDITORXX/newcrm` as a secondary remote while keeping the current `origin` unchanged.

## Git Remotes

- Current primary remote: `origin = https://github.com/EDITORXX/test.git`
- Secondary publish target: `newcrm = https://github.com/EDITORXX/newcrm`
- Source branch: `main`

Push policy:

- add `newcrm` as a second remote
- inspect the remote before pushing
- push `main` only if the remote is empty or shares history
- stop on unrelated remote history instead of force-pushing

## What Must Not Be Committed

- `.env` and any environment-specific variants
- Firebase service-account JSON files
- runtime storage contents
- `vendor/`
- `node_modules/`
- machine-specific secrets, SSH keys, and webhook credentials

## Required Services

- PHP 8.1+ with common Laravel extensions, including `pdo_mysql`, `curl`, `mbstring`, `openssl`, `fileinfo`, and `gd`
- MySQL
- Node.js/npm for asset builds
- Redis when using queued jobs, cache, or background processing outside local development
- SMTP provider for production email
- Pusher-compatible broadcast service for realtime notifications
- Firebase project and service account for FCM/web push features

## Environment Checklist

Start from `.env.example` and set real values for:

- app/base: `APP_*`, logging, session, cache
- database: `DB_*`, optional `DATABASE_URL`
- Redis/queue: `REDIS_*`, `QUEUE_*`
- broadcasting: `PUSHER_*` or compatible host settings
- mail: `MAIL_*`
- Firebase/web push: `FIREBASE_*`, `VAPID_*`, `FIREBASE_CREDENTIALS_PATH`
- deployment hooks: `DEPLOYMENT_*`
- optional infra/integrations: `AWS_*`, SQS, memcached, Papertrail/Slack logging
- app-specific secrets: `DEVELOPER_DOCS_ACCESS_KEY`, `CRM_DANGER_DELETE_ALL_LEADS_PASSWORD`

Credential file notes:

- place the Firebase service account JSON outside Git, typically at `storage/app/firebase-service-account.json`
- ensure the configured path in `FIREBASE_CREDENTIALS_PATH` matches the server file location

## Fresh Server Setup

1. Clone the repository and check out `main`.
2. Copy `.env.example` to `.env` and provide real values.
3. Run `composer install`.
4. Run `npm install`.
5. Run `php artisan key:generate`.
6. Run `php artisan migrate`.
7. Run `php artisan db:seed`.
8. Run `npm run build`.
9. Run `php artisan storage:link` if public storage access is required.
10. Ensure `storage/` and `bootstrap/cache/` are writable.
11. Optionally warm caches with:
    - `php artisan config:cache`
    - `php artisan route:cache`
    - `php artisan view:cache`

Alternative bootstrap:

- the `/install` web flow can create `.env`, test DB access, run migrations/seeders, and create the first admin user

## Scheduler And Queue Expectations

The app defines scheduled work in `app/Console/Kernel.php`. Production should run:

- Laravel scheduler every minute: `php artisan schedule:run`
- a queue worker when `QUEUE_CONNECTION` is not `sync`

Current scheduled tasks include:

- Google Sheets sync
- overdue task/call processing
- reminder notifications
- ASM CNP automation
- recurring task generation
- daily reset and auto-assignment jobs

## Deployment Behavior

Admin deployment settings are driven by `config/deployment.php` and `app/Services/DeploymentService.php`.

Important behavior:

- deployment commands default to composer install, migrate, and Laravel cache warming
- server-side admin deploy uses `git push origin <branch>` from the deployed clone
- because of that, the production server should clone the intended canonical repo as `origin`

If production should deploy from `EDITORXX/newcrm`, make sure the deployed server's `origin` points at `newcrm`, not `test.git`.

## Verification After Publish

- `git remote -v` shows both `origin` and `newcrm`
- `git ls-remote newcrm` or equivalent confirms remote visibility
- `main` exists on `newcrm`
- remote `main` head matches local `HEAD`
- `.env.example` covers the environment variables currently referenced by the codebase
