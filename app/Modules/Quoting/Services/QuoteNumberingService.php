<?php

namespace App\Modules\Quoting\Services;

use Illuminate\Support\Facades\DB;

class QuoteNumberingService
{
    public function next(int $tenantId, \DateTimeInterface $issuedAt): string
    {
        $year = (int) $issuedAt->format('Y');
        $month = (int) $issuedAt->format('m');

        $row = DB::selectOne(
            'INSERT INTO tenant_quote_counters (tenant_id, year, month, seq)
             VALUES (?, ?, ?, 1)
             ON CONFLICT (tenant_id, year, month)
             DO UPDATE SET seq = tenant_quote_counters.seq + 1
             RETURNING seq',
            [$tenantId, $year, $month]
        );

        return sprintf('%04d/%02d/%03d', $year, $month, $row->seq);
    }
}
