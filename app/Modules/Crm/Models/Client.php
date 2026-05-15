<?php

namespace App\Modules\Crm\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = ['name', 'phone', 'email', 'nip', 'address_id', 'custom_fields'];

    protected $casts = [
        'custom_fields'         => 'array',
        'access_keys_encrypted' => 'encrypted',
    ];
}
