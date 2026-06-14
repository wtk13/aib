<?php

namespace App\Modules\Pricing\Services;

use App\Modules\Crm\Models\Client;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Scheduling\Models\Job;
use App\Modules\Tenancy\Models\Tenant;

class PricingContextBuilder
{
    public function build(Client $client, ?Job $job = null, ?string $serviceTypeKey = null): array
    {
        $tenant = Tenant::current();

        $serviceTypes = [];
        if ($tenant !== null) {
            try {
                $preset = $tenant->preset();
                $serviceTypes = $preset->serviceTypes();
            } catch (\RuntimeException) {
                $serviceTypes = [];
            }
        }

        $pastQuotes = Quote::where('client_id', $client->id)
            ->whereIn('status', ['accepted', 'sent'])
            ->orderByDesc('issued_at')
            ->limit(5)
            ->with('items')
            ->get();

        $coldStart = $pastQuotes->count() < 2;

        return [
            'client' => [
                'name' => $client->name,
                'custom_fields' => $client->custom_fields ?? [],
                'area_m2' => $client->custom_fields['area_m2'] ?? null,
                'property_type' => $client->custom_fields['property_type'] ?? null,
            ],
            'service_type_key' => $serviceTypeKey ?? ($job?->service_type_key),
            'service_types' => $serviceTypes,
            'past_quotes' => $pastQuotes->map(fn (Quote $q) => [
                'id' => $q->id,
                'status' => $q->status,
                'issued_at' => $q->issued_at?->toDateString(),
                'total' => $q->total,
                'items' => $q->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'rate' => $item->rate,
                    'line_total' => $item->line_total,
                ])->all(),
            ])->all(),
            'cold_start' => $coldStart,
        ];
    }
}
