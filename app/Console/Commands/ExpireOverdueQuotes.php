<?php

namespace App\Console\Commands;

use App\Modules\Quoting\Models\Quote;
use App\Modules\Quoting\Services\QuoteTransitionService;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Console\Command;

class ExpireOverdueQuotes extends Command
{
    protected $signature = 'quotes:expire-overdue';

    protected $description = 'Expire sent quotes past their valid_until date';

    public function handle(QuoteTransitionService $service): void
    {
        Tenant::bypass(function () use ($service) {
            Quote::withoutGlobalScopes()
                ->where('status', 'sent')
                ->where('valid_until', '<', now()->startOfDay())
                ->each(function (Quote $quote) use ($service) {
                    $service->transition($quote, 'expired', ['reason' => 'cron']);
                });
        });

        $this->info('Done');
    }
}
