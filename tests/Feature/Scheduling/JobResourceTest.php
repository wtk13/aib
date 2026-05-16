<?php

use App\Modules\Crm\Models\Client;
use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages\CreateJob;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages\EditJob;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages\ListJobs;
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

function jobOwner(): array
{
    $preset = VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
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
