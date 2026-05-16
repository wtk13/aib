<?php

namespace App\Modules\Crm\Filament\Resources;

use App\Modules\Crm\Filament\Resources\ClientResource\Pages;
use App\Modules\Crm\Filament\Resources\ClientResource\RelationManagers;
use App\Modules\Crm\Models\Client;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationLabel(): string
    {
        return __('client.nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('client.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('client.model_label_plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('client.section.basic'))
                ->columns(2)
                ->schema([
                    Select::make('client_type')
                        ->label(__('client.fields.client_type'))
                        ->options([
                            'person'  => __('client.type.person'),
                            'company' => __('client.type.company'),
                        ])
                        ->default('person')
                        ->required()
                        ->live(),
                    TextInput::make('name')
                        ->label(__('client.fields.name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label(__('client.fields.phone'))
                        ->tel()
                        ->maxLength(30),
                    TextInput::make('email')
                        ->label(__('client.fields.email'))
                        ->email()
                        ->maxLength(255),
                ]),

            Section::make(__('client.section.company'))
                ->columns(2)
                ->visible(fn ($get) => $get('client_type') === 'company')
                ->schema([
                    TextInput::make('nip')
                        ->label(__('client.fields.nip'))
                        ->maxLength(10),
                    TextInput::make('regon')
                        ->label(__('client.fields.regon'))
                        ->maxLength(14),
                ]),

            Section::make(__('client.section.address'))
                ->columns(3)
                ->schema([
                    TextInput::make('addr_line1')
                        ->label(__('client.fields.address_line1'))
                        ->dehydrated(false)
                        ->maxLength(255),
                    TextInput::make('addr_postcode')
                        ->label(__('client.fields.address_postcode'))
                        ->dehydrated(false)
                        ->maxLength(10),
                    TextInput::make('addr_city')
                        ->label(__('client.fields.address_city'))
                        ->dehydrated(false)
                        ->maxLength(100),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('client.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('client.fields.phone'))
                    ->searchable(),
                TextColumn::make('client_type')
                    ->label(__('client.fields.client_type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => __('client.type.' . $state))
                    ->color(fn ($state) => $state === 'company' ? 'warning' : 'gray'),
                TextColumn::make('address.city')
                    ->label(__('client.fields.address_city'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('client.fields.created_at'))
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->searchable()
            ->filters([
                SelectFilter::make('client_type')
                    ->label(__('client.fields.client_type'))
                    ->options([
                        'person'  => __('client.type.person'),
                        'company' => __('client.type.company'),
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NoteRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view'   => Pages\ViewClient::route('/{record}'),
            'edit'   => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
