<?php

namespace App\Modules\Quoting\Filament\Resources\QuoteResource\Pages;

use App\Modules\Quoting\Filament\Resources\QuoteResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewQuote extends ViewRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make(__('quote.section.details'))->schema([
                TextEntry::make('number')
                    ->label(__('quote.fields.number'))
                    ->copyable(),
                TextEntry::make('client.name')
                    ->label(__('quote.fields.client')),
                TextEntry::make('status')
                    ->label(__('quote.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? __('quote.status.' . $state) : '')
                    ->color(fn (?string $state): string => match ($state) {
                        'accepted' => 'success',
                        'sent' => 'warning',
                        'rejected', 'expired' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('issued_at')
                    ->label(__('quote.fields.issued_at'))
                    ->date('d.m.Y'),
                TextEntry::make('valid_until')
                    ->label(__('quote.fields.valid_until'))
                    ->date('d.m.Y'),
                TextEntry::make('subtotal')
                    ->label(__('quote.fields.subtotal'))
                    ->money('PLN', locale: 'pl_PL'),
                TextEntry::make('total')
                    ->label(__('quote.fields.total'))
                    ->money('PLN', locale: 'pl_PL'),
                TextEntry::make('internal_note')
                    ->label(__('quote.fields.internal_note'))
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }
}
