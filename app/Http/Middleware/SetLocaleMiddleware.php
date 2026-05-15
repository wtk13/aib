<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'pl';

        if (auth()->check() && property_exists(auth()->user(), 'tenant_id')) {
            $setting = \DB::table('tenant_settings')
                ->where('tenant_id', auth()->user()->tenant_id)
                ->value('locale');
            $locale = $setting ?? 'pl';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
