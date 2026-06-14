<?php

namespace App\Modules\Scheduling\Models;

use App\Modules\Crm\Models\Client;
use App\Modules\Employees\Models\JobEmployee;
use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\JobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    /** @use HasFactory<JobFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected static function newFactory(): JobFactory
    {
        return JobFactory::new();
    }

    protected $fillable = [
        'client_id', 'service_type_key', 'custom_fields',
        'recurrence_rule', 'starts_at', 'duration_minutes',
        'assigned_to', 'status', 'price_pln', 'internal_notes',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'starts_at' => 'datetime',
        'price_pln' => 'decimal:2',
    ];

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasMany<JobOccurrence, $this> */
    public function occurrences(): HasMany
    {
        return $this->hasMany(JobOccurrence::class);
    }

    /** @return HasMany<JobEmployee, $this> */
    public function jobEmployees(): HasMany
    {
        return $this->hasMany(JobEmployee::class);
    }
}
