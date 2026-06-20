<?php

namespace App\Modules\Quoting\Filament\Resources\QuoteResource\Pages;

use App\Modules\Crm\Models\Client;
use App\Modules\Pricing\Models\PricingSuggestion;
use App\Modules\Pricing\Services\PricingSuggestionFeedbackRecorder;
use App\Modules\Pricing\Services\PricingSuggestionService;
use App\Modules\Quoting\Filament\Resources\QuoteResource;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Quoting\Services\QuoteNumberingService;
use App\Modules\Quoting\Services\QuoteTotalsService;
use App\Modules\Tenancy\Models\Tenant;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;

    public ?int $cachedSuggestionId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $issuedAt = Carbon::parse($data['issued_at'] ?? now());
        $tenantId = Tenant::currentId();

        $data['number'] = app(QuoteNumberingService::class)->next($tenantId, $issuedAt);
        $data['status'] = $data['status'] ?? 'draft';
        $data['subtotal'] = 0;
        $data['total'] = 0;
        $data['vat_rate'] = 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Quote $record */
        $record = $this->getRecord();
        app(QuoteTotalsService::class)->recalculate($record);

        $suggestion = $this->cachedSuggestionId
            ? PricingSuggestion::find($this->cachedSuggestionId)
            : null;

        if ($suggestion) {
            $suggestion->update(['quote_id' => $record->id]);
            $suggestion->refresh();
            app(PricingSuggestionFeedbackRecorder::class)->record($suggestion, (float) $record->total);
            $this->cachedSuggestionId = null;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('get_suggestion')
                ->label(__('pricing.actions.suggest'))
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->form([
                    Select::make('service_type_key')
                        ->label(__('pricing.fields.service_type'))
                        ->options(fn () => $this->getServiceTypeOptions())
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $clientId = $this->form->getRawState()['client_id'] ?? null;

                    if (! $clientId) {
                        Notification::make()
                            ->title(__('pricing.errors.select_client_first'))
                            ->warning()
                            ->send();

                        return;
                    }

                    $client = Client::find($clientId);

                    if (! $client) {
                        Notification::make()
                            ->title(__('pricing.errors.select_client_first'))
                            ->warning()
                            ->send();

                        return;
                    }

                    $this->cachedSuggestionId = null;

                    $suggestion = app(PricingSuggestionService::class)->suggest(
                        $client,
                        null,
                        $data['service_type_key'],
                    );

                    if ($suggestion === null) {
                        Notification::make()
                            ->title(__('pricing.errors.ai_unavailable'))
                            ->warning()
                            ->send();

                        return;
                    }

                    $this->cachedSuggestionId = $suggestion->id;

                    $items = collect($suggestion->breakdown)->map(fn (array $item) => [
                        'description' => $item['description'] ?? '',
                        'unit' => $item['unit'] ?? 'piece',
                        'quantity' => $item['quantity'] ?? 1,
                        'rate' => $item['rate'] ?? 0,
                        'discount_pct' => 0,
                        'vat_pct' => 23,
                        'line_total' => $item['line_total'] ?? (($item['quantity'] ?? 1) * ($item['rate'] ?? 0)),
                    ])->all();

                    $this->form->fill([
                        ...$this->form->getRawState(),
                        'items' => $items,
                    ]);

                    Notification::make()
                        ->title(__('pricing.suggestion_applied', ['total' => number_format((float) $suggestion->suggested_total, 2, ',', ' ').' PLN']))
                        ->success()
                        ->send();
                }),
        ];
    }

    private function getServiceTypeOptions(): array
    {
        $tenant = Tenant::current();

        if ($tenant === null) {
            return [];
        }

        try {
            $preset = $tenant->preset();
            $serviceTypes = $preset->serviceTypes();
        } catch (\RuntimeException) {
            return [];
        }

        $options = [];
        foreach ($serviceTypes as $type) {
            $key = $type['key'] ?? null;
            $labelKey = $type['label_key'] ?? null;

            if ($key === null) {
                continue;
            }

            $options[$key] = $labelKey ? __($labelKey) : $key;
        }

        return $options;
    }
}
