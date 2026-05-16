<?php

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

it('today widget shows only todays planned and completed occurrences', function () {
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

    // Planned job this week (should not count toward revenue)
    $job2 = Job::factory()->for($client)->create(['price_pln' => '200.00']);
    JobOccurrence::factory()->for($job2)->create([
        'occurrence_at' => now()->startOfWeek()->addDays(2),
        'status' => 'planned',
    ]);

    Livewire::actingAs($user)
        ->test(WeekRevenueWidget::class)
        ->assertSee('300');
});

it('overdue widget shows clients with no completed job in 42+ days', function () {
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
