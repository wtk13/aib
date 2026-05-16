<?php

namespace App\Filament\Widgets;

use App\Modules\Scheduling\Models\JobOccurrence;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingJobsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('dashboard.widgets.upcoming.title'))
            ->query(
                JobOccurrence::query()
                    ->with(['job.client'])
                    ->whereBetween('occurrence_at', [now()->startOfDay()->addDay(), now()->addDays(7)->endOfDay()])
                    ->where('status', 'planned')
                    ->orderBy('occurrence_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('occurrence_at')
                    ->label(__('job.fields.starts_at'))
                    ->dateTime('d.m.Y H:i'),
                TextColumn::make('job.client.name')
                    ->label(__('job.fields.client')),
                TextColumn::make('job.service_type_key')
                    ->label(__('job.fields.service_type'))
                    ->formatStateUsing(fn (string $state): string => __('presets.cleaning.services.'.$state)),
                TextColumn::make('job.duration_minutes')
                    ->label(__('job.fields.duration_minutes'))
                    ->suffix(' min'),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
