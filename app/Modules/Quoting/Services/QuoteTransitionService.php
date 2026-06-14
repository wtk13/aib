<?php

namespace App\Modules\Quoting\Services;

use App\Modules\Quoting\Models\Quote;
use App\Modules\Quoting\Models\QuoteStatusLog;
use Illuminate\Support\Facades\Auth;

class QuoteTransitionService
{
    public function transition(Quote $quote, string $toStatus, array $meta = []): void
    {
        $fromStatus = $quote->status;
        $now = now();

        $update = ['status' => $toStatus];
        match ($toStatus) {
            'sent' => $update['sent_at'] = $now,
            'accepted', 'rejected' => $update['decided_at'] = $now,
            'expired' => $update['expired_at'] = $now,
            default => null,
        };

        $quote->update($update);

        QuoteStatusLog::create([
            'quote_id' => $quote->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'transitioned_at' => $now,
            'transitioned_by_user_id' => Auth::id(),
            'meta' => $meta,
        ]);
    }
}
