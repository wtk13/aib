# Sprint 2 — Jobs, Dashboard & Tenant Settings Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Jobs CRUD, occurrence scheduling, commute distance display, tenant settings page, and a four-widget dashboard home screen that replaces the Filament default landing page.

**Architecture:** Filament-first modular monolith. Jobs live in `app/Modules/Scheduling/`, distance logic in `app/Modules/Integrations/Distance/`, tenant settings in `app/Modules/Tenancy/`. Dashboard + widgets live in `app/Filament/`. Recurrence uses simple enum strings (not RRULE). Commute uses Haversine over existing geocoded lat/lng — no extra API key.

**Tech Stack:** Laravel 11, Filament v3, Pest, Livewire, PostgreSQL. Run all tests via `bin/test`. Lint: `bin/pint`. Static analysis: `bin/stan`.

---

## File Map

| File | Status | Responsibility |
|------|--------|----------------|
| `database/migrations/YYYY_MM_DD_add_price_pln_to_jobs.php` | create | Add `price_pln DECIMAL(8,2) nullable` to jobs table |
| `database/factories/JobFactory.php` | create | Factory for Job model |
| `database/factories/JobOccurrenceFactory.php` | create | Factory for JobOccurrence model |
| `app/Modules/Scheduling/Models/Job.php` | create | Job model with BelongsToTenant, SoftDeletes |
| `app/Modules/Scheduling/Models/JobOccurrence.php` | modify | Add `job()` BelongsTo relation |
| `app/Modules/Crm/Models/Client.php` | modify | Add `jobs()` HasMany relation |
| `app/Modules/Scheduling/Filament/Resources/JobResource.php` | create | JobResource with form, table, filters |
| `app/Modules/Scheduling/Filament/Resources/JobResource/Pages/ListJobs.php` | create | List page |
| `app/Modules/Scheduling/Filament/Resources/JobResource/Pages/CreateJob.php` | create | Create page; generates occurrences in afterCreate() |
| `app/Modules/Scheduling/Filament/Resources/JobResource/Pages/EditJob.php` | create | Edit page |
| `app/Modules/Scheduling/Filament/Resources/JobResource/Pages/ViewJob.php` | create | View page with commute infolist entry |
| `app/Modules/Scheduling/Filament/Resources/JobResource/RelationManagers/OccurrenceRelationManager.php` | create | Complete/skip/reschedule actions |
| `app/Modules/Tenancy/Models/TenantSettings.php` | create | TenantSettings model (no auto-increment PK, no timestamps) |
| `app/Modules/Integrations/Distance/DistanceResult.php` | create | Readonly DTO: distanceKm, commuteCostPln, label |
| `app/Modules/Integrations/Distance/DistanceService.php` | create | Haversine calc, distance_caches read/write |
| `app/Filament/Pages/TenantSettingsPage.php` | create | Settings page: home base address + fuel rate |
| `app/Filament/Pages/Dashboard.php` | create | Custom dashboard replacing Filament default |
| `app/Filament/Widgets/TodayJobsWidget.php` | create | Today's occurrences with quick actions |
| `app/Filament/Widgets/WeekRevenueWidget.php` | create | StatsOverviewWidget: revenue + job counts |
| `app/Filament/Widgets/UpcomingJobsWidget.php` | create | Next 7 days occurrences (max 10) |
| `app/Filament/Widgets/OverdueClientsWidget.php` | create | Clients with no job in 42+ days |
| `app/Providers/Filament/AppPanelProvider.php` | modify | Register JobResource, Dashboard page, TenantSettingsPage, widgets |
| `lang/pl.json` | modify | Add Sprint 2 translation keys |
| `lang/en.json` | modify | Add Sprint 2 translation keys |
| `tests/Feature/Scheduling/JobResourceTest.php` | create | JobResource CRUD + occurrence generation tests |
| `tests/Feature/Scheduling/OccurrenceTest.php` | create | OccurrenceRelationManager action tests |
| `tests/Feature/Tenancy/TenantSettingsTest.php` | create | TenantSettingsPage save tests |
| `tests/Feature/Integrations/DistanceServiceTest.php` | create | DistanceService Haversine + cache tests |
| `tests/Feature/Dashboard/DashboardTest.php` | create | Dashboard widget rendering tests |

---

## Task 1: Migration + Job Model + Factories

**Files:**
- Create: `database/migrations/2026_05_16_000001_add_price_pln_to_jobs.php`
- Create: `database/factories/JobFactory.php`
- Create: `database/factories/JobOccurrenceFactory.php`
- Create: `app/Modules/Scheduling/Models/Job.php`
- Modify: `app/Modules/Scheduling/Models/JobOccurrence.php`
- Modify: `app/Modules/Crm/Models/Client.php`
- Test: `tests/Feature/Scheduling/JobResourceTest.php`

- [ ] **Step 1: Write failing model tests**

```php
<?php
// tests/Feature/Scheduling/JobResourceTest.php
use App\Modules\Crm\Models\Client;
use App\Modules\Scheduling\Models\Job;
use App\Modules\Scheduling\Models\JobOccurrence;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

function jobOwner(): array
{
    $preset = \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user = Tenant::bypass(fn () => \App\Modules\Tenancy\Models\User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);
    return [$tenant, $user];
}

it('can create a job with price_pln', function () {
    [$tenant, $user] = jobOwner();
    $client = Client::factory()->create();

    $job = Job::create([
        'client_id' => $client->id,
        'service_type_key' => 'basic',
        'starts_at' => now()->addDay(),
        'duration_minutes' => 90,
        'price_pln' => '350.00',
        'status' => 'planned',
    ]);

    expect($job->fresh()->price_pln)->toBe('350.00');
});

it('job belongs to client', function () {
    [$tenant, $user] = jobOwner();
    $client = Client::factory()->create();
    $job = Job::factory()->for($client)->create();

    expect($job->client->id)->toBe($client->id);
});

it('client has many jobs', function () {
    [$tenant, $user] = jobOwner();
    $client = Client::factory()->create();
    Job::factory()->count(3)->for($client)->create();

    expect($client->jobs)->toHaveCount(3);
});

it('job occurrence belongs to job', function () {
    [$tenant, $user] = jobOwner();
    $client = Client::factory()->create();
    $job = Job::factory()->for($client)->create();
    $occ = JobOccurrence::factory()->for($job)->create([
        'occurrence_at' => now()->addDay(),
        'status' => 'planned',
    ]);

    expect($occ->job->id)->toBe($job->id);
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker compose exec app bin/test tests/Feature/Scheduling/JobResourceTest.php --filter="can create a job"
```

Expected: FAIL — `App\Modules\Scheduling\Models\Job` not found

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_05_16_000001_add_price_pln_to_jobs.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->decimal('price_pln', 8, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('price_pln');
        });
    }
};
```

- [ ] **Step 4: Create the Job model**

```php
<?php
// app/Modules/Scheduling/Models/Job.php
namespace App\Modules\Scheduling\Models;

use App\Modules\Crm\Models\Client;
use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\JobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    /** @use HasFactory<JobFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected static function newFactory(): JobFactory
    {
        return JobFactory::new();
    }

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

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasMany<JobOccurrence, $this> */
    public function occurrences(): HasMany
    {
        return $this->hasMany(JobOccurrence::class);
    }
}
```

- [ ] **Step 5: Add job() relation to JobOccurrence**

In `app/Modules/Scheduling/Models/JobOccurrence.php`, add after the existing casts:

```php
use App\Modules\Scheduling\Models\Job;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Add inside the class:
/** @return BelongsTo<Job, $this> */
public function job(): BelongsTo
{
    return $this->belongsTo(Job::class);
}
```

The full file becomes:
```php
<?php

namespace App\Modules\Scheduling\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\JobOccurrenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOccurrence extends Model
{
    /** @use HasFactory<JobOccurrenceFactory> */
    use BelongsToTenant, HasFactory;

    protected static function newFactory(): JobOccurrenceFactory
    {
        return JobOccurrenceFactory::new();
    }

    protected $fillable = ['job_id', 'occurrence_at', 'status', 'rescheduled_to', 'completed_at'];

    protected $casts = [
        'occurrence_at' => 'datetime',
        'rescheduled_to' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /** @return BelongsTo<Job, $this> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
```

- [ ] **Step 6: Add jobs() relation to Client**

In `app/Modules/Crm/Models/Client.php`, add import and method:

```php
use App\Modules\Scheduling\Models\Job;

// Add inside the class, after the notes() method:
/** @return HasMany<Job, $this> */
public function jobs(): HasMany
{
    return $this->hasMany(Job::class);
}
```

- [ ] **Step 7: Create JobFactory**

```php
<?php
// database/factories/JobFactory.php
namespace Database\Factories;

use App\Modules\Scheduling\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Job> */
class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        return [
            'service_type_key' => 'basic',
            'starts_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            'duration_minutes' => 60,
            'status' => 'planned',
            'recurrence_rule' => null,
            'price_pln' => null,
            'custom_fields' => [],
            'internal_notes' => null,
        ];
    }
}
```

- [ ] **Step 8: Create JobOccurrenceFactory**

```php
<?php
// database/factories/JobOccurrenceFactory.php
namespace Database\Factories;

use App\Modules\Scheduling\Models\JobOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobOccurrence> */
class JobOccurrenceFactory extends Factory
{
    protected $model = JobOccurrence::class;

    public function definition(): array
    {
        return [
            'occurrence_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => 'planned',
            'rescheduled_to' => null,
            'completed_at' => null,
        ];
    }
}
```

- [ ] **Step 9: Run tests to verify they pass**

```bash
docker compose exec app bin/test tests/Feature/Scheduling/JobResourceTest.php
```

Expected: 4 tests PASS

- [ ] **Step 10: Commit**

```bash
git add database/migrations/2026_05_16_000001_add_price_pln_to_jobs.php \
        database/factories/JobFactory.php \
        database/factories/JobOccurrenceFactory.php \
        app/Modules/Scheduling/Models/Job.php \
        app/Modules/Scheduling/Models/JobOccurrence.php \
        app/Modules/Crm/Models/Client.php \
        tests/Feature/Scheduling/JobResourceTest.php
git commit -m "feat(s2): Job model, migration, factories; add job() and jobs() relations"
```

---

## Task 2: JobResource CRUD

**Files:**
- Create: `app/Modules/Scheduling/Filament/Resources/JobResource.php`
- Create: `app/Modules/Scheduling/Filament/Resources/JobResource/Pages/ListJobs.php`
- Create: `app/Modules/Scheduling/Filament/Resources/JobResource/Pages/CreateJob.php`
- Create: `app/Modules/Scheduling/Filament/Resources/JobResource/Pages/EditJob.php`
- Create: `app/Modules/Scheduling/Filament/Resources/JobResource/Pages/ViewJob.php`
- Modify: `app/Providers/Filament/AppPanelProvider.php`
- Test: `tests/Feature/Scheduling/JobResourceTest.php`

- [ ] **Step 1: Add Filament resource tests**

Append to `tests/Feature/Scheduling/JobResourceTest.php`:

```php
use App\Modules\Scheduling\Filament\Resources\JobResource;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages\CreateJob;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages\ListJobs;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages\EditJob;
use Livewire\Livewire;

it('can list jobs', function () {
    [$tenant, $user] = jobOwner();
    $client = Client::factory()->create();
    Job::factory()->count(3)->for($client)->create();

    Livewire::actingAs($user)
        ->test(ListJobs::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords(Job::all());
});

it('can create a job via resource form', function () {
    [$tenant, $user] = jobOwner();
    $client = Client::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateJob::class)
        ->fillForm([
            'client_id' => $client->id,
            'service_type_key' => 'basic',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 90,
            'price_pln' => '280.00',
            'recurrence_rule' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Job::where('client_id', $client->id)->count())->toBe(1);
});

it('can edit a job', function () {
    [$tenant, $user] = jobOwner();
    $client = Client::factory()->create();
    $job = Job::factory()->for($client)->create(['price_pln' => '200.00']);

    Livewire::actingAs($user)
        ->test(EditJob::class, ['record' => $job->getKey()])
        ->fillForm(['price_pln' => '250.00'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($job->fresh()->price_pln)->toBe('250.00');
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker compose exec app bin/test tests/Feature/Scheduling/JobResourceTest.php --filter="can list jobs"
```

Expected: FAIL — `JobResource` not found

- [ ] **Step 3: Create directory structure**

```bash
mkdir -p app/Modules/Scheduling/Filament/Resources/JobResource/Pages
mkdir -p app/Modules/Scheduling/Filament/Resources/JobResource/RelationManagers
```

- [ ] **Step 4: Create JobResource**

```php
<?php
// app/Modules/Scheduling/Filament/Resources/JobResource.php
namespace App\Modules\Scheduling\Filament\Resources;

use App\Modules\Crm\Models\Client;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages;
use App\Modules\Scheduling\Filament\Resources\JobResource\RelationManagers;
use App\Modules\Scheduling\Models\Job;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function getNavigationLabel(): string
    {
        return __('job.nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('job.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('job.model_label_plural');
    }

    public static function form(Form $form): Form
    {
        $preset = \App\Modules\Tenancy\Models\Tenant::current()?->preset();
        $serviceTypeOptions = [];
        if ($preset) {
            foreach ($preset->serviceTypes() as $st) {
                $serviceTypeOptions[$st['key']] = __($st['label_key']);
            }
        }

        $difficultyOptions = [];
        if ($preset) {
            foreach ($preset->jobFields() as $field) {
                if ($field['key'] === 'difficulty') {
                    foreach ($field['options'] as $opt) {
                        $difficultyOptions[$opt['value']] = __($opt['label_key']);
                    }
                }
            }
        }

        return $form->schema([
            Section::make(__('job.section.details'))
                ->columns(2)
                ->schema([
                    Select::make('client_id')
                        ->label(__('job.fields.client'))
                        ->options(fn () => Client::pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('service_type_key')
                        ->label(__('job.fields.service_type'))
                        ->options($serviceTypeOptions)
                        ->required(),
                    Select::make('recurrence_rule')
                        ->label(__('job.fields.recurrence_rule'))
                        ->options([
                            '' => __('job.recurrence.once'),
                            'weekly' => __('job.recurrence.weekly'),
                            'biweekly' => __('job.recurrence.biweekly'),
                            'monthly' => __('job.recurrence.monthly'),
                        ])
                        ->default('')
                        ->nullable(),
                    TextInput::make('price_pln')
                        ->label(__('job.fields.price_pln'))
                        ->numeric()
                        ->prefix('PLN')
                        ->nullable(),
                ]),
            Section::make(__('job.section.schedule'))
                ->columns(2)
                ->schema([
                    DateTimePicker::make('starts_at')
                        ->label(__('job.fields.starts_at'))
                        ->required(),
                    TextInput::make('duration_minutes')
                        ->label(__('job.fields.duration_minutes'))
                        ->numeric()
                        ->suffix('min')
                        ->default(60)
                        ->required(),
                    Select::make('custom_fields.difficulty')
                        ->label(__('job.fields.difficulty'))
                        ->options($difficultyOptions)
                        ->nullable(),
                    Textarea::make('internal_notes')
                        ->label(__('job.fields.internal_notes'))
                        ->nullable()
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label(__('job.fields.client'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_type_key')
                    ->label(__('job.fields.service_type'))
                    ->formatStateUsing(fn (string $state): string => __('presets.cleaning.services.' . $state)),
                TextColumn::make('starts_at')
                    ->label(__('job.fields.starts_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('job.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'cancelled', 'skipped' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('duration_minutes')
                    ->label(__('job.fields.duration_minutes'))
                    ->suffix(' min'),
                TextColumn::make('price_pln')
                    ->label(__('job.fields.price_pln'))
                    ->prefix('PLN ')
                    ->sortable(),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('job.fields.status'))
                    ->options([
                        'planned' => __('job.status.planned'),
                        'completed' => __('job.status.completed'),
                        'cancelled' => __('job.status.cancelled'),
                        'skipped' => __('job.status.skipped'),
                    ]),
                SelectFilter::make('recurrence_rule')
                    ->label(__('job.fields.recurrence_rule'))
                    ->options([
                        '' => __('job.recurrence.once'),
                        'weekly' => __('job.recurrence.weekly'),
                        'biweekly' => __('job.recurrence.biweekly'),
                        'monthly' => __('job.recurrence.monthly'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OccurrenceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJob::route('/create'),
            'edit' => Pages\EditJob::route('/{record}/edit'),
            'view' => Pages\ViewJob::route('/{record}'),
        ];
    }
}
```

- [ ] **Step 5: Create page classes**

```php
<?php
// app/Modules/Scheduling/Filament/Resources/JobResource/Pages/ListJobs.php
namespace App\Modules\Scheduling\Filament\Resources\JobResource\Pages;

use App\Modules\Scheduling\Filament\Resources\JobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
```

```php
<?php
// app/Modules/Scheduling/Filament/Resources/JobResource/Pages/CreateJob.php
namespace App\Modules\Scheduling\Filament\Resources\JobResource\Pages;

use App\Modules\Scheduling\Filament\Resources\JobResource;
use App\Modules\Scheduling\Models\Job;
use App\Modules\Scheduling\Models\JobOccurrence;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateJob extends CreateRecord
{
    protected static string $resource = JobResource::class;

    protected function afterCreate(): void
    {
        /** @var Job $job */
        $job = $this->getRecord();

        DB::transaction(function () use ($job): void {
            $this->generateOccurrences($job);
        });
    }

    private function generateOccurrences(Job $job): void
    {
        $starts = Carbon::instance($job->starts_at);
        $rule = $job->recurrence_rule ?: null;

        $dates = match ($rule) {
            'weekly'   => $this->weeklyDates($starts, 12),
            'biweekly' => $this->biweeklyDates($starts, 6),
            'monthly'  => $this->monthlyDates($starts, 3),
            default    => [$starts],
        };

        foreach ($dates as $date) {
            JobOccurrence::create([
                'job_id'       => $job->id,
                'occurrence_at' => $date,
                'status'       => 'planned',
            ]);
        }
    }

    /** @return Carbon[] */
    private function weeklyDates(Carbon $start, int $count): array
    {
        return array_map(fn (int $i) => $start->copy()->addWeeks($i), range(0, $count - 1));
    }

    /** @return Carbon[] */
    private function biweeklyDates(Carbon $start, int $count): array
    {
        return array_map(fn (int $i) => $start->copy()->addDays($i * 14), range(0, $count - 1));
    }

    /** @return Carbon[] */
    private function monthlyDates(Carbon $start, int $count): array
    {
        return array_map(fn (int $i) => $start->copy()->addMonths($i), range(0, $count - 1));
    }
}
```

```php
<?php
// app/Modules/Scheduling/Filament/Resources/JobResource/Pages/EditJob.php
namespace App\Modules\Scheduling\Filament\Resources\JobResource\Pages;

use App\Modules\Scheduling\Filament\Resources\JobResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJob extends EditRecord
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
```

```php
<?php
// app/Modules/Scheduling/Filament/Resources/JobResource/Pages/ViewJob.php
namespace App\Modules\Scheduling\Filament\Resources\JobResource\Pages;

use App\Modules\Scheduling\Filament\Resources\JobResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewJob extends ViewRecord
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
```

- [ ] **Step 6: Register resource in AppPanelProvider**

In `app/Providers/Filament/AppPanelProvider.php`:
1. Add import: `use App\Modules\Scheduling\Filament\Resources\JobResource;`
2. Change `->resources([ClientResource::class])` to `->resources([ClientResource::class, JobResource::class])`

- [ ] **Step 7: Run tests to verify they pass**

```bash
docker compose exec app bin/test tests/Feature/Scheduling/JobResourceTest.php
```

Expected: All tests PASS

- [ ] **Step 8: Commit**

```bash
git add app/Modules/Scheduling/Filament/ \
        app/Providers/Filament/AppPanelProvider.php \
        tests/Feature/Scheduling/JobResourceTest.php
git commit -m "feat(s2): JobResource CRUD with form, table, and pages"
```

---

## Task 3: Occurrence Generation

**Files:**
- Modify: `app/Modules/Scheduling/Filament/Resources/JobResource/Pages/CreateJob.php` (already contains logic — this task tests it)
- Test: `tests/Feature/Scheduling/OccurrenceTest.php`

- [ ] **Step 1: Write occurrence generation tests**

```php
<?php
// tests/Feature/Scheduling/OccurrenceTest.php
use App\Modules\Crm\Models\Client;
use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages\CreateJob;
use App\Modules\Scheduling\Models\Job;
use App\Modules\Scheduling\Models\JobOccurrence;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

function occurrenceOwner(): array
{
    $preset = VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);
    return [$tenant, $user];
}

it('creates 1 occurrence for a one-time job', function () {
    [$tenant, $user] = occurrenceOwner();
    $client = Client::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateJob::class)
        ->fillForm([
            'client_id' => $client->id,
            'service_type_key' => 'basic',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'recurrence_rule' => '',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $job = Job::first();
    expect(JobOccurrence::where('job_id', $job->id)->count())->toBe(1);
});

it('creates 12 occurrences for a weekly job', function () {
    [$tenant, $user] = occurrenceOwner();
    $client = Client::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateJob::class)
        ->fillForm([
            'client_id' => $client->id,
            'service_type_key' => 'basic',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'recurrence_rule' => 'weekly',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $job = Job::first();
    expect(JobOccurrence::where('job_id', $job->id)->count())->toBe(12);
});

it('creates 6 occurrences for a biweekly job', function () {
    [$tenant, $user] = occurrenceOwner();
    $client = Client::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateJob::class)
        ->fillForm([
            'client_id' => $client->id,
            'service_type_key' => 'basic',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'recurrence_rule' => 'biweekly',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $job = Job::first();
    expect(JobOccurrence::where('job_id', $job->id)->count())->toBe(6);
});

it('creates 3 occurrences for a monthly job', function () {
    [$tenant, $user] = occurrenceOwner();
    $client = Client::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateJob::class)
        ->fillForm([
            'client_id' => $client->id,
            'service_type_key' => 'basic',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'recurrence_rule' => 'monthly',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $job = Job::first();
    expect(JobOccurrence::where('job_id', $job->id)->count())->toBe(3);
});

it('weekly occurrences are 7 days apart', function () {
    [$tenant, $user] = occurrenceOwner();
    $client = Client::factory()->create();
    $start = now()->addDay()->startOfHour();

    Livewire::actingAs($user)
        ->test(CreateJob::class)
        ->fillForm([
            'client_id' => $client->id,
            'service_type_key' => 'basic',
            'starts_at' => $start->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'recurrence_rule' => 'weekly',
        ])
        ->call('create');

    $job = Job::first();
    $occurrences = JobOccurrence::where('job_id', $job->id)->orderBy('occurrence_at')->get();

    expect($occurrences->first()->occurrence_at->toDateString())->toBe($start->toDateString());
    expect($occurrences->get(1)->occurrence_at->diffInDays($occurrences->first()->occurrence_at))->toBe(7);
});
```

- [ ] **Step 2: Run tests to verify they pass**

```bash
docker compose exec app bin/test tests/Feature/Scheduling/OccurrenceTest.php
```

Expected: All 5 tests PASS (occurrence generation was implemented in Task 2 CreateJob page)

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Scheduling/OccurrenceTest.php
git commit -m "test(s2): occurrence generation tests for all recurrence rules"
```

---

## Task 4: OccurrenceRelationManager

**Files:**
- Create: `app/Modules/Scheduling/Filament/Resources/JobResource/RelationManagers/OccurrenceRelationManager.php`
- Test: `tests/Feature/Scheduling/OccurrenceTest.php`

- [ ] **Step 1: Add relation manager action tests**

Append to `tests/Feature/Scheduling/OccurrenceTest.php`:

```php
use App\Modules\Scheduling\Filament\Resources\JobResource\RelationManagers\OccurrenceRelationManager;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages\ViewJob;

it('can complete an occurrence', function () {
    [$tenant, $user] = occurrenceOwner();
    $client = Client::factory()->create();
    $job = Job::factory()->for($client)->create();
    $occ = JobOccurrence::factory()->for($job)->create([
        'occurrence_at' => now()->subDay(),
        'status' => 'planned',
    ]);

    Livewire::actingAs($user)
        ->test(OccurrenceRelationManager::class, [
            'ownerRecord' => $job,
            'pageClass' => ViewJob::class,
        ])
        ->callTableAction('complete', $occ)
        ->assertHasNoErrors();

    expect($occ->fresh()->status)->toBe('completed');
    expect($occ->fresh()->completed_at)->not->toBeNull();
});

it('can skip an occurrence', function () {
    [$tenant, $user] = occurrenceOwner();
    $client = Client::factory()->create();
    $job = Job::factory()->for($client)->create();
    $occ = JobOccurrence::factory()->for($job)->create([
        'occurrence_at' => now()->addDay(),
        'status' => 'planned',
    ]);

    Livewire::actingAs($user)
        ->test(OccurrenceRelationManager::class, [
            'ownerRecord' => $job,
            'pageClass' => ViewJob::class,
        ])
        ->callTableAction('skip', $occ)
        ->assertHasNoErrors();

    expect($occ->fresh()->status)->toBe('skipped');
});

it('can reschedule an occurrence', function () {
    [$tenant, $user] = occurrenceOwner();
    $client = Client::factory()->create();
    $job = Job::factory()->for($client)->create();
    $occ = JobOccurrence::factory()->for($job)->create([
        'occurrence_at' => now()->addDay(),
        'status' => 'planned',
    ]);
    $newDate = now()->addWeek();

    Livewire::actingAs($user)
        ->test(OccurrenceRelationManager::class, [
            'ownerRecord' => $job,
            'pageClass' => ViewJob::class,
        ])
        ->mountTableAction('reschedule', $occ)
        ->setTableActionData(['rescheduled_to' => $newDate->format('Y-m-d H:i:s')])
        ->callMountedTableAction()
        ->assertHasNoErrors();

    expect($occ->fresh()->status)->toBe('rescheduled');
    expect($occ->fresh()->rescheduled_to->toDateString())->toBe($newDate->toDateString());
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker compose exec app bin/test tests/Feature/Scheduling/OccurrenceTest.php --filter="can complete"
```

Expected: FAIL — `OccurrenceRelationManager` not found

- [ ] **Step 3: Create OccurrenceRelationManager**

```php
<?php
// app/Modules/Scheduling/Filament/Resources/JobResource/RelationManagers/OccurrenceRelationManager.php
namespace App\Modules\Scheduling\Filament\Resources\JobResource\RelationManagers;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OccurrenceRelationManager extends RelationManager
{
    protected static string $relationship = 'occurrences';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('job.occurrences.title');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            DateTimePicker::make('rescheduled_to')
                ->label(__('job.fields.starts_at'))
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('occurrence_at')
            ->columns([
                TextColumn::make('occurrence_at')
                    ->label(__('job.fields.starts_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('job.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'skipped', 'cancelled' => 'danger',
                        'rescheduled' => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('completed_at')
                    ->label(__('job.occurrences.completed_at'))
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—'),
            ])
            ->defaultSort('occurrence_at', 'asc')
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label(__('job.occurrences.complete'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Model $record): bool => $record->status === 'planned')
                    ->action(function (Model $record): void {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('skip')
                    ->label(__('job.occurrences.skip'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (Model $record): bool => $record->status === 'planned')
                    ->action(fn (Model $record): bool => $record->update(['status' => 'skipped'])),
                Tables\Actions\Action::make('reschedule')
                    ->label(__('job.occurrences.reschedule'))
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->visible(fn (Model $record): bool => $record->status === 'planned')
                    ->form([
                        DateTimePicker::make('rescheduled_to')
                            ->label(__('job.fields.starts_at'))
                            ->required(),
                    ])
                    ->action(function (Model $record, array $data): void {
                        $record->update([
                            'status' => 'rescheduled',
                            'rescheduled_to' => $data['rescheduled_to'],
                        ]);
                    }),
            ])
            ->bulkActions([]);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
docker compose exec app bin/test tests/Feature/Scheduling/OccurrenceTest.php
```

Expected: All tests PASS

- [ ] **Step 5: Commit**

```bash
git add app/Modules/Scheduling/Filament/Resources/JobResource/RelationManagers/OccurrenceRelationManager.php \
        tests/Feature/Scheduling/OccurrenceTest.php
git commit -m "feat(s2): OccurrenceRelationManager with complete/skip/reschedule actions"
```

---

## Task 5: TenantSettings Model + DistanceService Models

**Files:**
- Create: `app/Modules/Tenancy/Models/TenantSettings.php`
- Create: `app/Modules/Integrations/Distance/DistanceResult.php`
- Test: `tests/Feature/Tenancy/TenantSettingsTest.php`

- [ ] **Step 1: Write TenantSettings tests**

```php
<?php
// tests/Feature/Tenancy/TenantSettingsTest.php
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSettings;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

it('can create tenant settings', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $settings = TenantSettings::create([
        'tenant_id' => $tenant->id,
        'fuel_rate_pln_per_km' => '2.00',
        'is_vat_payer' => false,
        'locale' => 'pl',
    ]);

    expect($settings->fuel_rate_pln_per_km)->toBe('2.00');
    expect($settings->is_vat_payer)->toBeFalse();
});

it('tenant settings uses tenant_id as primary key', function () {
    $tenant = Tenant::factory()->create();

    $settings = TenantSettings::create([
        'tenant_id' => $tenant->id,
        'fuel_rate_pln_per_km' => '1.80',
    ]);

    expect(TenantSettings::find($tenant->id)?->fuel_rate_pln_per_km)->toBe('1.80');
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker compose exec app bin/test tests/Feature/Tenancy/TenantSettingsTest.php
```

Expected: FAIL — `TenantSettings` not found

- [ ] **Step 3: Create TenantSettings model**

```php
<?php
// app/Modules/Tenancy/Models/TenantSettings.php
namespace App\Modules\Tenancy\Models;

use App\Modules\Crm\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSettings extends Model
{
    protected $table = 'tenant_settings';

    protected $primaryKey = 'tenant_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'origin_address_id',
        'fuel_rate_pln_per_km',
        'is_vat_payer',
        'default_vat_rate',
        'locale',
    ];

    protected $casts = [
        'fuel_rate_pln_per_km' => 'decimal:2',
        'is_vat_payer'         => 'boolean',
    ];

    /** @return BelongsTo<Address, $this> */
    public function originAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'origin_address_id');
    }
}
```

- [ ] **Step 4: Create DistanceResult readonly DTO**

```php
<?php
// app/Modules/Integrations/Distance/DistanceResult.php
namespace App\Modules\Integrations\Distance;

readonly class DistanceResult
{
    public string $label;

    public function __construct(
        public float $distanceKm,
        public float $commuteCostPln,
    ) {
        $this->label = sprintf('~%d km · ~%d PLN', (int) round($distanceKm), (int) round($commuteCostPln));
    }
}
```

- [ ] **Step 5: Run TenantSettings tests**

```bash
docker compose exec app bin/test tests/Feature/Tenancy/TenantSettingsTest.php
```

Expected: Both tests PASS

- [ ] **Step 6: Commit**

```bash
git add app/Modules/Tenancy/Models/TenantSettings.php \
        app/Modules/Integrations/Distance/DistanceResult.php \
        tests/Feature/Tenancy/TenantSettingsTest.php
git commit -m "feat(s2): TenantSettings model and DistanceResult DTO"
```

---

## Task 6: DistanceService

**Files:**
- Create: `app/Modules/Integrations/Distance/DistanceService.php`
- Test: `tests/Feature/Integrations/DistanceServiceTest.php`

- [ ] **Step 1: Write DistanceService tests**

```php
<?php
// tests/Feature/Integrations/DistanceServiceTest.php
use App\Modules\Crm\Models\Address;
use App\Modules\Integrations\Distance\DistanceResult;
use App\Modules\Integrations\Distance\DistanceService;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns null when origin has no lat/lng', function () {
    $tenant = Tenant::factory()->create();
    $origin = Address::create(['line1' => 'ul. Marszałkowska 1', 'city' => 'Warszawa']);
    $dest = Address::create(['line1' => 'ul. Mokotowska 12', 'city' => 'Warszawa', 'lat' => 52.2, 'lng' => 21.0]);

    $service = app(DistanceService::class);
    $result = $service->getDistance($tenant->id, $origin, $dest);

    expect($result)->toBeNull();
});

it('returns null when destination has no lat/lng', function () {
    $tenant = Tenant::factory()->create();
    $origin = Address::create(['line1' => 'ul. Marszałkowska 1', 'city' => 'Warszawa', 'lat' => 52.23, 'lng' => 21.01]);
    $dest = Address::create(['line1' => 'ul. Mokotowska 12', 'city' => 'Warszawa']);

    $service = app(DistanceService::class);
    $result = $service->getDistance($tenant->id, $origin, $dest);

    expect($result)->toBeNull();
});

it('returns DistanceResult with Haversine distance', function () {
    $tenant = Tenant::factory()->create();
    // Warsaw city center to Mokotów — ~3 km straight line
    $origin = Address::create(['line1' => 'ul. Marszałkowska 1', 'city' => 'Warszawa', 'lat' => 52.2297, 'lng' => 21.0122]);
    $dest = Address::create(['line1' => 'ul. Puławska 100', 'city' => 'Warszawa', 'lat' => 52.1976, 'lng' => 21.0122]);

    $service = app(DistanceService::class);
    $result = $service->getDistance($tenant->id, $origin, $dest);

    expect($result)->toBeInstanceOf(DistanceResult::class);
    expect($result->distanceKm)->toBeGreaterThan(2.0)->toBeLessThan(5.0);
    expect($result->label)->toContain('km');
});

it('caches the distance result in distance_caches', function () {
    $tenant = Tenant::factory()->create();
    $origin = Address::create(['line1' => 'ul. A', 'city' => 'Warszawa', 'lat' => 52.2297, 'lng' => 21.0122]);
    $dest = Address::create(['line1' => 'ul. B', 'city' => 'Warszawa', 'lat' => 52.1976, 'lng' => 21.0122]);

    $service = app(DistanceService::class);
    $service->getDistance($tenant->id, $origin, $dest);

    expect(\DB::table('distance_caches')
        ->where('tenant_id', $tenant->id)
        ->where('origin_address_id', $origin->id)
        ->where('destination_address_id', $dest->id)
        ->exists()
    )->toBeTrue();
});

it('returns cached result on second call', function () {
    $tenant = Tenant::factory()->create();
    $origin = Address::create(['line1' => 'ul. A', 'city' => 'Warszawa', 'lat' => 52.2297, 'lng' => 21.0122]);
    $dest = Address::create(['line1' => 'ul. B', 'city' => 'Warszawa', 'lat' => 52.1976, 'lng' => 21.0122]);

    $service = app(DistanceService::class);
    $first = $service->getDistance($tenant->id, $origin, $dest);
    $second = $service->getDistance($tenant->id, $origin, $dest);

    expect(\DB::table('distance_caches')->count())->toBe(1);
    expect($second->distanceKm)->toBe($first->distanceKm);
});

it('computes commute cost with fuel rate', function () {
    $tenant = Tenant::factory()->create();
    $origin = Address::create(['line1' => 'ul. A', 'city' => 'Warszawa', 'lat' => 52.2297, 'lng' => 21.0122]);
    $dest = Address::create(['line1' => 'ul. B', 'city' => 'Warszawa', 'lat' => 52.1976, 'lng' => 21.0122]);

    $service = app(DistanceService::class);
    $result = $service->getDistance($tenant->id, $origin, $dest, fuelRatePln: 2.00);

    // commuteCostPln = distanceKm * 2 * fuelRate
    expect($result->commuteCostPln)->toBeCloseTo($result->distanceKm * 2 * 2.00, 1);
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker compose exec app bin/test tests/Feature/Integrations/DistanceServiceTest.php
```

Expected: FAIL — `DistanceService` not found

- [ ] **Step 3: Create DistanceService**

```php
<?php
// app/Modules/Integrations/Distance/DistanceService.php
namespace App\Modules\Integrations\Distance;

use App\Modules\Crm\Models\Address;
use Illuminate\Support\Facades\DB;

class DistanceService
{
    public function getDistance(
        int $tenantId,
        Address $origin,
        Address $destination,
        float $fuelRatePln = 1.80,
    ): ?DistanceResult {
        if ($origin->lat === null || $origin->lng === null) {
            return null;
        }
        if ($destination->lat === null || $destination->lng === null) {
            return null;
        }

        $cached = DB::table('distance_caches')
            ->where('tenant_id', $tenantId)
            ->where('origin_address_id', $origin->id)
            ->where('destination_address_id', $destination->id)
            ->first();

        if ($cached !== null) {
            $km = $cached->distance_meters / 1000.0;
            return new DistanceResult($km, $km * 2 * $fuelRatePln);
        }

        $distanceMeters = $this->haversineMeters(
            (float) $origin->lat,
            (float) $origin->lng,
            (float) $destination->lat,
            (float) $destination->lng,
        );

        DB::table('distance_caches')->insert([
            'tenant_id'              => $tenantId,
            'origin_address_id'      => $origin->id,
            'destination_address_id' => $destination->id,
            'distance_meters'        => (int) round($distanceMeters),
            'duration_seconds'       => 0,
            'raw_response'           => json_encode(['source' => 'haversine']),
        ]);

        $km = $distanceMeters / 1000.0;
        return new DistanceResult($km, $km * 2 * $fuelRatePln);
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
docker compose exec app bin/test tests/Feature/Integrations/DistanceServiceTest.php
```

Expected: All 6 tests PASS

- [ ] **Step 5: Commit**

```bash
git add app/Modules/Integrations/Distance/DistanceService.php \
        tests/Feature/Integrations/DistanceServiceTest.php
git commit -m "feat(s2): DistanceService with Haversine formula and distance_caches"
```

---

## Task 7: TenantSettingsPage + Commute Display

**Files:**
- Create: `app/Filament/Pages/TenantSettingsPage.php`
- Modify: `app/Modules/Scheduling/Filament/Resources/JobResource/Pages/ViewJob.php`
- Modify: `app/Providers/Filament/AppPanelProvider.php`
- Test: `tests/Feature/Tenancy/TenantSettingsTest.php`

- [ ] **Step 1: Add TenantSettingsPage tests**

Append to `tests/Feature/Tenancy/TenantSettingsTest.php`:

```php
use App\Filament\Pages\TenantSettingsPage;
use App\Modules\Tenancy\Models\User;
use Livewire\Livewire;

it('can load the settings page', function () {
    $tenant = Tenant::factory()->create();
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    Livewire::actingAs($user)
        ->test(TenantSettingsPage::class)
        ->assertSuccessful();
});

it('can save fuel rate in settings', function () {
    $tenant = Tenant::factory()->create();
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    Livewire::actingAs($user)
        ->test(TenantSettingsPage::class)
        ->fillForm(['fuel_rate_pln_per_km' => '2.50'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(TenantSettings::find($tenant->id)?->fuel_rate_pln_per_km)->toBe('2.50');
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker compose exec app bin/test tests/Feature/Tenancy/TenantSettingsTest.php --filter="can load"
```

Expected: FAIL — `TenantSettingsPage` not found

- [ ] **Step 3: Create TenantSettingsPage**

```php
<?php
// app/Filament/Pages/TenantSettingsPage.php
namespace App\Filament\Pages;

use App\Modules\Crm\Models\Address;
use App\Modules\Integrations\Geocoding\GeocodeAddressJob;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TenantSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.tenant-settings-page';

    protected static ?string $slug = 'settings';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('settings.nav_label');
    }

    public function mount(): void
    {
        $tenantId = Tenant::currentId();
        $settings = TenantSettings::find($tenantId)
            ?? new TenantSettings(['tenant_id' => $tenantId, 'fuel_rate_pln_per_km' => '1.80', 'locale' => 'pl']);

        $formData = [
            'fuel_rate_pln_per_km' => $settings->fuel_rate_pln_per_km,
            'is_vat_payer' => $settings->is_vat_payer ?? false,
            'default_vat_rate' => $settings->default_vat_rate ?? 23,
            'locale' => $settings->locale ?? 'pl',
            'addr_line1' => $settings->originAddress?->line1 ?? '',
            'addr_city' => $settings->originAddress?->city ?? '',
        ];

        $this->form->fill($formData);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('settings.section.location'))
                    ->schema([
                        TextInput::make('addr_line1')
                            ->label(__('settings.fields.address_line1'))
                            ->dehydrated(false),
                        TextInput::make('addr_city')
                            ->label(__('settings.fields.address_city'))
                            ->dehydrated(false),
                        TextInput::make('fuel_rate_pln_per_km')
                            ->label(__('settings.fields.fuel_rate'))
                            ->numeric()
                            ->suffix('PLN/km')
                            ->default('1.80'),
                    ]),
                Section::make(__('settings.section.billing'))
                    ->schema([
                        Toggle::make('is_vat_payer')
                            ->label(__('settings.fields.is_vat_payer'))
                            ->live(),
                        TextInput::make('default_vat_rate')
                            ->label(__('settings.fields.default_vat_rate'))
                            ->numeric()
                            ->suffix('%')
                            ->visible(fn ($get) => $get('is_vat_payer')),
                        Select::make('locale')
                            ->label(__('settings.fields.locale'))
                            ->options(['pl' => 'Polski', 'en' => 'English'])
                            ->default('pl'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $raw = $this->form->getRawState();
        $tenantId = Tenant::currentId();

        $settings = TenantSettings::find($tenantId)
            ?? new TenantSettings(['tenant_id' => $tenantId]);

        $settings->fuel_rate_pln_per_km = $data['fuel_rate_pln_per_km'];
        $settings->is_vat_payer = $data['is_vat_payer'] ?? false;
        $settings->default_vat_rate = $data['default_vat_rate'] ?? 23;
        $settings->locale = $data['locale'] ?? 'pl';

        $line1 = $raw['addr_line1'] ?? '';
        $city = $raw['addr_city'] ?? '';

        if (! empty($line1)) {
            if ($settings->originAddress) {
                $settings->originAddress->update(['line1' => $line1, 'city' => $city]);
                GeocodeAddressJob::dispatch($settings->origin_address_id);
            } else {
                $address = Address::create(['line1' => $line1, 'city' => $city]);
                $settings->origin_address_id = $address->id;
                GeocodeAddressJob::dispatch($address->id);
            }
        }

        $settings->save();

        Notification::make()
            ->title(__('settings.saved'))
            ->success()
            ->send();
    }
}
```

- [ ] **Step 4: Create the Blade view**

```bash
mkdir -p resources/views/filament/pages
```

```blade
{{-- resources/views/filament/pages/tenant-settings-page.blade.php --}}
<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                {{ __('settings.saved') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
```

- [ ] **Step 5: Register TenantSettingsPage in AppPanelProvider**

Add import: `use App\Filament\Pages\TenantSettingsPage;`

Change `->pages([Pages\Dashboard::class])` to:
`->pages([Pages\Dashboard::class, TenantSettingsPage::class])`

(The custom Dashboard page doesn't exist yet — it will be created in Task 8. For now keep `Pages\Dashboard::class` which is the Filament built-in.)

- [ ] **Step 6: Run tests to verify they pass**

```bash
docker compose exec app bin/test tests/Feature/Tenancy/TenantSettingsTest.php
```

Expected: All 4 tests PASS

- [ ] **Step 7: Commit**

```bash
git add app/Filament/Pages/TenantSettingsPage.php \
        resources/views/filament/pages/tenant-settings-page.blade.php \
        app/Providers/Filament/AppPanelProvider.php \
        tests/Feature/Tenancy/TenantSettingsTest.php
git commit -m "feat(s2): TenantSettingsPage with home base address and fuel rate"
```

---

## Task 8: Dashboard + Four Widgets

**Files:**
- Create: `app/Filament/Pages/Dashboard.php`
- Create: `app/Filament/Widgets/TodayJobsWidget.php`
- Create: `app/Filament/Widgets/WeekRevenueWidget.php`
- Create: `app/Filament/Widgets/UpcomingJobsWidget.php`
- Create: `app/Filament/Widgets/OverdueClientsWidget.php`
- Modify: `app/Providers/Filament/AppPanelProvider.php`
- Test: `tests/Feature/Dashboard/DashboardTest.php`

- [ ] **Step 1: Write dashboard tests**

```php
<?php
// tests/Feature/Dashboard/DashboardTest.php
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\OverdueClientsWidget;
use App\Filament\Widgets\TodayJobsWidget;
use App\Filament\Widgets\UpcomingJobsWidget;
use App\Filament\Widgets\WeekRevenueWidget;
use App\Modules\Crm\Models\Client;
use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Scheduling\Models\Job;
use App\Modules\Scheduling\Models\JobOccurrence;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Carbon\Carbon;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

function dashboardOwner(): array
{
    $preset = VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);
    return [$tenant, $user];
}

it('loads the dashboard without errors', function () {
    [$tenant, $user] = dashboardOwner();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSuccessful();
});

it('today widget shows only todays planned occurrences', function () {
    [$tenant, $user] = dashboardOwner();
    $client = Client::factory()->create();
    $job = Job::factory()->for($client)->create();

    $todayOcc = JobOccurrence::factory()->for($job)->create([
        'occurrence_at' => now()->setTime(10, 0),
        'status' => 'planned',
    ]);
    $tomorrowOcc = JobOccurrence::factory()->for($job)->create([
        'occurrence_at' => now()->addDay()->setTime(10, 0),
        'status' => 'planned',
    ]);

    Livewire::actingAs($user)
        ->test(TodayJobsWidget::class)
        ->assertCanSeeTableRecords([$todayOcc])
        ->assertCanNotSeeTableRecords([$tomorrowOcc]);
});

it('week revenue widget shows correct sum', function () {
    [$tenant, $user] = dashboardOwner();
    $client = Client::factory()->create();

    // Completed job this week
    $job = Job::factory()->for($client)->create(['price_pln' => '300.00']);
    JobOccurrence::factory()->for($job)->create([
        'occurrence_at' => now()->startOfWeek()->addDay(),
        'status' => 'completed',
        'completed_at' => now()->startOfWeek()->addDay(),
    ]);

    // Planned job this week (should not count)
    $job2 = Job::factory()->for($client)->create(['price_pln' => '200.00']);
    JobOccurrence::factory()->for($job2)->create([
        'occurrence_at' => now()->startOfWeek()->addDays(2),
        'status' => 'planned',
    ]);

    Livewire::actingAs($user)
        ->test(WeekRevenueWidget::class)
        ->assertSee('300');
});

it('overdue widget shows clients with no job in 42+ days', function () {
    [$tenant, $user] = dashboardOwner();
    $activeClient = Client::factory()->create(['name' => 'Active Client']);
    $overdueClient = Client::factory()->create(['name' => 'Overdue Client']);

    // Active client — completed occurrence 10 days ago
    $job1 = Job::factory()->for($activeClient)->create();
    JobOccurrence::factory()->for($job1)->create([
        'occurrence_at' => now()->subDays(10),
        'status' => 'completed',
        'completed_at' => now()->subDays(10),
    ]);

    // Overdue client — completed occurrence 50 days ago
    $job2 = Job::factory()->for($overdueClient)->create();
    JobOccurrence::factory()->for($job2)->create([
        'occurrence_at' => now()->subDays(50),
        'status' => 'completed',
        'completed_at' => now()->subDays(50),
    ]);

    Livewire::actingAs($user)
        ->test(OverdueClientsWidget::class)
        ->assertCanSeeTableRecords([$overdueClient])
        ->assertCanNotSeeTableRecords([$activeClient]);
});

it('upcoming widget shows next 7 days occurrences excluding today', function () {
    [$tenant, $user] = dashboardOwner();
    $client = Client::factory()->create();
    $job = Job::factory()->for($client)->create();

    $todayOcc = JobOccurrence::factory()->for($job)->create([
        'occurrence_at' => now()->setTime(11, 0),
        'status' => 'planned',
    ]);
    $upcomingOcc = JobOccurrence::factory()->for($job)->create([
        'occurrence_at' => now()->addDays(3)->setTime(11, 0),
        'status' => 'planned',
    ]);
    $tooFarOcc = JobOccurrence::factory()->for($job)->create([
        'occurrence_at' => now()->addDays(10)->setTime(11, 0),
        'status' => 'planned',
    ]);

    Livewire::actingAs($user)
        ->test(UpcomingJobsWidget::class)
        ->assertCanSeeTableRecords([$upcomingOcc])
        ->assertCanNotSeeTableRecords([$todayOcc, $tooFarOcc]);
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker compose exec app bin/test tests/Feature/Dashboard/DashboardTest.php --filter="loads the dashboard"
```

Expected: FAIL — `App\Filament\Pages\Dashboard` not found

- [ ] **Step 3: Create Dashboard page**

```php
<?php
// app/Filament/Pages/Dashboard.php
namespace App\Filament\Pages;

use App\Filament\Widgets\OverdueClientsWidget;
use App\Filament\Widgets\TodayJobsWidget;
use App\Filament\Widgets\UpcomingJobsWidget;
use App\Filament\Widgets\WeekRevenueWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.nav_label');
    }

    public function getWidgets(): array
    {
        return [
            WeekRevenueWidget::class,
            TodayJobsWidget::class,
            UpcomingJobsWidget::class,
            OverdueClientsWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}
```

- [ ] **Step 4: Create WeekRevenueWidget**

```php
<?php
// app/Filament/Widgets/WeekRevenueWidget.php
namespace App\Filament\Widgets;

use App\Modules\Scheduling\Models\Job;
use App\Modules\Scheduling\Models\JobOccurrence;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeekRevenueWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $lastWeekStart = $weekStart->copy()->subWeek();
        $lastWeekEnd = $weekEnd->copy()->subWeek();

        $thisWeekRevenue = Job::query()
            ->join('job_occurrences', 'jobs.id', '=', 'job_occurrences.job_id')
            ->whereBetween('job_occurrences.occurrence_at', [$weekStart, $weekEnd])
            ->where('job_occurrences.status', 'completed')
            ->sum('jobs.price_pln');

        $lastWeekRevenue = Job::query()
            ->join('job_occurrences', 'jobs.id', '=', 'job_occurrences.job_id')
            ->whereBetween('job_occurrences.occurrence_at', [$lastWeekStart, $lastWeekEnd])
            ->where('job_occurrences.status', 'completed')
            ->sum('jobs.price_pln');

        $thisWeekJobs = JobOccurrence::query()
            ->whereBetween('occurrence_at', [$weekStart, $weekEnd])
            ->where('status', 'completed')
            ->count();

        $nextWeekJobs = JobOccurrence::query()
            ->whereBetween('occurrence_at', [$weekEnd->copy()->addSecond(), $weekEnd->copy()->addWeek()])
            ->where('status', 'planned')
            ->count();

        $revenueDiff = $lastWeekRevenue > 0
            ? (int) round((($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100)
            : 0;

        $description = $revenueDiff >= 0
            ? "↑ {$revenueDiff}% " . __('dashboard.widgets.revenue.vs_last_week')
            : "↓ " . abs($revenueDiff) . "% " . __('dashboard.widgets.revenue.vs_last_week');

        return [
            Stat::make(__('dashboard.widgets.revenue.this_week'), 'PLN ' . number_format((float) $thisWeekRevenue, 2))
                ->description($description)
                ->color($revenueDiff >= 0 ? 'success' : 'danger'),
            Stat::make(__('dashboard.widgets.revenue.jobs_this_week'), (string) $thisWeekJobs),
            Stat::make(__('dashboard.widgets.revenue.jobs_next_week'), (string) $nextWeekJobs),
        ];
    }
}
```

- [ ] **Step 5: Create TodayJobsWidget**

```php
<?php
// app/Filament/Widgets/TodayJobsWidget.php
namespace App\Filament\Widgets;

use App\Modules\Scheduling\Models\JobOccurrence;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TodayJobsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('dashboard.widgets.today_jobs.title'))
            ->query(
                JobOccurrence::query()
                    ->with(['job.client'])
                    ->whereBetween('occurrence_at', [now()->startOfDay(), now()->endOfDay()])
                    ->whereIn('status', ['planned', 'completed'])
                    ->orderBy('occurrence_at')
            )
            ->columns([
                TextColumn::make('occurrence_at')
                    ->label(__('job.fields.starts_at'))
                    ->time('H:i'),
                TextColumn::make('job.client.name')
                    ->label(__('job.fields.client')),
                TextColumn::make('job.service_type_key')
                    ->label(__('job.fields.service_type'))
                    ->formatStateUsing(fn (string $state): string => __('presets.cleaning.services.' . $state)),
                TextColumn::make('status')
                    ->label(__('job.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        default => 'warning',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label(__('job.occurrences.complete'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (JobOccurrence $record): bool => $record->status === 'planned')
                    ->action(fn (JobOccurrence $record): bool => $record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ])),
                Tables\Actions\Action::make('skip')
                    ->label(__('job.occurrences.skip'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (JobOccurrence $record): bool => $record->status === 'planned')
                    ->action(fn (JobOccurrence $record): bool => $record->update(['status' => 'skipped'])),
            ])
            ->emptyStateHeading(__('dashboard.widgets.today_jobs.empty'))
            ->bulkActions([]);
    }
}
```

- [ ] **Step 6: Create UpcomingJobsWidget**

```php
<?php
// app/Filament/Widgets/UpcomingJobsWidget.php
namespace App\Filament\Widgets;

use App\Modules\Scheduling\Filament\Resources\JobResource;
use App\Modules\Scheduling\Models\JobOccurrence;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingJobsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('dashboard.widgets.upcoming.title'))
            ->query(
                JobOccurrence::query()
                    ->with(['job.client'])
                    ->whereBetween('occurrence_at', [now()->startOfDay()->addDay(), now()->addDays(7)->endOfDay()])
                    ->where('status', 'planned')
                    ->orderBy('occurrence_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('occurrence_at')
                    ->label(__('job.fields.starts_at'))
                    ->date('d.m.Y'),
                TextColumn::make('occurrence_at')
                    ->label('')
                    ->time('H:i'),
                TextColumn::make('job.client.name')
                    ->label(__('job.fields.client')),
                TextColumn::make('job.service_type_key')
                    ->label(__('job.fields.service_type'))
                    ->formatStateUsing(fn (string $state): string => __('presets.cleaning.services.' . $state)),
                TextColumn::make('job.duration_minutes')
                    ->label(__('job.fields.duration_minutes'))
                    ->suffix(' min'),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
```

- [ ] **Step 7: Create OverdueClientsWidget**

```php
<?php
// app/Filament/Widgets/OverdueClientsWidget.php
namespace App\Filament\Widgets;

use App\Modules\Crm\Models\Client;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class OverdueClientsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('dashboard.widgets.overdue.title'))
            ->query(
                Client::query()
                    ->whereHas('jobs.occurrences', function (Builder $q): void {
                        $q->where('status', 'completed');
                    })
                    ->whereDoesntHave('jobs.occurrences', function (Builder $q): void {
                        $q->where('status', 'completed')
                          ->where('occurrence_at', '>=', now()->subDays(42));
                    })
                    ->withMax('jobs.occurrences as last_completed_at', 'occurrence_at')
                    ->orderBy('last_completed_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('client.fields.name')),
                TextColumn::make('last_completed_at')
                    ->label(__('dashboard.widgets.overdue.last_job'))
                    ->date('d.m.Y')
                    ->placeholder('—'),
                TextColumn::make('last_completed_at')
                    ->label(__('dashboard.widgets.overdue.days_since'))
                    ->state(fn (Client $record): string => $record->last_completed_at
                        ? (string) now()->diffInDays($record->last_completed_at) . ' ' . __('dashboard.widgets.overdue.days_ago')
                        : '—'),
            ])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading(__('dashboard.widgets.overdue.empty'));
    }
}
```

- [ ] **Step 8: Replace Dashboard in AppPanelProvider**

In `app/Providers/Filament/AppPanelProvider.php`:
1. Add import: `use App\Filament\Pages\Dashboard;`
2. Remove: `use Filament\Pages;`
3. Change `->pages([Pages\Dashboard::class, TenantSettingsPage::class])` to `->pages([Dashboard::class, TenantSettingsPage::class])`

- [ ] **Step 9: Run dashboard tests**

```bash
docker compose exec app bin/test tests/Feature/Dashboard/DashboardTest.php
```

Expected: All 5 tests PASS

- [ ] **Step 10: Commit**

```bash
git add app/Filament/Pages/Dashboard.php \
        app/Filament/Widgets/ \
        app/Providers/Filament/AppPanelProvider.php \
        tests/Feature/Dashboard/DashboardTest.php
git commit -m "feat(s2): custom Dashboard page with 4 widgets"
```

---

## Task 9: Translation Keys + Final Wiring

**Files:**
- Modify: `lang/pl.json`
- Modify: `lang/en.json`

- [ ] **Step 1: Add Sprint 2 keys to lang/pl.json**

Merge the following into `lang/pl.json` (preserve all existing keys):

```json
{
    "job.nav_label": "Zlecenia",
    "job.model_label": "Zlecenie",
    "job.model_label_plural": "Zlecenia",
    "job.section.details": "Szczegóły",
    "job.section.schedule": "Harmonogram",
    "job.section.occurrences": "Wizyty",
    "job.fields.client": "Klient",
    "job.fields.service_type": "Typ usługi",
    "job.fields.starts_at": "Data i godzina",
    "job.fields.duration_minutes": "Czas trwania",
    "job.fields.price_pln": "Cena",
    "job.fields.recurrence_rule": "Powtarzalność",
    "job.fields.difficulty": "Trudność",
    "job.fields.internal_notes": "Notatki wewnętrzne",
    "job.fields.status": "Status",
    "job.status.planned": "Zaplanowane",
    "job.status.completed": "Wykonane",
    "job.status.cancelled": "Anulowane",
    "job.status.skipped": "Pominięte",
    "job.status.rescheduled": "Przełożone",
    "job.recurrence.once": "Jednorazowo",
    "job.recurrence.weekly": "Co tydzień",
    "job.recurrence.biweekly": "Co dwa tygodnie",
    "job.recurrence.monthly": "Co miesiąc",
    "job.occurrences.title": "Wizyty",
    "job.occurrences.complete": "Oznacz jako wykonane",
    "job.occurrences.skip": "Pomiń",
    "job.occurrences.reschedule": "Przesuń",
    "job.occurrences.completed_at": "Wykonano",

    "settings.nav_label": "Ustawienia",
    "settings.section.location": "Lokalizacja i dojazd",
    "settings.section.billing": "Fakturowanie",
    "settings.fields.address_line1": "Ulica i numer",
    "settings.fields.address_city": "Miasto",
    "settings.fields.fuel_rate": "Koszt paliwa",
    "settings.fields.is_vat_payer": "Płatnik VAT",
    "settings.fields.default_vat_rate": "Stawka VAT",
    "settings.fields.locale": "Język",
    "settings.saved": "Zapisano ustawienia",

    "dashboard.nav_label": "Pulpit",
    "dashboard.widgets.today_jobs.title": "Dzisiejsze wizyty",
    "dashboard.widgets.today_jobs.empty": "Brak wizyt na dziś",
    "dashboard.widgets.revenue.title": "Przychód",
    "dashboard.widgets.revenue.this_week": "Przychód w tym tygodniu",
    "dashboard.widgets.revenue.jobs_this_week": "Wizyty w tym tygodniu",
    "dashboard.widgets.revenue.jobs_next_week": "Wizyty w przyszłym tygodniu",
    "dashboard.widgets.revenue.vs_last_week": "vs. poprzedni tydzień",
    "dashboard.widgets.upcoming.title": "Nadchodzące wizyty",
    "dashboard.widgets.overdue.title": "Klienci wymagający kontaktu",
    "dashboard.widgets.overdue.empty": "Wszyscy klienci są aktywni",
    "dashboard.widgets.overdue.last_job": "Ostatnia wizyta",
    "dashboard.widgets.overdue.days_since": "Dni temu",
    "dashboard.widgets.overdue.days_ago": "dni temu",

    "presets.cleaning.services.basic": "Sprzątanie standardowe",
    "presets.cleaning.services.deep": "Sprzątanie gruntowne",
    "presets.cleaning.services.post_renovation": "Sprzątanie po remoncie",
    "presets.cleaning.services.windows": "Mycie okien",
    "presets.cleaning.services.upholstery": "Pranie tapicerki",
    "presets.cleaning.fields.difficulty": "Trudność",
    "presets.cleaning.difficulty.standard": "Standardowe",
    "presets.cleaning.difficulty.hard": "Trudne",
    "presets.cleaning.vocab.client_singular": "Klient",
    "presets.cleaning.vocab.client_plural": "Klienci",
    "presets.cleaning.vocab.job_singular": "Zlecenie",
    "presets.cleaning.vocab.job_plural": "Zlecenia",

    "commute.label": "Dojazd",
    "commute.not_geocoded": "Adres nie został jeszcze zlokalizowany",
    "commute.distance_km": "km",
    "commute.cost_pln": "PLN"
}
```

- [ ] **Step 2: Add Sprint 2 keys to lang/en.json**

Merge the following into `lang/en.json`:

```json
{
    "job.nav_label": "Jobs",
    "job.model_label": "Job",
    "job.model_label_plural": "Jobs",
    "job.section.details": "Details",
    "job.section.schedule": "Schedule",
    "job.section.occurrences": "Visits",
    "job.fields.client": "Client",
    "job.fields.service_type": "Service type",
    "job.fields.starts_at": "Date & time",
    "job.fields.duration_minutes": "Duration",
    "job.fields.price_pln": "Price",
    "job.fields.recurrence_rule": "Recurrence",
    "job.fields.difficulty": "Difficulty",
    "job.fields.internal_notes": "Internal notes",
    "job.fields.status": "Status",
    "job.status.planned": "Planned",
    "job.status.completed": "Completed",
    "job.status.cancelled": "Cancelled",
    "job.status.skipped": "Skipped",
    "job.status.rescheduled": "Rescheduled",
    "job.recurrence.once": "One-time",
    "job.recurrence.weekly": "Weekly",
    "job.recurrence.biweekly": "Every 2 weeks",
    "job.recurrence.monthly": "Monthly",
    "job.occurrences.title": "Visits",
    "job.occurrences.complete": "Mark as completed",
    "job.occurrences.skip": "Skip",
    "job.occurrences.reschedule": "Reschedule",
    "job.occurrences.completed_at": "Completed at",

    "settings.nav_label": "Settings",
    "settings.section.location": "Location & commute",
    "settings.section.billing": "Billing",
    "settings.fields.address_line1": "Street & number",
    "settings.fields.address_city": "City",
    "settings.fields.fuel_rate": "Fuel cost",
    "settings.fields.is_vat_payer": "VAT payer",
    "settings.fields.default_vat_rate": "VAT rate",
    "settings.fields.locale": "Language",
    "settings.saved": "Settings saved",

    "dashboard.nav_label": "Dashboard",
    "dashboard.widgets.today_jobs.title": "Today's jobs",
    "dashboard.widgets.today_jobs.empty": "No jobs scheduled for today",
    "dashboard.widgets.revenue.title": "Revenue",
    "dashboard.widgets.revenue.this_week": "This week's revenue",
    "dashboard.widgets.revenue.jobs_this_week": "Jobs this week",
    "dashboard.widgets.revenue.jobs_next_week": "Jobs next week",
    "dashboard.widgets.revenue.vs_last_week": "vs. last week",
    "dashboard.widgets.upcoming.title": "Upcoming jobs",
    "dashboard.widgets.overdue.title": "Clients needing attention",
    "dashboard.widgets.overdue.empty": "All clients are active",
    "dashboard.widgets.overdue.last_job": "Last job",
    "dashboard.widgets.overdue.days_since": "Days ago",
    "dashboard.widgets.overdue.days_ago": "days ago",

    "presets.cleaning.services.basic": "Standard cleaning",
    "presets.cleaning.services.deep": "Deep cleaning",
    "presets.cleaning.services.post_renovation": "Post-renovation cleaning",
    "presets.cleaning.services.windows": "Window cleaning",
    "presets.cleaning.services.upholstery": "Upholstery cleaning",
    "presets.cleaning.fields.difficulty": "Difficulty",
    "presets.cleaning.difficulty.standard": "Standard",
    "presets.cleaning.difficulty.hard": "Hard",
    "presets.cleaning.vocab.client_singular": "Client",
    "presets.cleaning.vocab.client_plural": "Clients",
    "presets.cleaning.vocab.job_singular": "Job",
    "presets.cleaning.vocab.job_plural": "Jobs",

    "commute.label": "Commute",
    "commute.not_geocoded": "Address not geocoded yet",
    "commute.distance_km": "km",
    "commute.cost_pln": "PLN"
}
```

- [ ] **Step 3: Run full test suite**

```bash
docker compose exec app bin/test
```

Expected: All tests PASS (existing 36 + new Sprint 2 tests)

- [ ] **Step 4: Run static analysis**

```bash
docker compose exec app bin/stan
```

Expected: 0 errors

- [ ] **Step 5: Run code style check**

```bash
docker compose exec app bin/pint --test
```

Expected: No violations. If violations exist, run `docker compose exec app bin/pint` to fix then re-check.

- [ ] **Step 6: Commit translation keys**

```bash
git add lang/pl.json lang/en.json
git commit -m "feat(s2): add Sprint 2 translation keys for jobs, settings, dashboard"
```

- [ ] **Step 7: Final green check commit**

```bash
git add -A
git status  # verify nothing unexpected
git commit -m "chore(s2): Sprint 2 complete — all tests green, stan clean, pint clean"
```

---

## Self-Review

**Spec coverage check:**
- [x] S2.1 Job model + JobResource — Task 1 + Task 2
- [x] S2.2 Occurrence generation + OccurrenceRelationManager — Task 3 + Task 4
- [x] S2.3 TenantSettingsPage — Task 7
- [x] S2.4 DistanceService + commute display — Task 6
- [x] S2.5 Dashboard + 4 widgets — Task 8
- [x] S2.6 Translation keys — Task 9
- [x] `price_pln` migration — Task 1
- [x] `BelongsToTenant` on Job model — Task 1
- [x] `TenantSettings.$timestamps = false` — Task 5
- [x] Occurrence generation wrapped in DB::transaction — Task 2 CreateJob
- [x] `DistanceService` null check for lat/lng — Task 6
- [x] Dashboard widgets handle empty state — Task 8 (emptyStateHeading)
- [x] No hardcoded Polish in PHP — all strings via `__('key')`
- [x] bin/test, bin/stan, bin/pint check — Task 9
