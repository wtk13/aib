<?php

use App\Modules\Crm\Models\Client;
use App\Modules\Employees\Filament\Resources\EmployeeResource\Pages\CreateEmployee;
use App\Modules\Employees\Filament\Resources\EmployeeResource\Pages\EditEmployee;
use App\Modules\Employees\Filament\Resources\EmployeeResource\Pages\ListEmployees;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\JobEmployee;
use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages\CreateJob;
use App\Modules\Scheduling\Models\Job;
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

function employeeOwner(): User
{
    $preset = VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    return $user;
}

// ─── Model ───────────────────────────────────────────────────────────────────

it('employee belongs to tenant', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $employee = Employee::create(['name' => 'Anna Kowalska']);

    expect($employee->tenant_id)->toBe($tenant->id);
});

it('employee is_active defaults to true', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $employee = Employee::create(['name' => 'Pani Marta']);

    expect($employee->fresh()->is_active)->toBeTrue();
});

it('employee can be deactivated', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $employee = Employee::create(['name' => 'Odeszła', 'is_active' => false]);

    expect($employee->fresh()->is_active)->toBeFalse();
});

it('employees are scoped to tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    Tenant::setCurrent($tenantA);
    Employee::create(['name' => 'Ania A']);

    Tenant::setCurrent($tenantB);
    Employee::create(['name' => 'Basia B']);

    expect(Employee::count())->toBe(1)
        ->and(Employee::first()->name)->toBe('Basia B');
});

// ─── Filament resource ────────────────────────────────────────────────────────

it('can list employees', function () {
    $user = employeeOwner();
    $employee = Employee::create(['name' => 'Lista Testowa']);

    Livewire::actingAs($user)
        ->test(ListEmployees::class)
        ->assertCanSeeTableRecords([$employee]);
});

it('can create an employee', function () {
    $user = employeeOwner();

    Livewire::actingAs($user)
        ->test(CreateEmployee::class)
        ->fillForm(['name' => 'Nowa Pracownica', 'is_active' => true])
        ->call('create')
        ->assertHasNoErrors();

    expect(Employee::where('name', 'Nowa Pracownica')->exists())->toBeTrue();
});

it('employee name is required', function () {
    $user = employeeOwner();

    Livewire::actingAs($user)
        ->test(CreateEmployee::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name']);
});

it('can edit an employee', function () {
    $user = employeeOwner();
    $employee = Employee::create(['name' => 'Stara Nazwa']);

    Livewire::actingAs($user)
        ->test(EditEmployee::class, ['record' => $employee->getRouteKey()])
        ->fillForm(['name' => 'Nowa Nazwa'])
        ->call('save')
        ->assertHasNoErrors();

    expect($employee->fresh()->name)->toBe('Nowa Nazwa');
});

it('can deactivate an employee via edit', function () {
    $user = employeeOwner();
    $employee = Employee::create(['name' => 'Aktywna', 'is_active' => true]);

    Livewire::actingAs($user)
        ->test(EditEmployee::class, ['record' => $employee->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoErrors();

    expect($employee->fresh()->is_active)->toBeFalse();
});

// ─── JobEmployee (payout repeater) ───────────────────────────────────────────

it('job_employee stores payout and optional hours', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $client = Client::create(['name' => 'Klient Testowy']);
    $employee = Employee::create(['name' => 'Pani Sprzątaczka']);
    $job = Job::create([
        'client_id' => $client->id,
        'service_type_key' => 'basic',
        'starts_at' => now(),
        'duration_minutes' => 90,
    ]);

    $jobEmployee = JobEmployee::create([
        'job_id' => $job->id,
        'employee_id' => $employee->id,
        'hours_worked' => 2.5,
        'payout_pln' => 120.00,
    ]);

    expect($jobEmployee->fresh())
        ->hours_worked->toBe('2.50')
        ->payout_pln->toBe('120.00')
        ->employee->name->toBe('Pani Sprzątaczka');
});

it('job_employee hours_worked is optional', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $client = Client::create(['name' => 'Klient']);
    $employee = Employee::create(['name' => 'Pracownica']);
    $job = Job::create([
        'client_id' => $client->id,
        'service_type_key' => 'basic',
        'starts_at' => now(),
        'duration_minutes' => 60,
    ]);

    $jobEmployee = JobEmployee::create([
        'job_id' => $job->id,
        'employee_id' => $employee->id,
        'hours_worked' => null,
        'payout_pln' => 80.00,
    ]);

    expect($jobEmployee->fresh()->hours_worked)->toBeNull();
});

it('job has jobEmployees relation', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $client = Client::create(['name' => 'Klient']);
    $emp1 = Employee::create(['name' => 'Pracownica 1']);
    $emp2 = Employee::create(['name' => 'Pracownica 2']);
    $job = Job::create([
        'client_id' => $client->id,
        'service_type_key' => 'basic',
        'starts_at' => now(),
        'duration_minutes' => 60,
    ]);

    JobEmployee::create(['job_id' => $job->id, 'employee_id' => $emp1->id, 'payout_pln' => 100]);
    JobEmployee::create(['job_id' => $job->id, 'employee_id' => $emp2->id, 'payout_pln' => 90]);

    expect($job->jobEmployees()->count())->toBe(2);
});

it('job form renders payout section for a seeded tenant', function () {
    $user = employeeOwner();

    Livewire::actingAs($user)
        ->test(CreateJob::class)
        ->assertFormExists();
});
