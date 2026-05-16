<?php

namespace App\Modules\Crm\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }

    protected $attributes = ['client_type' => 'person'];

    protected $fillable = [
        'client_type', 'name', 'phone', 'email',
        'nip', 'regon', 'address_id',
        'custom_fields', 'access_keys_encrypted',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'access_keys_encrypted' => 'encrypted',
    ];
}
