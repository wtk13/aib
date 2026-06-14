<?php

namespace App\Modules\Quoting\Models;

use App\Modules\Crm\Models\Client;
use App\Modules\Scheduling\Models\Job;
use App\Modules\Tenancy\Concerns\BelongsToTenant;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'job_id', 'number', 'status',
        'issued_at', 'valid_until', 'subtotal', 'vat_rate', 'total',
        'internal_note', 'sent_at', 'decided_at', 'expired_at',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'sent_at' => 'datetime',
        'decided_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('position');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(QuoteStatusLog::class);
    }

    public function shareTokens(): HasMany
    {
        return $this->hasMany(QuoteShareToken::class);
    }

    public function scopeForStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }
}
