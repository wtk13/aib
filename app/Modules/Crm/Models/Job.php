<?php

namespace App\Modules\Crm\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'service_type_key', 'custom_fields',
        'recurrence_rule', 'starts_at', 'duration_minutes',
        'assigned_to', 'status', 'internal_notes',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'starts_at'     => 'datetime',
    ];
}
