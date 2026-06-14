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
        $appDomain = config('app.app_domain', 'app.aib.app');

        // Subdomain path: "ania.app.aib.app" => slug "ania"
        if (str_ends_with($host, '.'.$appDomain)) {
            $subdomain = substr($host, 0, strlen($host) - strlen('.'.$appDomain));
            $tenant = Tenant::bypass(fn () => Tenant::where('slug', $subdomain)->first());

            if (! $tenant) {
                abort(404, "Tenant '{$subdomain}' not found.");
            }

            Tenant::setCurrent($tenant);

            return $next($request);
        }

        // Fallback for requests on the root domain: resolve from authenticated user.
        // Wrapped in bypass() to avoid TenantScope chicken-and-egg when loading the User model.
        Tenant::bypass(function () {
            $user = auth()->user();
            if ($user && isset($user->tenant_id)) {
                $tenant = Tenant::find($user->tenant_id);
                if ($tenant) {
                    Tenant::setCurrent($tenant);
                }
            }
        });

        return $next($request);
    }
}
