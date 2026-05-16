<?php

namespace App\Filament\Widgets;

use App\Modules\Crm\Models\Client;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class OverdueClientsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('dashboard.widgets.overdue.title'))
            ->query(
                Client::query()
                    ->whereHas('jobs', function (Builder $q): void {
                        $q->whereHas('occurrences', function (Builder $inner): void {
                            $inner->where('status', 'completed');
                        });
                    })
                    ->whereDoesntHave('jobs', function (Builder $q): void {
                        $q->whereHas('occurrences', function (Builder $inner): void {
                            $inner->where('status', 'completed')
                                ->where('occurrence_at', '>=', now()->subDays(42));
                        });
                    })
                    ->orderBy('id')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('client.fields.name')),
            ])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading(__('dashboard.widgets.overdue.empty'));
    }
}
