<?php

namespace App\Modules\Pricing\Services;

use App\Modules\Pricing\Models\PricingSuggestion;
use App\Modules\Pricing\Models\PricingSuggestionFeedback;
use App\Modules\Tenancy\Models\Tenant;

class PricingSuggestionFeedbackRecorder
{
    public function record(PricingSuggestion $suggestion, float $finalTotal): void
    {
        $suggestedTotal = (float) $suggestion->suggested_total;

        if ($suggestedTotal > 0) {
            $diffPct = abs($finalTotal - $suggestedTotal) / $suggestedTotal * 100;
        } else {
            $diffPct = 0.0;
        }

        if ($diffPct < 15) {
            $decision = 'accepted';
        } elseif ($diffPct < 50) {
            $decision = 'adjusted';
        } else {
            $decision = 'manual';
        }

        PricingSuggestionFeedback::create([
            'suggestion_id' => $suggestion->id,
            'tenant_id'     => Tenant::currentId(),
            'decision'      => $decision,
            'final_total'   => $finalTotal,
            'diff_pct'      => round($diffPct, 2),
        ]);
    }
}
