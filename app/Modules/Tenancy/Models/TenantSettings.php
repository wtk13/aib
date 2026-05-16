<?php

namespace App\Modules\Tenancy\Models;

use App\Modules\Crm\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSettings extends Model
{
    protected $table = 'tenant_settings';

    protected $primaryKey = 'tenant_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'origin_address_id',
        'fuel_rate_pln_per_km',
        'is_vat_payer',
        'default_vat_rate',
        'locale',
    ];

    protected $casts = [
        'fuel_rate_pln_per_km' => 'decimal:2',
        'is_vat_payer'         => 'boolean',
    ];

    /** @return BelongsTo<Address, $this> */
    public function originAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'origin_address_id');
    }
}
