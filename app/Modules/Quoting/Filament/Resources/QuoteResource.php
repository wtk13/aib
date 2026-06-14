<?php

namespace App\Modules\Quoting\Filament\Resources;

use App\Modules\Crm\Models\Client;
use App\Modules\Integrations\Distance\DistanceService;
use App\Modules\Quoting\Filament\Resources\QuoteResource\Pages;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSettings;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('quote.nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('quote.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('quote.model_label_plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('quote.section.details'))
                ->columns(2)
                ->schema([
                    Select::make('client_id')
                        ->label(__('quote.fields.client'))
                        ->relationship('client', 'name')
                        ->searchable()
                        ->preload(false)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (?int $state, Get $get, Set $set): void {
                            if (! $state) {
                                return;
                            }

                            $client = Client::with('address')->find($state);
                            $tenant = Tenant::current();

                            if (! $client || ! $tenant || ! $client->address) {
                                return;
                            }

                            $settings = TenantSettings::find($tenant->id);

                            if (! $settings?->origin_address_id) {
                                return;
                            }

                            $originAddress = $settings->originAddress;

                            if (! $originAddress) {
                                return;
                            }

                            $fuelRate = (float) ($settings->fuel_rate_pln_per_km ?? 0);
                            $distance = app(DistanceService::class)->getDistance(
                                $tenant->id,
                                $originAddress,
                                $client->address,
                                $fuelRate,
                            );

                            if (! $distance) {
                                return;
                            }

                            $items = collect($get('items') ?? [])
                                ->filter(fn (array $i) => ($i['source'] ?? 'manual') !== 'commute')
                                ->values()
                                ->all();

                            $items[] = [
                                'description'  => __('quote.line.commute'),
                                'unit'         => 'flat',
                                'quantity'     => 1,
                                'rate'         => round($distance->commuteCostPln, 2),
                                'discount_pct' => 0,
                                'vat_pct'      => 23,
                                'line_total'   => round($distance->commuteCostPln, 2),
                                'source'       => 'commute',
                            ];

                            $set('items', $items);
                        }),
                    Select::make('job_id')
                        ->label(__('quote.fields.job'))
                        ->relationship('job', 'starts_at')
                        ->nullable()
                        ->searchable()
                        ->preload(false),
                    DatePicker::make('issued_at')
                        ->label(__('quote.fields.issued_at'))
                        ->default(now())
                        ->required(),
                    DatePicker::make('valid_until')
                        ->label(__('quote.fields.valid_until'))
                        ->nullable(),
                    Textarea::make('internal_note')
                        ->label(__('quote.fields.internal_note'))
                        ->nullable()
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
            Section::make(__('quote.section.items'))
                ->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->label('')
                        ->schema([
                            TextInput::make('description')
                                ->label(__('quote.fields.description'))
                                ->required()
                                ->columnSpan(3),
                            Select::make('unit')
                                ->label(__('quote.fields.unit'))
                                ->options([
                                    'm2' => __('quote.unit.m2'),
                                    'h' => __('quote.unit.h'),
                                    'piece' => __('quote.unit.piece'),
                                    'flat' => __('quote.unit.flat'),
                                ])
                                ->default('piece')
                                ->required(),
                            TextInput::make('quantity')
                                ->label(__('quote.fields.quantity'))
                                ->numeric()
                                ->default(1)
                                ->required(),
                            TextInput::make('rate')
                                ->label(__('quote.fields.rate'))
                                ->numeric()
                                ->prefix('PLN')
                                ->required(),
                            TextInput::make('discount_pct')
                                ->label(__('quote.fields.discount_pct'))
                                ->numeric()
                                ->default(0)
                                ->suffix('%'),
                            TextInput::make('line_total')
                                ->label(__('quote.fields.line_total'))
                                ->numeric()
                                ->prefix('PLN')
                                ->disabled()
                                ->dehydrated(false),
                            Hidden::make('source')
                                ->default('manual')
                                ->dehydrateStateUsing(fn (?string $state): string => $state ?? 'manual'),
                        ])
                        ->columns(7)
                        ->addActionLabel('+ ' . __('quote.add_item'))
                        ->defaultItems(0)
                        ->reorderable(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('quote.fields.number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('client.name')
                    ->label(__('quote.fields.client'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('quote.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? __('quote.status.' . $state) : '')
                    ->color(fn (?string $state): string => match ($state) {
                        'accepted' => 'success',
                        'sent' => 'warning',
                        'rejected', 'expired' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('total')
                    ->label(__('quote.fields.total'))
                    ->money('PLN', locale: 'pl_PL')
                    ->sortable(),
                TextColumn::make('issued_at')
                    ->label(__('quote.fields.issued_at'))
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('issued_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
            'view' => Pages\ViewQuote::route('/{record}'),
        ];
    }
}
