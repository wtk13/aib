<?php

use App\Modules\Notes\Models\Note;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
    Storage::fake('local');
});

it('streams audio for the owning tenant user', function () {
    $tenant = Tenant::factory()->create();
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    Storage::disk('local')->put('notes/audio/1/test.mp3', 'fake-audio-data');

    $note = Note::create([
        'client_id'          => \App\Modules\Crm\Models\Client::create(['name' => 'Test'])->id,
        'audio_path'         => 'notes/audio/1/test.mp3',
        'status'             => 'ready',
        'source'             => 'audio',
        'created_by_user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('note.audio', $note->id))
        ->assertOk();
});

it('returns 403 when a user from a different tenant tries to access audio', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = Tenant::bypass(fn () => User::factory()->for($tenantA, 'tenant')->create());
    $userB = Tenant::bypass(fn () => User::factory()->for($tenantB, 'tenant')->create());

    Tenant::setCurrent($tenantA);

    Storage::disk('local')->put('notes/audio/1/secret.mp3', 'private-audio');

    $note = Note::create([
        'client_id'  => \App\Modules\Crm\Models\Client::create(['name' => 'Tenant A Client'])->id,
        'audio_path' => 'notes/audio/1/secret.mp3',
        'status'     => 'ready',
        'source'     => 'audio',
    ]);

    $this->actingAs($userB)
        ->get(route('note.audio', $note->id))
        ->assertForbidden();
});

it('returns 404 when note has no audio', function () {
    $tenant = Tenant::factory()->create();
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    $note = Note::create([
        'client_id' => \App\Modules\Crm\Models\Client::create(['name' => 'Test'])->id,
        'status'    => 'ready',
        'source'    => 'text',
        'body'      => 'Just a text note',
    ]);

    $this->actingAs($user)
        ->get(route('note.audio', $note->id))
        ->assertNotFound();
});

it('returns 401 for unauthenticated request', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $note = Note::create([
        'client_id'  => \App\Modules\Crm\Models\Client::create(['name' => 'Test'])->id,
        'audio_path' => 'notes/audio/1/test.mp3',
        'status'     => 'ready',
        'source'     => 'audio',
    ]);

    $this->get(route('note.audio', $note->id))
        ->assertRedirect();
});
