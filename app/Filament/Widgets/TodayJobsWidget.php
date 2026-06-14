<?php

namespace App\Filament\Widgets;

use App\Modules\Scheduling\Models\JobOccurrence;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayJobsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('dashboard.widgets.today_jobs.title'))
            ->query(
                JobOccurrence::query()
                    ->with(['job.client'])
                    ->whereBetween('occurrence_at', [now()->startOfDay(), now()->endOfDay()])
                    ->whereIn('status', ['planned', 'completed'])
                    ->orderBy('occurrence_at')
            )
            ->columns([
                TextColumn::make('occurrence_at')
                    ->label(__('job.fields.starts_at'))
                    ->time('H:i'),
                TextColumn::make('job.client.name')
                    ->label(__('job.fields.client')),
                TextColumn::make('job.service_type_key')
                    ->label(__('job.fields.service_type'))
                    ->formatStateUsing(fn (string $state): string => __('presets.cleaning.services.'.$state)),
                TextColumn::make('status')
                    ->label(__('job.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? __('job.status.'.$state) : '')
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        default => 'warning',
                    }),
            ])
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
            ])
            ->emptyStateHeading(__('dashboard.widgets.today_jobs.empty'))
            ->bulkActions([]);
    }
}
