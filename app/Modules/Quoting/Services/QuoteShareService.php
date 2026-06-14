<?php

namespace App\Modules\Quoting\Services;

use App\Modules\Quoting\Models\Quote;
use App\Modules\Quoting\Models\QuoteShareToken;
use Illuminate\Support\Str;

class QuoteShareService
{
    public function createLink(Quote $quote, int $validDays = 30): string
    {
        $token = QuoteShareToken::create([
            'quote_id' => $quote->id,
            'tenant_id' => $quote->tenant_id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays($validDays),
        ]);

        return route('quote.public', $token->token);
    }

    public function findValidToken(string $token): ?QuoteShareToken
    {
        return QuoteShareToken::withoutGlobalScopes()
            ->where('token', $token)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->whereNull('accepted_at')
            ->first();
    }
}
