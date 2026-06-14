<?php

namespace App\Modules\Quoting\Filament\Resources\QuoteResource\Pages;

use App\Modules\Quoting\Filament\Resources\QuoteResource;
use App\Modules\Quoting\Services\QuoteTransitionService;
use Filament\Actions\Action;
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
        $quote = $this->getRecord();
        $actions = [EditAction::make()];

        if ($quote->status === 'draft') {
            $actions[] = Action::make('send')
                ->label(__('quote.actions.send'))
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->action(fn () => app(QuoteTransitionService::class)->transition($quote, 'sent'));
        }

        if ($quote->status === 'sent') {
            $actions[] = Action::make('accept')
                ->label(__('quote.actions.accept'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => app(QuoteTransitionService::class)->transition($quote, 'accepted'));

            $actions[] = Action::make('reject')
                ->label(__('quote.actions.reject'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn () => app(QuoteTransitionService::class)->transition($quote, 'rejected'));
        }

        return $actions;
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
