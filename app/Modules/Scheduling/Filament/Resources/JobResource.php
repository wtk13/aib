<?php

namespace App\Modules\Scheduling\Filament\Resources;

use App\Modules\Employees\Models\Employee;
use App\Modules\Scheduling\Filament\Resources\JobResource\Pages;
use App\Modules\Scheduling\Filament\Resources\JobResource\RelationManagers;
use App\Modules\Scheduling\Models\Job;
use App\Modules\Tenancy\Models\Tenant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function getNavigationLabel(): string
    {
        return __('job.nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('job.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('job.model_label_plural');
    }

    public static function form(Form $form): Form
    {
        $preset = Tenant::current()?->preset();
        $serviceTypeOptions = [];
        if ($preset) {
            foreach ($preset->serviceTypes() as $st) {
                $serviceTypeOptions[$st['key']] = __($st['label_key']);
            }
        }

        $difficultyOptions = [];
        if ($preset) {
            foreach ($preset->jobFields() as $field) {
                if ($field['key'] === 'difficulty') {
                    foreach ($field['options'] as $opt) {
                        $value = is_array($opt) ? $opt['value'] : $opt;
                        $labelKey = is_array($opt) ? $opt['label_key'] : 'presets.cleaning.difficulty.'.$opt;
                        $difficultyOptions[$value] = __($labelKey);
                    }
                }
            }
        }

        return $form->schema([
            Section::make(__('job.section.details'))
                ->columns(2)
                ->schema([
                    Select::make('client_id')
                        ->label(__('job.fields.client'))
                        ->relationship('client', 'name')
                        ->searchable()
                        ->preload(false)
                        ->required(),
                    Select::make('service_type_key')
                        ->label(__('job.fields.service_type'))
                        ->options($serviceTypeOptions)
                        ->required(),
                    Select::make('recurrence_rule')
                        ->label(__('job.fields.recurrence_rule'))
                        ->options([
                            'weekly' => __('job.recurrence.weekly'),
                            'biweekly' => __('job.recurrence.biweekly'),
                            'monthly' => __('job.recurrence.monthly'),
                        ])
                        ->placeholder(__('job.recurrence.once'))
                        ->default(null)
                        ->nullable(),
                    TextInput::make('price_pln')
                        ->label(__('job.fields.price_pln'))
                        ->numeric()
                        ->prefix('PLN')
                        ->nullable(),
                ]),
            Section::make(__('job.section.schedule'))
                ->columns(2)
                ->schema([
                    DateTimePicker::make('starts_at')
                        ->label(__('job.fields.starts_at'))
                        ->required(),
                    TextInput::make('duration_minutes')
                        ->label(__('job.fields.duration_minutes'))
                        ->numeric()
                        ->suffix('min')
                        ->default(60)
                        ->required(),
                    Select::make('custom_fields->difficulty')
                        ->label(__('job.fields.difficulty'))
                        ->options($difficultyOptions)
                        ->nullable(),
                    Textarea::make('internal_notes')
                        ->label(__('job.fields.internal_notes'))
                        ->nullable()
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
            Section::make(__('job.section.payout'))
                ->collapsed()
                ->schema([
                    Repeater::make('jobEmployees')
                        ->relationship()
                        ->label('')
                        ->schema([
                            Select::make('employee_id')
                                ->label(__('job.payout.employee'))
                                ->options(fn () => Employee::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                                ->required()
                                ->columnSpan(2),
                            TextInput::make('hours_worked')
                                ->label(__('job.payout.hours_worked'))
                                ->numeric()
                                ->minValue(0)
                                ->step(0.5)
                                ->nullable(),
                            TextInput::make('payout_pln')
                                ->label(__('job.payout.payout_pln'))
                                ->numeric()
                                ->minValue(0)
                                ->prefix('PLN')
                                ->required(),
                        ])
                        ->columns([
                            'default' => 2,
                            'md' => 4,
                        ])
                        ->addActionLabel(__('job.payout.add'))
                        ->defaultItems(0)
                        ->reorderable(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label(__('job.fields.client'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_type_key')
                    ->label(__('job.fields.service_type'))
                    ->formatStateUsing(fn (string $state): string => __('presets.cleaning.services.'.$state)),
                TextColumn::make('starts_at')
                    ->label(__('job.fields.starts_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('job.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? __('job.status.'.$state) : '')
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        'cancelled', 'skipped' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('duration_minutes')
                    ->label(__('job.fields.duration_minutes'))
                    ->suffix(' min'),
                TextColumn::make('price_pln')
                    ->label(__('job.fields.price_pln'))
                    ->prefix('PLN ')
                    ->sortable(),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('job.fields.status'))
                    ->options([
                        'planned' => __('job.status.planned'),
                        'completed' => __('job.status.completed'),
                        'cancelled' => __('job.status.cancelled'),
                        'skipped' => __('job.status.skipped'),
                    ]),
                SelectFilter::make('recurrence_rule')
                    ->label(__('job.fields.recurrence_rule'))
                    ->options([
                        'weekly' => __('job.recurrence.weekly'),
                        'biweekly' => __('job.recurrence.biweekly'),
                        'monthly' => __('job.recurrence.monthly'),
                    ])
                    ->placeholder(__('job.recurrence.once')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OccurrenceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJob::route('/create'),
            'edit' => Pages\EditJob::route('/{record}/edit'),
            'view' => Pages\ViewJob::route('/{record}'),
        ];
    }
}
