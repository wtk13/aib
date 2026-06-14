<?php

namespace App\Modules\Quoting\Filament\Resources\QuoteResource\Pages;

use App\Modules\Quoting\Filament\Resources\QuoteResource;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Quoting\Services\QuoteNumberingService;
use App\Modules\Quoting\Services\QuoteTotalsService;
use App\Modules\Tenancy\Models\Tenant;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $issuedAt = Carbon::parse($data['issued_at'] ?? now());
        $tenantId = Tenant::currentId();

        $data['number']   = app(QuoteNumberingService::class)->next($tenantId, $issuedAt);
        $data['status']   = $data['status'] ?? 'draft';
        $data['subtotal'] = 0;
        $data['total']    = 0;
        $data['vat_rate'] = 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Quote $record */
        $record = $this->getRecord();
        app(QuoteTotalsService::class)->recalculate($record);
    }
}
