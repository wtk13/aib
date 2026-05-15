<?php

namespace App\Modules\Tenancy\Middleware;

use App\Modules\Tenancy\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $appDomain = config('app.app_domain', 'app.wyceny.app');

        // Extract subdomain: "ania.app.wyceny.app" => "ania"
        if (str_ends_with($host, '.' . $appDomain)) {
            $subdomain = substr($host, 0, strlen($host) - strlen('.' . $appDomain));
            $tenant = Tenant::bypass(fn () => Tenant::where('slug', $subdomain)->first());
            if ($tenant) {
                Tenant::setCurrent($tenant);
                return $next($request);
            }
        }

        // Fallback: resolve from authenticated user's tenant_id
        if (auth()->check() && property_exists(auth()->user(), 'tenant_id')) {
            $tenant = Tenant::bypass(fn () => Tenant::find(auth()->user()->tenant_id));
            if ($tenant) {
                Tenant::setCurrent($tenant);
            }
        }

        return $next($request);
    }
}
