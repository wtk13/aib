<?php

namespace App\Modules\Tenancy\Concerns;

use App\Modules\Tenancy\Exceptions\TenantNotResolvedException;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = Tenant::currentId()
                    ?? throw new TenantNotResolvedException(
                        'Cannot create ' . static::class . ' without tenant context.'
                    );
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
