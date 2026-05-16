<?php

namespace App\Modules\Scheduling\Filament\Resources\JobResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OccurrenceRelationManager extends RelationManager
{
    protected static string $relationship = 'occurrences';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('job.occurrences.title');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('occurrence_at')
            ->columns([])
            ->actions([])
            ->bulkActions([]);
    }
}
