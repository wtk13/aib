<?php

namespace App\Filament\Pages\Auth;

use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as FilamentRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Register extends FilamentRegister
{
    public function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Wizard\Step::make(__('auth.register.step_account'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('auth.register.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('auth.register.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class, 'email'),
                        TextInput::make('password')
                            ->label(__('auth.register.password'))
                            ->password()
                            ->required()
                            ->minLength(8),
                        TextInput::make('password_confirmation')
                            ->label(__('auth.register.password_confirmation'))
                            ->password()
                            ->required()
                            ->same('password')
                            ->dehydrated(false),
                    ]),
                Wizard\Step::make(__('auth.register.step_industry'))
                    ->schema([
                        Radio::make('preset_id')
                            ->label(__('auth.register.industry'))
                            ->options(fn () => VerticalPreset::where('is_active', true)->pluck('name', 'id')->toArray())
                            ->required(),
                    ]),
            ])
                ->submitAction(new HtmlString(Blade::render(
                    '<x-filament::button type="submit" size="sm">{{ __("auth.register.submit") }}</x-filament::button>'
                ))),
        ]);
    }

    protected function getFormActions(): array
    {
        return [];
    }

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
                'email' => $data['email'],
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

        return $user;
    }

    private function uniqueSlug(string $base): string
    {
        $prefix = $base ?: 'tenant';
        $slug = $prefix;
        $i = 1;
        while (Tenant::bypass(fn () => Tenant::where('slug', $slug)->exists())) {
            $slug = $prefix.'-'.$i++;
        }

        return $slug;
    }
}
