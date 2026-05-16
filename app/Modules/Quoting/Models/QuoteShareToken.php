<?php

namespace App\Modules\Quoting\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteShareToken extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['quote_id', 'token', 'expires_at', 'accepted_at', 'accepted_ip', 'accepted_user_agent'];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];
}
