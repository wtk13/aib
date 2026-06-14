<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/regulamin', 'regulamin')->name('regulamin');
Route::view('/polityka-prywatnosci', 'polityka-prywatnosci')->name('polityka-prywatnosci');

// Industry landing pages
Route::view('/dla-firm-sprzatajacych', 'landing.dla-firm-sprzatajacych')->name('landing.sprzatanie');

// Blog
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

Route::redirect('/zacznij', '/admin/register', 301)->name('register.start');

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => url('/'),                           'priority' => '1.0', 'changefreq' => 'monthly', 'lastmod' => '2026-06-14'],
        ['loc' => url('/dla-firm-sprzatajacych'),     'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => '2026-06-14'],
        ['loc' => url('/blog'),                       'priority' => '0.7', 'changefreq' => 'weekly',  'lastmod' => '2026-06-14'],
        ['loc' => url('/blog/jak-wyceniac-sprzatanie-mieszkania'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => '2026-06-14'],
    ];

    return response()->view('sitemap', compact('urls'))
        ->header('Content-Type', 'application/xml; charset=utf-8');
})->name('sitemap');

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
