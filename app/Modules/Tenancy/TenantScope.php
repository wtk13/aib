<?php

namespace App\Modules\Tenancy;

use App\Modules\Tenancy\Exceptions\TenantContextMissingException;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = Tenant::currentId();

        if ($tenantId !== null) {
            $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
            return;
        }

        if (app()->runningInConsole() || Tenant::isBypassed()) {
            return;
        }

        throw new TenantContextMissingException(
            'TenantScope: no tenant context bound. Call Tenant::setCurrent() or use Tenant::bypass().'
        );
    }
}
