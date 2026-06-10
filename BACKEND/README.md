# Neox OMNI Backend

## Overview

Neox OMNI is a web-based interface designed to manage multiple communication channels within a single platform.
It provides four major user roles: Superadmin, Tenant Admin, User/Agent, and Manager. Each role has specific
login access and permissions to relevant modules as defined by the administrator.

The User/Agent role enables handling calls and related activities through the Neox Dial Center CRM configuration.
Neox OMNI also supports support ticket management with SLA tracking, reporting, and multi-channel communication,
including Email, Agent Chat, Live Chat, SMS, and more.

This repository folder contains the Laravel 11 backend for these modules.

## Tech Stack

- PHP 8.2+
- Laravel 11
- MySQL (tenant-aware architecture)
- JWT authentication (`tymon/jwt-auth`)
- Laravel Reverb (real-time/broadcast stack)
- Vite for frontend assets used by backend views
- Swagger/OpenAPI via `darkaonline/l5-swagger`

## Core Modules

- Authentication and role-aware API access
- Ticketing and SLA workflows
- Call and call-history related APIs
- Chat and chatbot integrations
- Customer portal OTP endpoints
- External token-protected APIs
- Multi-tenant scheduling and queue processing

## Roles

- Superadmin
- Tenant Admin
- User/Agent
- Manager

## Prerequisites

Install the following tools on your machine:

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and npm
- MySQL

<!-- ## Environment Setup

No `.env.example` template is maintained currently.

Use the active backend environment file as provided by your team:

- `BACKEND/.env`

Do not commit secrets or credentials. -->

## Installation

From the `BACKEND` directory:

```bash
composer install
npm install
```

Generate the application key (if not already generated):

```bash
php artisan key:generate
```

Generate JWT secret when needed:

```bash
php artisan jwt:secret
```

Run migrations:

```bash
php artisan migrate
```

## Running Locally

Native local workflow is the supported development mode.

Start the backend API server:

```bash
php artisan serve
```

Start Vite Frontend in development mode:

```bash
npm start
```

Run required background services in separate terminals.

Queue worker/listener:

```bash
php artisan queue:work
```

Scheduler:

```bash
php artisan schedule:work
```

Reverb server:

```bash
php artisan reverb:start
```

## Authentication Model

Officially supported authentication in this backend:

- JWT for core APIs
- Custom external auth-token middleware for external use endpoints

Notes:

- JWT guards are configured for admin, users, and end users in `config/auth.php`.
- External token flow is exposed in `routes/externalUse.php` via `generate-auth-token/{uinId}` and middleware-protected endpoints.

## Multitenancy and Scheduling

The backend uses tenant-aware behavior for runtime operations:

- Dynamic tenant cron configuration execution from `routes/console.php`
- Tenant-aware queue listener command: `php artisan app:tenant-queue-listen`
- Queue tenant awareness is enabled in `config/multitenancy.php`

Regular scheduled tasks include SLA notifications, hopper processing, scheduled email dispatch,
and Telescope pruning.

## API Documentation (Swagger)

Swagger UI route:

- `/api/documentation`

Swagger docs route:

- `/docs`

Generate API docs:

```bash
php artisan l5-swagger:generate
```

Swagger configuration is maintained in `config/l5-swagger.php`.

## Common Commands

Install dependencies:

```bash
composer install
npm install
```

Run tests:

```bash
php artisan test
```

Alternative PHPUnit command:

```bash
./vendor/bin/phpunit
```

Build assets:

```bash
npm run build
```

## Deployment (Nginx + PHP-FPM + PM2)

Deployment target stack for this backend is:

- Nginx
- PHP-FPM
- PM2 (for managed long-running processes where applicable)

Recommended production checklist:

1. Configure production `.env` values securely.
2. Run dependency install in production mode.
3. Run migrations.
4. Cache framework metadata:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

5. Ensure queue workers are supervised and auto-restarted.
6. Ensure scheduler is running continuously.
7. Ensure Reverb is running under process supervision when required.
8. Restrict CORS and allowed origins for production security.

## Troubleshooting

If APIs return unauthorized responses:

- Verify JWT secret exists and is correct.
- Verify request token format and guard usage.
- Verify external endpoints are called with valid auth token middleware credentials.

If background jobs are not processed:

- Verify queue connection settings.
- Verify queue worker/listener process is running.
- Verify tenant queue command usage where tenant-specific jobs are expected.

If scheduled jobs do not run:

- Verify `php artisan schedule:work` is running.
- Verify tenant `cron_configurations` data and status values.

If websocket events are missing:

- Verify Reverb process is running.
- Verify Reverb app key/secret/id and host settings.

## Ownership

Maintained by: Neox DevOps Team
