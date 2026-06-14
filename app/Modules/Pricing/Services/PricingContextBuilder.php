<?php

namespace App\Modules\Pricing\Services;

use App\Modules\Crm\Models\Client;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Scheduling\Models\Job;
use App\Modules\Tenancy\Models\Tenant;

class PricingContextBuilder
{
    public function __construct(
        private readonly Client $client,
        private readonly ?Job $job = null,
        private readonly ?string $serviceTypeKey = null,
    ) {}

    public function build(): array
    {
        $tenant = Tenant::current();

        $serviceTypes = [];
        if ($tenant !== null) {
            $preset = $tenant->preset();
            $serviceTypes = $preset->serviceTypes();
        }

        $pastQuotes = Quote::where('client_id', $this->client->id)
            ->whereIn('status', ['accepted', 'sent'])
            ->orderByDesc('issued_at')
            ->limit(5)
            ->with('items')
            ->get();

        $coldStart = $pastQuotes->count() < 2;

        return [
            'client' => [
                'name' => $this->client->name,
                'custom_fields' => $this->client->custom_fields ?? [],
                'area_m2' => $this->client->custom_fields['area_m2'] ?? null,
                'property_type' => $this->client->custom_fields['property_type'] ?? null,
            ],
            'service_type_key' => $this->serviceTypeKey ?? ($this->job?->service_type_key),
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
