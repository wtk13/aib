<?php

namespace App\Modules\Quoting\Services;

use Illuminate\Support\Facades\DB;

class QuoteNumberingService
{
    public function next(int $tenantId, \DateTimeInterface $issuedAt): string
    {
        return DB::transaction(function () use ($tenantId, $issuedAt) {
            $year = (int) $issuedAt->format('Y');
            $month = (int) $issuedAt->format('m');

            // Increment atomically using raw SQL
            DB::statement(
                'INSERT INTO tenant_quote_counters (tenant_id, year, month, seq) VALUES (?, ?, ?, 1)
                 ON CONFLICT (tenant_id, year, month) DO UPDATE SET seq = tenant_quote_counters.seq + 1',
                [$tenantId, $year, $month]
            );

            $seq = DB::table('tenant_quote_counters')
                ->where('tenant_id', $tenantId)
                ->where('year', $year)
                ->where('month', $month)
                ->value('seq');

            return sprintf('%04d/%02d/%03d', $year, $month, $seq);
        });
    }
}
