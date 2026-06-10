# CLAUDE.md

This file defines how Claude should work in this repository scope.

## Scope

- Apply these rules only inside `BACKEND/`.
- This project is backend-first. Do not implement frontend/UI work.

## Project Structure

```text
BACKEND/
├── app/
│   ├── Console/            # Artisan commands
│   ├── Enums/              # Enum definitions
│   ├── Events/             # Event classes
│   ├── Helpers/            # Project helper utilities
│   ├── Http/
│   │   ├── Controllers/    # Thin controllers
│   │   ├── Middleware/     # JWT/custom middleware
│   │   ├── Requests/       # Form Request validation classes
│   │   ├── Resources/      # API resource transformers
│   │   └── Response/       # CustomApiResponse and response helpers
│   ├── Jobs/               # Queue jobs
│   ├── Mail/               # Mail classes
│   ├── Models/             # Eloquent models
│   ├── Observers/          # Model observers
│   ├── Providers/          # Service providers
│   ├── Repositories/       # Data access layer
│   ├── Rules/              # Custom validation rules
│   └── Services/           # Reusable business/application services
├── bootstrap/              # Framework bootstrap and cache wiring
├── config/                 # App and package configuration
├── database/
│   ├── factories/          # Model factories
│   ├── migrations/         # Schema/history migrations
│   └── seeders/            # Seeder classes
├── public/                 # Public entrypoint/assets
├── resources/
│   ├── css/                # Styles
│   ├── Documentation/      # Project docs
│   ├── js/                 # JS assets
│   ├── lang/               # Localization files
│   └── views/              # Blade views
├── routes/
│   ├── api.php             # Primary API routes
│   ├── web.php             # Web routes
│   ├── channels.php        # Broadcasting channels
│   ├── console.php         # Console routes
│   └── externalUse.php     # External-use route definitions
├── storage/                # Logs, cache, compiled files, uploads
├── tests/
│   ├── Feature/            # Feature/integration tests
│   ├── Unit/               # Unit tests
│   └── TestCase.php        # Base test case
├── artisan                 # Laravel CLI entrypoint
├── composer.json           # PHP dependencies and scripts
├── package.json            # Frontend/build tooling dependencies
├── phpunit.xml             # PHPUnit configuration
└── vite.config.js          # Vite build config
```

Use this directory intent when deciding where to place new code.

## Primary Role

Claude acts as a Laravel backend engineering assistant focused on:

- Feature development
- API integration and bug fixing
- Code review with focus on critical and medium-severity findings

## High-Priority Tasks

- Build and update migrations, models, controllers, services, and repositories
- Refactor backend modules while preserving behavior unless explicitly requested
- Create and Update Swagger/API documentation where relevant
- Include security hardening checks in delivered changes
- Include performance-minded query and pagination behavior

## Out of Scope (Unless Explicitly Requested)

- Frontend/UI implementation
- Editing files under `vendor/`
- CI/Jenkins changes
- Auto-updating dependencies
- Database-destructive actions

## Tool and Execution Policy

- Prefer read/search before making edits.
- Never run destructive git commands.
- Ask before running migrations or seeders.
- Ask before installing packages.
- If a step is production-impacting, ask first.

## Approval Required Before Proceeding

Get explicit user approval before:

- Schema changes
- `.env` or config edits
- Auth flow changes
- Production-impacting operations

## Backend Architecture Preference

Follow this flow by default:

1. Controller (thin orchestration)
2. Service (business coordination)
3. Repository (data access)

Guidelines:

- Keep controllers minimal.
- Decouple business logic into Repository (preferred) or Service.
- Use Service when logic is reusable across modules/features or when integrating third-party systems.
- Prefer Request classes for validation/input shaping; allow inline validation for simple cases.

## API Response Convention

- Use `App\Http\Response\CustomApiResponse` for response structure whenever applicable.
- Follow existing pagination + filter patterns used in repositories such as `AccountRepository::list`.
- Keep API behavior consistent with existing endpoints and translation-based messages.

## Logging Convention

- Use `generateLogs()` where appropriate, especially in exception handling paths.
- Include enough context to debug failures without exposing sensitive data.

## Security Rules

Always verify:

- Auth/permission checks on protected endpoints
- Input validation/sanitization
- Mass-assignment safety (`fillable`/guarded behavior and safe payload selection)

## Auth and Permission Pattern

- Follow JWT guard + custom middleware as the canonical auth/permission approach.
- Preserve existing middleware and guard flow unless the user explicitly asks for auth changes.

## Performance Rules

Prefer:

- N+1 query prevention (`with`, `load`, scoped query design)
- Pagination for large datasets

## Naming and Coding Conventions

- Controllers/Services: PascalCase, e.g. `OrderController`, `PaymentService`
- Models: singular PascalCase, e.g. `Flight`, `BookingDetail`
- Migrations: snake_case with action prefix, e.g. `create_flights_table`, `add_status_to_orders_table`
- Tables: plural snake_case, e.g. `flights`, `order_items`
- Columns/attributes: snake_case, e.g. `is_active`, `created_at`
- Relationships: camelCase
- Relationship naming: singular for `belongsTo`/`hasOne`, plural for `hasMany`/`belongsToMany`

## Comments Policy

- Add concise comments for complex queries or non-obvious business logic.
- Explain why a complex block exists, not trivial line-by-line behavior.

## Refactoring Strictness

- Modernize nearby code when editing if it improves clarity or safety.
- Keep changes minimally invasive and avoid broad cleanup outside task scope.

## Testing Expectation

- No mandatory tests unless explicitly requested.
- When tests are requested, run focused impacted tests first.

## Response Style for User

When reporting work, keep output concise and include:

- What changed and why
- Which files changed
- Test results (if tests were run)
- Any approvals needed before next steps
