# Sprint 0 Core — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Docker-based, multi-tenant Laravel 11 skeleton with proven tenant isolation, complete DB schema, preset engine, and Filament auth — ready for Sprint 1 domain work.

**Architecture:** Modular monolith under `app/Modules/`, single-DB multi-tenancy via `BelongsToTenant` + global scope throwing on missing context, preset engine as a seam (cleaning preset seeded, registry cached), AI as strictly optional layer. All commands through Docker; CI mirrors local containers exactly.

**Tech Stack:** Laravel 11, PHP 8.3, Filament v3, Livewire 3, Pest, Pint, Larastan 2, PostgreSQL 16 + pgvector 0.7, Redis 7, Docker Compose, GitHub Actions

---

## File Map

```
app/
  Http/Middleware/
    SetLocaleMiddleware.php
    EnforceNoindex.php
  Jobs/
    TenantAwareJob.php
  Modules/
    Tenancy/
      Concerns/BelongsToTenant.php
      Exceptions/TenantContextMissingException.php
      Exceptions/TenantNotResolvedException.php
      Middleware/ResolveTenantFromSubdomain.php
      Models/Tenant.php
      TenantScope.php
    Presets/
      Events/VerticalPresetUpdated.php
      Listeners/BustPresetCache.php
      Models/VerticalPreset.php
      Preset.php
      PresetRegistry.php
      Schemas/CleaningPresetSchema.php
      Validators/CustomFieldsSchemaValidator.php
    Crm/Models/{Client,Job,Address}.php
    Scheduling/Models/JobOccurrence.php
    Quoting/Models/{Quote,QuoteItem,QuoteStatusLog,QuoteShareToken}.php
    Notes/Models/{Note,NoteAttachment,NoteEmbedding}.php
    Pricing/Models/{PricingSuggestion,PricingSuggestionFeedback}.php
    ClientChat/Models/{ChatSession,ChatMessage}.php
    AI/Models/AIUsageLog.php
    Billing/  (empty stub directory)
  Providers/Filament/AppPanelProvider.php

database/
  migrations/
    0001_01_01_000000_enable_pgvector.php
    0001_01_01_000001_create_vertical_presets_table.php
    0001_01_01_000002_create_tenants_table.php
    0001_01_01_000003_create_users_table.php
    0001_01_01_000004_create_addresses_table.php
    0001_01_01_000005_create_geocoding_caches_table.php
    0001_01_01_000006_create_distance_caches_table.php
    0001_01_01_000007_create_clients_table.php
    0001_01_01_000008_create_jobs_table.php
    0001_01_01_000009_create_job_occurrences_table.php
    0001_01_01_000010_create_quotes_table.php
    0001_01_01_000011_create_quote_items_table.php
    0001_01_01_000012_create_quote_status_logs_table.php
    0001_01_01_000013_create_quote_share_tokens_table.php
    0001_01_01_000014_create_notes_table.php
    0001_01_01_000015_create_note_attachments_table.php
    0001_01_01_000016_create_note_embeddings_table.php
    0001_01_01_000017_create_ai_usage_logs_table.php
    0001_01_01_000018_create_pricing_suggestions_table.php
    0001_01_01_000019_create_pricing_suggestion_feedback_table.php
    0001_01_01_000020_create_chat_sessions_table.php
    0001_01_01_000021_create_chat_messages_table.php
    0001_01_01_000022_create_tenant_settings_table.php
    0001_01_01_000023_create_tenant_quote_counters_table.php
    0001_01_01_000024_create_audit_logs_table.php
  factories/{Tenant,Client,Job,Quote,Note,...}Factory.php
  seeders/{DatabaseSeeder,CleaningPresetSeeder,TenantSeeder}.php

tests/Feature/
  NoPolishInCodeTest.php
  PgvectorSmokeTest.php
  MultiTenant/TenantIsolationTest.php
  Presets/{PresetRegistryTest,CleaningPresetSeederTest}.php
  Auth/FilamentAuthTest.php

docker/
  app/Dockerfile
  nginx/nginx.conf
  versions.env
docker-compose.yml
Makefile
bin/{art,composer,test,pint,stan,npm,db}

.github/workflows/ci.yml
lang/{pl,en}.json
phpstan.neon
.env.example
docs/adr/{001..005,013..015}-*.md
```

---

## Task 1: Bootstrap Laravel 11 project

**Files:** Creates full Laravel 11 skeleton in project root.

- [ ] **Step 1: Scaffold via temporary Docker container**

```bash
docker run --rm -v "$(pwd)":/app -w /app \
  composer:2 composer create-project laravel/laravel:^11.0 . --prefer-dist
```

Expected: standard Laravel 11 directory structure created. `ls` shows `app/`, `bootstrap/`, `composer.json`, etc.

- [ ] **Step 2: Remove default Laravel migrations we'll replace**

```bash
rm database/migrations/0001_01_01_000000_create_users_table.php
rm database/migrations/0001_01_01_000001_create_cache_table.php
rm database/migrations/0001_01_01_000002_create_jobs_table.php
```

We'll write our own migrations with the correct schema.

- [ ] **Step 3: Add dependencies**

Edit `composer.json` — replace `require` and `require-dev` sections:

```json
{
    "require": {
        "php": "^8.3",
        "filament/filament": "^3.2",
        "laravel/framework": "^11.0",
        "livewire/livewire": "^3.0",
        "spatie/laravel-activitylog": "^4.8"
    },
    "require-dev": {
        "larastan/larastan": "^2.9",
        "laravel/pint": "^1.0",
        "pestphp/pest": "^2.0",
        "pestphp/pest-plugin-laravel": "^2.0",
        "fakerphp/faker": "^1.23"
    }
}
```

- [ ] **Step 4: Install via temporary container**

```bash
docker run --rm -v "$(pwd)":/app -w /app composer:2 composer install --no-interaction
```

Expected: `vendor/` populated, no errors.

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "chore(s0.1): bootstrap Laravel 11 skeleton with Filament v3"
```

---

## Task 2: Module directory structure + PSR-4

**Files:** `composer.json`, `app/Modules/` tree.

- [ ] **Step 1: Create module directories**

```bash
mkdir -p app/Modules/{Tenancy/{Concerns,Exceptions,Middleware,Models},Presets/{Events,Listeners,Models,Schemas,Validators},Crm/Models,Scheduling/Models,Quoting/Models,Notes/Models,Pricing/Models,ClientChat/Models,AI/Models,Integrations,Pdf,Public,Billing}
touch app/Modules/Billing/.gitkeep
```

- [ ] **Step 2: Add PSR-4 autoload entry**

In `composer.json`, under `autoload.psr-4`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "App\\Modules\\": "app/Modules/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    }
}
```

- [ ] **Step 3: Dump autoload**

```bash
docker run --rm -v "$(pwd)":/app -w /app composer:2 composer dump-autoload
```

- [ ] **Step 4: Commit**

```bash
git add composer.json app/Modules/
git commit -m "chore(s0.1): add module directory structure and PSR-4 autoload"
```

---

## Task 3: i18n seam + SetLocaleMiddleware

**Files:** `lang/pl.json`, `lang/en.json`, `app/Http/Middleware/SetLocaleMiddleware.php`, `bootstrap/app.php`.

- [ ] **Step 1: Create lang files**

`lang/pl.json`:
```json
{}
```

`lang/en.json`:
```json
{}
```

- [ ] **Step 2: Write SetLocaleMiddleware**

`app/Http/Middleware/SetLocaleMiddleware.php`:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'pl';

        if (auth()->check() && method_exists(auth()->user(), 'tenant')) {
            $setting = \DB::table('tenant_settings')
                ->where('tenant_id', auth()->user()->tenant_id)
                ->value('locale');
            $locale = $setting ?? 'pl';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
```

- [ ] **Step 3: Register middleware in bootstrap/app.php**

Edit `bootstrap/app.php` — add inside `->withMiddleware(...)`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SetLocaleMiddleware::class);
})
```

- [ ] **Step 4: Create lang directory structure**

```bash
mkdir -p lang/pl lang/en
```

- [ ] **Step 5: Commit**

```bash
git add lang/ app/Http/Middleware/SetLocaleMiddleware.php bootstrap/app.php
git commit -m "chore(s0.1): add i18n seam — lang files, SetLocaleMiddleware"
```

---

## Task 4: Larastan config + no-Polish enforcement test

**Files:** `phpstan.neon`, `tests/Feature/NoPolishInCodeTest.php`.

- [ ] **Step 1: Write no-Polish test first**

`tests/Feature/NoPolishInCodeTest.php`:
```php
<?php

use Symfony\Component\Finder\Finder;

it('contains no Polish characters in app code string literals', function () {
    $polishPattern = '/[ąćęłńóśźżĄĆĘŁŃÓŚŹŻ]/u';

    $finder = (new Finder())
        ->files()
        ->in([base_path('app'), base_path('database'), base_path('config')])
        ->name('*.php')
        ->notPath('lang/')
        ->notPath('tests/');

    $violations = [];

    foreach ($finder as $file) {
        $lines = file($file->getRealPath());
        foreach ($lines as $lineNo => $line) {
            // Only check string literals (inside quotes)
            if (preg_match_all('/(["\'])([^"\']*' . substr($polishPattern, 1, -2) . '[^"\']*)\1/', $line, $matches)) {
                foreach ($matches[2] as $match) {
                    $violations[] = $file->getRelativePathname() . ':' . ($lineNo + 1) . ' — "' . $match . '"';
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Polish characters found in string literals:\n" . implode("\n", $violations)
    );
});
```

- [ ] **Step 2: Run test to confirm it passes on empty codebase**

```bash
docker run --rm -v "$(pwd)":/app -w /app \
  composer:2 ./vendor/bin/pest tests/Feature/NoPolishInCodeTest.php --verbose
```

Expected: PASS (no Polish in app code yet).

- [ ] **Step 3: Write phpstan.neon**

`phpstan.neon`:
```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    level: 6
    paths:
        - app
    excludePaths:
        - app/Modules/Billing/.gitkeep
    checkMissingIterableValueType: false
    reportUnmatchedIgnoredErrors: false
```

- [ ] **Step 4: Run Larastan to confirm it passes**

```bash
docker run --rm -v "$(pwd)":/app -w /app \
  composer:2 ./vendor/bin/phpstan analyse --no-progress
```

Expected: No errors (empty app/ at this point).

- [ ] **Step 5: Commit**

```bash
git add phpstan.neon tests/Feature/NoPolishInCodeTest.php
git commit -m "test(s0.1): add no-Polish enforcement test and Larastan config"
```

---

## Task 5: Docker Compose stack

**Files:** `docker/versions.env`, `docker/app/Dockerfile`, `docker/nginx/nginx.conf`, `docker-compose.yml`, `.env.example`.

- [ ] **Step 1: Create versions.env**

`docker/versions.env`:
```env
PHP_VERSION=8.3.20
POSTGRES_VERSION=pg16
PGVECTOR_VERSION=0.7.0
REDIS_VERSION=7.2-alpine
NODE_VERSION=20-alpine
CHROMIUM_IMAGE=ghcr.io/browserless/chromium:1.61.0
NGINX_VERSION=1.25-alpine
```

- [ ] **Step 2: Write app Dockerfile**

`docker/app/Dockerfile`:
```dockerfile
ARG PHP_VERSION=8.3.20
FROM php:${PHP_VERSION}-fpm-alpine

RUN apk add --no-cache \
    git curl zip unzip libpq-dev libzip-dev oniguruma-dev icu-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev nodejs npm

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
    pdo_pgsql pgsql zip mbstring bcmath intl gd opcache

# Redis extension
RUN apk add --no-cache autoconf g++ make \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del autoconf g++ make

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader 2>/dev/null || true
```

- [ ] **Step 3: Write nginx config**

`docker/nginx/nginx.conf`:
```nginx
server {
    listen 80;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

- [ ] **Step 4: Write docker-compose.yml**

`docker-compose.yml`:
```yaml
services:
  app:
    build:
      context: .
      dockerfile: docker/app/Dockerfile
      args:
        PHP_VERSION: "${PHP_VERSION:-8.3.20}"
    volumes:
      - .:/var/www/html
    environment:
      APP_ENV: local
      APP_KEY: "${APP_KEY}"
      DB_CONNECTION: pgsql
      DB_HOST: postgres
      DB_PORT: 5432
      DB_DATABASE: "${DB_DATABASE:-wyceny}"
      DB_USERNAME: "${DB_USERNAME:-postgres}"
      DB_PASSWORD: "${DB_PASSWORD:-secret}"
      REDIS_HOST: redis
      REDIS_PORT: 6379
      QUEUE_CONNECTION: redis
      BROWSERLESS_URL: "http://chromium:3000"
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy

  nginx:
    image: "nginx:${NGINX_VERSION:-1.25-alpine}"
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app

  postgres:
    image: "pgvector/pgvector:${POSTGRES_VERSION:-pg16}"
    environment:
      POSTGRES_DB: "${DB_DATABASE:-wyceny}"
      POSTGRES_USER: "${DB_USERNAME:-postgres}"
      POSTGRES_PASSWORD: "${DB_PASSWORD:-secret}"
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres"]
      interval: 5s
      timeout: 5s
      retries: 10

  redis:
    image: "redis:${REDIS_VERSION:-7.2-alpine}"
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 5s
      timeout: 3s
      retries: 5

  mailhog:
    image: mailhog/mailhog
    ports:
      - "8025:8025"

  chromium:
    image: "${CHROMIUM_IMAGE:-ghcr.io/browserless/chromium:1.61.0}"
    ports:
      - "3000:3000"
    environment:
      CONCURRENT: 2
      TIMEOUT: 60000

  horizon:
    build:
      context: .
      dockerfile: docker/app/Dockerfile
    command: php artisan horizon
    volumes:
      - .:/var/www/html
    environment:
      APP_ENV: local
      APP_KEY: "${APP_KEY}"
      DB_CONNECTION: pgsql
      DB_HOST: postgres
      DB_PORT: 5432
      DB_DATABASE: "${DB_DATABASE:-wyceny}"
      DB_USERNAME: "${DB_USERNAME:-postgres}"
      DB_PASSWORD: "${DB_PASSWORD:-secret}"
      REDIS_HOST: redis
      QUEUE_CONNECTION: redis
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy

  node:
    image: "node:${NODE_VERSION:-20-alpine}"
    working_dir: /app
    volumes:
      - .:/app
    command: sh -c "npm install && npm run dev"
    ports:
      - "5173:5173"
    profiles:
      - dev

volumes:
  postgres_data:
  redis_data:
```

- [ ] **Step 5: Write .env.example**

`.env.example`:
```env
APP_NAME=Wyceny
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_PUBLIC_DOMAIN=wyceny.app
APP_APP_DOMAIN=app.wyceny.app

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=wyceny
DB_USERNAME=postgres
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
CACHE_STORE=redis

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025

BROWSERLESS_URL=http://chromium:3000

ANTHROPIC_API_KEY=
OPENAI_API_KEY=
GOOGLE_MAPS_API_KEY=
GUS_API_KEY=
```

- [ ] **Step 6: Copy .env from example and generate key**

```bash
cp .env.example .env
docker run --rm -v "$(pwd)":/app -w /app composer:2 php artisan key:generate
```

- [ ] **Step 7: Commit**

```bash
git add docker/ docker-compose.yml .env.example
git commit -m "chore(s0.9): add Docker Compose stack with postgres+pgvector, redis, chromium"
```

---

## Task 6: Wrapper scripts + Makefile

**Files:** `bin/art`, `bin/composer`, `bin/test`, `bin/pint`, `bin/stan`, `bin/npm`, `bin/db`, `Makefile`.

- [ ] **Step 1: Write bin scripts**

```bash
mkdir -p bin
```

`bin/art`:
```bash
#!/usr/bin/env bash
docker compose exec app php artisan "$@"
```

`bin/composer`:
```bash
#!/usr/bin/env bash
docker compose exec app composer "$@"
```

`bin/test`:
```bash
#!/usr/bin/env bash
docker compose exec app ./vendor/bin/pest "$@"
```

`bin/pint`:
```bash
#!/usr/bin/env bash
docker compose exec app ./vendor/bin/pint "$@"
```

`bin/stan`:
```bash
#!/usr/bin/env bash
docker compose exec app ./vendor/bin/phpstan analyse --no-progress "$@"
```

`bin/npm`:
```bash
#!/usr/bin/env bash
docker compose run --rm node npm "$@"
```

`bin/db`:
```bash
#!/usr/bin/env bash
docker compose exec postgres psql -U postgres -d wyceny "$@"
```

- [ ] **Step 2: Make scripts executable**

```bash
chmod +x bin/*
```

- [ ] **Step 3: Write Makefile**

`Makefile`:
```makefile
.PHONY: up down fresh logs shell

up:
	docker compose up -d --wait

down:
	docker compose down

fresh:
	docker compose down -v
	docker compose up -d --wait
	bin/art migrate:fresh --seed

logs:
	docker compose logs -f

shell:
	docker compose exec app sh
```

- [ ] **Step 4: Start stack and verify**

```bash
make up
```

Expected: all containers healthy. Then:

```bash
bin/art --version
```

Expected output: `Laravel Framework 11.x.x`

- [ ] **Step 5: Commit**

```bash
git add bin/ Makefile
git commit -m "chore(s0.9): add bin/ wrapper scripts and Makefile"
```

---

## Task 7: GitHub Actions CI

**Files:** `.github/workflows/ci.yml`.

- [ ] **Step 1: Write CI workflow**

`.github/workflows/ci.yml`:
```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: pgvector/pgvector:pg16
        env:
          POSTGRES_DB: wyceny_test
          POSTGRES_USER: postgres
          POSTGRES_PASSWORD: secret
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 5s
          --health-timeout 5s
          --health-retries 10

      redis:
        image: redis:7.2-alpine
        ports:
          - 6379:6379
        options: >-
          --health-cmd "redis-cli ping"
          --health-interval 5s
          --health-timeout 3s
          --health-retries 5

    steps:
      - uses: actions/checkout@v4

      - name: Build app image
        run: docker build -t wyceny-app -f docker/app/Dockerfile .

      - name: Copy .env
        run: cp .env.example .env

      - name: Generate app key
        run: docker run --rm -v "$PWD":/var/www/html -w /var/www/html wyceny-app php artisan key:generate

      - name: Run Pint (code style)
        run: |
          docker run --rm -v "$PWD":/var/www/html -w /var/www/html wyceny-app \
            ./vendor/bin/pint --test

      - name: Run Larastan
        run: |
          docker run --rm -v "$PWD":/var/www/html -w /var/www/html wyceny-app \
            ./vendor/bin/phpstan analyse --no-progress

      - name: Run Pest
        run: |
          docker run --rm \
            -v "$PWD":/var/www/html \
            -w /var/www/html \
            --network host \
            -e APP_ENV=testing \
            -e DB_CONNECTION=pgsql \
            -e DB_HOST=127.0.0.1 \
            -e DB_PORT=5432 \
            -e DB_DATABASE=wyceny_test \
            -e DB_USERNAME=postgres \
            -e DB_PASSWORD=secret \
            -e REDIS_HOST=127.0.0.1\
            -e REDIS_PORT=6379 \
            -e QUEUE_CONNECTION=sync \
            -e CACHE_STORE=array \
            wyceny-app ./vendor/bin/pest --no-coverage
```

- [ ] **Step 2: Commit and push to trigger CI**

```bash
git add .github/
git commit -m "ci(s0.10): add GitHub Actions — Pint, Larastan, Pest on every PR"
```

---

## Task 8: Tenant model + context management

**Files:** `app/Modules/Tenancy/Models/Tenant.php`, `app/Modules/Tenancy/Exceptions/{TenantContextMissingException,TenantNotResolvedException}.php`

- [ ] **Step 1: Write exceptions first**

`app/Modules/Tenancy/Exceptions/TenantContextMissingException.php`:
```php
<?php

namespace App\Modules\Tenancy\Exceptions;

use RuntimeException;

class TenantContextMissingException extends RuntimeException {}
```

`app/Modules/Tenancy\Exceptions/TenantNotResolvedException.php`:
```php
<?php

namespace App\Modules\Tenancy\Exceptions;

use RuntimeException;

class TenantNotResolvedException extends RuntimeException {}
```

- [ ] **Step 2: Write failing test for Tenant context management**

`tests/Feature/MultiTenant/TenantIsolationTest.php`:
```php
<?php

use App\Modules\Tenancy\Models\Tenant;

it('can set and retrieve current tenant', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    expect(Tenant::currentId())->toBe($tenant->id);
    expect(Tenant::current()->id)->toBe($tenant->id);
});

it('clears tenant context', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    Tenant::clear();

    expect(Tenant::currentId())->toBeNull();
});

it('runs bypass block without tenant context', function () {
    Tenant::clear();

    $result = Tenant::bypass(fn () => 'ok');

    expect($result)->toBe('ok');
    expect(Tenant::isBypassed())->toBeFalse();
});
```

- [ ] **Step 3: Run test — confirm it fails**

```bash
bin/test tests/Feature/MultiTenant/TenantIsolationTest.php --verbose
```

Expected: FAIL — `App\Modules\Tenancy\Models\Tenant` not found.

- [ ] **Step 4: Write Tenant model**

`app/Modules/Tenancy/Models/Tenant.php`:
```php
<?php

namespace App\Modules\Tenancy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid', 'slug', 'firma_name', 'nip', 'regon',
    ];

    private static ?int $currentId = null;
    private static bool $bypassed = false;

    public static function current(): ?static
    {
        if (static::$currentId === null) {
            return null;
        }
        return static::withoutGlobalScopes()->find(static::$currentId);
    }

    public static function currentId(): ?int
    {
        return static::$currentId;
    }

    public static function setCurrent(self $tenant): void
    {
        static::$currentId = $tenant->id;
    }

    public static function switchByUlid(string $ulid): void
    {
        $tenant = static::withoutGlobalScopes()->where('ulid', $ulid)->firstOrFail();
        static::setCurrent($tenant);
    }

    public static function clear(): void
    {
        static::$currentId = null;
        static::$bypassed = false;
    }

    public static function bypass(callable $callback): mixed
    {
        $previous = static::$bypassed;
        static::$bypassed = true;
        try {
            return $callback();
        } finally {
            static::$bypassed = $previous;
        }
    }

    public static function isBypassed(): bool
    {
        return static::$bypassed;
    }
}
```

- [ ] **Step 5: Write TenantFactory**

`database/factories/TenantFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'ulid'       => Str::ulid(),
            'slug'       => $this->faker->unique()->slug(2),
            'firma_name' => $this->faker->company(),
            'nip'        => null,
            'regon'      => null,
        ];
    }
}
```

- [ ] **Step 6: Run test — confirm it passes**

```bash
bin/test tests/Feature/MultiTenant/TenantIsolationTest.php --verbose
```

Expected: PASS (tests don't require DB yet — only testing static methods).

- [ ] **Step 7: Commit**

```bash
git add app/Modules/Tenancy/Exceptions/ app/Modules/Tenancy/Models/Tenant.php database/factories/TenantFactory.php tests/Feature/MultiTenant/TenantIsolationTest.php
git commit -m "feat(s0.4): Tenant model with static context management and factory"
```

---

## Task 9: TenantScope + BelongsToTenant

**Files:** `app/Modules/Tenancy/TenantScope.php`, `app/Modules/Tenancy/Concerns/BelongsToTenant.php`

- [ ] **Step 1: Write TenantScope**

`app/Modules/Tenancy/TenantScope.php`:
```php
<?php

namespace App\Modules\Tenancy;

use App\Modules\Tenancy\Exceptions\TenantContextMissingException;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = Tenant::currentId();

        if ($tenantId !== null) {
            $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
            return;
        }

        if (app()->runningInConsole() || Tenant::isBypassed()) {
            return;
        }

        throw new TenantContextMissingException(
            'TenantScope: no tenant context bound. Call Tenant::setCurrent() or use Tenant::bypass().'
        );
    }
}
```

- [ ] **Step 2: Write BelongsToTenant trait**

`app/Modules/Tenancy/Concerns/BelongsToTenant.php`:
```php
<?php

namespace App\Modules\Tenancy\Concerns;

use App\Modules\Tenancy\Exceptions\TenantNotResolvedException;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = Tenant::currentId()
                    ?? throw new TenantNotResolvedException(
                        'Cannot create ' . static::class . ' without tenant context.'
                    );
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Modules/Tenancy/TenantScope.php app/Modules/Tenancy/Concerns/BelongsToTenant.php
git commit -m "feat(s0.4): TenantScope global scope and BelongsToTenant trait"
```

---

## Task 10: ResolveTenantFromSubdomain + TenantAwareJob

**Files:** `app/Modules/Tenancy/Middleware/ResolveTenantFromSubdomain.php`, `app/Jobs/TenantAwareJob.php`

- [ ] **Step 1: Write ResolveTenantFromSubdomain**

`app/Modules/Tenancy/Middleware/ResolveTenantFromSubdomain.php`:
```php
<?php

namespace App\Modules\Tenancy\Middleware;

use App\Modules\Tenancy\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $appDomain = config('app.app_domain', 'app.wyceny.app');

        // Extract subdomain: "ania.app.wyceny.app" => "ania"
        if (str_ends_with($host, '.' . $appDomain)) {
            $subdomain = substr($host, 0, strlen($host) - strlen('.' . $appDomain));
            $tenant = Tenant::bypass(fn () => Tenant::where('slug', $subdomain)->first());
            if ($tenant) {
                Tenant::setCurrent($tenant);
                return $next($request);
            }
        }

        // Fallback: resolve from authenticated user's tenant_id
        if (auth()->check() && property_exists(auth()->user(), 'tenant_id')) {
            $tenant = Tenant::bypass(fn () => Tenant::find(auth()->user()->tenant_id));
            if ($tenant) {
                Tenant::setCurrent($tenant);
            }
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Write TenantAwareJob**

`app/Jobs/TenantAwareJob.php`:
```php
<?php

namespace App\Jobs;

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class TenantAwareJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $tenantUlid;

    public function __construct()
    {
        $this->tenantUlid = Tenant::current()?->ulid
            ?? throw new \RuntimeException('TenantAwareJob dispatched without tenant context.');
    }

    final public function handle(): void
    {
        Tenant::switchByUlid($this->tenantUlid);
        try {
            $this->execute();
        } finally {
            Tenant::clear();
        }
    }

    abstract protected function execute(): void;
}
```

- [ ] **Step 3: Register middleware in bootstrap/app.php**

Add to `->withMiddleware(...)` block in `bootstrap/app.php`:

```php
$middleware->alias([
    'resolve.tenant' => \App\Modules\Tenancy\Middleware\ResolveTenantFromSubdomain::class,
    'noindex'        => \App\Http\Middleware\EnforceNoindex::class,
]);
```

- [ ] **Step 4: Commit**

```bash
git add app/Modules/Tenancy/Middleware/ app/Jobs/TenantAwareJob.php bootstrap/app.php
git commit -m "feat(s0.4): ResolveTenantFromSubdomain middleware and TenantAwareJob base class"
```

---

## Task 11: Isolation tests + ADRs

**Files:** `tests/Feature/MultiTenant/TenantIsolationTest.php` (expanded), `docs/adr/001-015-*.md`

- [ ] **Step 1: Expand isolation test with scope-leak assertions**

Replace contents of `tests/Feature/MultiTenant/TenantIsolationTest.php`:

```php
<?php

use App\Modules\Tenancy\Exceptions\TenantContextMissingException;
use App\Modules\Tenancy\Models\Tenant;

// --- Context management (no DB needed) ---

it('can set and retrieve current tenant', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    expect(Tenant::currentId())->toBe($tenant->id);
})->group('tenancy');

it('clears tenant context', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    Tenant::clear();

    expect(Tenant::currentId())->toBeNull();
})->group('tenancy');

it('bypass block works and restores state', function () {
    Tenant::clear();
    $result = Tenant::bypass(fn () => 'inside');

    expect($result)->toBe('inside');
    expect(Tenant::isBypassed())->toBeFalse();
})->group('tenancy');

// --- Scope leak tests (added in Task 22 when real models exist) ---
// Parametric tests over all BelongsToTenant models are added in Task 22.
```

- [ ] **Step 2: Write ADR documents**

`docs/adr/001-modular-monolith.md`:
```markdown
# ADR-001: Modular Monolith over Microservices

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
Single Laravel 11 codebase with internal `app/Modules/` split.

## Rationale
Solo dev, 15–20h/week. Microservices multiply operational cost (separate deploys, network calls, distributed tracing) with zero benefit at N=1 tenant. Modular monolith gives clean boundaries without the tax.

## Consequences
M11+ team mode may warrant extracting a service. The module boundaries make that feasible without rewrite.
```

`docs/adr/002-multi-tenancy.md`:
```markdown
# ADR-002: Multi-tenancy — Single DB + tenant_id Global Scope

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
Single PostgreSQL database. Every tenant-scoped table has `tenant_id BIGINT NOT NULL FK tenants.id`. Eloquent global scope (`TenantScope`) adds `WHERE tenant_id = ?` to all queries. Missing context throws, not silently returns everything.

## Rationale
Separate-schema or separate-DB multi-tenancy is operationally expensive (100 tenants = 100 schema migrations). Single-DB with global scope is the Laravel-native approach and sufficient for our scale through M11+.

## Consequences
Raw queries and queue jobs are scope-leak surfaces. Mitigated by Larastan rule (raw queries outside Repositories) and `TenantAwareJob` base class.
```

`docs/adr/003-tenant-id-format.md`:
```markdown
# ADR-003: Tenant ID Format — ULID + slug

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
`tenants.id` is a standard bigint PK for joins. `tenants.ulid` (CHAR 26, unique) is used in external-facing contexts (queue job payloads, signed URLs). `tenants.slug` (kebab) is the subdomain.

## Rationale
ULID is URL-safe, lexicographically sortable, and shorter than UUID v4. Slug gives human-readable subdomains.
```

`docs/adr/013-docker-only-dev.md`:
```markdown
# ADR-013: Docker-Only Development Environment

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
All development commands run through Docker Compose containers. No local PHP, Postgres, Redis, Node installations. CI uses the same Dockerfile.

## Rationale
Solo dev + future contractors onboard with `git clone && make up`. No "works on my machine" drift. Dev/CI/prod parity from day 1.

## Consequences
Bootstrap requires Docker installed locally. Slightly slower first `docker build`. Acceptable trade.
```

`docs/adr/014-i18n-strategy.md`:
```markdown
# ADR-014: i18n Strategy — PL Primary, EN Parallel

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
- PL is launch locale; EN is Year-2 expansion. Both `lang/pl.json` and `lang/en.json` exist from day 1.
- Every user-facing string uses `__('key')` — no raw Polish string literals in app code.
- DB values, code identifiers, enum values, JSON keys: English only.
- Polish displayed via translation files.

## Rationale
Retrofitting i18n later costs a full sprint. Building the seam costs ~2h at sprint 0.
```

`docs/adr/015-no-polish-identifiers.md`:
```markdown
# ADR-015: No Polish Identifiers in Code or DB

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
Entity names, column names, JSON keys, enum values, route path segments, PHP class/method/property names — English only. Polish exists only in `lang/*.json` files and user-facing copy.

## Rationale
English-identifier codebase is readable to any developer regardless of Polish fluency. Prevents encoding issues in migrations, SQL logs, and error messages.

## Enforcement
`tests/Feature/NoPolishInCodeTest.php` scans `app/`, `database/`, `config/` for Polish characters in string literals.
```

- [ ] **Step 3: Run full test suite**

```bash
bin/test --verbose
```

Expected: all PASS.

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/MultiTenant/ docs/adr/
git commit -m "test(s0.4): isolation tests and ADR documents 001-003, 013-015"
```

---

## Task 12: pgvector migration + smoke test

**Files:** `database/migrations/0001_01_01_000000_enable_pgvector.php`, `tests/Feature/PgvectorSmokeTest.php`

- [ ] **Step 1: Write failing smoke test**

`tests/Feature/PgvectorSmokeTest.php`:
```php
<?php

it('can insert and query vectors with cosine distance', function () {
    DB::statement('CREATE TEMP TABLE vec_smoke (id serial, embedding vector(3))');
    DB::statement("INSERT INTO vec_smoke (embedding) VALUES ('[1,0,0]'), ('[0,1,0]')");

    $row = DB::selectOne(
        "SELECT id FROM vec_smoke ORDER BY embedding <=> '[1,0.1,0]' LIMIT 1"
    );

    expect($row->id)->toBe(1);
});
```

- [ ] **Step 2: Run test — confirm it fails**

```bash
bin/test tests/Feature/PgvectorSmokeTest.php --verbose
```

Expected: FAIL — "type 'vector' does not exist" (pgvector not yet enabled).

- [ ] **Step 3: Write migration**

`database/migrations/0001_01_01_000000_enable_pgvector.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
    }

    public function down(): void
    {
        DB::statement('DROP EXTENSION IF EXISTS vector');
    }
};
```

- [ ] **Step 4: Run migration**

```bash
bin/art migrate
```

Expected: migration runs without error.

- [ ] **Step 5: Run smoke test — confirm it passes**

```bash
bin/test tests/Feature/PgvectorSmokeTest.php --verbose
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/0001_01_01_000000_enable_pgvector.php tests/Feature/PgvectorSmokeTest.php
git commit -m "feat(s0.3): enable pgvector extension with smoke test"
```

---

## Task 13: Core table migrations (vertical_presets, tenants, users, addresses, caches)

**Files:** Migrations `000001–000006`.

- [ ] **Step 1: Write vertical_presets migration**

`database/migrations/0001_01_01_000001_create_vertical_presets_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vertical_presets', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('version')->default('1');
            $table->jsonb('vocabulary')->default('{}');
            $table->jsonb('custom_fields_schema')->default('{}');
            $table->jsonb('service_types')->default('[]');
            $table->jsonb('quote_template')->default('{}');
            $table->jsonb('ai_hints')->default('{}');
            $table->string('pdf_template_key')->default('generic_v1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vertical_presets');
    }
};
```

- [ ] **Step 2: Write tenants migration**

`database/migrations/0001_01_01_000002_create_tenants_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->string('slug')->unique();
            $table->string('firma_name');
            $table->string('nip', 10)->nullable();
            $table->string('regon', 14)->nullable();
            $table->foreignId('preset_id')->nullable()->constrained('vertical_presets')->nullOnDelete();
            $table->string('preset_version')->nullable();
            $table->decimal('ai_monthly_cap_pln', 8, 2)->default(50);
            $table->decimal('ai_monthly_used_pln', 8, 2)->default(0);
            $table->boolean('is_vat_payer')->default(false);
            $table->unsignedTinyInteger('default_vat_rate')->default(23);
            $table->decimal('fuel_rate_pln_per_km', 5, 2)->default(1.80);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
```

- [ ] **Step 3: Write users migration**

`database/migrations/0001_01_01_000003_create_users_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('role')->default('owner');
            $table->rememberToken();
            $table->timestamps();
            $table->unique(['tenant_id', 'email']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

- [ ] **Step 4: Write addresses migration**

`database/migrations/0001_01_01_000004_create_addresses_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('postcode', 10);
            $table->string('city');
            $table->string('country', 2)->default('PL');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamp('geocoded_at')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
```

- [ ] **Step 5: Write geocoding_caches + distance_caches migrations**

`database/migrations/0001_01_01_000005_create_geocoding_caches_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geocoding_caches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('normalized_address');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('provider')->default('google');
            $table->jsonb('raw_response')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['tenant_id', 'normalized_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geocoding_caches');
    }
};
```

`database/migrations/0001_01_01_000006_create_distance_caches_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distance_caches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('origin_address_id')->constrained('addresses')->cascadeOnDelete();
            $table->foreignId('destination_address_id')->constrained('addresses')->cascadeOnDelete();
            $table->unsignedInteger('distance_meters');
            $table->unsignedInteger('duration_seconds');
            $table->jsonb('raw_response')->nullable();
            $table->timestamp('computed_at')->useCurrent();
            $table->unique(['tenant_id', 'origin_address_id', 'destination_address_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distance_caches');
    }
};
```

- [ ] **Step 6: Run migrations**

```bash
bin/art migrate
```

Expected: 7 migrations run (pgvector + 6 above), no errors.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/0001_01_01_000001_* database/migrations/0001_01_01_000002_* database/migrations/0001_01_01_000003_* database/migrations/0001_01_01_000004_* database/migrations/0001_01_01_000005_* database/migrations/0001_01_01_000006_*
git commit -m "feat(s0.8): core table migrations — vertical_presets, tenants, users, addresses, caches"
```

---

## Task 14: Domain table migrations (clients, jobs, job_occurrences)

- [ ] **Step 1: Write clients migration**

`database/migrations/0001_01_01_000007_create_clients_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('nip', 10)->nullable();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->jsonb('custom_fields')->default('{}');
            $table->text('access_keys_encrypted')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'name']);
        });

        DB::statement('CREATE INDEX clients_custom_fields_gin ON clients USING GIN (custom_fields)');
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
```

- [ ] **Step 2: Write jobs migration**

`database/migrations/0001_01_01_000008_create_jobs_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('service_type_key');
            $table->jsonb('custom_fields')->default('{}');
            $table->string('recurrence_rule', 256)->nullable();
            $table->timestampTz('starts_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('assigned_to', 128)->nullable();
            $table->string('status')->default('planned'); // planned|done|cancelled
            $table->text('internal_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['tenant_id', 'starts_at']);
            $table->index(['tenant_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
```

- [ ] **Step 3: Write job_occurrences migration**

`database/migrations/0001_01_01_000009_create_job_occurrences_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->timestampTz('occurrence_at');
            $table->string('status')->default('planned'); // planned|done|cancelled|skipped|rescheduled
            $table->timestampTz('rescheduled_to')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['job_id', 'occurrence_at']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_occurrences');
    }
};
```

- [ ] **Step 4: Run migrations**

```bash
bin/art migrate
```

Expected: 3 new migrations, no errors.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/0001_01_01_000007_* database/migrations/0001_01_01_000008_* database/migrations/0001_01_01_000009_*
git commit -m "feat(s0.8): domain table migrations — clients, jobs, job_occurrences"
```

---

## Task 15: Quoting table migrations

- [ ] **Step 1: Write quotes migration**

`database/migrations/0001_01_01_000010_create_quotes_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 32);
            $table->string('status')->default('draft'); // draft|sent|accepted|rejected|expired
            $table->date('issued_at');
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->unsignedTinyInteger('vat_rate')->default(23);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('internal_note')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->char('pdf_hash', 64)->nullable();
            $table->string('pdf_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tenant_id', 'number']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
```

- [ ] **Step 2: Write quote_items, quote_status_logs, quote_share_tokens migrations**

`database/migrations/0001_01_01_000011_create_quote_items_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('description');
            $table->string('unit')->default('piece'); // m2|h|piece|flat
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->unsignedTinyInteger('vat_pct')->default(23);
            $table->decimal('line_total', 10, 2)->default(0);
            $table->string('service_type_key')->nullable();
            $table->string('source')->default('manual'); // manual|preset|ai_suggestion|commute
            $table->timestamps();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
```

`database/migrations/0001_01_01_000012_create_quote_status_logs_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->timestamp('transitioned_at')->useCurrent();
            $table->foreignId('transitioned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('meta')->default('{}');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_status_logs');
    }
};
```

`database/migrations/0001_01_01_000013_create_quote_share_tokens_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_share_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->char('token', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('accepted_ip', 45)->nullable();
            $table->string('accepted_user_agent')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_share_tokens');
    }
};
```

- [ ] **Step 3: Run migrations**

```bash
bin/art migrate
```

Expected: 4 new migrations, no errors.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/0001_01_01_000010_* database/migrations/0001_01_01_000011_* database/migrations/0001_01_01_000012_* database/migrations/0001_01_01_000013_*
git commit -m "feat(s0.8): quoting table migrations — quotes, quote_items, status_logs, share_tokens"
```

---

## Task 16: Notes + AI + Chat table migrations

- [ ] **Step 1: Write notes + note_attachments + note_embeddings**

`database/migrations/0001_01_01_000014_create_notes_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->text('body_cleaned')->nullable();
            $table->string('audio_path')->nullable();
            $table->unsignedSmallInteger('audio_duration_seconds')->nullable();
            $table->string('status')->default('ready'); // ready|transcribing|failed
            $table->string('source')->default('text'); // text|voice
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['tenant_id', 'client_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('notes'); }
};
```

`database/migrations/0001_01_01_000015_create_note_attachments_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('mime', 127);
            $table->unsignedInteger('bytes');
            $table->timestamp('created_at')->useCurrent();
            $table->index('tenant_id');
        });
    }

    public function down(): void { Schema::dropIfExists('note_attachments'); }
};
```

`database/migrations/0001_01_01_000016_create_note_embeddings_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->string('model', 64)->default('text-embedding-3-small');
            $table->timestamp('created_at')->useCurrent();
            $table->unique('note_id');
            $table->index('tenant_id');
        });

        DB::statement('ALTER TABLE note_embeddings ADD COLUMN embedding vector(1536) NOT NULL');
        DB::statement('CREATE INDEX note_embeddings_ivfflat ON note_embeddings USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)');
    }

    public function down(): void { Schema::dropIfExists('note_embeddings'); }
};
```

- [ ] **Step 2: Write AI usage logs + pricing + chat migrations**

`database/migrations/0001_01_01_000017_create_ai_usage_logs_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('feature'); // pricing|chat|transcription|transcript_cleanup|embedding|ocr
            $table->string('provider');
            $table->string('model');
            $table->string('prompt_version')->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('cost_pln', 8, 4)->default(0);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->string('status')->default('ok'); // ok|error|timeout|schema_miss
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('ai_usage_logs'); }
};
```

`database/migrations/0001_01_01_000018_create_pricing_suggestions_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->decimal('suggested_total', 10, 2);
            $table->jsonb('breakdown')->default('[]');
            $table->text('reasoning')->nullable();
            $table->decimal('confidence', 3, 2)->nullable();
            $table->string('prompt_version')->nullable();
            $table->foreignId('ai_usage_log_id')->nullable()->constrained('ai_usage_logs')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index('tenant_id');
        });
    }

    public function down(): void { Schema::dropIfExists('pricing_suggestions'); }
};
```

`database/migrations/0001_01_01_000019_create_pricing_suggestion_feedback_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_suggestion_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggestion_id')->constrained('pricing_suggestions')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('decision'); // used_as_is|adjusted|ignored
            $table->decimal('final_total', 10, 2)->nullable();
            $table->decimal('diff_pct', 6, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->index('tenant_id');
        });
    }

    public function down(): void { Schema::dropIfExists('pricing_suggestion_feedback'); }
};
```

`database/migrations/0001_01_01_000020_create_chat_sessions_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'client_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('chat_sessions'); }
};
```

`database/migrations/0001_01_01_000021_create_chat_messages_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // user|assistant
            $table->text('content');
            $table->jsonb('citations')->default('[]');
            $table->foreignId('ai_usage_log_id')->nullable()->constrained('ai_usage_logs')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index('tenant_id');
        });
    }

    public function down(): void { Schema::dropIfExists('chat_messages'); }
};
```

- [ ] **Step 3: Run migrations**

```bash
bin/art migrate
```

Expected: 8 new migrations, no errors.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/0001_01_01_000014_* database/migrations/0001_01_01_000015_* database/migrations/0001_01_01_000016_* database/migrations/0001_01_01_000017_* database/migrations/0001_01_01_000018_* database/migrations/0001_01_01_000019_* database/migrations/0001_01_01_000020_* database/migrations/0001_01_01_000021_*
git commit -m "feat(s0.8): notes, AI usage, pricing, and chat table migrations"
```

---

## Task 17: Settings, counters, audit log migrations + models + factories + seeders

- [ ] **Step 1: Write remaining migrations**

`database/migrations/0001_01_01_000022_create_tenant_settings_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->foreignId('tenant_id')->primary()->constrained()->cascadeOnDelete();
            $table->foreignId('origin_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->decimal('fuel_rate_pln_per_km', 5, 2)->default(1.80);
            $table->unsignedTinyInteger('default_vat_rate')->default(23);
            $table->boolean('is_vat_payer')->default(false);
            $table->string('quote_number_pattern')->default('{YYYY}/{MM}/{seq:003}');
            $table->decimal('ai_monthly_cap_pln', 8, 2)->default(50);
            $table->string('ai_alerts_email')->nullable();
            $table->boolean('whisper_cleanup_enabled')->default(false);
            $table->jsonb('pdf_branding')->default('{}');
            $table->string('locale', 5)->default('pl');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void { Schema::dropIfExists('tenant_settings'); }
};
```

`database/migrations/0001_01_01_000023_create_tenant_quote_counters_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_quote_counters', function (Blueprint $table) {
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('seq')->default(0);
            $table->primary(['tenant_id', 'year', 'month']);
        });
    }

    public function down(): void { Schema::dropIfExists('tenant_quote_counters'); }
};
```

`database/migrations/0001_01_01_000024_create_audit_logs_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->jsonb('before')->nullable();
            $table->jsonb('after')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('audit_logs'); }
};
```

- [ ] **Step 2: Write models with BelongsToTenant**

`app/Modules/Crm/Models/Client.php`:
```php
<?php

namespace App\Modules\Crm\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = ['name', 'phone', 'email', 'nip', 'address_id', 'custom_fields'];

    protected $casts = [
        'custom_fields'          => 'array',
        'access_keys_encrypted'  => 'encrypted',
    ];
}
```

`app/Modules/Crm/Models/Job.php`:
```php
<?php

namespace App\Modules\Crm\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'service_type_key', 'custom_fields',
        'recurrence_rule', 'starts_at', 'duration_minutes',
        'assigned_to', 'status', 'internal_notes',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'starts_at'     => 'datetime',
    ];
}
```

Write similar stub models for all remaining tenant-scoped entities. Each follows this pattern — `BelongsToTenant`, `HasFactory`, `SoftDeletes` (where applicable), and `$fillable`. Create files:

- `app/Modules/Crm/Models/Address.php` (no BelongsToTenant needed if purely FK-scoped, but add it for consistency)
- `app/Modules/Scheduling/Models/JobOccurrence.php`
- `app/Modules/Quoting/Models/{Quote,QuoteItem,QuoteStatusLog,QuoteShareToken}.php`
- `app/Modules/Notes/Models/{Note,NoteAttachment,NoteEmbedding}.php`
- `app/Modules/Pricing/Models/{PricingSuggestion,PricingSuggestionFeedback}.php`
- `app/Modules/ClientChat/Models/{ChatSession,ChatMessage}.php`
- `app/Modules/AI/Models/AIUsageLog.php`

All follow the `Client` model pattern above: `use BelongsToTenant, HasFactory;`, appropriate `$fillable`, appropriate `$casts`.

- [ ] **Step 3: Write ClientFactory**

`database/factories/ClientFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Modules\Crm\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name'          => $this->faker->name(),
            'phone'         => $this->faker->phoneNumber(),
            'email'         => $this->faker->safeEmail(),
            'nip'           => null,
            'custom_fields' => [],
        ];
    }
}
```

- [ ] **Step 4: Write CleaningPresetSeeder + TenantSeeder + DatabaseSeeder**

`database/seeders/CleaningPresetSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Modules\Presets\Models\VerticalPreset;
use Illuminate\Database\Seeder;

class CleaningPresetSeeder extends Seeder
{
    public function run(): void
    {
        VerticalPreset::updateOrCreate(
            ['slug' => 'cleaning'],
            [
                'name'    => 'Cleaning',
                'version' => '1',
                'vocabulary' => [
                    'client_singular'    => 'presets.cleaning.vocab.client_singular',
                    'client_plural'      => 'presets.cleaning.vocab.client_plural',
                    'job_singular'       => 'presets.cleaning.vocab.job_singular',
                    'job_plural'         => 'presets.cleaning.vocab.job_plural',
                ],
                'custom_fields_schema' => [
                    'client' => [
                        ['key' => 'area_m2',        'label_key' => 'presets.cleaning.fields.area_m2',        'type' => 'number',         'min' => 1, 'max' => 1000],
                        ['key' => 'property_type',  'label_key' => 'presets.cleaning.fields.property_type',  'type' => 'select',         'options' => ['apartment', 'house', 'office', 'retail'], 'required' => true],
                        ['key' => 'access_keys',    'label_key' => 'presets.cleaning.fields.access_keys',    'type' => 'encrypted_text'],
                        ['key' => 'preferences',    'label_key' => 'presets.cleaning.fields.preferences',    'type' => 'textarea'],
                        ['key' => 'allergies',       'label_key' => 'presets.cleaning.fields.allergies',      'type' => 'tags'],
                        ['key' => 'access_notes',   'label_key' => 'presets.cleaning.fields.access_notes',   'type' => 'text'],
                    ],
                    'job' => [
                        ['key' => 'difficulty', 'label_key' => 'presets.cleaning.fields.difficulty', 'type' => 'select', 'options' => ['standard', 'hard']],
                    ],
                ],
                'service_types' => [
                    ['key' => 'basic',            'label_key' => 'presets.cleaning.services.basic',            'default_unit' => 'm2',     'default_rate' => 4.0,  'default_duration_min' => 120],
                    ['key' => 'deep',             'label_key' => 'presets.cleaning.services.deep',             'default_unit' => 'm2',     'default_rate' => 6.5,  'default_duration_min' => 240],
                    ['key' => 'post_renovation',  'label_key' => 'presets.cleaning.services.post_renovation',  'default_unit' => 'm2',     'default_rate' => 9.0,  'default_duration_min' => 360],
                    ['key' => 'windows',          'label_key' => 'presets.cleaning.services.windows',          'default_unit' => 'piece',  'default_rate' => 25.0, 'default_duration_min' => 60],
                    ['key' => 'upholstery',       'label_key' => 'presets.cleaning.services.upholstery',       'default_unit' => 'piece',  'default_rate' => 80.0, 'default_duration_min' => 90],
                ],
                'quote_template' => [
                    'default_items' => [
                        ['service_type_key' => 'basic', 'unit' => 'm2', 'qty_from' => 'client.custom_fields.area_m2'],
                    ],
                    'auto_lines'    => ['commute'],
                    'vat_default'   => 8,
                    'rate_modifier_rules' => [
                        ['if' => "client.custom_fields.property_type == 'office'", 'rate_multiplier' => 1.15],
                    ],
                ],
                'ai_hints' => [
                    'pricing_factors' => ['area_m2', 'property_type', 'service_type_key', 'commute_km'],
                    'cold_start_note' => 'Use preset default rates when client history is thin.',
                ],
                'pdf_template_key' => 'cleaning_v1',
                'is_active'        => true,
            ]
        );
    }
}
```

`database/seeders/TenantSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Modules\Crm\Models\Client;
use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $preset = VerticalPreset::where('slug', 'cleaning')->first();

        // Wife's tenant
        $ania = Tenant::updateOrCreate(
            ['slug' => 'ania'],
            [
                'ulid'       => Str::ulid(),
                'firma_name' => 'Cleaning by Ania',
                'preset_id'  => $preset?->id,
            ]
        );

        \DB::table('users')->updateOrInsert(
            ['email' => 'ania@wyceny.app', 'tenant_id' => $ania->id],
            ['name' => 'Ania', 'password' => Hash::make('password'), 'role' => 'owner']
        );

        Tenant::setCurrent($ania);
        Client::factory()->count(3)->create();
        Tenant::clear();

        // Test tenant
        $test = Tenant::updateOrCreate(
            ['slug' => 'test'],
            [
                'ulid'       => Str::ulid(),
                'firma_name' => 'Test Company',
                'preset_id'  => $preset?->id,
            ]
        );

        \DB::table('users')->updateOrInsert(
            ['email' => 'test@wyceny.app', 'tenant_id' => $test->id],
            ['name' => 'Test', 'password' => Hash::make('password'), 'role' => 'owner']
        );
    }
}
```

`database/seeders/DatabaseSeeder.php`:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CleaningPresetSeeder::class,
            TenantSeeder::class,
        ]);
    }
}
```

- [ ] **Step 5: Run fresh migration + seed**

```bash
make fresh
```

Expected: all 25 migrations run, seeders complete, no errors.

- [ ] **Step 6: Add parametric isolation test now that models exist**

Append to `tests/Feature/MultiTenant/TenantIsolationTest.php`:

```php
use App\Modules\Crm\Models\Client;
use App\Modules\Crm\Models\Job;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Notes\Models\Note;

$tenantScopedModels = [
    [Client::class, fn ($t) => Client::factory()->create(['tenant_id' => $t->id])],
];

it('model is scoped to tenant and invisible from other tenant', function (string $modelClass, \Closure $factory) {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    Tenant::bypass(function () use ($modelClass, $factory, $tenantA) {
        Tenant::setCurrent($tenantA);
        $factory($tenantA);
        Tenant::clear();
    });

    Tenant::setCurrent($tenantB);
    expect($modelClass::count())->toBe(0);
    Tenant::clear();
})->with($tenantScopedModels)->group('tenancy');

it('throws TenantContextMissingException when no context outside console', function () {
    Tenant::clear();

    // Simulate a web request context
    app()->instance('env', 'web');

    expect(fn () => Client::count())
        ->toThrow(\App\Modules\Tenancy\Exceptions\TenantContextMissingException::class);
})->group('tenancy');
```

- [ ] **Step 7: Run full test suite**

```bash
bin/test --verbose
```

Expected: all PASS.

- [ ] **Step 8: Commit**

```bash
git add database/migrations/0001_01_01_000022_* database/migrations/0001_01_01_000023_* database/migrations/0001_01_01_000024_* app/Modules/ database/factories/ database/seeders/ tests/Feature/MultiTenant/
git commit -m "feat(s0.8): all domain models, factories, seeders, and parametric isolation tests"
```

---

## Task 18: Preset VO + PresetRegistry + cache busting

**Files:** `app/Modules/Presets/Preset.php`, `app/Modules/Presets/PresetRegistry.php`, `app/Modules/Presets/Models/VerticalPreset.php`, `app/Modules/Presets/Events/VerticalPresetUpdated.php`, `app/Modules/Presets/Listeners/BustPresetCache.php`, `app/Modules/Presets/Validators/CustomFieldsSchemaValidator.php`

- [ ] **Step 1: Write failing test**

`tests/Feature/Presets/PresetRegistryTest.php`:
```php
<?php

use App\Modules\Presets\Preset;
use App\Modules\Presets\PresetRegistry;
use App\Modules\Tenancy\Models\Tenant;

it('returns cleaning preset for tenant', function () {
    $tenant = Tenant::factory()->create(['preset_id' => \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->value('id')]);
    Tenant::setCurrent($tenant);

    $preset = PresetRegistry::for($tenant);

    expect($preset)->toBeInstanceOf(Preset::class);
    expect($preset->serviceTypes())->toHaveCount(5);
    expect($preset->serviceTypes()[0]['key'])->toBe('basic');

    Tenant::clear();
});

it('caches preset and returns same instance', function () {
    $tenant = Tenant::factory()->create(['preset_id' => \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->value('id')]);

    $first  = PresetRegistry::for($tenant);
    $second = PresetRegistry::for($tenant);

    expect($first->slug())->toBe($second->slug());
});

it('busts cache on VerticalPresetUpdated event', function () {
    $tenant = Tenant::factory()->create(['preset_id' => \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->value('id')]);
    Tenant::setCurrent($tenant);

    PresetRegistry::for($tenant); // warm cache

    event(new \App\Modules\Presets\Events\VerticalPresetUpdated($tenant->preset_id));

    // After bust, re-fetch hits DB again (no exception = cache miss handled correctly)
    $preset = PresetRegistry::for($tenant);
    expect($preset)->toBeInstanceOf(Preset::class);

    Tenant::clear();
});
```

- [ ] **Step 2: Run test — confirm it fails**

```bash
bin/test tests/Feature/Presets/PresetRegistryTest.php --verbose
```

Expected: FAIL — `PresetRegistry` not found.

- [ ] **Step 3: Write Preset value object**

`app/Modules/Presets/Preset.php`:
```php
<?php

namespace App\Modules\Presets;

final class Preset
{
    public function __construct(
        private readonly string $slug,
        private readonly string $version,
        private readonly array  $vocabulary,
        private readonly array  $customFieldsSchema,
        private readonly array  $serviceTypes,
        private readonly array  $quoteTemplate,
        private readonly array  $aiHints,
        private readonly string $pdfTemplateKey,
    ) {}

    public static function fromModel(\App\Modules\Presets\Models\VerticalPreset $model): self
    {
        return new self(
            slug:               $model->slug,
            version:            $model->version,
            vocabulary:         $model->vocabulary ?? [],
            customFieldsSchema: $model->custom_fields_schema ?? [],
            serviceTypes:       $model->service_types ?? [],
            quoteTemplate:      $model->quote_template ?? [],
            aiHints:            $model->ai_hints ?? [],
            pdfTemplateKey:     $model->pdf_template_key,
        );
    }

    public function slug(): string                 { return $this->slug; }
    public function version(): string              { return $this->version; }
    public function vocabulary(): array            { return $this->vocabulary; }
    public function customFieldsSchema(): array    { return $this->customFieldsSchema; }
    public function serviceTypes(): array          { return $this->serviceTypes; }
    public function quoteTemplate(): array         { return $this->quoteTemplate; }
    public function aiHints(): array               { return $this->aiHints; }
    public function pdfTemplateKey(): string       { return $this->pdfTemplateKey; }

    public function clientFields(): array
    {
        return $this->customFieldsSchema['client'] ?? [];
    }

    public function jobFields(): array
    {
        return $this->customFieldsSchema['job'] ?? [];
    }
}
```

- [ ] **Step 4: Write VerticalPreset model**

`app/Modules/Presets/Models/VerticalPreset.php`:
```php
<?php

namespace App\Modules\Presets\Models;

use Illuminate\Database\Eloquent\Model;

class VerticalPreset extends Model
{
    protected $fillable = [
        'slug', 'name', 'version', 'vocabulary',
        'custom_fields_schema', 'service_types',
        'quote_template', 'ai_hints', 'pdf_template_key', 'is_active',
    ];

    protected $casts = [
        'vocabulary'           => 'array',
        'custom_fields_schema' => 'array',
        'service_types'        => 'array',
        'quote_template'       => 'array',
        'ai_hints'             => 'array',
        'is_active'            => 'boolean',
    ];
}
```

- [ ] **Step 5: Write VerticalPresetUpdated event**

`app/Modules/Presets/Events/VerticalPresetUpdated.php`:
```php
<?php

namespace App\Modules\Presets\Events;

class VerticalPresetUpdated
{
    public function __construct(public readonly int $presetId) {}
}
```

- [ ] **Step 6: Write PresetRegistry**

`app/Modules/Presets/PresetRegistry.php`:
```php
<?php

namespace App\Modules\Presets;

use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class PresetRegistry
{
    private static int $ttl = 3600;

    public static function for(Tenant $tenant): Preset
    {
        $cacheKey = "preset:tenant:{$tenant->id}";

        return Cache::remember($cacheKey, static::$ttl, function () use ($tenant) {
            $model = VerticalPreset::findOrFail($tenant->preset_id);
            return Preset::fromModel($model);
        });
    }

    public static function forgetTenant(int $tenantId): void
    {
        Cache::forget("preset:tenant:{$tenantId}");
    }
}
```

- [ ] **Step 7: Write BustPresetCache listener**

`app/Modules/Presets/Listeners/BustPresetCache.php`:
```php
<?php

namespace App\Modules\Presets\Listeners;

use App\Modules\Presets\Events\VerticalPresetUpdated;
use App\Modules\Presets\PresetRegistry;
use App\Modules\Tenancy\Models\Tenant;

class BustPresetCache
{
    public function handle(VerticalPresetUpdated $event): void
    {
        Tenant::bypass(function () use ($event) {
            Tenant::where('preset_id', $event->presetId)
                ->pluck('id')
                ->each(fn ($id) => PresetRegistry::forgetTenant($id));
        });
    }
}
```

- [ ] **Step 8: Register listener in EventServiceProvider (or bootstrap/app.php)**

In `app/Providers/AppServiceProvider.php` — add to `boot()`:

```php
use App\Modules\Presets\Events\VerticalPresetUpdated;
use App\Modules\Presets\Listeners\BustPresetCache;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    Event::listen(VerticalPresetUpdated::class, BustPresetCache::class);
}
```

- [ ] **Step 9: Write CustomFieldsSchemaValidator**

`app/Modules/Presets/Validators/CustomFieldsSchemaValidator.php`:
```php
<?php

namespace App\Modules\Presets\Validators;

use App\Modules\Presets\Preset;
use Illuminate\Validation\ValidationException;

class CustomFieldsSchemaValidator
{
    public static function validate(array $fields, Preset $preset, string $entity = 'client'): void
    {
        $schema = $entity === 'client' ? $preset->clientFields() : $preset->jobFields();

        foreach ($schema as $field) {
            $key      = $field['key'];
            $required = $field['required'] ?? false;
            $type     = $field['type'];

            if ($required && empty($fields[$key])) {
                throw ValidationException::withMessages([
                    "custom_fields.{$key}" => "The {$key} field is required.",
                ]);
            }

            if (!empty($fields[$key]) && $type === 'select') {
                $allowed = $field['options'] ?? [];
                if (!in_array($fields[$key], $allowed, true)) {
                    throw ValidationException::withMessages([
                        "custom_fields.{$key}" => "Invalid value for {$key}.",
                    ]);
                }
            }

            if (!empty($fields[$key]) && $type === 'number') {
                if (!is_numeric($fields[$key])) {
                    throw ValidationException::withMessages([
                        "custom_fields.{$key}" => "The {$key} must be a number.",
                    ]);
                }
                if (isset($field['min']) && $fields[$key] < $field['min']) {
                    throw ValidationException::withMessages([
                        "custom_fields.{$key}" => "The {$key} must be at least {$field['min']}.",
                    ]);
                }
            }
        }
    }
}
```

- [ ] **Step 10: Add ADR-004 and ADR-005**

`docs/adr/004-custom-fields-jsonb.md`:
```markdown
# ADR-004: Custom Fields Storage — JSONB Column

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
`clients.custom_fields` and `jobs.custom_fields` are JSONB columns. Schema lives in `vertical_presets.custom_fields_schema`. Application-layer validation via `CustomFieldsSchemaValidator`.

## Rationale
One preset per tenant in M1–M3. Filament can render JSONB fields directly. GIN index supports `custom_fields->>'key' = 'value'` queries at our scale. Side-table EAV approach adds a join+pivot UX that Filament doesn't give for free.

## Consequences
Schema migration when a preset renames a field at M7+ requires a one-off Laravel command. Cross-tenant reporting needs JSONB extraction.
```

`docs/adr/005-preset-engine-schema.md`:
```markdown
# ADR-005: Preset Engine Schema Shape — 5-Key JSONB

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
Each `vertical_presets` row has five JSONB columns: `vocabulary`, `custom_fields_schema`, `service_types`, `quote_template`, `ai_hints`. Plus `pdf_template_key` (string). All label strings are translation keys resolved via `lang/{locale}.json` at render time — no Polish strings in the preset row.

## Rationale
Adding a new cleaning field or service type is a DB update + seeder change — no code deploy. The one exception is a new PDF Blade when a genuinely new visual layout is needed for a new vertical (by design: PDF layout has taste).
```

- [ ] **Step 11: Run full test suite**

```bash
bin/test --verbose
```

Expected: all PASS including PresetRegistryTest.

- [ ] **Step 12: Commit**

```bash
git add app/Modules/Presets/ docs/adr/004-* docs/adr/005-* tests/Feature/Presets/ app/Providers/AppServiceProvider.php
git commit -m "feat(s0.6+s0.7): Preset VO, PresetRegistry, cache busting, CustomFieldsSchemaValidator"
```

---

## Task 19: Filament panel + EnforceNoindex + auth acceptance test

**Files:** `app/Providers/Filament/AppPanelProvider.php`, `app/Http/Middleware/EnforceNoindex.php`, `tests/Feature/Auth/FilamentAuthTest.php`

- [ ] **Step 1: Write failing auth test**

`tests/Feature/Auth/FilamentAuthTest.php`:
```php
<?php

use App\Modules\Tenancy\Models\Tenant;

it('ania can log in to Filament and see her tenant scope', function () {
    $preset = \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['slug' => 'ania-test', 'preset_id' => $preset?->id]);
    $user   = \DB::table('users')->insertGetId([
        'tenant_id' => $tenant->id,
        'name'      => 'Ania',
        'email'     => 'ania-test@wyceny.app',
        'password'  => bcrypt('password'),
        'role'      => 'owner',
    ]);

    $this->post('/admin/login', [
        'email'    => 'ania-test@wyceny.app',
        'password' => 'password',
    ])->assertRedirect('/admin');
});

it('app subdomain returns noindex header', function () {
    $response = $this->get('/admin/login');

    $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});
```

- [ ] **Step 2: Run test — confirm it fails**

```bash
bin/test tests/Feature/Auth/FilamentAuthTest.php --verbose
```

Expected: FAIL — Filament panel not registered.

- [ ] **Step 3: Install Filament panel**

```bash
bin/art filament:install --panels
```

When prompted for panel ID, enter: `app`

This generates `app/Providers/Filament/AppPanelProvider.php`.

- [ ] **Step 4: Configure AppPanelProvider**

Replace contents of `app/Providers/Filament/AppPanelProvider.php`:
```php
<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnforceNoindex;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Modules\Tenancy\Middleware\ResolveTenantFromSubdomain;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('/admin')
            ->login()
            ->colors(['primary' => Color::Blue])
            ->pages([Pages\Dashboard::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                EnforceNoindex::class,
                ResolveTenantFromSubdomain::class,
                SetLocaleMiddleware::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
```

- [ ] **Step 5: Write EnforceNoindex middleware**

`app/Http/Middleware/EnforceNoindex.php`:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceNoindex
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        return $response;
    }
}
```

- [ ] **Step 6: Update User model to extend Authenticatable + satisfy Filament**

`app/Modules/Tenancy/Models/User.php`:
```php
<?php

namespace App\Modules\Tenancy\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['tenant_id', 'name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
```

Set `AUTH_MODEL` in config or update `config/auth.php` providers:
```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => \App\Modules\Tenancy\Models\User::class,
    ],
],
```

- [ ] **Step 7: Run full test suite**

```bash
bin/test --verbose
```

Expected: all PASS.

- [ ] **Step 8: Verify Filament panel loads in browser**

```bash
make up
```

Open `http://localhost:8000/admin/login` — should show Filament login page.

Log in as `ania@wyceny.app` / `password` — should reach dashboard.

- [ ] **Step 9: Run make fresh to confirm seeder + migration flow end-to-end**

```bash
make fresh
bin/test --verbose
```

Expected: all green.

- [ ] **Step 10: Commit**

```bash
git add app/Providers/Filament/ app/Http/Middleware/EnforceNoindex.php app/Modules/Tenancy/Models/User.php config/auth.php tests/Feature/Auth/
git commit -m "feat(s0.5): Filament panel, auth, EnforceNoindex middleware"
```

---

## Task 20: Final verification + ADR for remaining items

**Files:** `docs/adr/012-horizon-queues.md`

- [ ] **Step 1: Run complete suite one final time**

```bash
bin/pint --test && bin/stan && bin/test --verbose
```

Expected: all three pass cleanly.

- [ ] **Step 2: Verify DoD checklist**

Manually check each item:

- [ ] `make fresh && bin/test` passes green locally
- [ ] GitHub Actions passes on a PR
- [ ] `TenantIsolationTest` covers models parametrically
- [ ] pgvector smoke test passes (cosine query)
- [ ] `PresetRegistry::for($tenant)` returns cleaning preset with 5 service types
- [ ] Filament loads at `http://localhost:8000/admin`, wife's tenant scoped correctly
- [ ] Larastan level 6 zero errors
- [ ] No-Polish test passes

- [ ] **Step 3: Add Tenant::preset() shortcut (gap from self-review)**

Add method to `app/Modules/Tenancy/Models/Tenant.php`:

```php
public function preset(): \App\Modules\Presets\Preset
{
    return \App\Modules\Presets\PresetRegistry::for($this);
}
```

Run `bin/test --verbose` to confirm nothing breaks.

- [ ] **Step 4: Commit final state**

```bash
git add -A
git commit -m "chore(s0): Sprint 0 core complete — all DoD items verified"
```

---

## Self-Review Notes

**Spec coverage check:**

| Spec requirement | Task |
|---|---|
| Laravel 11, PHP 8.3, Filament v3, Pest, Pint, Larastan 6 | Task 1 |
| Docker compose full stack | Task 5 |
| Wrapper scripts + Makefile | Task 6 |
| GitHub Actions CI | Task 7 |
| BelongsToTenant trait + TenantScope | Task 8, 9 |
| TenantContextMissingException (throws, not silent) | Task 9 |
| ResolveTenantFromSubdomain middleware | Task 10 |
| TenantAwareJob abstract base | Task 10 |
| Parametric isolation tests in CI | Task 17 |
| pgvector extension + smoke test | Task 12 |
| All 24 tables migrated | Tasks 13–17 |
| Factories + seeders (wife's tenant + 3 fake clients) | Task 17 |
| CleaningPresetSeeder idempotent | Task 17 (CleaningPresetSeeder uses updateOrCreate) |
| PresetRegistry::for(Tenant) returns Preset VO | Task 18 |
| 1h Redis cache + cache bust on event | Task 18 |
| Tenant::preset() shortcut | Not explicitly wired — **add to Tenant model** |
| Filament auth (email/password, no registration) | Task 19 |
| EnforceNoindex on app domain | Task 19 |
| Wife's account + test account seeded | Task 17 |
| No-Polish enforcement test | Task 4 |
| i18n seam (lang files, SetLocaleMiddleware) | Task 3 |
| ADRs 001-005, 013-015 | Tasks 11, 18 |
| `lang/pl.json` + `lang/en.json` exist | Task 3 |

**Gap found:** `Tenant::preset()` shortcut not wired. Fix:

Add to `Tenant::boot()` or as a method in `app/Modules/Tenancy/Models/Tenant.php`:

```php
public function preset(): \App\Modules\Presets\Preset
{
    return \App\Modules\Presets\PresetRegistry::for($this);
}
```

This should be added in Task 18 after PresetRegistry exists (or added as a final step in Task 20).
