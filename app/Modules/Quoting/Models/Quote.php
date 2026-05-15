<?php

namespace App\Modules\Quoting\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'job_id', 'number', 'status',
        'issued_at', 'valid_until', 'subtotal', 'vat_rate', 'total',
        'internal_note',
    ];

    protected $casts = [
        'issued_at'   => 'date',
        'valid_until' => 'date',
        'subtotal'    => 'decimal:2',
        'total'       => 'decimal:2',
    ];
}
