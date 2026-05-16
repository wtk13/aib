<?php

use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Crm\Filament\Resources\ClientResource\Pages\CreateClient;
use App\Modules\Crm\Filament\Resources\ClientResource\Pages\EditClient;
use App\Modules\Crm\Filament\Resources\ClientResource\Pages\ListClients;
use App\Modules\Crm\Filament\Resources\ClientResource\RelationManagers\NoteRelationManager;
use App\Modules\Crm\Models\Client;
use App\Modules\Notes\Models\Note;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

// Helper — creates seeded tenant + user, sets context
function actingAsOwner(): User
{
    $preset = \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user   = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    return $user;
}

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

it('can list clients', function () {
    $user   = actingAsOwner();
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
            'client_type'   => 'person',
            'name'          => 'Jan Testowy',
            'phone'         => '600100200',
            'email'         => 'jan@test.pl',
            'addr_line1'    => 'ul. Przykładowa 1',
            'addr_postcode' => '00-001',
            'addr_city'     => 'Warszawa',
        ])
        ->call('create')
        ->assertHasNoErrors();

    $client = Client::where('name', 'Jan Testowy')->first();
    expect($client)->not->toBeNull()
        ->and($client->phone)->toBe('600100200')
        ->and($client->address->line1)->toBe('ul. Przykładowa 1');
});

it('can edit a client', function () {
    $user   = actingAsOwner();
    $client = Client::create(['name' => 'Stara Nazwa', 'client_type' => 'person']);

    Livewire::actingAs($user)
        ->test(EditClient::class, ['record' => $client->getRouteKey()])
        ->fillForm(['name' => 'Nowa Nazwa'])
        ->call('save')
        ->assertHasNoErrors();

    expect($client->fresh()->name)->toBe('Nowa Nazwa');
});

it('saves cleaning custom fields and encrypted access_keys', function () {
    $user = actingAsOwner();

    Livewire::actingAs($user)
        ->test(CreateClient::class)
        ->fillForm([
            'client_type'           => 'person',
            'name'                  => 'Pani Cleaning',
            'custom_fields'         => [
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

it('can add a note from the relation manager', function () {
    $user   = actingAsOwner();
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
    $user   = actingAsOwner();
    $client = Client::create(['name' => 'Test Klient 2', 'client_type' => 'person']);
    $note   = Note::create([
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
