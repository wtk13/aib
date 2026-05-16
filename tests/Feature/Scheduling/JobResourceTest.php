<?php

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
