# Sprint 0 Core — Design Spec

**Date:** 2026-05-15
**Scope:** S0.1, S0.9, S0.10, S0.4, S0.3, S0.8, S0.6, S0.7, S0.5 (local only; S0.2 Hetzner/Forge and S0.11 SEO deferred)
**Source docs:** `sprint-plan.md §2`, `architecture.md §2–5, §16, §21–22`

---

## Goal

Produce a deployable-locally skeleton of Wyceny with:
- Full Docker dev stack running via `make up`
- Multi-tenant data model with proven row isolation in CI
- Preset engine seam (cleaning preset populated, registry cached)
- Filament auth scoped to wife's tenant
- Green CI on every PR before any domain feature work begins

---

## Execution Order

Stories run in this order to pay down architectural risk first:

| Step | Story | Rationale |
|---|---|---|
| 1 | S0.1 Laravel 11 skeleton | Nothing else can start without this |
| 2 | S0.9 Docker compose | All subsequent work runs inside containers |
| 3 | S0.10 CI | Green baseline before any real story ships |
| 4 | S0.4 Multi-tenancy | #1 architectural risk (per `architecture.md §18.1`); prove isolation before domain models exist |
| 5 | S0.3 pgvector | Extension must exist before migrations reference `vector` type |
| 6 | S0.8 Migrations (all tables) | Full data model in place; factories + seeders work from here |
| 7 | S0.6 Preset schema | `vertical_presets` table + cleaning seeder |
| 8 | S0.7 Preset registry | Registry + VO + cache layer on top of the table |
| 9 | S0.5 Auth | Filament login last — everything it depends on is already wired |

---

## Story Specs

### S0.1 — Laravel 11 Skeleton

**Output:** clean repo that passes `bin/pint`, `bin/stan`, `bin/test`

- Laravel 11, PHP 8.3, Filament v3, Livewire 3, Pest, Pint, Larastan level 6
- Module layout: `app/Modules/{Tenancy,Presets,Crm,Scheduling,Quoting,Notes,Pricing,ClientChat,AI,Integrations,Pdf,Public,Billing}/`
- PSR-4 autoload in `composer.json` for `App\Modules\*`
- `lang/pl.json` and `lang/en.json` exist (empty arrays `{}` is fine)
- `SetLocaleMiddleware` registered in `bootstrap/app.php`; reads `tenant_settings.locale`, defaults to `pl`
- Pest test: grep `app/**/*.php` and `resources/views/**/*.blade.php` for non-ASCII chars in string literals — fails if found (enforces `feedback_no_polish_in_code.md` + `architecture.md §21.2`)
- Larastan baseline at level 6; `phpstan.neon` committed

### S0.9 — Docker Compose

**Output:** `docker compose up -d` brings up full stack; `make fresh` produces a clean seeded DB

Services (pinned versions in `docker/versions.env`):

| Service | Image | Port |
|---|---|---|
| `app` | `./docker/app/Dockerfile` (php:8.3-fpm-alpine) | — |
| `nginx` | `nginx:alpine` | 8000 |
| `postgres` | `pgvector/pgvector:pg16` | 5432 |
| `redis` | `redis:7-alpine` | 6379 |
| `mailhog` | `mailhog/mailhog` | 8025 |
| `chromium` | `browserless/chrome` (pinned tag) | 3000 |
| `node` | `node:20-alpine` | 5173 (dev only) |

Wrapper scripts in `bin/`: `art`, `composer`, `test`, `pint`, `stan`, `npm`, `db`

Makefile targets: `up`, `down`, `fresh` (down + wipe volumes + up + migrate:fresh + seed), `logs`

Acceptance: `make fresh && bin/test` runs to completion with no errors.

### S0.10 — CI (GitHub Actions)

**Output:** `.github/workflows/ci.yml` — Pint → Larastan → Pest on every PR; red = no merge

- Builds app Docker image from same Dockerfile used locally
- Postgres + Redis as service containers (same versions as `docker/versions.env`)
- No `setup-php` action — all PHP runs inside the container
- Branch protection rule: require CI green before merge to `main`

### S0.4 — Multi-tenancy

**Output:** `BelongsToTenant` trait applied to all tenant-scoped models; isolation tests passing in CI

Key artifacts per `architecture.md §4`:

- `App\Modules\Tenancy\Concerns\BelongsToTenant` trait
- `App\Modules\Tenancy\TenantScope` global scope — throws `TenantContextMissingException` (not silently returns all rows)
- `App\Modules\Tenancy\Middleware\ResolveTenantFromSubdomain` — subdomain → `Tenant::current()`
- `App\Jobs\TenantAwareJob` abstract base — carries `$tenantUlid`, switches context in `handle()`, clears in `finally`
- `Tenant::current()`, `Tenant::currentId()`, `Tenant::switchByUlid()`, `Tenant::bypass()`, `Tenant::clear()`
- ADR-002 (`docs/adr/002-multi-tenancy.md`) and ADR-003 (`docs/adr/003-tenant-id-format.md`) written

Isolation tests in `tests/Feature/MultiTenant/TenantIsolationTest.php`:
1. Creates tenant A and tenant B
2. Parametric test over every `BelongsToTenant` model: row created under A is invisible under B's scope
3. Asserts `TenantContextMissingException` thrown when no context and not in console/bypass block

Larastan custom rule: `DB::select` / `DB::statement` / `whereRaw` outside `app/Modules/*/Internal/Repositories/` triggers a warning. Implemented as a PHPStan rule class registered in `phpstan.neon`.

### S0.3 — pgvector

**Output:** `vector` type available in Postgres; smoke test green

- Migration `0001_enable_pgvector.php`: `CREATE EXTENSION IF NOT EXISTS vector`
- `tests/Feature/PgvectorSmokeTest.php`: creates a temp table with `vector(3)` column, inserts two rows, runs `ORDER BY embedding <=> '[1,0,0]' LIMIT 1`, asserts correct row returned

### S0.8 — Migrations (all tables)

**Output:** all tables from `architecture.md §3.1` migrated; factories and seeders produce wife's tenant + 3 fake clients

Tables (in dependency order):
1. `tenants`
2. `users`
3. `vertical_presets`
4. `addresses` + `geocoding_cache` + `distance_cache`
5. `clients`
6. `jobs` + `job_occurrences`
7. `quotes` + `quote_items` + `quote_status_log` + `quote_share_tokens`
8. `notes` + `note_attachments` + `note_embeddings`
9. `ai_usage_logs` + `pricing_suggestions` + `pricing_suggestion_feedback`
10. `chat_sessions` + `chat_messages`
11. `tenant_settings` + `tenant_quote_counters`
12. `audit_logs`

All tenant-scoped tables: `tenant_id BIGINT NOT NULL`, FK to `tenants.id ON DELETE CASCADE`, indexed.

`BelongsToTenant` trait applied to every tenant-scoped model at this step.

Factories: every model has a factory; all factories read `tenant_id` from `Tenant::current()` (or accept override).

Seeders:
- `CleaningPresetSeeder` (idempotent `updateOrCreate` on slug) — runs in `DatabaseSeeder`
- `TenantSeeder` — creates wife's tenant (`slug='ania'`) + her user account + 3 fake clients

### S0.6 — Preset Schema

**Output:** `vertical_presets` migration (already done in S0.8) populated with cleaning preset; JSON Schema validators

- `App\Modules\Presets\Schemas\` — PHP JSON Schema definitions for `custom_fields_schema`, `service_types`, `quote_template`, `ai_hints`, `vocabulary`
- `CleaningPresetSeeder` populates all 5 JSONB columns per `architecture.md §5.2–5.4`
- `CustomFieldsSchemaValidator::validate(array $fields, Preset $preset): void` — validates client/job custom field values against preset schema on save
- ADR-005 (`docs/adr/005-preset-engine-schema.md`)
- Pest test: `CleaningPresetSeeder` can be run twice without error; result is one row with correct `service_types` count

### S0.7 — Preset Registry

**Output:** `PresetRegistry::for(Tenant)` returns hydrated `Preset` VO cached 1h; `Tenant::preset()` shortcut

- `App\Modules\Presets\PresetRegistry` — loads from DB, caches per `tenant_id` in Redis for 3600s
- `App\Modules\Presets\Preset` value object — typed accessors: `serviceTypes()`, `customFieldsSchema()`, `quoteTemplate()`, `vocabulary()`, `aiHints()`, `pdfTemplateKey()`
- Cache bust: `VerticalPresetUpdated` event listener calls `PresetRegistry::forgetTenant($tenantId)`
- `Tenant::preset(): Preset` — convenience method
- Pest test: change preset slug in DB → `PresetRegistry::for($tenant)` returns updated data after cache bust

### S0.5 — Auth

**Output:** Filament login works; wife's account exists; second test account proves tenant isolation at the UI layer

- Filament v3 built-in auth (no Breezy — Filament v3 ships its own auth panel out of the box)
- Email/password login only (no registration — tenants seeded manually in M1–M3)
- `SetLocaleMiddleware` sets locale from `tenant_settings.locale` after auth resolves
- `EnforceNoindex` middleware on `domain.app` group sets `X-Robots-Tag: noindex, nofollow`
- Seeded accounts: `ania@wyceny.app` (wife's tenant) + `test@wyceny.app` (second tenant)
- Acceptance: log in as ania, see Filament panel with empty resource lists; log in as test, confirm different tenant context; can't see each other's (empty) data

---

## Cross-Cutting Constraints

- **No Polish in code or DB.** Entity names, column names, JSON keys, enum values, route segments — English only. Polish lives exclusively in `lang/pl.json` (per `feedback_no_polish_in_code.md`)
- **i18n seam from step 1.** Every user-facing string via `__('key')`. Pest test enforces no non-ASCII in app code.
- **Docker only.** All commands run through `bin/` wrappers or `make` targets. No bare `php artisan`.
- **`TenantAwareJob` from step 4.** Any queue job written in S1+ must extend it — enforced by Larastan rule checking `implements ShouldQueue` without extending base class.
- **ADRs.** One ADR per architectural decision listed below, written alongside the story that depends on it.

---

## ADRs to Write

| ADR | Story | Decision |
|---|---|---|
| ADR-001 | S0.1 | Modular monolith over microservices |
| ADR-002 | S0.4 | Multi-tenancy: single DB + `tenant_id` global scope |
| ADR-003 | S0.4 | Tenant ID format: ULID + slug |
| ADR-004 | S0.8 | Custom-fields storage: JSONB column on entities |
| ADR-005 | S0.6 | Preset engine schema shape |
| ADR-013 | S0.9 | Dev environment: Docker-only |
| ADR-014 | S0.1 | i18n strategy: PL primary, EN parallel, all UI via `__()` |
| ADR-015 | S0.1 | No Polish identifiers in code/DB |

---

## Definition of Done

Sprint 0 core is complete when all of the following are true:

1. `make fresh && bin/test` passes green locally
2. GitHub Actions green on a PR to `main`
3. `TenantIsolationTest` covers every `BelongsToTenant` model parametrically
4. `pgvector` smoke test passes (cosine query returns correct row)
5. `PresetRegistry::for($tenant)` returns the cleaning preset with correct service types and vocabulary
6. Filament panel loads at `localhost:8000/admin`; wife's tenant (`ania`) sees scoped empty resources; second tenant cannot see ania's data
7. Larastan level 6 clean (zero errors)
8. Pest non-ASCII string literal test passes (zero violations)

---

## Deferred (not in this sprint)

- S0.2 Hetzner/Forge production deploy
- S0.11 SEO foundations (public routes, SSR test, GSC/Plausible setup)
- Any S1+ feature work
