<?php

namespace App\Filament\Pages;

use App\Modules\Crm\Models\Address;
use App\Modules\Integrations\Geocoding\GeocodeAddressJob;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSettings;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * @property-read ComponentContainer $form
 */
class TenantSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.tenant-settings-page';

    protected static ?string $slug = 'settings';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('settings.nav_label');
    }

    public function mount(): void
    {
        $tenantId = Tenant::currentId() ?? abort(403);
        $settings = TenantSettings::find($tenantId)
            ?? new TenantSettings(['tenant_id' => $tenantId, 'locale' => 'pl']);

        $this->form->fill([
            'is_vat_payer' => $settings->is_vat_payer ?? false,
            'default_vat_rate' => $settings->default_vat_rate ?? 23,
            'locale' => $settings->locale ?? 'pl',
            'fuel_rate_pln_per_km' => (float) ($settings->fuel_rate_pln_per_km ?? 0),
            'addr_line1' => $settings->originAddress?->line1 ?? '',
            'addr_city' => $settings->originAddress?->city ?? '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('settings.section.location'))
                    ->schema([
                        TextInput::make('addr_line1')
                            ->label(__('settings.fields.address_line1'))
                            ->maxLength(255)
                            ->dehydrated(false),
                        TextInput::make('addr_city')
                            ->label(__('settings.fields.address_city'))
                            ->maxLength(100)
                            ->dehydrated(false),
                        TextInput::make('fuel_rate_pln_per_km')
                            ->label(__('settings.fields.fuel_rate'))
                            ->numeric()
                            ->suffix('PLN/km')
                            ->step(0.1)
                            ->minValue(0)
                            ->default(1.80),
                    ]),
                Section::make(__('settings.section.billing'))
                    ->schema([
                        Toggle::make('is_vat_payer')
                            ->label(__('settings.fields.is_vat_payer'))
                            ->live(),
                        TextInput::make('default_vat_rate')
                            ->label(__('settings.fields.default_vat_rate'))
                            ->numeric()
                            ->suffix('%')
                            ->visible(fn ($get) => $get('is_vat_payer')),
                        Select::make('locale')
                            ->label(__('settings.fields.locale'))
                            ->options(['pl' => 'Polski', 'en' => 'English'])
                            ->default('pl'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $raw = $this->form->getRawState();
        $tenantId = Tenant::currentId() ?? abort(403);

        $settings = TenantSettings::find($tenantId)
            ?? new TenantSettings(['tenant_id' => $tenantId]);

        $settings->is_vat_payer = $data['is_vat_payer'] ?? false;
        $settings->default_vat_rate = $data['default_vat_rate'] ?? 23;
        $settings->fuel_rate_pln_per_km = max(0, (float) ($data['fuel_rate_pln_per_km'] ?? 0));

        $allowedLocales = ['pl', 'en'];
        $settings->locale = in_array($data['locale'] ?? '', $allowedLocales, true) ? $data['locale'] : 'pl';

        $line1 = mb_substr(trim($raw['addr_line1'] ?? ''), 0, 255);
        $city = mb_substr(trim($raw['addr_city'] ?? ''), 0, 100);

        if (! empty($line1)) {
            if ($settings->originAddress) {
                $settings->originAddress->update(['line1' => $line1, 'city' => $city]);
                GeocodeAddressJob::dispatch($settings->origin_address_id);
            } else {
                $address = Address::create(['line1' => $line1, 'city' => $city]);
                $settings->origin_address_id = $address->id;
                GeocodeAddressJob::dispatch($address->id);
            }
        }

        $settings->save();

        Notification::make()
            ->title(__('settings.saved'))
            ->success()
            ->send();
    }
}
