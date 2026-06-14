<?php

namespace App\Modules\Quoting\Filament\Resources\QuoteResource\Pages;

use App\Modules\Quoting\Filament\Resources\QuoteResource;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSettings;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        /** @var Quote $record */
        $record = $this->getRecord();
        $this->recalculateTotals($record);
    }

    private function recalculateTotals(Quote $record): void
    {
        $tenantId = Tenant::currentId();
        $settings = TenantSettings::find($tenantId);
        $isVatPayer = $settings?->is_vat_payer ?? true;
        $vatRate = $settings?->default_vat_rate ?? 23;

        $subtotal = 0.0;

        $record->load('items');

        foreach ($record->items as $item) {
            $quantity = (float) $item->quantity;
            $rate = (float) $item->rate;
            $discountPct = (float) $item->discount_pct;

            $lineTotal = $quantity * $rate * (1 - $discountPct / 100);
            $lineTotal = round($lineTotal, 2);

            $item->line_total = $lineTotal;
            $item->save();

            $subtotal += $lineTotal;
        }

        $subtotal = round($subtotal, 2);
        $vatRateToStore = $isVatPayer ? $vatRate : 0;
        $total = $isVatPayer
            ? round($subtotal * (1 + $vatRate / 100), 2)
            : $subtotal;

        $record->update([
            'subtotal' => $subtotal,
            'vat_rate' => $vatRateToStore,
            'total' => $total,
        ]);
    }
}
