<?php

namespace App\Modules\Quoting\Services;

use App\Modules\Quoting\Models\Quote;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSettings;

class QuoteTotalsService
{
    public function recalculate(Quote $record): void
    {
        $tenantId = Tenant::currentId();
        $settings = TenantSettings::find($tenantId);
        $isVatPayer = $settings?->is_vat_payer ?? true;
        $vatRate = (int) ($settings?->default_vat_rate ?? 23);

        $subtotal = 0.0;

        $record->load('items');

        foreach ($record->items as $item) {
            $quantity = (float) $item->quantity;
            $rate = (float) $item->rate;
            $discountPct = (float) $item->discount_pct;

            $lineTotal = round($quantity * $rate * (1 - $discountPct / 100), 2);

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
