<?php

namespace App\Modules\Crm\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class NoteRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([]);
    }
}
