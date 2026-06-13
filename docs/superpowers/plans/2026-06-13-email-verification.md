# Email Verification Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Block panel access until a registered user confirms their email; send the verification email automatically on registration.

**Architecture:** Filament's built-in `->emailVerification()` handles the prompt and resend pages. We add `MustVerifyEmail` to the `User` model, a migration for `email_verified_at`, and a `sendEmailVerificationNotification()` call after user creation in `Register::handleRegistration()`.

**Tech Stack:** Laravel 11, Filament 3, PestPHP

---

### Task 1: Migration — add `email_verified_at` to `users`

**Files:**
- Create: `database/migrations/2026_06_13_200000_add_email_verified_at_to_users_table.php`

- [ ] **Step 1: Create the migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });
    }
};
```

Save to `database/migrations/2026_06_13_200000_add_email_verified_at_to_users_table.php`.

- [ ] **Step 2: Commit**

```bash
git add database/migrations/2026_06_13_200000_add_email_verified_at_to_users_table.php
git commit -m "feat(auth): add email_verified_at column to users table"
```

---

### Task 2: User model — implement `MustVerifyEmail`, update `canAccessPanel()`

**Files:**
- Modify: `app/Modules/Tenancy/Models/User.php`

- [ ] **Step 1: Update the model**

Replace the contents of `app/Modules/Tenancy/Models/User.php`:

```php
<?php

namespace App\Modules\Tenancy\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasFactory;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected $fillable = ['tenant_id', 'name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'owner' && $this->hasVerifiedEmail();
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Modules/Tenancy/Models/User.php
git commit -m "feat(auth): implement MustVerifyEmail and gate canAccessPanel on verification"
```

---

### Task 3: UserFactory — add `verified()` state

**Files:**
- Modify: `database/factories/UserFactory.php`

- [ ] **Step 1: Add verified state**

```php
<?php

namespace Database\Factories;

use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => 'owner',
            'email_verified_at' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(['email_verified_at' => now()]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add database/factories/UserFactory.php
git commit -m "feat(auth): add verified() factory state for email verification"
```

---

### Task 4: Enable `emailVerification()` on the Filament panel

**Files:**
- Modify: `app/Providers/Filament/AppPanelProvider.php`

- [ ] **Step 1: Add `->emailVerification()` after `->registration()`**

In `app/Providers/Filament/AppPanelProvider.php`, locate the `->registration(Register::class)` line and add `->emailVerification()` directly after it:

```php
->login(Login::class)
->registration(Register::class)
->emailVerification()
```

- [ ] **Step 2: Commit**

```bash
git add app/Providers/Filament/AppPanelProvider.php
git commit -m "feat(auth): enable Filament email verification flow"
```

---

### Task 5: Send verification email after registration

**Files:**
- Modify: `app/Filament/Pages/Auth/Register.php`

- [ ] **Step 1: Call `sendEmailVerificationNotification()` after user is created**

In `handleRegistration()`, after `Tenant::setCurrent($tenant)`:

```php
protected function handleRegistration(array $data): Model
{
    /** @var array{User, int} $result */
    $result = DB::transaction(function () use ($data) {
        $slug = $this->uniqueSlug(Str::slug($data['name']));

        $tenant = Tenant::bypass(fn () => Tenant::create([
            'ulid' => (string) Str::ulid(),
            'slug' => $slug,
            'company_name' => $data['name'],
            'preset_id' => $data['preset_id'],
        ]));

        $user = Tenant::bypass(fn () => User::create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'email' => mb_strtolower($data['email']),
            'password' => $data['password'],
            'role' => 'owner',
        ]));

        return [$user, $tenant->id];
    });

    [$user, $tenantId] = $result;

    // Set tenant context after transaction commits (not inside, to avoid stale state on rollback)
    $tenant = Tenant::bypass(fn () => Tenant::find($tenantId));
    if ($tenant !== null) {
        Tenant::setCurrent($tenant);
    }

    $user->sendEmailVerificationNotification();

    return $user;
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Filament/Pages/Auth/Register.php
git commit -m "feat(auth): send email verification notification after registration"
```

---

### Task 6: Fix `.env.example` MAIL_FROM_ADDRESS

**Files:**
- Modify: `.env.example`

- [ ] **Step 1: Update MAIL_FROM_ADDRESS**

Change:
```
MAIL_FROM_ADDRESS="hello@example.com"
```
To:
```
MAIL_FROM_ADDRESS="noreply@tbasystem.pl"
```

- [ ] **Step 2: Commit**

```bash
git add .env.example
git commit -m "chore: fix MAIL_FROM_ADDRESS in .env.example"
```

---

### Task 7: Tests — email verification flow

**Files:**
- Modify: `tests/Feature/Auth/RegistrationTest.php`
- Create: `tests/Feature/Auth/EmailVerificationTest.php`

- [ ] **Step 1: Update RegistrationTest to assert verification email is sent and `email_verified_at` is null**

Add `use Illuminate\Support\Facades\Notification;` and `use Illuminate\Auth\Notifications\VerifyEmail;` imports. Update the "can register" test:

```php
<?php

use App\Filament\Pages\Auth\Register;
use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
    Notification::fake();

    $preset = VerticalPreset::where('slug', 'cleaning')->firstOrFail();

    Livewire::test(Register::class)
        ->set('data.name', 'Ania Cleaning')
        ->set('data.email', 'ania@example.com')
        ->set('data.password', 'password123')
        ->set('data.password_confirmation', 'password123')
        ->set('data.preset_id', $preset->id)
        ->call('register')
        ->assertHasNoErrors();

    $user = Tenant::bypass(fn () => User::where('email', 'ania@example.com')->first());
    expect($user)->not->toBeNull();
    expect($user->email_verified_at)->toBeNull();

    $tenant = Tenant::bypass(fn () => Tenant::find($user->tenant_id));
    expect($tenant)->not->toBeNull()
        ->and($tenant->preset_id)->toBe($preset->id)
        ->and($tenant->company_name)->toBe('Ania Cleaning');

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('registration fails with duplicate email', function () {
    $preset = VerticalPreset::where('slug', 'cleaning')->firstOrFail();
    $tenant = Tenant::factory()->create();
    Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create(['email' => 'taken@example.com']));

    Livewire::test(Register::class)
        ->set('data.name', 'Other')
        ->set('data.email', 'taken@example.com')
        ->set('data.password', 'password123')
        ->set('data.password_confirmation', 'password123')
        ->set('data.preset_id', $preset->id)
        ->call('register')
        ->assertHasErrors(['data.email']);
});
```

- [ ] **Step 2: Create `tests/Feature/Auth/EmailVerificationTest.php`**

```php
<?php

use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

it('unverified user is redirected to email verification prompt', function () {
    $user = Tenant::bypass(fn () => User::factory()->create(['email_verified_at' => null]));
    $tenant = Tenant::bypass(fn () => Tenant::find($user->tenant_id));
    Tenant::setCurrent($tenant);

    $this->actingAs($user)
        ->get('/admin')
        ->assertRedirect('/admin/email-verification/prompt');
});

it('verified user can access the panel', function () {
    $user = Tenant::bypass(fn () => User::factory()->verified()->create());
    $tenant = Tenant::bypass(fn () => Tenant::find($user->tenant_id));
    Tenant::setCurrent($tenant);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk();
});
```

- [ ] **Step 3: Run the tests**

```bash
docker compose exec app php artisan test tests/Feature/Auth/
```

Expected: all tests in `tests/Feature/Auth/` pass (3 in RegistrationTest + 2 in EmailVerificationTest).

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/Auth/RegistrationTest.php tests/Feature/Auth/EmailVerificationTest.php
git commit -m "test(auth): add email verification tests, update registration test"
```
