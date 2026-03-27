# Real Estate CRM System

A Laravel 10 + Vite CRM for real estate operations with role-based dashboards, lead management, automation, exports, notifications, mobile/API support, and admin deployment tooling.

## Stack

- PHP 8.1+
- Laravel 10
- MySQL
- Redis for queue/cache workloads
- Vite for frontend assets
- Pusher-compatible broadcasting
- Firebase / Web Push integrations

## Quick Start

1. Copy `.env.example` to `.env` and fill in real values.
2. Install backend dependencies with `composer install`.
3. Install frontend dependencies with `npm install`.
4. Generate the app key with `php artisan key:generate`.
5. Run migrations with `php artisan migrate`.
6. Seed base data with `php artisan db:seed`.
7. Build assets with `npm run build`.
8. Start the app with `php artisan serve`.

## Install Wizard

Fresh installs can also use the browser-based installer at `/install`. The installer writes `.env`, tests the database connection, runs migrations and seeders, and creates the first admin user.

## Deployment Handoff

Use `DEPLOYMENT_HANDOFF.md` for the full migration and deployment checklist, including:

- required services and secrets
- queue/scheduler expectations
- Firebase/Web Push credential handling
- secondary remote publishing to `EDITORXX/newcrm`
- server-side deployment notes

## Notes

- Do not commit `.env`, Firebase credential files, runtime storage artifacts, `vendor/`, or `node_modules/`.
- The admin deployment panel uses the repo's configured `origin` remote on the deployed server. If production should deploy from `newcrm`, the server clone must use `newcrm` as its `origin`.
