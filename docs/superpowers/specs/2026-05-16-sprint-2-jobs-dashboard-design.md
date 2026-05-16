# Sprint 2 — Jobs, Dashboard & Tenant Settings Design

**Date:** 2026-05-16
**Sprint:** 2 (Weeks 4–5)
**Stories:** S2.1 – S2.6
**Source docs:** `product-vision.md`, `adr/016-ai-business-assistant.md`, `sprint-plan.md §4`

---

## Goal

Wife can plan and track her entire work week in the app. She sees today's jobs on the dashboard when she opens it, can create recurring jobs linked to clients, mark them done, and see how far each client is from home. The app becomes her daily operations centre — the data layer the AI will read from Sprint 3 onward.

**Demo at end:** Wife opens app → dashboard shows today's 3 jobs with client names, times, and drive distances. She creates a new weekly job for a new client, marks yesterday's job as completed, and sees this week's revenue on the dashboard.

---

## Approach

Filament-first, consistent with Sprint 1. Jobs and settings are Filament resources/pages. Dashboard uses Filament's widget system. Recurrence is a simplified enum (not full RRULE) for Sprint 2. Commute distance uses Haversine formula over existing geocoded lat/lng — no additional API key required.

---

## Architecture

Five units added on top of the Sprint 1 state:

| Unit | Responsibility |
|---|---|
| `Job` model + `JobResource` | CRUD for cleaning jobs linked to clients; recurrence rule; price per visit |
| `OccurrenceRelationManager` | Manages job occurrences (generated on job create); complete / skip / reschedule |
| `TenantSettingsPage` | Filament page for home base address + fuel rate; creates/updates `tenant_settings` row |
| `DistanceService` | Haversine distance between two geocoded addresses; caches in `distance_caches` |
| `DashboardPage` + widgets | Replaces Filament default landing; four widgets: today, week, revenue, overdue |

---

## Existing Schema (Sprint 0, no changes needed except one column)

**`jobs` table** (already migrated):
- `client_id`, `service_type_key`, `custom_fields` JSONB
- `recurrence_rule` (nullable string) — Sprint 2 uses: `null` / `weekly` / `biweekly` / `monthly`
- `starts_at` timestampTz, `duration_minutes` smallint (default 60)
- `status` string (default `planned`) — Sprint 2 values: `planned` / `completed` / `cancelled` / `skipped`
- `internal_notes` text nullable
- **Missing:** `price_pln DECIMAL(8,2) nullable` — must add via migration (needed for revenue widget)

**`job_occurrences` table** (already migrated):
- `job_id`, `occurrence_at`, `status`, `rescheduled_to`, `completed_at`
- `JobOccurrence` model already exists at `app/Modules/Scheduling/Models/JobOccurrence.php`

**`tenant_settings` table** (already migrated):
- `origin_address_id` FK to `addresses` (nullable) — home base
- `fuel_rate_pln_per_km` decimal (default 1.80)
- `is_vat_payer`, `default_vat_rate`, `locale`

**`distance_caches` table** (already migrated):
- `origin_address_id`, `destination_address_id`, `distance_meters`, `duration_seconds`
- Sprint 2 stores Haversine result in `distance_meters`; `duration_seconds` = 0 (routing not yet computed)

---

## Stories

### S2.1 — Job Model + JobResource

**Migration:** `add_price_pln_to_jobs` — adds `price_pln DECIMAL(8,2) nullable after status`.

**`App\Modules\Scheduling\Models\Job`:**
```php
use BelongsToTenant, HasFactory, SoftDeletes;

protected $fillable = [
    'client_id', 'service_type_key', 'custom_fields',
    'recurrence_rule', 'starts_at', 'duration_minutes',
    'status', 'price_pln', 'internal_notes',
];

protected $casts = [
    'custom_fields' => 'array',
    'starts_at'     => 'datetime',
    'price_pln'     => 'decimal:2',
];

public function client(): BelongsTo  // → Client
public function occurrences(): HasMany  // → JobOccurrence
```

**`App\Modules\Scheduling\Filament\Resources\JobResource`** with pages: ListJobs, CreateJob, EditJob, ViewJob.

**Form fields:**
- `client_id` — Select, searchable, options from `Client::pluck('name', 'id')`, required
- `service_type_key` — Select, options from `$tenant->preset()->serviceTypes()` (key → `__($labelKey)`), required
- `starts_at` — DateTimePicker, required
- `duration_minutes` — TextInput numeric, suffix "min", default 60
- `price_pln` — TextInput numeric, prefix "PLN", nullable
- `recurrence_rule` — Select, options: `[null => __('job.recurrence.once'), 'weekly' => ..., 'biweekly' => ..., 'monthly' => ...]`, default null
- `custom_fields.difficulty` — Select (standard / hard), from preset job custom_fields_schema
- `internal_notes` — Textarea, nullable

**Table columns:** client name (searchable), service_type_key (formatted via preset), starts_at (sortable), status badge, duration_minutes, price_pln.

**Table filters:** status select, recurrence_rule select, client_type (via join).

**Default sort:** `starts_at DESC`.

---

### S2.2 — Job Occurrences

**Occurrence generation** (in `CreateJob::afterCreate()`):

When a job is saved, generate occurrences based on `recurrence_rule`:
- `null` → 1 occurrence at `starts_at`
- `weekly` → 12 occurrences (weekly from `starts_at`)
- `biweekly` → 6 occurrences (every 14 days from `starts_at`)
- `monthly` → 3 occurrences (monthly from `starts_at`)

Each occurrence: `status = 'planned'`, `occurrence_at = computed date`.

Do not regenerate if editing an existing job (occurrences are managed independently after creation).

**`OccurrenceRelationManager`** on `JobResource::getRelations()`:

Table columns: `occurrence_at` (date+time), status badge, `completed_at`.

Actions:
- **Complete** — sets `status = 'completed'`, `completed_at = now()`. Available when status is `planned`.
- **Skip** — sets `status = 'skipped'`. Available when status is `planned`.
- **Reschedule** — form with DateTimePicker `rescheduled_to`, sets `status = 'rescheduled'`. Available when status is `planned`.

No create action (occurrences are generated, not manually added).
No edit action (use reschedule instead).
Bulk actions: none.

Default sort: `occurrence_at ASC`.

---

### S2.3 — TenantSettings Page

**`App\Modules\Tenancy\Models\TenantSettings`:**
```php
protected $table = 'tenant_settings';
protected $primaryKey = 'tenant_id';
public $incrementing = false;
public $timestamps = false;  // table has only updated_at via useCurrent

protected $fillable = [
    'origin_address_id', 'fuel_rate_pln_per_km',
    'is_vat_payer', 'default_vat_rate', 'locale',
];

protected $casts = [
    'fuel_rate_pln_per_km' => 'decimal:2',
    'is_vat_payer'         => 'boolean',
];

public function originAddress(): BelongsTo  // → Address
```

**`App\Filament\Pages\TenantSettingsPage`** extends `Filament\Pages\Page`:

- Navigation icon: `heroicon-o-cog-6-tooth`, label `__('settings.nav_label')`
- Route: `/admin/settings`
- On mount: loads `TenantSettings::where('tenant_id', Tenant::currentId())->first()` (or creates with defaults)
- On save: upsert settings row; if address fields changed, create/update `Address` record, geocode it via `GeocodeAddressJob`, set `origin_address_id`

**Form fields:**
- `addr_line1` — TextInput `->dehydrated(false)`, label "Ulica i numer"
- `addr_city` — TextInput `->dehydrated(false)`, label "Miasto"
- `fuel_rate_pln_per_km` — TextInput numeric, suffix "PLN/km", default 1.80
- `is_vat_payer` — Toggle
- `default_vat_rate` — TextInput numeric, suffix "%", visible when `is_vat_payer = true`
- `locale` — Select (pl / en)

Address fields use the same `->dehydrated(false)` + `getRawState()` pattern established in Sprint 1 (CreateClient/EditClient).

---

### S2.4 — DistanceService + Commute Display

**`App\Modules\Integrations\Distance\DistanceService`:**

```php
public function getDistance(
    int $tenantId,
    Address $origin,
    Address $destination
): ?DistanceResult
```

Logic:
1. Check `distance_caches` for `(tenant_id, origin_address_id, destination_address_id)` — return cached if found.
2. If either address has null `lat`/`lng` — return null (not yet geocoded).
3. Compute Haversine distance in meters.
4. Store in `distance_caches` with `duration_seconds = 0` (routing not computed in Sprint 2).
5. Return `DistanceResult`.

**`App\Modules\Integrations\Distance\DistanceResult`** (readonly class):
```php
readonly class DistanceResult {
    public float $distanceKm;
    public float $commuteCostPln;  // distanceKm * 2 * fuelRate (round trip)
    public string $label;          // "~12 km · ~22 PLN"
}
```

**Commute display locations:**

1. **Job ViewClient infolist** — entry `Commute` showing `DistanceResult->label`, or "Address not geocoded yet" when null.
2. **Job table column** — `TextColumn` `commute` computed via `->state(fn($record) => ...)`.
3. **Dashboard TodayJobsWidget** — each job card shows distance + cost.

---

### S2.5 — Dashboard Home Screen

**`App\Filament\Pages\Dashboard`** extends `Filament\Pages\Dashboard`:

- Registered in `AppPanelProvider` as the default page (replaces built-in dashboard)
- Navigation icon: `heroicon-o-home`, label `__('dashboard.nav_label')`
- Route: `/admin` (root)

**Four widgets** (registered in `getWidgets()`):

**`App\Filament\Widgets\TodayJobsWidget`** extends `BaseWidget`:
- Displays a table of today's `job_occurrences` (occurrence_at between today 00:00 and today 23:59, status = 'planned' or 'completed')
- Columns: time (occurrence_at formatted as HH:mm), client name, service type, distance (via DistanceService), status badge
- Quick actions: Complete, Skip (same as OccurrenceRelationManager)
- Empty state: "No jobs scheduled for today"

**`App\Filament\Widgets\WeekRevenueWidget`** extends `StatsOverviewWidget`:
- Stat 1: "This week's revenue" — sum of `price_pln` on jobs with completed occurrences this Mon–Sun
- Stat 2: "Jobs this week" — count of completed occurrences this week
- Stat 3: "Jobs next week" — count of planned occurrences next week
- Revenue stat includes week-over-week comparison (↑/↓ vs last week)

**`App\Filament\Widgets\UpcomingJobsWidget`** extends `BaseWidget`:
- Table of planned `job_occurrences` in the next 7 days (excluding today)
- Columns: date, client name, service type, duration
- Max 10 rows; "See all" link to JobResource

**`App\Filament\Widgets\OverdueClientsWidget`** extends `BaseWidget`:
- Clients who have at least one completed job ever, but whose last occurrence was > 42 days ago (6 weeks)
- Columns: client name, last job date, days since
- Max 5 rows; "See all" link to ClientResource
- Empty state: "All clients are active"

---

### S2.6 — Translation Keys

Add to `lang/pl.json` and `lang/en.json`:

```
job.nav_label, job.model_label, job.model_label_plural
job.section.details, job.section.schedule, job.section.occurrences
job.fields.client, job.fields.service_type, job.fields.starts_at
job.fields.duration_minutes, job.fields.price_pln, job.fields.recurrence_rule
job.fields.difficulty, job.fields.internal_notes, job.fields.status
job.status.planned, job.status.completed, job.status.cancelled, job.status.skipped, job.status.rescheduled
job.recurrence.once, job.recurrence.weekly, job.recurrence.biweekly, job.recurrence.monthly
job.occurrences.title, job.occurrences.complete, job.occurrences.skip, job.occurrences.reschedule

settings.nav_label, settings.section.location, settings.section.billing
settings.fields.address_line1, settings.fields.address_city
settings.fields.fuel_rate, settings.fields.is_vat_payer, settings.fields.default_vat_rate, settings.fields.locale
settings.saved

dashboard.nav_label
dashboard.widgets.today_jobs.title, dashboard.widgets.today_jobs.empty
dashboard.widgets.revenue.title, dashboard.widgets.revenue.this_week, dashboard.widgets.revenue.jobs_this_week, dashboard.widgets.revenue.jobs_next_week
dashboard.widgets.upcoming.title
dashboard.widgets.overdue.title, dashboard.widgets.overdue.empty

presets.cleaning.services.basic, presets.cleaning.services.deep
presets.cleaning.services.post_renovation, presets.cleaning.services.windows, presets.cleaning.services.upholstery
presets.cleaning.fields.difficulty
presets.cleaning.difficulty.standard, presets.cleaning.difficulty.hard
presets.cleaning.vocab.client_singular, presets.cleaning.vocab.client_plural
presets.cleaning.vocab.job_singular, presets.cleaning.vocab.job_plural

commute.label, commute.not_geocoded, commute.distance_km, commute.cost_pln
```

---

## File Map

```
app/
  Filament/
    Pages/
      Dashboard.php                          ← new (extends Filament\Pages\Dashboard)
      TenantSettingsPage.php                 ← new
    Widgets/
      TodayJobsWidget.php                    ← new
      WeekRevenueWidget.php                  ← new
      UpcomingJobsWidget.php                 ← new
      OverdueClientsWidget.php               ← new
  Modules/
    Scheduling/
      Models/
        Job.php                              ← new
        JobOccurrence.php                    ← exists (add job() relation)
      Filament/
        Resources/
          JobResource.php                    ← new
          JobResource/
            Pages/
              ListJobs.php                   ← new
              CreateJob.php                  ← new (generates occurrences in afterCreate)
              EditJob.php                    ← new
              ViewJob.php                    ← new
            RelationManagers/
              OccurrenceRelationManager.php  ← new
    Tenancy/
      Models/
        TenantSettings.php                   ← new
    Integrations/
      Distance/
        DistanceService.php                  ← new
        DistanceResult.php                   ← new (readonly class)

database/migrations/
  YYYY_MM_DD_add_price_pln_to_jobs.php      ← new

lang/
  pl.json                                    ← modify (add Sprint 2 keys)
  en.json                                    ← modify (add Sprint 2 keys)

tests/
  Feature/
    Scheduling/
      JobResourceTest.php                    ← new
      OccurrenceTest.php                     ← new
    Tenancy/
      TenantSettingsTest.php                 ← new
    Integrations/
      DistanceServiceTest.php                ← new
    Dashboard/
      DashboardTest.php                      ← new
```

---

## Cross-Cutting Constraints

- All user-facing labels via `__('key')` — no hardcoded Polish strings in PHP
- `Job` model must use `BelongsToTenant` trait (tenant scope + `tenant_id` auto-set)
- `JobOccurrence` model already uses `BelongsToTenant` — no change needed
- `TenantSettings` does NOT use `BelongsToTenant` (it IS the tenant row, not scoped by it)
- `DistanceService` must check for null lat/lng gracefully (address not yet geocoded)
- Dashboard widgets must handle empty state (no jobs, no clients) without errors
- `CreateJob::afterCreate()` occurrence generation must be wrapped in a transaction
- Larastan level 6, Pint clean, all 36 existing tests stay green

---

## Definition of Done

1. `JobResource` CRUD works: create job with client, service type, date, price, recurrence
2. Creating a recurring job generates correct occurrences (weekly → 12, biweekly → 6, monthly → 3)
3. Occurrences can be marked complete, skipped, or rescheduled via OccurrenceRelationManager
4. TenantSettingsPage saves home base address (geocoded) and fuel rate
5. DistanceService returns correct km for two geocoded addresses; caches result
6. Commute label ("~12 km · ~22 PLN") appears on job view and today's dashboard widget
7. Dashboard home screen loads with all four widgets; today's jobs reflect real data
8. WeekRevenueWidget shows correct revenue sum and job counts
9. OverdueClientsWidget correctly identifies clients with no job in 42+ days
10. `bin/test` all green, `bin/stan` zero errors, `bin/pint --test` clean

---

## Deferred

- Google Maps Distance Matrix API for actual drive time (Sprint 3 — needs GOOGLE_MAPS_API_KEY used in production)
- AI pricing suggestions from job history (Sprint 3)
- Quote generation from job + pricing suggestion (Sprint 4)
- Voice note input for assessment notes (Sprint 5+)
- Multi-staff assignment (Sprint 5+)
