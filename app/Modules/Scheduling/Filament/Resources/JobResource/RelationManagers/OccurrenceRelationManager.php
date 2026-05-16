<?php

namespace App\Modules\Scheduling\Filament\Resources\JobResource\RelationManagers;

use App\Modules\Scheduling\Models\JobOccurrence;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            DateTimePicker::make('rescheduled_to')
                ->label(__('job.fields.starts_at'))
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('occurrence_at')
            ->columns([
                TextColumn::make('occurrence_at')
                    ->label(__('job.fields.starts_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('job.fields.status'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        'skipped', 'cancelled' => 'danger',
                        'rescheduled' => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('completed_at')
                    ->label(__('job.occurrences.completed_at'))
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—'),
            ])
            ->defaultSort('occurrence_at', 'asc')
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label(__('job.occurrences.complete'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (JobOccurrence $record): bool => $record->status === 'planned')
                    ->action(function (JobOccurrence $record): void {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('skip')
                    ->label(__('job.occurrences.skip'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (JobOccurrence $record): bool => $record->status === 'planned')
                    ->action(function (JobOccurrence $record): void {
                        $record->update(['status' => 'skipped']);
                    }),
                Tables\Actions\Action::make('reschedule')
                    ->label(__('job.occurrences.reschedule'))
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->visible(fn (JobOccurrence $record): bool => $record->status === 'planned')
                    ->form([
                        DateTimePicker::make('rescheduled_to')
                            ->label(__('job.fields.starts_at'))
                            ->required(),
                    ])
                    ->action(function (JobOccurrence $record, array $data): void {
                        $record->update([
                            'status' => 'rescheduled',
                            'rescheduled_to' => $data['rescheduled_to'],
                        ]);
                    }),
            ])
            ->bulkActions([]);
    }
}
