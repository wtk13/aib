<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/quotes/{quote}/pdf', function (\App\Modules\Quoting\Models\Quote $quote) {
        abort_unless(auth()->user()->tenant_id === $quote->tenant_id, 403);

        return app(\App\Modules\Quoting\Services\QuotePdfService::class)->download($quote);
    })->name('quote.pdf');
});

Route::get('/wycena/{token}', [\App\Modules\Quoting\Http\Controllers\PublicQuoteController::class, 'show'])
    ->name('quote.public');

Route::post('/wycena/{token}/accept', [\App\Modules\Quoting\Http\Controllers\PublicQuoteController::class, 'accept'])
    ->name('quote.public.accept')
    ->middleware('throttle:10,1');
