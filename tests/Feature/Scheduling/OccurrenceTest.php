<?php

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
            'recurrence_rule' => null,
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
    expect((int) $occurrences->first()->occurrence_at->diffInDays($occurrences->get(1)->occurrence_at))->toBe(7);
});
