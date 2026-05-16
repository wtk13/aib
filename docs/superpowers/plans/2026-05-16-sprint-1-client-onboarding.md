# Sprint 1 — Client + Onboarding Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wife can register her own tenant, manage clients with cleaning-specific custom fields, search/filter them, and add text notes. NIP lookup autofills B2B company data. Address geocoding runs in background.

**Architecture:** Filament-first — registration extends `Filament\Pages\Auth\Register` with a two-step Wizard; client CRUD lives in `ClientResource` inside `app/Modules/Crm/Filament/Resources/`; cleaning custom fields are hard-coded (no dynamic renderer); address inline via virtual form fields + lifecycle hooks; two external services (`GeocodingService`, `GusNipLookupService`) wrapped with `Http::fake()` in tests.

**Tech Stack:** Laravel 11, Filament v3, Livewire 3, Pest, Laravel HTTP client (`Http::fake()`), PostgreSQL, `BelongsToTenant` trait, `TenantAwareJob`

---

## Codebase Context (read before starting)

Key files to understand before writing any code:

| File | Why it matters |
|---|---|
| `app/Modules/Tenancy/Models/Tenant.php` | `Tenant::current()`, `Tenant::bypass()`, `Tenant::setCurrent()` — tenant context API |
| `app/Modules/Tenancy/Concerns/BelongsToTenant.php` | Applied to all tenant-scoped models; auto-sets `tenant_id` on create |
| `app/Jobs/TenantAwareJob.php` | Abstract base for queue jobs; stores `tenantUlid` and switches context in `handle()`. Subclasses MUST call `parent::__construct()` and implement `execute()` |
| `app/Providers/Filament/AppPanelProvider.php` | Where resources and pages are registered with the Filament panel |
| `database/migrations/0001_01_01_000007_create_clients_table.php` | Existing `clients` schema — `name`, `phone`, `email`, `nip`, `address_id`, `custom_fields` JSONB, `access_keys_encrypted` |
| `database/migrations/0001_01_01_000004_create_addresses_table.php` | `line1`, `line2`, `postcode`, `city`, `country` (default PL), `lat`, `lng`, `geocoded_at` |
| `database/migrations/0001_01_01_000014_create_notes_table.php` | `body`, `status` (default 'ready'), `source` (default 'text'), `created_by_user_id` |
| `database/seeders/CleaningPresetSeeder.php` | Populates `vertical_presets` with the cleaning preset. Client custom fields schema lives here. |
| `app/Modules/Presets/Preset.php` | Value object — `vocabulary()`, `customFieldsSchema()`, `serviceTypes()` etc. |
| `tests/Feature/Auth/FilamentAuthTest.php` | Pattern for Livewire/Filament feature tests with `RefreshDatabase` |

**Address field names** (use these exactly — wrong names cause silent failures):
- `line1` (street address), `line2` (optional), `postcode`, `city`, `country` (default `'PL'`)

**Note field names**: `body` (not `content`), `created_by_user_id` (not `user_id`)

**Cleaning custom field keys** (from `CleaningPresetSeeder`):
`area_m2`, `property_type` (options: `apartment/house/office/retail`), `preferences`, `allergies`, `access_notes`
The `access_keys` field has `type: 'encrypted_text'` and maps to the separate `access_keys_encrypted` column — NOT to `custom_fields` JSONB.

---

## File Map

```
app/
  Filament/
    Pages/
      Auth/
        Register.php                             ← new (extends Filament\Pages\Auth\Register)
  Modules/
    Crm/
      Filament/
        Resources/
          ClientResource.php                     ← new
          ClientResource/
            Pages/
              ListClients.php                    ← new
              CreateClient.php                   ← new
              EditClient.php                     ← new
              ViewClient.php                     ← new
            RelationManagers/
              NoteRelationManager.php            ← new
    Integrations/
      Geocoding/
        GeocodingService.php                     ← new
        GeocodeAddressJob.php                    ← new
      Gus/
        GusNipLookupService.php                  ← new

config/
  services.php                                   ← modify (add gus + google_maps entries)

database/migrations/
  YYYY_MM_DD_add_client_type_regon_to_clients.php  ← new

lang/
  pl.json                                        ← modify (add all translation keys)
  en.json                                        ← modify (add all translation keys)

tests/
  Feature/
    Auth/
      RegistrationTest.php                       ← new
    Crm/
      ClientResourceTest.php                     ← new
    Integrations/
      GeocodingServiceTest.php                   ← new
      GusNipLookupServiceTest.php                ← new
```

---

## Task 1: Migration — add `client_type` and `regon` to clients

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_add_client_type_regon_to_clients.php`
- Modify: `app/Modules/Crm/Models/Client.php`
- Modify: `database/factories/ClientFactory.php`

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Crm/ClientResourceTest.php
<?php

use App\Modules\Crm\Models\Client;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

it('client has a client_type defaulting to person', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $client = Client::create(['name' => 'Jan Kowalski']);

    expect($client->client_type)->toBe('person');
});

it('client can store regon', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $client = Client::create([
        'name' => 'Firma ABC',
        'client_type' => 'company',
        'regon' => '123456789',
    ]);

    expect($client->fresh()->regon)->toBe('123456789');
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php --filter="client has a client_type"
```

Expected: FAIL with `Unknown column 'client_type'` or similar.

- [ ] **Step 3: Create the migration**

```bash
docker compose exec app php artisan make:migration add_client_type_regon_to_clients --table=clients
```

Fill in the generated file (name will have a timestamp — use whatever artisan generated):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('client_type', 10)->default('person')->after('id');
            $table->string('regon', 14)->nullable()->after('nip');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'regon']);
        });
    }
};
```

- [ ] **Step 4: Update `Client` model**

```php
<?php

namespace App\Modules\Crm\Models;

use App\Modules\Crm\Models\Address;
use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }

    protected $fillable = [
        'client_type', 'name', 'phone', 'email',
        'nip', 'regon', 'address_id',
        'custom_fields', 'access_keys_encrypted',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'access_keys_encrypted' => 'encrypted',
    ];

    /** @return BelongsTo<Address, $this> */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
```

- [ ] **Step 5: Update `ClientFactory`**

```php
<?php

namespace Database\Factories;

use App\Modules\Crm\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Client> */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'client_type' => 'person',
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'nip' => null,
            'regon' => null,
            'custom_fields' => [],
        ];
    }
}
```

- [ ] **Step 6: Run migration on test DB and run tests**

```bash
docker compose exec -e DB_DATABASE=wyceny_test app php artisan migrate
bin/test tests/Feature/Crm/ClientResourceTest.php
```

Expected: both tests PASS.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/ app/Modules/Crm/Models/Client.php database/factories/ClientFactory.php tests/Feature/Crm/ClientResourceTest.php
git commit -m "feat(s1): add client_type + regon to clients, update model and factory"
```

---

## Task 2: GeocodingService + GeocodeAddressJob

**Files:**
- Create: `app/Modules/Integrations/Geocoding/GeocodingService.php`
- Create: `app/Modules/Integrations/Geocoding/GeocodeAddressJob.php`
- Modify: `config/services.php`
- Create: `tests/Feature/Integrations/GeocodingServiceTest.php`

- [ ] **Step 1: Add config entry to `config/services.php`**

Add inside the returned array:

```php
'google_maps' => [
    'api_key' => env('GOOGLE_MAPS_API_KEY', 'placeholder'),
],
```

- [ ] **Step 2: Write the failing test**

```php
// tests/Feature/Integrations/GeocodingServiceTest.php
<?php

use App\Modules\Crm\Models\Address;
use App\Modules\Integrations\Geocoding\GeocodingService;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('geocodes an address and stores lat/lng', function () {
    $this->seed(CleaningPresetSeeder::class);
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    Http::fake([
        'maps.googleapis.com/*' => Http::response([
            'status' => 'OK',
            'results' => [[
                'geometry' => ['location' => ['lat' => 52.2297, 'lng' => 21.0122]],
            ]],
        ]),
    ]);

    $address = Address::create([
        'line1' => 'ul. Marszałkowska 1',
        'postcode' => '00-001',
        'city' => 'Warszawa',
    ]);

    $service = new GeocodingService();
    $service->geocode($address);

    $fresh = $address->fresh();
    expect((float) $fresh->lat)->toBe(52.2297)
        ->and((float) $fresh->lng)->toBe(21.0122)
        ->and($fresh->geocoded_at)->not->toBeNull();
});

it('does nothing silently when API returns no results', function () {
    $this->seed(CleaningPresetSeeder::class);
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    Http::fake([
        'maps.googleapis.com/*' => Http::response(['status' => 'ZERO_RESULTS', 'results' => []]),
    ]);

    $address = Address::create([
        'line1' => 'Nieznana 999',
        'postcode' => '00-000',
        'city' => 'Nowhere',
    ]);

    $service = new GeocodingService();
    $service->geocode($address);

    expect($address->fresh()->lat)->toBeNull();
});
```

- [ ] **Step 3: Run test to verify it fails**

```bash
bin/test tests/Feature/Integrations/GeocodingServiceTest.php
```

Expected: FAIL with class not found.

- [ ] **Step 4: Create `GeocodingService`**

```php
<?php

namespace App\Modules\Integrations\Geocoding;

use App\Modules\Crm\Models\Address;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    public function geocode(Address $address): void
    {
        $query = implode(', ', array_filter([
            $address->line1,
            $address->postcode,
            $address->city,
            'Polska',
        ]));

        $cacheKey = 'geocode:' . md5($query);

        $result = cache()->rememberForever($cacheKey, function () use ($query) {
            try {
                $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $query,
                    'key' => config('services.google_maps.api_key'),
                    'language' => 'pl',
                    'region' => 'pl',
                ]);

                $data = $response->json();

                if (($data['status'] ?? '') !== 'OK' || empty($data['results'])) {
                    return null;
                }

                return $data['results'][0]['geometry']['location'];
            } catch (\Exception $e) {
                Log::warning('Geocoding failed: ' . $e->getMessage(), ['query' => $query]);
                return null;
            }
        });

        if ($result === null) {
            return;
        }

        $address->update([
            'lat' => $result['lat'],
            'lng' => $result['lng'],
            'geocoded_at' => now(),
        ]);
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

```bash
bin/test tests/Feature/Integrations/GeocodingServiceTest.php
```

Expected: both tests PASS.

- [ ] **Step 6: Write test for GeocodeAddressJob**

Add to `tests/Feature/Integrations/GeocodingServiceTest.php`:

```php
use App\Modules\Integrations\Geocoding\GeocodeAddressJob;
use Illuminate\Support\Facades\Queue;

it('GeocodeAddressJob geocodes the address in tenant context', function () {
    $this->seed(CleaningPresetSeeder::class);
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    Http::fake([
        'maps.googleapis.com/*' => Http::response([
            'status' => 'OK',
            'results' => [[
                'geometry' => ['location' => ['lat' => 50.0614, 'lng' => 19.9372]],
            ]],
        ]),
    ]);

    $address = Address::create([
        'line1' => 'ul. Floriańska 1',
        'postcode' => '31-019',
        'city' => 'Kraków',
    ]);

    // Run with sync queue (default in tests)
    GeocodeAddressJob::dispatch($address->id, $tenant->ulid);

    expect((float) $address->fresh()->lat)->toBe(50.0614);
});
```

Wait — `TenantAwareJob` captures the tenant ULID in `__construct()` automatically. The job stores `$this->tenantUlid`. Let me revise: the job only receives `$addressId`, not the ULID — the ULID is captured from `Tenant::current()` in the constructor.

Revise the test:

```php
it('GeocodeAddressJob geocodes the address in tenant context', function () {
    $this->seed(CleaningPresetSeeder::class);
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    Http::fake([
        'maps.googleapis.com/*' => Http::response([
            'status' => 'OK',
            'results' => [[
                'geometry' => ['location' => ['lat' => 50.0614, 'lng' => 19.9372]],
            ]],
        ]),
    ]);

    $address = Address::create([
        'line1' => 'ul. Floriańska 1',
        'postcode' => '31-019',
        'city' => 'Kraków',
    ]);

    // QUEUE_CONNECTION=sync in tests — job runs immediately
    GeocodeAddressJob::dispatch($address->id);

    expect((float) $address->fresh()->lat)->toBe(50.0614);
});
```

- [ ] **Step 7: Create `GeocodeAddressJob`**

```php
<?php

namespace App\Modules\Integrations\Geocoding;

use App\Jobs\TenantAwareJob;
use App\Modules\Crm\Models\Address;

class GeocodeAddressJob extends TenantAwareJob
{
    public function __construct(
        public readonly int $addressId,
    ) {
        parent::__construct();
    }

    protected function execute(): void
    {
        $address = Address::find($this->addressId);

        if ($address === null) {
            return;
        }

        (new GeocodingService())->geocode($address);
    }
}
```

- [ ] **Step 8: Run all geocoding tests**

```bash
bin/test tests/Feature/Integrations/GeocodingServiceTest.php
```

Expected: all PASS.

- [ ] **Step 9: Commit**

```bash
git add config/services.php app/Modules/Integrations/Geocoding/ tests/Feature/Integrations/GeocodingServiceTest.php
git commit -m "feat(s1): GeocodingService + GeocodeAddressJob with Google Maps API"
```

---

## Task 3: GusNipLookupService

**Files:**
- Create: `app/Modules/Integrations/Gus/GusNipLookupService.php`
- Modify: `config/services.php`
- Create: `tests/Feature/Integrations/GusNipLookupServiceTest.php`

- [ ] **Step 1: Add config entry to `config/services.php`**

Add inside the returned array:

```php
'gus' => [
    'api_key' => env('GUS_API_KEY', 'placeholder'),
    'base_url' => env('GUS_BASE_URL', 'https://wyszukiwarkaregon.stat.gov.pl/api/'),
],
```

- [ ] **Step 2: Write the failing test**

```php
// tests/Feature/Integrations/GusNipLookupServiceTest.php
<?php

use App\Modules\Integrations\Gus\GusNipLookupService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('returns company data for a valid NIP', function () {
    Http::fake([
        'wyszukiwarkaregon.stat.gov.pl/*' => Http::sequence()
            // Login response
            ->push(['sessionId' => 'fake-session-123'])
            // Search response
            ->push([
                'name' => 'Firma ABC Sp. z o.o.',
                'street' => 'ul. Testowa 1',
                'city' => 'Warszawa',
                'postcode' => '00-001',
                'regon' => '123456789',
            ])
            // Logout response
            ->push(['ok' => true]),
    ]);

    $service = new GusNipLookupService();
    $result = $service->lookup('1234567890');

    expect($result)->not->toBeNull()
        ->and($result['name'])->toBe('Firma ABC Sp. z o.o.')
        ->and($result['regon'])->toBe('123456789');
});

it('returns null for an unknown NIP', function () {
    Http::fake([
        'wyszukiwarkaregon.stat.gov.pl/*' => Http::sequence()
            ->push(['sessionId' => 'fake-session-123'])
            ->push(null)  // empty result
            ->push(['ok' => true]),
    ]);

    $service = new GusNipLookupService();
    $result = $service->lookup('0000000000');

    expect($result)->toBeNull();
});

it('returns null and logs warning on API error', function () {
    Http::fake([
        'wyszukiwarkaregon.stat.gov.pl/*' => Http::response(null, 500),
    ]);

    $service = new GusNipLookupService();
    $result = $service->lookup('1234567890');

    expect($result)->toBeNull();
});
```

- [ ] **Step 3: Run test to verify it fails**

```bash
bin/test tests/Feature/Integrations/GusNipLookupServiceTest.php
```

Expected: FAIL with class not found.

- [ ] **Step 4: Create `GusNipLookupService`**

The GUS BIR1 API uses a REST-like interface where you POST to login, then POST to search, then POST to logout. The service abstracts this completely — callers just call `lookup(nip)`.

```php
<?php

namespace App\Modules\Integrations\Gus;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GusNipLookupService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.gus.base_url'), '/');
        $this->apiKey = config('services.gus.api_key');
    }

    public function lookup(string $nip): ?array
    {
        $cacheKey = 'gus:nip:' . $nip;

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($nip) {
            try {
                $sessionId = $this->login();
                if ($sessionId === null) {
                    return null;
                }

                $data = $this->search($sessionId, $nip);
                $this->logout($sessionId);

                return $data;
            } catch (\Exception $e) {
                Log::warning('GUS NIP lookup failed', ['nip' => $nip, 'error' => $e->getMessage()]);
                return null;
            }
        });
    }

    private function login(): ?string
    {
        $response = Http::timeout(5)
            ->withHeader('userKey', $this->apiKey)
            ->post($this->baseUrl . '/Login');

        if (! $response->successful()) {
            return null;
        }

        return $response->json('sessionId');
    }

    private function search(string $sessionId, string $nip): ?array
    {
        $response = Http::timeout(5)
            ->withHeader('sid', $sessionId)
            ->post($this->baseUrl . '/DaneSzukajPodmioty', ['Nip' => $nip]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (empty($data) || ! isset($data['name'])) {
            return null;
        }

        return [
            'name' => $data['name'],
            'line1' => $data['street'] ?? '',
            'city' => $data['city'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'regon' => $data['regon'] ?? '',
        ];
    }

    private function logout(string $sessionId): void
    {
        Http::timeout(3)
            ->withHeader('sid', $sessionId)
            ->post($this->baseUrl . '/Wyloguj')
            ->successful();
    }
}
```

- [ ] **Step 5: Run tests**

```bash
bin/test tests/Feature/Integrations/GusNipLookupServiceTest.php
```

Expected: all PASS.

- [ ] **Step 6: Commit**

```bash
git add config/services.php app/Modules/Integrations/Gus/ tests/Feature/Integrations/GusNipLookupServiceTest.php
git commit -m "feat(s1): GusNipLookupService — GUS BIR1 NIP lookup with 30-day cache"
```

---

## Task 4: Registration Wizard (S1.8)

**Files:**
- Create: `app/Filament/Pages/Auth/Register.php`
- Modify: `app/Providers/Filament/AppPanelProvider.php`
- Create: `tests/Feature/Auth/RegistrationTest.php`

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Auth/RegistrationTest.php
<?php

use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Filament\Pages\Auth\Register as FilamentRegister;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

it('registration page is accessible', function () {
    $this->get('/admin/register')->assertOk();
});

it('can register a new tenant and user', function () {
    $preset = VerticalPreset::where('slug', 'cleaning')->firstOrFail();

    Livewire::test(\App\Filament\Pages\Auth\Register::class)
        ->set('data.name', 'Ania Cleaning')
        ->set('data.email', 'ania@example.com')
        ->set('data.password', 'password123')
        ->set('data.password_confirmation', 'password123')
        ->set('data.preset_id', $preset->id)
        ->call('register')
        ->assertHasNoErrors();

    $user = Tenant::bypass(fn () => User::where('email', 'ania@example.com')->first());
    expect($user)->not->toBeNull();

    $tenant = Tenant::bypass(fn () => Tenant::find($user->tenant_id));
    expect($tenant)->not->toBeNull()
        ->and($tenant->preset_id)->toBe($preset->id)
        ->and($tenant->company_name)->toBe('Ania Cleaning');
});

it('registration fails with duplicate email', function () {
    $preset = VerticalPreset::where('slug', 'cleaning')->firstOrFail();
    $tenant = Tenant::factory()->create();
    Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create(['email' => 'taken@example.com']));

    Livewire::test(\App\Filament\Pages\Auth\Register::class)
        ->set('data.name', 'Other')
        ->set('data.email', 'taken@example.com')
        ->set('data.password', 'password123')
        ->set('data.password_confirmation', 'password123')
        ->set('data.preset_id', $preset->id)
        ->call('register')
        ->assertHasErrors(['data.email']);
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
bin/test tests/Feature/Auth/RegistrationTest.php
```

Expected: FAIL (Register page class not found or 404).

- [ ] **Step 3: Create `app/Filament/Pages/Auth/Register.php`**

```php
<?php

namespace App\Filament\Pages\Auth;

use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as FilamentRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Register extends FilamentRegister
{
    public function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Wizard\Step::make(__('auth.register.step_account'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('auth.register.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('auth.register.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class, 'email'),
                        TextInput::make('password')
                            ->label(__('auth.register.password'))
                            ->password()
                            ->required()
                            ->minLength(8),
                        TextInput::make('password_confirmation')
                            ->label(__('auth.register.password_confirmation'))
                            ->password()
                            ->required()
                            ->same('password'),
                    ]),
                Wizard\Step::make(__('auth.register.step_industry'))
                    ->schema([
                        Radio::make('preset_id')
                            ->label(__('auth.register.industry'))
                            ->options(fn () => VerticalPreset::pluck('name', 'id')->toArray())
                            ->required(),
                    ]),
            ])
            ->submitAction(new HtmlString(Blade::render(
                '<x-filament::button type="submit" size="sm">{{ __("auth.register.submit") }}</x-filament::button>'
            ))),
        ]);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function handleRegistration(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $slug = $this->uniqueSlug(Str::slug($data['name']));

            $tenant = Tenant::bypass(fn () => Tenant::create([
                'ulid' => (string) Str::ulid(),
                'slug' => $slug,
                'company_name' => $data['name'],
                'preset_id' => $data['preset_id'],
            ]));

            Tenant::setCurrent($tenant);

            /** @var User $user */
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'owner',
            ]);

            return $user;
        });
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: 'tenant';
        $i = 1;
        while (Tenant::bypass(fn () => Tenant::where('slug', $slug)->exists())) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
```

- [ ] **Step 4: Register the page in `AppPanelProvider`**

In `app/Providers/Filament/AppPanelProvider.php`, add `->registration(\App\Filament\Pages\Auth\Register::class)` after `->login()`:

```php
return $panel
    ->default()
    ->id('app')
    ->path('/admin')
    ->login()
    ->registration(\App\Filament\Pages\Auth\Register::class)
    // ... rest of config unchanged
```

- [ ] **Step 5: Run tests**

```bash
bin/test tests/Feature/Auth/RegistrationTest.php
```

Expected: all PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Pages/Auth/Register.php app/Providers/Filament/AppPanelProvider.php tests/Feature/Auth/RegistrationTest.php
git commit -m "feat(s1): self-registration wizard — creates tenant + user, selects industry"
```

---

## Task 5: ClientResource — generic fields + address

**Files:**
- Create: `app/Modules/Crm/Filament/Resources/ClientResource.php`
- Create: `app/Modules/Crm/Filament/Resources/ClientResource/Pages/ListClients.php`
- Create: `app/Modules/Crm/Filament/Resources/ClientResource/Pages/CreateClient.php`
- Create: `app/Modules/Crm/Filament/Resources/ClientResource/Pages/EditClient.php`
- Create: `app/Modules/Crm/Filament/Resources/ClientResource/Pages/ViewClient.php`
- Modify: `app/Providers/Filament/AppPanelProvider.php`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/Crm/ClientResourceTest.php`:

```php
use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Crm\Filament\Resources\ClientResource\Pages\CreateClient;
use App\Modules\Crm\Filament\Resources\ClientResource\Pages\ListClients;
use App\Modules\Crm\Filament\Resources\ClientResource\Pages\EditClient;
use App\Modules\Tenancy\Models\User;
use Livewire\Livewire;

// Helper — creates seeded tenant + user, sets context
function actingAsOwner(): User
{
    $preset = \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);
    return $user;
}

it('can list clients', function () {
    $user = actingAsOwner();

    $client = Client::create(['name' => 'Pani Nowak', 'client_type' => 'person']);

    Livewire::actingAs($user)
        ->test(ListClients::class)
        ->assertCanSeeTableRecords([$client]);
});

it('can create a person client', function () {
    $user = actingAsOwner();

    Livewire::actingAs($user)
        ->test(CreateClient::class)
        ->fillForm([
            'client_type' => 'person',
            'name' => 'Jan Testowy',
            'phone' => '600100200',
            'email' => 'jan@test.pl',
            'addr_line1' => 'ul. Przykładowa 1',
            'addr_postcode' => '00-001',
            'addr_city' => 'Warszawa',
        ])
        ->call('create')
        ->assertHasNoErrors();

    $client = Client::where('name', 'Jan Testowy')->first();
    expect($client)->not->toBeNull()
        ->and($client->phone)->toBe('600100200')
        ->and($client->address->line1)->toBe('ul. Przykładowa 1');
});

it('can edit a client', function () {
    $user = actingAsOwner();
    $client = Client::create(['name' => 'Stara Nazwa', 'client_type' => 'person']);

    Livewire::actingAs($user)
        ->test(EditClient::class, ['record' => $client->getRouteKey()])
        ->fillForm(['name' => 'Nowa Nazwa'])
        ->call('save')
        ->assertHasNoErrors();

    expect($client->fresh()->name)->toBe('Nowa Nazwa');
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php --filter="can list clients"
```

Expected: FAIL with class not found.

- [ ] **Step 3: Create `ClientResource.php`**

```php
<?php

namespace App\Modules\Crm\Filament\Resources;

use App\Modules\Crm\Filament\Resources\ClientResource\Pages;
use App\Modules\Crm\Filament\Resources\ClientResource\RelationManagers;
use App\Modules\Crm\Models\Client;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationLabel(): string
    {
        return __('client.nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('client.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('client.model_label_plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('client.section.basic'))
                ->columns(2)
                ->schema([
                    Select::make('client_type')
                        ->label(__('client.fields.client_type'))
                        ->options([
                            'person'  => __('client.type.person'),
                            'company' => __('client.type.company'),
                        ])
                        ->default('person')
                        ->required()
                        ->live(),
                    TextInput::make('name')
                        ->label(__('client.fields.name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label(__('client.fields.phone'))
                        ->tel()
                        ->maxLength(30),
                    TextInput::make('email')
                        ->label(__('client.fields.email'))
                        ->email()
                        ->maxLength(255),
                ]),

            Section::make(__('client.section.company'))
                ->columns(2)
                ->visible(fn ($get) => $get('client_type') === 'company')
                ->schema([
                    TextInput::make('nip')
                        ->label(__('client.fields.nip'))
                        ->maxLength(10),
                    TextInput::make('regon')
                        ->label(__('client.fields.regon'))
                        ->maxLength(14),
                ]),

            Section::make(__('client.section.address'))
                ->columns(3)
                ->schema([
                    TextInput::make('addr_line1')
                        ->label(__('client.fields.address_line1'))
                        ->dehydrated(false)
                        ->maxLength(255),
                    TextInput::make('addr_postcode')
                        ->label(__('client.fields.address_postcode'))
                        ->dehydrated(false)
                        ->maxLength(10),
                    TextInput::make('addr_city')
                        ->label(__('client.fields.address_city'))
                        ->dehydrated(false)
                        ->maxLength(100),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('client.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('client.fields.phone'))
                    ->searchable(),
                TextColumn::make('client_type')
                    ->label(__('client.fields.client_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => __('client.type.' . $state))
                    ->color(fn ($state) => $state === 'company' ? 'warning' : 'gray'),
                TextColumn::make('address.city')
                    ->label(__('client.fields.address_city'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('client.fields.created_at'))
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->searchable()
            ->filters([
                SelectFilter::make('client_type')
                    ->label(__('client.fields.client_type'))
                    ->options([
                        'person'  => __('client.type.person'),
                        'company' => __('client.type.company'),
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NoteRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view'   => Pages\ViewClient::route('/{record}'),
            'edit'   => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 4: Create page classes**

`app/Modules/Crm/Filament/Resources/ClientResource/Pages/ListClients.php`:
```php
<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\Pages;

use App\Modules\Crm\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
```

`app/Modules/Crm/Filament/Resources/ClientResource/Pages/CreateClient.php`:
```php
<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\Pages;

use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Crm\Models\Address;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $addressData = $this->extractAddressData($data);

        if ($addressData !== null) {
            $address = Address::create($addressData);
            $data['address_id'] = $address->id;
        }

        return $data;
    }

    private function extractAddressData(array &$data): ?array
    {
        $line1    = $data['addr_line1'] ?? '';
        $postcode = $data['addr_postcode'] ?? '';
        $city     = $data['addr_city'] ?? '';

        unset($data['addr_line1'], $data['addr_postcode'], $data['addr_city']);

        if (empty($line1) && empty($city)) {
            return null;
        }

        return ['line1' => $line1, 'postcode' => $postcode, 'city' => $city];
    }
}
```

`app/Modules/Crm/Filament/Resources/ClientResource/Pages/EditClient.php`:
```php
<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\Pages;

use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Crm\Models\Address;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $address = $this->getRecord()->address;

        if ($address) {
            $data['addr_line1']    = $address->line1;
            $data['addr_postcode'] = $address->postcode;
            $data['addr_city']     = $address->city;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $line1    = $data['addr_line1'] ?? '';
        $postcode = $data['addr_postcode'] ?? '';
        $city     = $data['addr_city'] ?? '';

        unset($data['addr_line1'], $data['addr_postcode'], $data['addr_city']);

        $addressData = ['line1' => $line1, 'postcode' => $postcode, 'city' => $city];
        $client = $this->getRecord();

        if ($client->address) {
            $client->address->update($addressData);
        } elseif (! empty($line1) || ! empty($city)) {
            $address = Address::create($addressData);
            $data['address_id'] = $address->id;
        }

        return $data;
    }
}
```

`app/Modules/Crm/Filament/Resources/ClientResource/Pages/ViewClient.php`:
```php
<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\Pages;

use App\Modules\Crm\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
```

- [ ] **Step 5: Register `ClientResource` in `AppPanelProvider`**

```php
use App\Modules\Crm\Filament\Resources\ClientResource;

// Inside panel() method, add:
->resources([
    ClientResource::class,
])
```

- [ ] **Step 6: Run tests**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php
```

Expected: all PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Modules/Crm/Filament/ app/Providers/Filament/AppPanelProvider.php
git commit -m "feat(s1): ClientResource — CRUD with inline address handling"
```

---

## Task 6: ClientResource — cleaning custom fields + encrypted access_keys (S1.2, S1.3)

**Files:**
- Modify: `app/Modules/Crm/Filament/Resources/ClientResource.php`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/Crm/ClientResourceTest.php`:

```php
it('saves cleaning custom fields and encrypted access_keys', function () {
    $user = actingAsOwner();

    Livewire::actingAs($user)
        ->test(CreateClient::class)
        ->fillForm([
            'client_type'          => 'person',
            'name'                 => 'Pani Cleaning',
            'custom_fields'        => [
                'area_m2'       => 65,
                'property_type' => 'apartment',
                'preferences'   => 'Proszę nie używać silnych środków',
                'allergies'     => 'Kot',
                'access_notes'  => '3 piętro, winda',
            ],
            'access_keys_encrypted' => 'klucz#42, kod alarmu: 1234',
        ])
        ->call('create')
        ->assertHasNoErrors();

    $client = Client::where('name', 'Pani Cleaning')->first();
    expect($client->custom_fields['area_m2'])->toBe(65)
        ->and($client->custom_fields['property_type'])->toBe('apartment')
        ->and($client->access_keys_encrypted)->toBe('klucz#42, kod alarmu: 1234');
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php --filter="saves cleaning custom fields"
```

Expected: FAIL (fields not in form).

- [ ] **Step 3: Add cleaning custom fields section to `ClientResource::form()`**

Add after the address section (inside `$form->schema([...])`):

```php
Section::make(__('client.section.cleaning'))
    ->schema([
        \Filament\Forms\Components\Grid::make(2)
            ->schema([
                TextInput::make('custom_fields.area_m2')
                    ->label(__('presets.cleaning.fields.area_m2'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(1000)
                    ->suffix('m²'),
                Select::make('custom_fields.property_type')
                    ->label(__('presets.cleaning.fields.property_type'))
                    ->options([
                        'apartment' => __('presets.cleaning.property_type.apartment'),
                        'house'     => __('presets.cleaning.property_type.house'),
                        'office'    => __('presets.cleaning.property_type.office'),
                        'retail'    => __('presets.cleaning.property_type.retail'),
                    ]),
            ]),
        \Filament\Forms\Components\Textarea::make('custom_fields.preferences')
            ->label(__('presets.cleaning.fields.preferences'))
            ->rows(2),
        TextInput::make('custom_fields.allergies')
            ->label(__('presets.cleaning.fields.allergies')),
        TextInput::make('custom_fields.access_notes')
            ->label(__('presets.cleaning.fields.access_notes')),
        \Filament\Forms\Components\Textarea::make('access_keys_encrypted')
            ->label(__('presets.cleaning.fields.access_keys'))
            ->hint(__('client.fields.access_keys_hint'))
            ->rows(2),
    ]),
```

Also add `property_type` to the table filter in `ClientResource::table()` — add after the `client_type` filter:

```php
SelectFilter::make('property_type')
    ->label(__('presets.cleaning.fields.property_type'))
    ->options([
        'apartment' => __('presets.cleaning.property_type.apartment'),
        'house'     => __('presets.cleaning.property_type.house'),
        'office'    => __('presets.cleaning.property_type.office'),
        'retail'    => __('presets.cleaning.property_type.retail'),
    ])
    ->query(fn ($query, $state) => $state['value']
        ? $query->whereJsonContains('custom_fields->property_type', $state['value'])
        : $query),
```

And add `property_type` column to the table:

```php
TextColumn::make('custom_fields.property_type')
    ->label(__('presets.cleaning.fields.property_type'))
    ->formatStateUsing(fn ($state) => $state ? __('presets.cleaning.property_type.' . $state) : '—'),
```

- [ ] **Step 4: Run tests**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php
```

Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Modules/Crm/Filament/Resources/ClientResource.php
git commit -m "feat(s1): cleaning custom fields + encrypted access_keys in ClientResource"
```

---

## Task 7: NoteRelationManager (S1.4)

**Files:**
- Create: `app/Modules/Crm/Filament/Resources/ClientResource/RelationManagers/NoteRelationManager.php`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/Crm/ClientResourceTest.php`:

```php
use App\Modules\Crm\Filament\Resources\ClientResource\RelationManagers\NoteRelationManager;
use App\Modules\Notes\Models\Note;

it('can add a note from the relation manager', function () {
    $user = actingAsOwner();
    $client = Client::create(['name' => 'Test Klient', 'client_type' => 'person']);

    Livewire::actingAs($user)
        ->test(NoteRelationManager::class, [
            'ownerRecord' => $client,
            'pageClass'   => \App\Modules\Crm\Filament\Resources\ClientResource\Pages\ViewClient::class,
        ])
        ->callTableAction('create', data: ['body' => 'Pierwsza notatka'])
        ->assertHasNoTableActionErrors();

    expect(Note::where('client_id', $client->id)->count())->toBe(1)
        ->and(Note::where('client_id', $client->id)->first()->body)->toBe('Pierwsza notatka');
});

it('can delete a note', function () {
    $user = actingAsOwner();
    $client = Client::create(['name' => 'Test Klient 2', 'client_type' => 'person']);
    $note = Note::create([
        'client_id'          => $client->id,
        'body'               => 'Do usunięcia',
        'source'             => 'text',
        'created_by_user_id' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test(NoteRelationManager::class, [
            'ownerRecord' => $client,
            'pageClass'   => \App\Modules\Crm\Filament\Resources\ClientResource\Pages\ViewClient::class,
        ])
        ->callTableAction('delete', $note)
        ->assertHasNoTableActionErrors();

    expect(Note::withTrashed()->find($note->id)?->deleted_at)->not->toBeNull();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php --filter="can add a note"
```

Expected: FAIL with class not found.

- [ ] **Step 3: Create `NoteRelationManager`**

```php
<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\RelationManagers;

use App\Modules\Notes\Models\Note;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class NoteRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('note.relation_title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Textarea::make('body')
                ->label(__('note.fields.body'))
                ->required()
                ->rows(3)
                ->maxLength(5000),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('body')
                    ->label(__('note.fields.body'))
                    ->limit(120)
                    ->wrap(),
                TextColumn::make('created_by_user.name')
                    ->label(__('note.fields.author'))
                    ->default('—'),
                TextColumn::make('created_at')
                    ->label(__('note.fields.created_at'))
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['source'] = 'text';
                        $data['created_by_user_id'] = Auth::id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}
```

- [ ] **Step 4: Add `notes()` relation to `Client` model**

In `app/Modules/Crm/Models/Client.php`, add after the `address()` method:

```php
use App\Modules\Notes\Models\Note;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @return HasMany<Note, $this> */
public function notes(): HasMany
{
    return $this->hasMany(Note::class);
}
```

Also add the import at the top.

- [ ] **Step 5: Run tests**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php
```

Expected: all PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Modules/Crm/Filament/Resources/ClientResource/RelationManagers/ app/Modules/Crm/Models/Client.php
git commit -m "feat(s1): NoteRelationManager — append-only text notes on client page"
```

---

## Task 8: GUS autofill action on ClientResource (S1.5)

**Files:**
- Modify: `app/Modules/Crm/Filament/Resources/ClientResource.php`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/Crm/ClientResourceTest.php`:

```php
use Illuminate\Support\Facades\Http;

it('GUS action fills company fields from NIP', function () {
    $user = actingAsOwner();

    Http::fake([
        'wyszukiwarkaregon.stat.gov.pl/*' => Http::sequence()
            ->push(['sessionId' => 'fake-session'])
            ->push([
                'name'     => 'ABC Service Sp. z o.o.',
                'street'   => 'ul. Nowa 5',
                'city'     => 'Kraków',
                'postcode' => '30-001',
                'regon'    => '987654321',
            ])
            ->push(['ok' => true]),
    ]);

    Livewire::actingAs($user)
        ->test(CreateClient::class)
        ->fillForm(['client_type' => 'company', 'nip' => '1234567890'])
        ->callFormComponentAction('nip', 'lookup_nip')
        ->assertFormSet([
            'name'          => 'ABC Service Sp. z o.o.',
            'regon'         => '987654321',
            'addr_line1'    => 'ul. Nowa 5',
            'addr_city'     => 'Kraków',
            'addr_postcode' => '30-001',
        ]);
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php --filter="GUS action fills"
```

Expected: FAIL.

- [ ] **Step 3: Add GUS action to `nip` field in `ClientResource::form()`**

Replace the current `nip` `TextInput` in the company section with:

```php
TextInput::make('nip')
    ->label(__('client.fields.nip'))
    ->maxLength(10)
    ->suffixAction(
        \Filament\Forms\Components\Actions\Action::make('lookup_nip')
            ->label(__('client.actions.lookup_nip'))
            ->icon('heroicon-o-magnifying-glass')
            ->action(function ($get, $set) {
                $nip = $get('nip');
                if (empty($nip)) {
                    \Filament\Notifications\Notification::make()
                        ->warning()
                        ->title(__('client.actions.lookup_nip_empty'))
                        ->send();
                    return;
                }

                $service = new \App\Modules\Integrations\Gus\GusNipLookupService();
                $data = $service->lookup($nip);

                if ($data === null) {
                    \Filament\Notifications\Notification::make()
                        ->warning()
                        ->title(__('client.actions.lookup_nip_not_found'))
                        ->send();
                    return;
                }

                $set('name', $data['name']);
                $set('regon', $data['regon']);
                $set('addr_line1', $data['line1']);
                $set('addr_city', $data['city']);
                $set('addr_postcode', $data['postcode']);

                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title(__('client.actions.lookup_nip_success'))
                    ->send();
            })
    ),
```

- [ ] **Step 4: Run tests**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php
```

Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Modules/Crm/Filament/Resources/ClientResource.php
git commit -m "feat(s1): GUS NIP autofill action on client form"
```

---

## Task 9: GeocodeAddressJob dispatch on client save (S1.6)

**Files:**
- Modify: `app/Modules/Crm/Filament/Resources/ClientResource/Pages/CreateClient.php`
- Modify: `app/Modules/Crm/Filament/Resources/ClientResource/Pages/EditClient.php`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/Crm/ClientResourceTest.php`:

```php
use App\Modules\Integrations\Geocoding\GeocodeAddressJob;
use Illuminate\Support\Facades\Queue;

it('dispatches GeocodeAddressJob when client is created with an address', function () {
    Queue::fake();
    $user = actingAsOwner();

    Http::fake(); // prevent any real HTTP

    Livewire::actingAs($user)
        ->test(CreateClient::class)
        ->fillForm([
            'client_type'  => 'person',
            'name'         => 'Geocode Test',
            'addr_line1'   => 'ul. Geocodowa 1',
            'addr_postcode' => '00-001',
            'addr_city'    => 'Warszawa',
        ])
        ->call('create')
        ->assertHasNoErrors();

    Queue::assertPushed(GeocodeAddressJob::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php --filter="dispatches GeocodeAddressJob"
```

Expected: FAIL (job not dispatched).

- [ ] **Step 3: Dispatch job in `CreateClient::afterCreate()`**

In `app/Modules/Crm/Filament/Resources/ClientResource/Pages/CreateClient.php`, add after `mutateFormDataBeforeCreate`:

```php
protected function afterCreate(): void
{
    $addressId = $this->getRecord()->address_id;

    if ($addressId !== null) {
        \App\Modules\Integrations\Geocoding\GeocodeAddressJob::dispatch($addressId);
    }
}
```

- [ ] **Step 4: Dispatch job in `EditClient::afterSave()`**

In `app/Modules/Crm/Filament/Resources/ClientResource/Pages/EditClient.php`, add:

```php
protected function afterSave(): void
{
    $addressId = $this->getRecord()->address_id;

    if ($addressId !== null) {
        \App\Modules\Integrations\Geocoding\GeocodeAddressJob::dispatch($addressId);
    }
}
```

- [ ] **Step 5: Run tests**

```bash
bin/test tests/Feature/Crm/ClientResourceTest.php
```

Expected: all PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Modules/Crm/Filament/Resources/ClientResource/Pages/
git commit -m "feat(s1): dispatch GeocodeAddressJob after client create/save"
```

---

## Task 10: Translation keys — pl.json + en.json

**Files:**
- Modify: `lang/pl.json`
- Modify: `lang/en.json`

- [ ] **Step 1: Replace `lang/pl.json` with full key set**

```json
{
    "auth.register.step_account": "Konto",
    "auth.register.step_industry": "Branża",
    "auth.register.name": "Imię i nazwisko / Nazwa firmy",
    "auth.register.email": "Adres e-mail",
    "auth.register.password": "Hasło",
    "auth.register.password_confirmation": "Potwierdź hasło",
    "auth.register.industry": "Wybierz branżę",
    "auth.register.submit": "Utwórz konto",

    "client.nav_label": "Klienci",
    "client.model_label": "Klient",
    "client.model_label_plural": "Klienci",
    "client.section.basic": "Dane podstawowe",
    "client.section.company": "Dane firmy",
    "client.section.address": "Adres",
    "client.section.cleaning": "Szczegóły sprzątania",
    "client.fields.client_type": "Typ klienta",
    "client.fields.name": "Imię i nazwisko / Nazwa",
    "client.fields.phone": "Telefon",
    "client.fields.email": "E-mail",
    "client.fields.nip": "NIP",
    "client.fields.regon": "REGON",
    "client.fields.address_line1": "Ulica i numer",
    "client.fields.address_postcode": "Kod pocztowy",
    "client.fields.address_city": "Miasto",
    "client.fields.created_at": "Dodano",
    "client.fields.access_keys_hint": "Przechowywane w zaszyfrowanej formie",
    "client.type.person": "Osoba fizyczna",
    "client.type.company": "Firma",
    "client.actions.lookup_nip": "Pobierz z GUS",
    "client.actions.lookup_nip_empty": "Wpisz NIP przed wyszukaniem",
    "client.actions.lookup_nip_not_found": "Nie znaleziono firmy dla tego NIP",
    "client.actions.lookup_nip_success": "Dane uzupełnione z GUS",

    "note.relation_title": "Notatki",
    "note.fields.body": "Treść",
    "note.fields.author": "Autor",
    "note.fields.created_at": "Data",

    "presets.cleaning.fields.area_m2": "Powierzchnia",
    "presets.cleaning.fields.property_type": "Typ lokalu",
    "presets.cleaning.fields.preferences": "Preferencje / uwagi",
    "presets.cleaning.fields.allergies": "Alergie / przeciwwskazania",
    "presets.cleaning.fields.access_notes": "Dostęp (domofon, piętro, pies...)",
    "presets.cleaning.fields.access_keys": "Klucze / kody dostępu",
    "presets.cleaning.property_type.apartment": "Mieszkanie",
    "presets.cleaning.property_type.house": "Dom",
    "presets.cleaning.property_type.office": "Biuro",
    "presets.cleaning.property_type.retail": "Lokal usługowy"
}
```

- [ ] **Step 2: Replace `lang/en.json` with English equivalents**

```json
{
    "auth.register.step_account": "Account",
    "auth.register.step_industry": "Industry",
    "auth.register.name": "Full name / Company name",
    "auth.register.email": "Email address",
    "auth.register.password": "Password",
    "auth.register.password_confirmation": "Confirm password",
    "auth.register.industry": "Select your industry",
    "auth.register.submit": "Create account",

    "client.nav_label": "Clients",
    "client.model_label": "Client",
    "client.model_label_plural": "Clients",
    "client.section.basic": "Basic details",
    "client.section.company": "Company details",
    "client.section.address": "Address",
    "client.section.cleaning": "Cleaning details",
    "client.fields.client_type": "Client type",
    "client.fields.name": "Name",
    "client.fields.phone": "Phone",
    "client.fields.email": "Email",
    "client.fields.nip": "NIP (tax ID)",
    "client.fields.regon": "REGON",
    "client.fields.address_line1": "Street and number",
    "client.fields.address_postcode": "Postcode",
    "client.fields.address_city": "City",
    "client.fields.created_at": "Created",
    "client.fields.access_keys_hint": "Stored encrypted — not visible in exports",
    "client.type.person": "Individual",
    "client.type.company": "Company",
    "client.actions.lookup_nip": "Look up from GUS",
    "client.actions.lookup_nip_empty": "Enter a NIP before looking up",
    "client.actions.lookup_nip_not_found": "No company found for this NIP",
    "client.actions.lookup_nip_success": "Company details filled from GUS",

    "note.relation_title": "Notes",
    "note.fields.body": "Content",
    "note.fields.author": "Author",
    "note.fields.created_at": "Date",

    "presets.cleaning.fields.area_m2": "Area",
    "presets.cleaning.fields.property_type": "Property type",
    "presets.cleaning.fields.preferences": "Preferences / notes",
    "presets.cleaning.fields.allergies": "Allergies / contraindications",
    "presets.cleaning.fields.access_notes": "Access (intercom, floor, dog...)",
    "presets.cleaning.fields.access_keys": "Keys / access codes",
    "presets.cleaning.property_type.apartment": "Apartment",
    "presets.cleaning.property_type.house": "House",
    "presets.cleaning.property_type.office": "Office",
    "presets.cleaning.property_type.retail": "Retail unit"
}
```

- [ ] **Step 3: Run full test suite**

```bash
bin/test
```

Expected: all 14 existing + new tests PASS.

- [ ] **Step 4: Run Pint and Larastan**

```bash
bin/pint
bin/stan
```

Both must pass clean.

- [ ] **Step 5: Commit**

```bash
git add lang/
git commit -m "feat(s1): add pl.json and en.json translation keys for Sprint 1"
```

---

## Task 11: Final wiring — migrate test DB + full green

- [ ] **Step 1: Run migration on test DB**

```bash
docker compose exec -e DB_DATABASE=wyceny_test app php artisan migrate
```

Expected: `Nothing to migrate` or migration runs cleanly.

- [ ] **Step 2: Run full test suite**

```bash
bin/test
```

Expected: all tests PASS.

- [ ] **Step 3: Pint + Larastan**

```bash
bin/pint --test
bin/stan
```

Both must report clean / zero errors.

- [ ] **Step 4: Final commit**

```bash
git add -A
git commit -m "feat(s1): Sprint 1 complete — client CRUD, registration wizard, NIP autofill, geocoding"
```

---

## Definition of Done

1. `GET /admin/register` shows two-step wizard; submitting creates Tenant + User, redirects to `/admin`
2. Client CRUD: person/company toggle, address inline, cleaning custom fields, encrypted access_keys
3. GUS button on NIP field (company clients only) fills name + address + REGON
4. Notes tab on client view: append-only textarea, newest first, deletable
5. GeocodeAddressJob dispatched on every client save that has an address
6. Table: search name/phone/email; filter by client_type + property_type
7. `bin/test` all green (existing 14 + new Sprint 1 tests)
8. `bin/stan` zero errors, `bin/pint --test` clean
