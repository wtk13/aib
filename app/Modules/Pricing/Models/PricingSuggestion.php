<?php

namespace App\Modules\Pricing\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingSuggestion extends Model
{
    use BelongsToTenant, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'quote_id', 'suggested_total', 'breakdown', 'reasoning',
        'confidence', 'prompt_version', 'ai_usage_log_id',
    ];

    protected $casts = [
        'breakdown'       => 'array',
        'suggested_total' => 'decimal:2',
    ];
}
