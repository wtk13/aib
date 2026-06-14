<?php

namespace App\Modules\Employees\Filament\Resources;

use App\Modules\Employees\Filament\Resources\EmployeeResource\Pages;
use App\Modules\Employees\Models\Employee;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('employee.nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('employee.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('employee.model_label_plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('employee.fields.name'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label(__('employee.fields.is_active'))
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('employee.fields.name'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('employee.fields.is_active'))
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->actions([ViewAction::make(), EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
            'view' => Pages\ViewEmployee::route('/{record}'),
        ];
    }
}
