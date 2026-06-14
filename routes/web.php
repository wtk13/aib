<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/regulamin', 'regulamin')->name('regulamin');
Route::view('/polityka-prywatnosci', 'polityka-prywatnosci')->name('polityka-prywatnosci');

Route::middleware(['auth'])->group(function () {
    Route::get('/audio/notes/{id}', function (int $id) {
        $note = \App\Modules\Notes\Models\Note::withoutGlobalScopes()->findOrFail($id);
        abort_unless(auth()->user()->tenant_id === $note->tenant_id, 403);
        abort_if(empty($note->audio_path), 404);

        return \Illuminate\Support\Facades\Storage::disk('local')->response($note->audio_path);
    })->name('note.audio');

    Route::get('/admin/quotes/{id}/pdf', function (int $id) {
        $quote = \App\Modules\Quoting\Models\Quote::withoutGlobalScopes()->findOrFail($id);
        abort_unless(auth()->user()->tenant_id === $quote->tenant_id, 403);

        return app(\App\Modules\Quoting\Services\QuotePdfService::class)->download($quote);
    })->name('quote.pdf');
});

Route::get('/wycena/{token}', [\App\Modules\Quoting\Http\Controllers\PublicQuoteController::class, 'show'])
    ->name('quote.public');

Route::post('/wycena/{token}/accept', [\App\Modules\Quoting\Http\Controllers\PublicQuoteController::class, 'accept'])
    ->name('quote.public.accept')
    ->middleware('throttle:10,1');
