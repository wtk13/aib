<?php

namespace App\Modules\Tenancy\Auth;

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TenantAwareUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        return Tenant::bypass(fn () => parent::retrieveById($identifier));
    }

    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?Authenticatable
    {
        return Tenant::bypass(fn () => parent::retrieveByCredentials($credentials));
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return Tenant::bypass(fn () => parent::retrieveByToken($identifier, $token));
    }
}
