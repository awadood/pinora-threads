# Threads Backend Codex Guide

## Purpose
Use this file as the default operating guide when working in `threads` (Laravel backend) so changes are faster, safer, and consistent with current project patterns.

## Stack Snapshot
- Framework: Laravel `12.x` (PHP `8.2+`)
- Auth: Sanctum (cookie SPA auth + token auth)
- Authorization: Spatie permissions (`permission:*` middleware)
- Data layer: Eloquent + Repository interfaces + Service layer
- API shape: JSON Resources (`JsonResource::withoutWrapping()` is enabled)
- Background jobs: Queue + jobs table (`QUEUE_CONNECTION=database` by default)
- Frontend assets in this repo: Vite (minimal)

## Canonical Commands
- Install PHP deps: `composer install`
- Install JS deps: `npm install`
- First-time setup: `cp .env.example .env && php artisan key:generate`
- Run migrations: `php artisan migrate`
- Seed data: `php artisan db:seed`
- Full local dev stack (server + queue + logs + vite): `composer run dev`
- API server only: `php artisan serve`
- Queue worker/listener: `php artisan queue:listen --tries=1`
- Tail logs: `php artisan pail --timeout=0`
- Run tests: `php artisan test`
- Run formatter: `./vendor/bin/pint`

## Environment and Cross-App Behavior
- API routes are under `/api/*` (configured in `bootstrap/app.php`).
- Storefront context middleware (`ResolveStoreContext`) is appended to the `api` middleware group for all API requests.
- CORS is driven by `APP_FRONTEND_URLS` in `config/cors.php`.
- Sanctum SPA stateful hosts come from `SANCTUM_STATEFUL_DOMAINS`.
- Storefront cookie/currency/country behavior is configured in `config/storefront.php`.
- `FILESYSTEM_DISK` controls media storage (`local` or `s3`).

## Architecture Map
- Routing:
  - Public + protected API endpoints in `routes/api.php`
  - Web cookie auth endpoints in `routes/web.php` (`/api/auth/login`, `/api/auth/logout`)
  - Scheduled tasks in `routes/console.php`
- Backend layering:
  - `Controller` -> `FormRequest` -> `Service` -> `Repository` -> `Model`
  - `Resource` classes shape API responses
- IoC bindings:
  - Interface-to-repository bindings are centralized in `app/Providers/AppServiceProvider.php`
- Filtering/sorting:
  - Many index endpoints use `App\Support\QueryFilterable`
- Permissions:
  - Permission slugs are centralized in `app/Support/Permissions.php`
  - Use those constants in route middleware and permission seeding
- Error response contract:
  - Global JSON exception format is configured in `bootstrap/app.php`:
    - `status`, `message`, `errors`, `trace` (trace only outside production)

## Conventions to Follow
- Prefer extending existing domain structure rather than bypassing it:
  - Add/adjust Request validation in `app/Http/Requests/*`
  - Keep business logic in `app/Services/*`
  - Keep DB-access composition in `app/Repositories/*`
  - Return Resources from controllers (`app/Http/Resources/*`)
- Reuse existing repository interfaces (`Contracts/*`) and bind implementations via `AppServiceProvider`.
- For index endpoints, prefer `QueryFilterable` patterns (`filter[...]`, `sort=`) over ad-hoc query formats.
- For deletions where FK conflicts are possible, use repository `disableIfNotDestroy()` behavior where applicable.
- Preserve style conventions:
  - 4-space indent
  - Typed signatures where already used
  - Keep imports organized and explicit

## Fast Playbooks
### Add a new API endpoint
1. Register route in `routes/api.php` in the correct domain section.
2. Create/update controller action in the domain controller namespace.
3. Add/update `FormRequest` validation class if input is non-trivial.
4. Add service/repository logic in the corresponding domain folders.
5. Return a domain `Resource` or `Resource::collection(...)`.
6. Add permission constant + middleware when endpoint is admin-only.

### Add a new admin-protected CRUD resource
1. Add constants in `app/Support/Permissions.php`.
2. Update permission seeding logic (`database/seeders/PermissionSeeder.php`).
3. Add protected routes with `auth:sanctum` + `permission:...`.
4. Implement controller + request + resource + repository/service updates.

### Add cart/checkout behavior safely
1. Keep cart identity flow compatible with `X-Cart-Key` and `ResolvesCart`.
2. Keep currency resolution compatible with store context (`store_ctx` request attribute).
3. Put checkout orchestration in `app/Services/Order/OrderService.php`.
4. Preserve API response shape expected by frontend (`CartResource`, `OrderResource`).

## Quality Checklist Before Finishing
- Run: `php artisan test` (or targeted test filter).
- Run: `./vendor/bin/pint` on touched files when formatting is needed.
- Sanity check routes: `php artisan route:list` for new/changed endpoints.
- If queue-dependent behavior changed, verify with queue listener running.

## Known Gotchas
- `routes/api.php` is large and domain-grouped; place new routes in the correct section to avoid regressions.
- `run:command` (artisan) drops cart/order tables; never run it casually.
- Media rendition flow currently has mismatch risk:
  - Observer imports `App\Jobs\Media\GenerateMediaRenditionsJob`
  - Existing class file is `app/Jobs/GenerateMediaRenditionsJob.php`
  - Validate namespace/path consistency before touching media job wiring.
- `GenerateMediaRenditionsJob::handle()` currently returns early; no renditions are generated until that is intentionally removed.
- README is partly generic Laravel text; trust code/config over README defaults.

