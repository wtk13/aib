<?php

namespace App\Modules\Quoting\Http\Controllers;

use App\Modules\Quoting\Models\Quote;
use App\Modules\Quoting\Services\QuoteShareService;
use App\Modules\Quoting\Services\QuoteTransitionService;
use Illuminate\Http\Request;

class PublicQuoteController
{
    public function show(string $token, QuoteShareService $service)
    {
        $shareToken = $service->findValidToken($token);
        abort_if(! $shareToken, 404);

        $quote = Quote::withoutGlobalScopes()->with(['client', 'items'])->find($shareToken->quote_id);
        abort_if(! $quote, 404);

        $unitLabels = [
            'm2' => __('quote.unit.m2'),
            'h' => __('quote.unit.h'),
            'piece' => __('quote.unit.piece'),
            'flat' => __('quote.unit.flat'),
        ];

        return view('quoting.public-quote', compact('quote', 'shareToken', 'token', 'unitLabels'));
    }

    public function accept(string $token, Request $request, QuoteShareService $shareService, QuoteTransitionService $transitionService)
    {
        $shareToken = $shareService->findValidToken($token);
        abort_if(! $shareToken, 404);

        $quote = Quote::withoutGlobalScopes()->find($shareToken->quote_id);
        abort_if(! $quote || $quote->status !== 'sent', 404);

        $shareToken->update([
            'accepted_at' => now(),
            'accepted_ip' => $request->ip(),
            'accepted_user_agent' => $request->userAgent(),
        ]);

        $transitionService->transition($quote, 'accepted', ['via' => 'share_link', 'token' => $token]);

        return redirect()->route('quote.public', $token)->with('accepted', true);
    }
}
