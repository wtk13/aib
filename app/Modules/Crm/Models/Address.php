<?php

namespace App\Modules\Crm\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['line1', 'line2', 'postcode', 'city', 'country', 'lat', 'lng', 'geocoded_at'];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];
}
