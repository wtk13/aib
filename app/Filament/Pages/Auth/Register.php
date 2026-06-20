<?php

namespace App\Filament\Pages\Auth;

use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as FilamentRegister;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class Register extends FilamentRegister
{
    protected static string $view = 'filament.pages.auth.register';

    protected static string $layout = 'filament.components.layout.auth-simple';

    private const RESERVED_SLUGS = [
        'admin', 'www', 'api', 'mail', 'ftp', 'smtp', 'pop', 'imap',
        'static', 'assets', 'cdn', 'app', 'tba', 'panel', 'dashboard',
        'login', 'register', 'health', 'up', 'status', 'ns1', 'ns2',
    ];

    public function hasLogo(): bool
    {
        return false;
    }

    public function getHeading(): string|Htmlable
    {
        return __('auth.register.heading');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('auth.register.name'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('email')
                ->label(__('auth.register.email'))
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(User::class, 'email')
                ->columnSpanFull(),
            TextInput::make('password')
                ->label(__('auth.register.password'))
                ->password()
                ->required()
                ->rules([Password::min(8)->letters()->numbers()]),
            TextInput::make('password_confirmation')
                ->label(__('auth.register.password_confirmation'))
                ->password()
                ->required()
                ->same('password')
                ->dehydrated(false),
            ToggleButtons::make('preset_id')
                ->label(__('auth.register.industry'))
                ->options(fn () => VerticalPreset::where('is_active', true)->get()->mapWithKeys(fn ($p) => [$p->id => __('presets.'.$p->slug.'.name')])->toArray())
                ->required()
                ->grouped()
                ->columnSpanFull(),
        ])->columns(2);
    }

    protected function handleRegistration(array $data): Model
    {
        // Rate limit: 5 registrations per IP per hour
        $key = 'register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]));
            throw new \RuntimeException('Too many registration attempts.');
        }
        RateLimiter::hit($key, 3600);

        // Retry loop handles TOCTOU race on slug uniqueness
        $maxAttempts = 5;
        $result = null;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
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
                        'password' => Hash::make($data['password']),
                        'role' => 'owner',
                    ]));

                    return [$user, $tenant->id];
                });
                break;
            } catch (UniqueConstraintViolationException) {
                if ($attempt === $maxAttempts - 1) {
                    throw new \RuntimeException('Could not allocate a unique subdomain. Please try again.');
                }
            }
        }

        [$user, $tenantId] = $result;

        // Set tenant context after transaction commits (not inside, to avoid stale state on rollback)
        $tenant = Tenant::bypass(fn () => Tenant::find($tenantId));
        if ($tenant !== null) {
            Tenant::setCurrent($tenant);
        }

        return $user;
    }

    private function uniqueSlug(string $base): string
    {
        $prefix = $base ?: 'tenant';

        if (in_array($prefix, self::RESERVED_SLUGS, true)) {
            $prefix = 'firma-'.$prefix;
        }

        $slug = $prefix;
        $i = 1;
        while (Tenant::bypass(fn () => Tenant::where('slug', $slug)->exists())) {
            $slug = $prefix.'-'.$i++;
        }

        return $slug;
    }
}
